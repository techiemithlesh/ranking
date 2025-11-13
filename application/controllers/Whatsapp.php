<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @package : SchoolExcel Management System
 * @version : 3.0
 * @developed by : EduprojectsGlobalTech
 * @support : techie.mithlesh@gmail.com
 * @author url : http://codewithmithlesh.com
 * @filename : Whatsapp.php
 */

class Whatsapp extends Admin_Controller
{

    
    public function __construct()
    {
        parent::__construct();
        $this->load->model('reward_model');
        $this->load->library('bulkwa_lib');
        $this->load->model('whatsapp_model');
    }


    public function config()
    {

        if(!get_permission('whatsapp_config', 'is_view')){
            access_denied();
        }
        
        $branchId = is_superadmin_loggedin() ? null : get_loggedin_branch_id();

        $this->data['configs'] = $this->whatsapp_model->getConfigList($branchId);

        $this->data['title'] = translate('whatsapp_config');
        $this->data['sub_page'] = 'whatsapp/config';
        $this->data['main_menu'] = 'whatsapp';
        $this->load->view('layout/index', $this->data);
    }
}