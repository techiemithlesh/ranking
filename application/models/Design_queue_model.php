<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Queue tables for "Download Design":
 *   design_jobs       — one row per super-admin request
 *   design_job_items  — one row per branch × template (the unit the worker renders)
 */
class Design_queue_model extends CI_Model
{
    const JOBS  = 'design_jobs';
    const ITEMS = 'design_job_items';

    /* ── creating & reading jobs (web side) ─────────────────── */

    public function createJob($userId, array $branchIds, array $templates)
    {
        // all queue timestamps use MySQL's clock: the CLI worker's PHP timezone can differ from the web's
        $this->db->trans_start();
        $this->db->set('created_at', 'NOW()', false)->insert(self::JOBS, [
            'created_by'     => (int) $userId,
            'status'         => 'pending',
            'branch_count'   => count($branchIds),
            'template_count' => count($templates),
            'total_items'    => count($branchIds) * count($templates),
        ]);
        $jobId = (int) $this->db->insert_id();

        // branch-first order so branches complete one after another
        $rows = [];
        foreach ($branchIds as $branchId) {
            foreach ($templates as $tpl) {
                $rows[] = [
                    'job_id'      => $jobId,
                    'branch_id'   => (int) $branchId,
                    'template_id' => (int) $tpl['id'],
                    'type'        => $tpl['type'],
                ];
                if (count($rows) === 500) {
                    $this->db->insert_batch(self::ITEMS, $rows);
                    $rows = [];
                }
            }
        }
        if ($rows) {
            $this->db->insert_batch(self::ITEMS, $rows);
        }
        $this->db->trans_complete();

        return $this->db->trans_status() ? $jobId : false;
    }

    public function getJob($jobId, $userId = null)
    {
        $this->db->where('id', (int) $jobId);
        if ($userId !== null) {
            $this->db->where('created_by', (int) $userId);
        }
        return $this->db->get(self::JOBS)->row_array();
    }

    public function countActiveJobs($userId)
    {
        return $this->db->where('created_by', (int) $userId)
            ->where_in('status', ['pending', 'processing', 'packaging'])
            ->count_all_results(self::JOBS);
    }

    public function listJobs($userId, $limit = 15)
    {
        return $this->db->select('*, TIMESTAMPDIFF(SECOND, started_at, NOW()) AS elapsed_seconds', false)
            ->where('created_by', (int) $userId)
            ->order_by('id', 'DESC')
            ->limit((int) $limit)
            ->get(self::JOBS)->result_array();
    }

    // [job_id => [status => count]] for many jobs in one query
    public function itemCounts(array $jobIds)
    {
        $out = [];
        if (!$jobIds) return $out;

        $rows = $this->db->select('job_id, status, COUNT(*) AS c', false)
            ->where_in('job_id', array_map('intval', $jobIds))
            ->group_by(['job_id', 'status'])
            ->get(self::ITEMS)->result_array();
        foreach ($rows as $r) {
            $out[(int) $r['job_id']][$r['status']] = (int) $r['c'];
        }
        return $out;
    }

    // per-branch progress for one job (used by the details view)
    public function branchProgress($jobId)
    {
        return $this->db->query(
            "SELECT i.branch_id, b.name,
                    SUM(i.status = 'done')                        AS done,
                    SUM(i.status = 'failed')                      AS failed,
                    SUM(i.status = 'cancelled')                   AS cancelled,
                    SUM(i.status = 'processing')                  AS active,
                    COUNT(*)                                      AS total
               FROM " . self::ITEMS . " i
               LEFT JOIN branch b ON b.id = i.branch_id
              WHERE i.job_id = ?
              GROUP BY i.branch_id, b.name
              ORDER BY MIN(i.id)",
            [(int) $jobId]
        )->result_array();
    }

    public function cancelJob($jobId)
    {
        $this->db->where('id', (int) $jobId)->update(self::JOBS, ['cancel_requested' => 1]);
        $this->db->where('job_id', (int) $jobId)->where('status', 'pending')
            ->update(self::ITEMS, ['status' => 'cancelled']);
    }

    /* ── worker side ────────────────────────────────────────── */

    // items left "processing" by a crashed worker go back to the queue (or fail after max attempts)
    public function resetStale($minutes, $maxAttempts)
    {
        $this->db->query(
            "UPDATE " . self::ITEMS . "
                SET status = IF(attempts >= ?, 'failed', 'pending'),
                    error = 'Worker stopped while generating',
                    worker_id = NULL
              WHERE status = 'processing' AND locked_at < (NOW() - INTERVAL ? MINUTE)",
            [(int) $maxAttempts, (int) $minutes]
        );
    }

