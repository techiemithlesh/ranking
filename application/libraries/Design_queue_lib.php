<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Background worker for "Download Design": renders queued branch × template items,
 * packages finished jobs into a ZIP and removes expired ones.
 * Run by: php index.php design_worker run   (cron, every minute)
 */
class Design_queue_lib
{
    protected $CI;
    protected $cfg = [];
    protected $slot;               // lock file handle for this worker's slot
    protected $templates = [];     // template id => row (cached per run)
    protected $overlays  = [];     // template id => image overlays
    protected $branches  = [];     // branch id  => row

    public function __construct()
    {
        $this->CI = &get_instance();
        $this->CI->config->load('design_queue', true);
        $this->cfg = $this->CI->config->item('design_queue');
        $this->CI->load->model('design_queue_model', 'queue');
    }

    public function storageDir()
    {
        return rtrim(str_replace('\\', '/', $this->cfg['design_queue_storage']), '/') . '/';
    }

    // seconds since a worker last ran (null if never)
    public function workerLastSeen()
    {
        $file = $this->storageDir() . '.heartbeat';
        return is_file($file) ? time() - filemtime($file) : null;
    }

    /* ── one cron run ───────────────────────────────────────── */

    public function run()
    {
        $dir = $this->storageDir();
        if (!is_dir($dir) && !mkdir($dir, 0775, true)) {
            $this->log('cannot create storage dir ' . $dir);
            return;
        }
        if (!$this->acquireSlot()) {
            $this->log('all worker slots busy, exiting');
            return;
        }

        $started  = time();
        $budget   = (int) $this->cfg['design_queue_time_budget'];
        $maxTries = (int) $this->cfg['design_queue_max_attempts'];
        $workerId = bin2hex(random_bytes(8));
        $rendered = 0;

        $this->heartbeat();
        $this->CI->queue->resetStale((int) $this->cfg['design_queue_stale_minutes'], $maxTries);

        while (time() - $started < $budget) {
            $items = $this->CI->queue->claimItems($workerId, (int) $this->cfg['design_queue_image_batch']);
            if (!$items) {
                // nothing pending (or lost a race) — check once more, then stop
                $items = $this->CI->queue->claimItems($workerId, (int) $this->cfg['design_queue_image_batch']);
                if (!$items) break;
            }
            foreach ($items as $item) {
                $this->processItem($item, $maxTries);
                $rendered++;
                $this->heartbeat();
            }
        }

        foreach ($this->CI->queue->jobsReadyToPackage() as $jobId) {
            if ($this->CI->queue->claimPackaging($jobId)) {
                $this->packageJob((int) $jobId);
            }
        }

        $this->cleanup();
        $this->log('run finished: ' . $rendered . ' item(s) in ' . (time() - $started) . 's');
    }

    /* ── rendering ──────────────────────────────────────────── */

    protected function processItem(array $item, $maxTries)
    {
        try {
            $template = $this->template($item['template_id']);
            $branch   = $this->branch($item['branch_id']);
            if (!$template || !$branch) {
                throw new RuntimeException('Template or branch no longer exists');
            }

            $relative = $this->entryName($branch, $template) . ($template['type'] === 'video' ? '.mp4' : '.png');
            $target   = $this->storageDir() . 'job_' . (int) $item['job_id'] . '/' . $relative;
            if (!is_dir(dirname($target))) {
                @mkdir(dirname($target), 0775, true);
            }

            if ($template['type'] === 'video') {
                $video = $this->CI->videorender_lib->render($template, $branch['id']);
                if (!$video || !rename($video, $target)) {
                    throw new RuntimeException('Video render failed');
                }
            } else {
                $png = $this->CI->templateengine_lib->renderImageBlob(
                    $template['file_path'],
                    get_branch_logo_file($branch['id']),
                    $this->overlays[$template['id']],
                    [
                        'branch_name'    => $branch['name']     ?? '',
                        'branch_address' => $branch['address']  ?? '',
                        'branch_contact' => $branch['mobileno'] ?? '',
                    ]
                );
                if (empty($png) || file_put_contents($target, $png) === false) {
                    throw new RuntimeException('Image render failed');
                }
            }

            $this->CI->queue->markDone($item['id'], $relative);
        } catch (Throwable $e) {
            $this->log('item ' . $item['id'] . ' failed: ' . $e->getMessage());
            $this->CI->queue->markFailed($item, $e->getMessage(), $maxTries);
        }
    }

    protected function template($id)
    {
        if (!array_key_exists($id, $this->templates)) {
            $this->CI->load->model('template_model');
            $tpl = $this->CI->template_model->getById($id) ?: null;
            $this->templates[$id] = $tpl;

            if ($tpl && $tpl['type'] === 'image') {
                $this->CI->load->model('templateOverlay_model');
                $this->CI->load->library('templateengine_lib');
                $this->overlays[$id] = $this->CI->templateOverlay_model->get_by_template($id);
            } elseif ($tpl) {
                $this->CI->load->library('videorender_lib');
            }
        }
        return $this->templates[$id];
    }

