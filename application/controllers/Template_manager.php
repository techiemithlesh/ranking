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

        log_message('info', "Running ffprobe command: $cmd");

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
                log_message('error', 'Overlay insert last_query: ' . $this->db->last_query());
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

        $templates = $this->template_model->getActiveTemplates();
        $this->data['templates'] = $templates;
        $this->data['title'] = translate('marketing_templates');
        $this->data['sub_page'] = 'template_manager/branch_templates';
        $this->data['main_menu'] = 'Template_manager';

        $this->load->view('layout/index', $this->data);
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

        $overlays = $this->templateOverlay_model->get_by_template($template_id);

        $branch = $this->db
            ->select('*')
            ->from('branch')
            ->where('id', get_loggedin_branch_id())
            ->get()
            ->row_array();

        $branchLogoUrl = get_branch_logo(get_loggedin_branch_id());
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