    /**
     * Atomically claims the next work: up to $imageBatch image items of one template,
     * or a single video item. Safe with several workers running at once.
     */
    public function claimItems($workerId, $imageBatch)
    {
        // a failed item keeps its locked_at as "failed at" — wait a minute before retrying it
        $ready = "status = 'pending' AND (locked_at IS NULL OR locked_at < NOW() - INTERVAL 1 MINUTE)";

        $next = $this->db->query(
            "SELECT job_id, template_id, type FROM " . self::ITEMS . "
              WHERE " . $ready . " ORDER BY id LIMIT 1"
        )->row_array();
        if (!$next) return [];

        $limit = $next['type'] === 'video' ? 1 : (int) $imageBatch;
        $this->db->query(
            "UPDATE " . self::ITEMS . "
                SET status = 'processing', worker_id = ?, locked_at = NOW(), attempts = attempts + 1
              WHERE " . $ready . " AND job_id = ? AND template_id = ?
              ORDER BY id LIMIT " . $limit,
            [$workerId, (int) $next['job_id'], (int) $next['template_id']]
        );
        if ($this->db->affected_rows() < 1) {
            return []; // another worker took them; caller simply tries again
        }

        $this->db->where('id', (int) $next['job_id'])->where('status', 'pending')
            ->set('started_at', 'NOW()', false)
            ->update(self::JOBS, ['status' => 'processing']);

        return $this->db->where('worker_id', $workerId)->where('status', 'processing')
            ->order_by('id')->get(self::ITEMS)->result_array();
    }

    public function markDone($itemId, $filePath)
    {
        $this->db->where('id', (int) $itemId)->update(self::ITEMS, [
            'status' => 'done', 'file_path' => $filePath, 'error' => null,
            'worker_id' => null, 'locked_at' => null,
        ]);
    }

    public function markFailed(array $item, $error, $maxAttempts)
    {
        $this->db->where('id', (int) $item['id'])
            ->set('locked_at', 'NOW()', false) // delays the retry (see claimItems)
            ->update(self::ITEMS, [
                'status'    => ((int) $item['attempts'] >= $maxAttempts) ? 'failed' : 'pending',
                'error'     => mb_substr($error, 0, 250),
                'worker_id' => null,
            ]);
    }

    // jobs with nothing left to render
    public function jobsReadyToPackage()
    {
        return array_column($this->db->query(
            "SELECT j.id FROM " . self::JOBS . " j
              WHERE j.status IN ('pending', 'processing')
                AND NOT EXISTS (SELECT 1 FROM " . self::ITEMS . " i
                                 WHERE i.job_id = j.id AND i.status IN ('pending', 'processing'))"
        )->result_array(), 'id');
    }

    // only one worker may package a job
    public function claimPackaging($jobId)
    {
        $this->db->where('id', (int) $jobId)->where_in('status', ['pending', 'processing'])
            ->update(self::JOBS, ['status' => 'packaging']);
        return $this->db->affected_rows() === 1;
    }

    public function jobItems($jobId, $status)
    {
        return $this->db->select('i.branch_id, i.template_id, i.file_path, i.error, b.name AS branch_name, t.title AS template_title')
            ->from(self::ITEMS . ' i')
            ->join('branch b', 'b.id = i.branch_id', 'left')
            ->join('template_assets t', 't.id = i.template_id', 'left')
            ->where('i.job_id', (int) $jobId)
            ->where('i.status', $status)
            ->order_by('i.id')
            ->get()->result_array();
    }

    public function finishJob($jobId, array $data)
    {
        $this->db->where('id', (int) $jobId)->set('finished_at', 'NOW()', false)->update(self::JOBS, $data);
    }

    public function expiredJobs($days)
    {
        return $this->db->where_in('status', ['done', 'failed'])
            ->where('finished_at < NOW() - INTERVAL ' . (int) $days . ' DAY', null, false)
            ->get(self::JOBS)->result_array();
    }

    public function expireJob($jobId)
    {
        $this->db->where('job_id', (int) $jobId)->delete(self::ITEMS);
        $this->db->where('id', (int) $jobId)->update(self::JOBS, ['status' => 'expired', 'zip_path' => null]);
    }
}
