<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Video_editor extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Template_model', 'assetModel');
        $this->load->model('Template_video_overlay_model', 'overlayModel');
    }

    public function index($template_id = null)
    {
        if (!$template_id) show_404();
        $template = $this->assetModel->getById($template_id);
        if (!$template) show_404();

        $overlays = $this->overlayModel->get_by_template($template_id);
        $this->data['template']      = $template;
        $this->data['video']         = $template;
        $this->data['overlays_json'] = json_encode($overlays, JSON_UNESCAPED_SLASHES);
        $this->data['title']         = translate('Marketing_template_manager(Video_editor)');
        $this->data['sub_page']      = 'template_manager/video_editor';
        $this->data['main_menu']     = 'Template_manager';
        $this->data['headerelements'] = [
            'css' => ['css/video_editor.css'],
            'js'  => ['vendor/interactjs/interact.min.js'],
        ];
        $this->load->view('layout/index', $this->data);
    }

    /* ─────────────────────────────────────────────────────────
       SAVE OVERLAYS
    ───────────────────────────────────────────────────────── */
    public function save_overlays()
    {
        if (!is_loggedin() || !is_superadmin_loggedin()) {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']); return;
        }

        $template_id = (int)$this->input->post('template_id');
        $raw         = $this->input->post('overlays');

        if (!$template_id || !$raw) {
            echo json_encode(['status' => 'error', 'message' => 'Missing data']); return;
        }

        $overlays = json_decode($raw, true);
        if (!is_array($overlays)) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid overlay data']); return;
        }

        $this->db->where('template_id', $template_id)->delete('template_video_overlays');

        $rows = [];
        foreach ($overlays as $ov) {
            $type = $ov['overlay_type'] ?? 'text';
            if (!in_array($type, ['logo', 'text'])) $type = 'text';

            $settings = $ov['settings'] ?? [];
            if ($type === 'text' && empty($settings['text_key'])) {
                $settings['text_key'] = $ov['variable'] ?? 'branch_name';
            }

            $rows[] = [
                'template_id'  => $template_id,
                'overlay_type' => $type,
                'x'            => (float)($ov['x']          ?? 0),
                'y'            => (float)($ov['y']          ?? 0),
                'width'        => (float)($ov['width']      ?? 0.2),
                'height'       => (float)($ov['height']     ?? 0.1),
                'start_time'   => (float)($ov['start_time'] ?? 0),
                'end_time'     => (float)($ov['end_time']   ?? 5),
                'settings'     => json_encode($settings, JSON_UNESCAPED_UNICODE),
                'updated_at'   => date('Y-m-d H:i:s'),
            ];
        }

        if (!empty($rows)) $this->db->insert_batch('template_video_overlays', $rows);

        echo json_encode(['status' => 'success', 'message' => 'Template saved successfully']);
    }

    /* ─────────────────────────────────────────────────────────
       BRANCH PREVIEW
    ───────────────────────────────────────────────────────── */
    public function preview($template_id = null)
    {
        $template = $this->assetModel->getById($template_id);
        $overlays = $this->overlayModel->get_by_template($template_id);
        usort($overlays, fn($a, $b) => $a['start_time'] <=> $b['start_time']);

        $branch     = $this->db->select('*')->from('branch')
                        ->where('id', get_loggedin_branch_id())->get()->row_array();
        $branchLogo = get_branch_logo(get_loggedin_branch_id());

        $this->data['template']      = $template;
        $this->data['video']         = $template;
        $this->data['overlays_json'] = json_encode($overlays);
        $this->data['branch']        = $branch;
        $this->data['branch_logo']   = $branchLogo;
        $this->data['title']         = translate('preview_template');
        $this->data['sub_page']      = 'template_manager/video_preview';
        $this->load->view('layout/index', $this->data);
    }

    /* ─────────────────────────────────────────────────────────
       DOWNLOAD — burn overlays into video via FFmpeg
       Fixed for Windows: paths use forward slashes,
       colons escaped inside filter_complex strings.
    ───────────────────────────────────────────────────────── */
    public function download($template_id)
    {
        if (!is_loggedin()) {
            redirect(base_url('dashboard')); return;
        }

        $template = $this->assetModel->getById($template_id);
        if (empty($template) || $template['type'] !== 'video') {
            show_404();
        }
        // branch admins may only generate active templates; super admin can generate any
        if (!is_superadmin_loggedin() && $template['status'] != 1) {
            show_404();
        }

        $branchId = resolve_template_branch_id();
        if (empty($branchId)) {
            show_error('Please select a branch to generate the design.', 400);
        }

        $this->load->library('videorender_lib');
        $outputFile = $this->videorender_lib->render($template, $branchId);

        if (!$outputFile) {
            set_alert('error', 'Video processing failed. Please try again.');
            redirect(base_url(is_superadmin_loggedin() ? 'Template_manager/download_design' : 'Template_manager/branch_templates'));
            return;
        }

        $filename = preg_replace('/[^a-z0-9_\-]/i', '_', $template['title'])
                  . '_' . date('Ymd') . '.mp4';
        header('Content-Type: video/mp4');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($outputFile));
        header('Cache-Control: no-cache');
        readfile($outputFile);

        register_shutdown_function(function () use ($outputFile) {
            if (file_exists($outputFile)) @unlink($outputFile);
        });
        exit;
    }

    /* ─────────────────────────────────────────────────────────
       HASH helper (for caching — used by download_ legacy)
    ───────────────────────────────────────────────────────── */
    private function renderHash($template, $overlays, $branch, $logoUrl)
    {
        return sha1(json_encode([
            $template['file_path'],
            $branch['name'], $branch['address'], $branch['mobileno'], $logoUrl,
            $overlays,
        ]));
    }
}