    protected function branch($id)
    {
        if (!array_key_exists($id, $this->branches)) {
            $this->branches[$id] = $this->CI->db->select('id, name, address, mobileno')
                ->where('id', (int) $id)->get('branch')->row_array() ?: null;
        }
        return $this->branches[$id];
    }

    protected function entryName(array $branch, array $template)
    {
        return $this->safeName($branch['name']) . '_' . $branch['id'] . '/'
             . $this->safeName($template['title']) . '_' . $template['id'];
    }

    protected function safeName($s)
    {
        $s = trim(preg_replace('/[^a-z0-9_\- ]/i', '_', (string) $s));
        return $s !== '' ? $s : 'untitled';
    }

    /* ── packaging ──────────────────────────────────────────── */

    protected function packageJob($jobId)
    {
        $jobDir  = $this->storageDir() . 'job_' . $jobId . '/';
        $zipPath = $this->storageDir() . 'designs_' . $jobId . '.zip';
        $done    = $this->CI->queue->jobItems($jobId, 'done');
        $failed  = $this->CI->queue->jobItems($jobId, 'failed');

        if (!$done) {
            $job = $this->CI->queue->getJob($jobId);
            $this->CI->queue->finishJob($jobId, [
                'status' => 'failed',
                'error'  => !empty($job['cancel_requested']) ? 'Cancelled before any design was finished' : 'No designs could be generated',
            ]);
            $this->removeDir($jobDir);
            return;
        }

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            $this->CI->queue->finishJob($jobId, ['status' => 'failed', 'error' => 'Could not create ZIP file']);
            return;
        }

        $count = 0;
        foreach ($done as $item) {
            $file = $jobDir . $item['file_path'];
            if (!is_file($file)) continue;
            $zip->addFile($file, $item['file_path']);
            // PNG/MP4 are already compressed — storing is much faster and barely larger
            $zip->setCompressionName($item['file_path'], ZipArchive::CM_STORE);
            $count++;
        }
        if ($failed) {
            $lines = array_map(fn($f) => ($f['branch_name'] ?: 'Branch #' . $f['branch_id']) . ' — '
                . ($f['template_title'] ?: 'Template #' . $f['template_id']) . ($f['error'] ? ' (' . $f['error'] . ')' : ''), $failed);
            $zip->addFromString('_failed.txt', "These designs could not be generated:\r\n" . implode("\r\n", $lines));
        }
        $zip->close();
        $this->removeDir($jobDir);

        if ($count === 0 || !is_file($zipPath)) {
            @unlink($zipPath);
            $this->CI->queue->finishJob($jobId, ['status' => 'failed', 'error' => 'Generated files were missing']);
            return;
        }

        $this->CI->queue->finishJob($jobId, [
            'status'    => 'done',
            'zip_path'  => basename($zipPath),
            'zip_size'  => filesize($zipPath),
            'zip_count' => $count,
        ]);
        $this->log('job ' . $jobId . ' packaged: ' . $count . ' file(s)');
    }

    /* ── housekeeping ───────────────────────────────────────── */

    protected function cleanup()
    {
        foreach ($this->CI->queue->expiredJobs((int) $this->cfg['design_queue_retention_days']) as $job) {
            if (!empty($job['zip_path'])) {
                @unlink($this->storageDir() . basename($job['zip_path']));
            }
            $this->removeDir($this->storageDir() . 'job_' . (int) $job['id']);
            $this->CI->queue->expireJob($job['id']);
        }
    }

    protected function acquireSlot()
    {
        $max = max(1, (int) $this->cfg['design_queue_max_workers']);
        for ($i = 1; $i <= $max; $i++) {
            $fh = fopen($this->storageDir() . '.worker_' . $i . '.lock', 'c');
            if ($fh && flock($fh, LOCK_EX | LOCK_NB)) {
                $this->slot = $fh; // released automatically when the process exits
                return true;
            }
            if ($fh) fclose($fh);
        }
        return false;
    }

    protected function heartbeat()
    {
        @touch($this->storageDir() . '.heartbeat');
    }

    protected function removeDir($dir)
    {
        $dir = rtrim($dir, '/');
        if (!is_dir($dir)) return;
        $items = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($items as $item) {
            $item->isDir() ? @rmdir($item->getPathname()) : @unlink($item->getPathname());
        }
        @rmdir($dir);
    }

    protected function log($msg)
    {
        log_message('info', 'DESIGN QUEUE: ' . $msg);
        if (is_cli()) {
            echo date('H:i:s') . ' ' . $msg . PHP_EOL;
        }
    }
}
