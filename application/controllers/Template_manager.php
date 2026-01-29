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

    // public function index()
    // {
    //     $TemplateData = [];
    //     if ($_POST) {
    //         $this->form_validation->set_rules('template_type', translate('template_type'), 'trim|required');
    //         $this->form_validation->set_rules('edit_status', translate('edit_status'), 'trim|required');
    //         $this->form_validation->set_rules('status', translate('status'), 'trim|required');

    //         if ($this->form_validation->run != false) {
    //             $templateType = $this->input->post('template_type', true); // 1=pamphlet,2=video
    //             $editStatus   = $this->input->post('edit_status', true);   // 1 edited, 0 raw
    //             $status       = $this->input->post('status', true);        // 1 active, 0 inactive
    //             $TemplateData = $this->Template_model->getTemplatesWithOverlayCount($templateType, $editStatus, $status);
    //         } else {
    //             $errors = $this->form_validation->error_array();
    //             set_alert('error', implode(' ', $errors));
    //         }
    //     } else {
    //         $TemplateData = $this->Template_model->getTemplatesWithOverlayCount('', '', '');
    //     }

    //     $this->data['templateData'] = $TemplateData;
    //     $this->data['title'] = translate('Marketing_template_manager');
    //     $this->data['sub_page'] = 'template_manager/index';
    //     $this->data['main_menu'] = 'Resources';
    //     $this->data['headerelements'] = array(
    //         'css' => array(
    //             'vendor/dropify/css/dropify.min.css',
    //         ),
    //         'js' => array(
    //             'vendor/dropify/js/dropify.min.js',
    //         ),
    //     );

    //     $this->load->view('layout/index', $this->data);
    // }

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

        $result = $this->templateengine_lib->storeTemplate();

        // ✅ SUCCESS: insert_id returned
        if (is_numeric($result) && (int)$result > 0) {
            $id = (int)$result;
            $type = $this->input->post('type', true);

            $url = ($type === 'image')
                ? base_url('Template_manager/edit/' . $id)
                : base_url('Template_manager');

            echo json_encode([
                'status'  => 'success',
                'message' => 'Template Uploaded Successfully!',
                'url'     => $url
            ]);
            exit;
        }

        // ✅ ERROR returned as array
        if (is_array($result) && isset($result['error'])) {
            echo json_encode(['status' => 'error', 'message' => $result['error']]);
            exit;
        }

        // ✅ DB exception returned as string (your saveTemplate try/catch)
        if (is_string($result) && $result !== '') {
            echo json_encode(['status' => 'error', 'message' => $result]);
            exit;
        }

        echo json_encode(['status' => 'error', 'message' => 'Upload failed']);
        exit;
    }

    public function edit($id)
    {
        $template = $this->template_model->getById($id);
        if (empty($template)) {
            show_404();
        }

        // Only image editor now
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
            'css' => array(),
            'js'  => array(
                'vendor/interactjs/interact.min.js',
            ),
        );

        $this->load->view('layout/index', $this->data);
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

        // Replace overlays
        $this->templateOverlay_model->delete_by_template($template_id);

        $rows = [];
        $z = 1;

        foreach ($overlays as $ov) {
            $settings = null;
            if (isset($ov['settings']) && is_array($ov['settings'])) {
                $settings = json_encode($ov['settings'], JSON_UNESCAPED_UNICODE);
                if ($settings === false) $settings = null;
            }

            $rows[] = [
                'template_id'  => (int)$template_id,
                'overlay_type' => 'logo',
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


        $branchLogo = get_branch_logo(get_loggedin_branch_id());


        if (empty($branchLogo)) {
            set_alert('warning', 'Branch logo not uploaded.');
            redirect(base_url('settings'));
            return;
        }

        $this->data['template'] = $template;
        $this->data['overlays'] = $overlays;
        $this->data['branch_logo'] = $branchLogo;

        $this->data['title'] = translate('preview_template');
        $this->data['sub_page'] = 'template_manager/preview';
        $this->data['main_menu'] = 'Resources';

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

        // get_branch_logo() RETURNS URL (do not change it)
        $branchLogoUrl = get_branch_logo();

        if (empty($branchLogoUrl)) {
            show_error('Branch logo not found');
        }


        $this->templateengine_lib->renderImage(
            $template['file_path'],   // relative path
            $branchLogoUrl,           // URL (will be converted internally)
            $overlays,
            $template['title']
        );
    }
}
