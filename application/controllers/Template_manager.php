<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @package : Eduproject Global PVT LTD
 * @version : 2.0
 * @developed by : Schoolexcel
 * @support : Mithlesh Patel
 * @author url : Mithlesh Patel
 * @filename : Template_manager.php
 * @copyright : Eduproject Global PVT LTD
 */

class Template_manager extends Admin_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('template_model');
        $this->load->model('templateOverlay_model');
        $this->load->library('templateengine_lib');

        if (!is_superadmin_loggedin() && !is_admin_loggedin()) {
            redirect(base_url('dashboard'), 'refresh');
            set_alert('info', "You are not authorized to access it !");
        }
    }


    public function index()
    {
        $templateType = $this->input->get('template_type', true) ?? '';
        $editStatus   = $this->input->get('edit_status', true) ?? '';
        $status       = $this->input->get('status', true) ?? '';

        $perPage = 12;
        $page = (int) ($this->input->get('page') ?? 1);
        if ($page < 1) $page = 1;
        $offset = ($page - 1) * $perPage;

        $total = $this->template_model->countTemplatesWithOverlayCount($templateType, $editStatus, $status);
        $rows  = $this->template_model->getTemplatesWithOverlayCountPaged($templateType, $editStatus, $status, $perPage, $offset);

        $query = $_GET;
        unset($query['page']);
        $suffix = !empty($query) ? '?' . http_build_query($query) : '';

        $config['base_url'] = base_url('Template_manager');
        $config['total_rows'] = $total;
        $config['per_page'] = $perPage;

        // We are using custom page param: ?page=
        $config['page_query_string'] = true;
        $config['query_string_segment'] = 'page';
        $config['reuse_query_string'] = true;

        // UI (bootstrap-ish)
        $config['full_tag_open'] = '<ul class="pagination">';
        $config['full_tag_close'] = '</ul>';
        $config['num_tag_open'] = '<li>';
        $config['num_tag_close'] = '</li>';
        $config['cur_tag_open'] = '<li class="active"><a href="javascript:void(0);">';
        $config['cur_tag_close'] = '</a></li>';
        $config['prev_tag_open'] = '<li>';
        $config['prev_tag_close'] = '</li>';
        $config['next_tag_open'] = '<li>';
        $config['next_tag_close'] = '</li>';
        $config['first_tag_open'] = '<li>';
        $config['first_tag_close'] = '</li>';
        $config['last_tag_open'] = '<li>';
        $config['last_tag_close'] = '</li>';
        $config['attributes'] = ['class' => 'page-link'];

        $this->pagination->initialize($config);

        $this->data['templateData'] = $rows;
        // printVar($rows);
        // die;
        $this->data['pagination_links'] = $this->pagination->create_links();
        $this->data['filters'] = [
            'template_type' => $templateType,
            'edit_status'   => $editStatus,
            'status'        => $status,
        ];

        $this->data['title'] = translate('Marketing_template_manager');
        $this->data['sub_page'] = 'template_manager/index';
        $this->data['main_menu'] = 'Template_manager';
        $this->load->view('layout/index', $this->data);
    }

    public function create()
    {
        $this->data['title'] = translate('Upload_template');
        $this->data['sub_page'] = 'template_manager/create';
        $this->data['main_menu'] = 'Template_manager';
        $this->load->view('layout/index', $this->data);
    }

    public function storeAssets()
    {
        $this->form_validation->set_rules('title', 'Title', 'trim|required|max_length[255]');
        $this->form_validation->set_rules('type', 'Template Type', 'trim|required|in_list[image,video]');

        if ($this->form_validation->run() == false) {
            echo json_encode(['status' => 'error', 'message' => strip_tags(validation_errors())]);
            exit;
        }

        $type = $this->input->post('type', true);

        // IMAGE → old flow stays same
        if ($type === 'image') {
            $result = $this->templateengine_lib->storeTemplate();

            if (is_numeric($result)) {
                echo json_encode([
                    'status' => 'success',
                    'url' => base_url('Template_manager/edit/' . $result)
                ]);
                exit;
            }

            echo json_encode(['status' => 'error', 'message' => $result['error'] ?? 'Upload failed']);
            exit;
        }

        // VIDEO → create DB record first
        $fileSize = (int)$this->input->post('file_size');

        $templateId = $this->template_model->saveTemplate([
            'title' => $this->input->post('title', true),
            'type' => 'video',
            'file_path' => null,
            'size' => $fileSize,
            'upload_status' => 'uploading',
            'created_by' => get_loggedin_user_id()
        ]);

        if (!$templateId) {
            echo json_encode(['status' => 'error', 'message' => 'DB insert failed']);
            exit;
        }

        echo json_encode([
            'status' => 'success',
            'template_id' => $templateId
        ]);
        exit;
    }

    public function uploadVideoChunk()
    {
        $templateId = (int)$this->input->post('template_id');
        $chunkIndex = (int)$this->input->post('chunk_index');
        $totalChunks = (int)$this->input->post('total_chunks');

        if ($templateId <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid ID']);
            return;
        }

        $chunkDir = FCPATH . "uploads/temp_chunks/$templateId/";

        // Check if directory exists or create it
        if (!is_dir($chunkDir)) {
            if (!mkdir($chunkDir, 0775, true)) {
                log_message('error', "Failed to create directory: $chunkDir");
                echo json_encode(['status' => 'error', 'message' => 'Server permission error']);
                return;
            }
        }

        $dest = $chunkDir . $chunkIndex;
        if (move_uploaded_file($_FILES['chunk']['tmp_name'], $dest)) {
            // Only merge if this is the last chunk AND it successfully moved
            if ($chunkIndex + 1 === $totalChunks) {
                $merged = $this->mergeChunks($templateId, $totalChunks);
                if ($merged) {
                    echo json_encode(['status' => 'ok']);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Merge failed']);
                }
            } else {
                echo json_encode(['status' => 'ok']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Chunk move failed']);
        }
    }

    private function mergeChunks($templateId, $totalChunks)
    {
        $finalDir = FCPATH . 'uploads/template-manager/video/';
        $finalName = uniqid('video_') . '.mp4';
        $finalPath = $finalDir . $finalName;

        $out = @fopen($finalPath, 'ab');
        if (!$out) return false;

        for ($i = 0; $i < $totalChunks; $i++) {
            $chunkPath = FCPATH . "uploads/temp_chunks/$templateId/$i";
            if (!file_exists($chunkPath)) {
                fclose($out);
                return false; // Stop if a chunk is missing!
            }

            $in = fopen($chunkPath, "rb");
            while ($buff = fread($in, 4096)) {
                fwrite($out, $buff);
            }
            fclose($in);
            unlink($chunkPath);
        }
        fclose($out);
        @rmdir(FCPATH . "uploads/temp_chunks/$templateId");

        $duration = $this->getVideoDuration($finalPath);
        $size = filesize($finalPath);

        // --- DATABASE TRANSACTION ---
        $this->db->trans_start();

        log_message('info', "Attempting to update template ID: $templateId with file path: $finalPath");

        $this->db->where('id', $templateId)->update('template_assets', [
            'file_path' => 'uploads/template-manager/video/' . $finalName,
            'duration'  => $duration,
            'size'      => $size,
            'upload_status' => 'completed'
        ]);

        $this->db->trans_complete();
        log_message('info', "Database transaction completed with status: " . ($this->db->trans_status() ? 'success' : 'failure'));
        return $this->db->trans_status();
    }

    private function getFfprobePath()
    {
        log_message('info', 'Determining ffprobe path based on OS');

        $os = strtoupper(substr(PHP_OS, 0, 3));

        log_message('info', "Detected OS: $os");

        if ($os === 'WIN') {
            return config_item('ffprobe')['windows'];
        }

        return config_item('ffprobe')['linux'];
    }


    private function getVideoDuration($path)
    {
        $ffprobe = $this->getFfprobePath();

        if (!file_exists($ffprobe)) {
            log_message('error', 'ffprobe not found: ' . $ffprobe);
            return 0;
        }

        $cmd = "\"{$ffprobe}\" -v error -show_entries format=duration "
            . "-of default=noprint_wrappers=1:nokey=1 "
            . escapeshellarg($path);

        // log_message('info', "Running ffprobe command: $cmd");

        return round((float)shell_exec($cmd), 2);
    }


    public function saveOverlays($template_id)
    {
        $this->output->set_content_type('application/json');

        $template = $this->template_model->getById($template_id);
        if (empty($template)) {
            echo json_encode(['status' => 'error', 'message' => 'Template not found']);
            exit;
        }

        $raw = $this->input->post('overlays', false);

        if (!$raw) {
            echo json_encode(['status' => 'error', 'message' => 'Missing overlays payload']);
            exit;
        }

        $overlays = json_decode($raw, true);
        if (!is_array($overlays)) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid JSON payload']);
            exit;
        }


        $this->templateOverlay_model->delete_by_template($template_id);

        $rows = [];
        $z = 1;

        foreach ($overlays as $ov) {
            $settings = null;
            if (isset($ov['settings']) && is_array($ov['settings'])) {
                $settings = json_encode($ov['settings'], JSON_UNESCAPED_UNICODE);
                if ($settings === false) $settings = null;
            }

            $overlayType = $ov['overlay_type'] ?? 'logo';
            if (!in_array($overlayType, ['logo', 'text'])) {
                $overlayType = 'logo';
            }

            $rows[] = [
                'template_id'  => (int)$template_id,
                'overlay_type' => $overlayType,
                'x'            => (float)($ov['x'] ?? 0),
                'y'            => (float)($ov['y'] ?? 0),
                'width'        => (float)($ov['width'] ?? 100),
                'height'       => (float)($ov['height'] ?? 100),
                'start_time'   => 0,
                'end_time'     => null,
                'z_index'      => $z++,
                'settings'     => $settings,
            ];
        }

        // ✅ Loop insert (stable in CI3)
        $ok = true;
        foreach ($rows as $r) {
            $this->db->insert('template_overlays', $r);
            if ($this->db->affected_rows() <= 0) {
                $ok = false;
                // log_message('error', 'Overlay insert failed: ' . json_encode($this->db->error()));
                // log_message('error', 'Overlay insert last_query: ' . $this->db->last_query());
                break;
            }
        }

        echo json_encode([
            'status'  => $ok ? 'success' : 'error',
            'message' => $ok ? 'Placements saved successfully.' : 'Failed to save overlays.'
        ]);
        exit;
    }

    public function edit($id)
    {
        $template = $this->template_model->getById($id);
        if (empty($template)) {
            show_404();
        }

        if ($template['type'] !== 'image') {
            set_alert('info', 'Video editor will be available soon.');
            redirect(base_url('template-manager'));
            return;
        }

        $overlays = $this->templateOverlay_model->get_by_template($id);

        $this->data['template'] = $template;
        $this->data['overlays'] = $overlays;
        $this->data['title'] = translate('edit_template');
        $this->data['sub_page'] = 'template_manager/editor';
        $this->data['main_menu'] = 'Template_manager';
        $this->data['headerelements'] = array(
            'css' => array('css/template_editor.css'),
            'js'  => array(
                'vendor/interactjs/interact.min.js',
            ),
        );

        $this->load->view('layout/index', $this->data);
    }

    public function delete($id)
    {
        $template = $this->template_model->getById($id);

        if (empty($template)) {
            show_404();
        }

        $this->db->trans_start();

        $this->templateOverlay_model->delete_by_template($id);

        if (!empty($template->file_path)) {
            $fullPath = FCPATH . $template->file_path;
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }
        }


        $this->template_model->delete_by_template($id);
        $this->db->trans_complete();
        if ($this->db->trans_status() === FALSE) {
            return responseMsg('error', 'Failed to delete template completely.', base_url('Template_manager'));
        }

        return responseMsg('success', 'Template and files deleted successfully.', base_url('Template_manager'));
    }

    public function branch_templates()
    {
        if (!is_loggedin() || !is_admin_loggedin()) {
            redirect(base_url('dashboard'), 'refresh');
        }

        // raw templates (no placements saved yet) are hidden from branches
        $templates = $this->template_model->getEditedActiveTemplates();
        $this->data['templates'] = $templates;
        $this->data['title'] = translate('marketing_templates');
        $this->data['sub_page'] = 'template_manager/branch_templates';
        $this->data['main_menu'] = 'Template_manager';

        $this->load->view('layout/index', $this->data);
    }

    
    public function download_design()
    {
        $this->superAdminOnly();

        $branches = $this->db->select('id, name, logo')->order_by('name', 'ASC')->get('branch')->result_array();
        foreach ($branches as &$b) {
            $b['has_logo'] = !empty($b['logo']) && file_exists(FCPATH . $b['logo']);
            unset($b['logo']);
        }
        unset($b);

        $this->data['branches']  = $branches;
        $this->data['templates'] = $this->template_model->getEditedActiveTemplates();
        $this->data['title']     = translate('download_design');
        $this->data['sub_page']  = 'template_manager/download_design';
        $this->data['main_menu'] = 'Template_manager';

        $this->load->view('layout/index', $this->data);
    }

    /* ─────────────────────────────────────────────────────────
       DOWNLOAD DESIGN QUEUE (super admin)
       The page queues a job; the cron worker (Design_worker) renders it in the
       background, so it keeps going when the tab is closed.
    ───────────────────────────────────────────────────────── */
    public function design_job_create()
    {
        $this->superAdminOnly(true);
        $this->load->model('design_queue_model', 'queue');
        $this->config->load('design_queue', true);
        $cfg = $this->config->item('design_queue');

        $branchIds   = array_values(array_unique(array_filter(array_map('intval', (array) $this->input->post('branch_ids')))));
        $templateIds = array_values(array_unique(array_filter(array_map('intval', (array) $this->input->post('template_ids')))));
        if (!$branchIds || !$templateIds) {
            $this->json(['status' => 'error', 'message' => 'Select at least one branch and one template.']);
        }

        // only real branches and edited, active templates
        $branchIds = array_map('intval', array_column(
            $this->db->select('id')->where_in('id', $branchIds)->get('branch')->result_array(), 'id'
        ));
        $templates = array_values(array_filter(
            $this->template_model->getEditedActiveTemplates(),
            fn($t) => in_array((int) $t['id'], $templateIds, true)
        ));
        if (!$branchIds || !$templates) {
            $this->json(['status' => 'error', 'message' => 'Selected branches or templates are no longer available.']);
        }

        $userId = get_loggedin_user_id();
        if ($this->queue->countActiveJobs($userId) >= (int) $cfg['design_queue_max_active_jobs']) {
            $this->json(['status' => 'error', 'message' => 'You already have ' . $cfg['design_queue_max_active_jobs'] . ' downloads in progress. Wait for one to finish or cancel it.']);
        }

        $jobId = $this->queue->createJob($userId, $branchIds, $templates);
        if (!$jobId) {
            $this->json(['status' => 'error', 'message' => 'Could not queue the download. Try again.']);
        }

        $this->json(['status' => 'success', 'job_id' => $jobId]);
    }

    // list of this user's downloads with live progress (polled by the page)
    public function design_jobs()
    {
        $this->superAdminOnly(true);
        $this->load->model('design_queue_model', 'queue');
        $this->load->library('design_queue_lib');

        $jobs   = $this->queue->listJobs(get_loggedin_user_id(), 15);
        $counts = $this->queue->itemCounts(array_column($jobs, 'id'));
        $seen   = $this->design_queue_lib->workerLastSeen();

        $this->json([
            'status' => 'success',
            'jobs'   => array_map(fn($j) => $this->jobSummary($j, $counts[(int) $j['id']] ?? [], $this->design_queue_lib->storageDir()), $jobs),
            'worker' => ['last_seen' => $seen, 'alive' => $seen !== null && $seen < 150],
        ]);
    }

    // per-branch progress of one job
    public function design_job_branches($jobId = 0)
    {
        $this->superAdminOnly(true);
        $this->load->model('design_queue_model', 'queue');

        if (!$this->queue->getJob($jobId, get_loggedin_user_id())) {
            $this->json(['status' => 'error', 'message' => 'Download not found']);
        }

        $rows = array_map(fn($r) => [
            'id'        => (int) $r['branch_id'],
            'name'      => $r['name'] ?: 'Branch #' . $r['branch_id'],
            'done'      => (int) $r['done'],
            'failed'    => (int) $r['failed'],
            'cancelled' => (int) $r['cancelled'],
            'active'    => (int) $r['active'],
            'total'     => (int) $r['total'],
        ], $this->queue->branchProgress($jobId));

        $this->json(['status' => 'success', 'branches' => $rows]);
    }

    public function design_job_cancel()
    {
        $this->superAdminOnly(true);
        $this->load->model('design_queue_model', 'queue');

        $job = $this->queue->getJob($this->input->post('job_id'), get_loggedin_user_id());
        if (!$job || !in_array($job['status'], ['pending', 'processing'], true)) {
            $this->json(['status' => 'error', 'message' => 'This download can no longer be cancelled.']);
        }

        $this->queue->cancelJob($job['id']);
        $this->json(['status' => 'success']);
    }

    // re-queue a failed job, or only the failed/cancelled designs of a finished one
    public function design_job_retry()
    {
        $this->superAdminOnly(true);
        $this->load->model('design_queue_model', 'queue');
        $this->load->library('design_queue_lib');
        $this->config->load('design_queue', true);
        $cfg = $this->config->item('design_queue');

        $userId = get_loggedin_user_id();
        $job = $this->queue->getJob($this->input->post('job_id'), $userId);
        if (!$job || !in_array($job['status'], ['done', 'failed'], true)) {
            $this->json(['status' => 'error', 'message' => 'This download cannot be retried. Create a new one instead.']);
        }
        if ($this->queue->countActiveJobs($userId) >= (int) $cfg['design_queue_max_active_jobs']) {
            $this->json(['status' => 'error', 'message' => 'You already have ' . $cfg['design_queue_max_active_jobs'] . ' downloads in progress. Wait for one to finish first.']);
        }

        // with a good ZIP only the missing designs are regenerated and added to it; otherwise everything is redone
        $zipOk = $job['status'] === 'done' && $this->jobZipExists($job, $this->design_queue_lib->storageDir());
        if ($zipOk) {
            $counts = $this->queue->itemCounts([$job['id']])[(int) $job['id']] ?? [];
            if (!(($counts['failed'] ?? 0) + ($counts['cancelled'] ?? 0))) {
                $this->json(['status' => 'error', 'message' => 'Nothing to retry — every design was generated.']);
            }
        } else {
            $this->design_queue_lib->deleteJobFiles($job);
        }

        $this->queue->retryJob($job['id'], $zipOk);
        $this->json(['status' => 'success']);
    }

    public function design_job_delete()
    {
        $this->superAdminOnly(true);
        $this->load->model('design_queue_model', 'queue');
        $this->load->library('design_queue_lib');

        $job = $this->queue->getJob($this->input->post('job_id'), get_loggedin_user_id());
        if (!$job) {
            $this->json(['status' => 'error', 'message' => 'Download not found']);
        }
        if (in_array($job['status'], ['pending', 'processing', 'packaging'], true)) {
            $this->json(['status' => 'error', 'message' => 'This download is still running. Cancel it first, then delete it.']);
        }

        $this->design_queue_lib->deleteJobFiles($job);
        $this->queue->deleteJob($job['id']);
        $this->json(['status' => 'success']);
    }

    private function jobZipExists(array $job, $storageDir)
    {
        return !empty($job['zip_path']) && is_file($storageDir . basename($job['zip_path']));
    }

    // normal link (not fetch) so the browser streams a large ZIP straight to disk
    public function design_job_download($jobId = 0)
    {
        $this->superAdminOnly();
        $this->load->model('design_queue_model', 'queue');
        $this->load->library('design_queue_lib');

        $job  = $this->queue->getJob($jobId, get_loggedin_user_id());
        $path = ($job && $job['status'] === 'done' && $job['zip_path'])
            ? $this->design_queue_lib->storageDir() . basename($job['zip_path'])
            : '';
        if (!$path || !is_file($path)) {
            show_error('This download is not available. It may have expired — please generate it again.', 404);
        }

        session_write_close();
        @set_time_limit(0);
        while (ob_get_level()) {
            ob_end_clean();
        }

        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="template_designs_' . (int) $job['id'] . '_' . date('Ymd', strtotime($job['created_at'])) . '.zip"');
        header('Content-Length: ' . filesize($path));
        header('Cache-Control: no-cache');

        $fp = fopen($path, 'rb');
        while (!feof($fp) && !connection_aborted()) {
            echo fread($fp, 1048576);
            flush();
        }
        fclose($fp);
        exit;
    }

    private function jobSummary(array $job, array $counts, $storageDir)
    {
        $status = $job['status'];
        $error  = $job['error'];

        // "done" is only real if the ZIP is still on disk
        $zipOk = $status === 'done' && $this->jobZipExists($job, $storageDir);
        if ($status === 'done' && !$zipOk) {
            $status = 'failed';
            $error  = 'The ZIP file is missing on the server — retry to generate it again.';
        }

        $done      = $counts['done'] ?? 0;
        $failed    = $counts['failed'] ?? 0;
        $cancelled = $counts['cancelled'] ?? 0;
        $active    = $counts['processing'] ?? 0;
        $remaining = ($counts['pending'] ?? 0) + $active;
        $total     = (int) $job['total_items'];
        $finished  = $done + $failed + $cancelled;

        $eta = null;
        if ($job['started_at'] && $remaining > 0 && ($done + $failed) > 0) {
            $elapsed = max(1, (int) $job['elapsed_seconds']); // measured by MySQL, same clock as started_at
            $eta = (int) round($elapsed / ($done + $failed) * $remaining);
        }

        return [
            'id'               => (int) $job['id'],
            'status'           => $status,
            'cancel_requested' => (bool) $job['cancel_requested'],
            'branch_count'     => (int) $job['branch_count'],
            'template_count'   => (int) $job['template_count'],
            'total'            => $total,
            'done'             => $done,
            'failed'           => $failed,
            'cancelled'        => $cancelled,
            'active'           => $active,
            'percent'          => $total ? (int) floor($finished / $total * 100) : 0,
            'eta_seconds'      => $eta,
            'created_at'       => date('d M Y, h:i A', strtotime($job['created_at'])),
            'zip_size'         => $job['zip_size'] !== null ? (int) $job['zip_size'] : null,
            'zip_count'        => $job['zip_count'] !== null ? (int) $job['zip_count'] : null,
            'error'            => $error,
            'download_url'     => $zipOk ? base_url('Template_manager/design_job_download/' . (int) $job['id']) : null,
            // failed job → retry everything; finished job with some failures → retry just those
            'retry'            => $status === 'failed' ? 'all' : (($zipOk && ($failed + $cancelled) > 0) ? 'failed' : null),
            'can_delete'       => !in_array($status, ['pending', 'processing', 'packaging'], true),
        ];
    }

    private function superAdminOnly($json = false)
    {
        if (is_superadmin_loggedin()) {
            return;
        }
        if ($json) {
            $this->json(['status' => 'error', 'message' => 'Not authorised']);
        }
        redirect(base_url('dashboard'), 'refresh');
    }

    private function json(array $data)
    {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    public function preview_($template_id)
    {
        // Branch admin only
        if (!is_loggedin() || is_superadmin_loggedin()) {
            redirect(base_url('dashboard'));
            return;
        }

        $template = $this->template_model->getById($template_id);
        if (empty($template) || $template['status'] != 1) {
            show_404();
        }

        // Image only for now
        if ($template['type'] !== 'image') {
            set_alert('info', 'Video preview coming soon.');
            redirect(base_url('Template_manager/branch_templates'));
            return;
        }

        $overlays = $this->templateOverlay_model->get_by_template($template_id);
        $branch = $this->db->select('*')->from('branch')->where('id', get_loggedin_branch_id())->get()->row_array();

        $branchLogo = get_branch_logo(get_loggedin_branch_id());

        if (empty($branchLogo)) {
            set_alert('warning', 'Branch logo not uploaded.');
            redirect(base_url('settings'));
            return;
        }

        $this->data['branch_text_map'] = [
            'branch_name'    => $branch['name'] ?? '',
            'branch_address' => $branch['address'] ?? '',
            'branch_contact'   => $branch['mobileno'] ?? '',
        ];


        $this->data['template'] = $template;
        $this->data['overlays'] = $overlays;
        $this->data['branch_logo'] = $branchLogo;

        $this->data['title'] = translate('preview_template');
        $this->data['sub_page'] = 'template_manager/preview';
        $this->data['main_menu'] = 'Template_manager';

        $this->load->view('layout/index', $this->data);
    }

    public function preview($template_id)
    {
        // Branch admin only
        if (!is_loggedin() || is_superadmin_loggedin()) {
            redirect(base_url('dashboard'));
            return;
        }

        $template = $this->template_model->getById($template_id);
        if (empty($template) || $template['status'] != 1) {
            show_404();
        }

        // Image only for now
        if ($template['type'] !== 'image') {
            set_alert('info', 'Video preview coming soon.');
            redirect(base_url('Template_manager/branch_templates'));
            return;
        }

        $overlays = $this->templateOverlay_model->get_by_template($template_id);

        foreach ($overlays as $idx => $ov) {
            $settings = is_array($ov['settings'])
                ? $ov['settings']
                : (json_decode($ov['settings'], true) ?? []);

            log_message('debug', "Overlay $idx type={$ov['overlay_type']} settings=" . json_encode($settings));
        }

        $branch   = $this->db->select('*')->from('branch')
            ->where('id', get_loggedin_branch_id())->get()->row_array();

        $branchLogo = get_branch_logo(get_loggedin_branch_id());

        if (empty($branchLogo)) {
            set_alert('warning', 'Branch logo not uploaded.');
            redirect(base_url('settings'));
            return;
        }

        $branchTextMap = [
            'branch_name'    => $branch['name']     ?? '',
            'branch_address' => $branch['address']  ?? '',
            'branch_contact' => $branch['mobileno'] ?? '',
        ];

        // Generate preview using same Imagick engine as download
        // Returns base64 PNG — guaranteed identical to download output
        $this->load->library('templateengine_lib');
        $previewSrc = $this->templateengine_lib->renderPreview(
            $template['file_path'],
            $branchLogo,
            $overlays,
            $branchTextMap
        );

        $this->data['template']    = $template;
        $this->data['overlays']    = $overlays;
        $this->data['branch_logo'] = $branchLogo;
        $this->data['preview_src'] = $previewSrc;  // base64 PNG for <img src="...">

        $this->data['title']     = translate('preview_template');
        $this->data['sub_page']  = 'template_manager/preview';
        $this->data['main_menu'] = 'Template_manager';

        $this->load->view('layout/index', $this->data);
    }

    public function download($template_id)
    {
        if (!is_loggedin()) {
            show_404();
        }

        $template = $this->template_model->getById($template_id);
        if (!$template || $template['type'] !== 'image') {
            show_404();
        }

        $branchId = resolve_template_branch_id();
        if (empty($branchId)) {
            show_error('Please select a branch to generate the design.', 400);
        }

        $overlays = $this->templateOverlay_model->get_by_template($template_id);

        $branch = $this->db
            ->select('*')
            ->from('branch')
            ->where('id', $branchId)
            ->get()
            ->row_array();

        $branchLogoUrl = get_branch_logo($branchId);
        if (empty($branchLogoUrl)) {
            show_error('Branch logo not found');
        }

        $branchTextMap = [
            'branch_name'    => $branch['name']     ?? '',
            'branch_address' => $branch['address']  ?? '',
            'branch_contact' => $branch['mobileno'] ?? '',
        ];

        $this->templateengine_lib->renderImage(
            $template['file_path'],
            $branchLogoUrl,
            $overlays,
            $template['title'],
            $branchTextMap
        );
    }
}
