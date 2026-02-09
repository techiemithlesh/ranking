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
            'css' => array('css/template_editor.css'),
            'js'  => array(
                'js/video-editor.js',
            ),
        );

        $this->load->view('layout/index', $this->data);
    }
}
