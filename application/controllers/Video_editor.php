<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * @package : Ramom school management system
 * @version : 4.0
 * @developed by : Eduprojects Global Tech (http://eduprojectsglobal.com)
 * @support : http://eduprojectsglobal.com/support
 * @author url : http://codewithmithlesh.com
 * @filename : Video_editor.php
 * @copyright : Reserved EduprojectsGlobalTech Team
 */

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
        if (!$template_id) {
            show_404();
        }

        $template = $this->assetModel->getById($template_id);
        if (!$template) {
            show_404();
        }

        $overlays = $this->overlayModel->get_by_template($template_id);
        $this->data['template'] = $template;
        $this->data['video'] = $template;

        $this->data['overlays_json'] = json_encode($overlays, JSON_UNESCAPED_SLASHES);


        $this->data['title'] = translate('Marketing_template_manager(Video_editor)');
        $this->data['sub_page'] = 'template_manager/video_editor';
        $this->data['main_menu'] = 'Template_manager';

        $this->data['headerelements'] = array(
            'css' => array('css/video_editor.css'),
            'js'  => array(
                'vendor/interactjs/interact.min.js',
            ),

        );

        $this->load->view('layout/index', $this->data);
    }

    public function save_overlays()
    {
        if ($_POST) {
            $template_id = $this->input->post('template_id');
            $overlays_data = json_decode($this->input->post('overlays'), true);

            log_message('debug', 'Received overlays data: ' . print_r($overlays_data, true));

            if (empty($template_id)) {
                echo json_encode(['status' => 'error', 'message' => 'Invalid Template ID']);
                return;
            }

            $this->db->where('template_id', $template_id);
            $this->db->delete('template_video_overlays');


            $insert_data = [];
            foreach ($overlays_data as $ov) {

                $settings = null;
                if (isset($ov['settings']) && is_array($ov['settings'])) {
                    $settings = json_encode($ov['settings'], JSON_UNESCAPED_UNICODE);
                    if ($settings === false) $settings = null;
                }

                $overlayType = $ov['overlay_type'] ?? 'logo';
                if (!in_array($overlayType, ['logo', 'text'])) {
                    $overlayType = 'logo';
                }


                $insert_data[] = [
                    'template_id'  => $template_id,
                    'overlay_type' => $ov['overlay_type'],
                    'x'            => $ov['x'],      // Stored as float (e.g., 0.125)
                    'y'            => $ov['y'],
                    'width'        => $ov['width'],
                    'height'       => $ov['height'],
                    'start_time'   => $ov['start_time'],
                    'end_time'     => $ov['end_time'],
                    'settings'     => $settings,
                    'updated_at'   => date('Y-m-d H:i:s')
                ];
            }

            if (!empty($insert_data)) {
                $this->db->insert_batch('template_video_overlays', $insert_data);
            }

            echo json_encode(['status' => 'success', 'message' => 'Template coordinates updated!']);
        }
    }
}
