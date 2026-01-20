<?
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

class Template_manager extends Admin_Controller{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Template_model');
        $this->load->model('TemplateOverlay_model');
        $this->load->library('TemplateEngine_lib');

        if(!is_superadmin_loggedin()){
            redirect(base_url('dashboard'), 'refresh');
            set_alert('info', "You are not authorized to access it !");
        }
    }
}