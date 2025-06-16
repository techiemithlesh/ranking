<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @package : Eduproject Global PVT LTD
 * @version : 3.0
 * @developed by : Schoolexcel
 * @support : techie.mithlesh@gmail.com
 * @author url : Mithlesh Patel
 * @filename : Leaderboard.php
 * @copyright : Eduproject Global PVT LTD
 */

class Leaderboard extends Admin_Controller
{

    public function __construct()
    {
        parent::__construct();

        $this->load->model('leaderboard_model');

        if (!moduleIsEnabled('leaderboard')) {
            access_denied();
        }
    }

    public function index()
    {
        if (!get_permission('leaderboard', 'is_view')) {
            access_denied();
        }

        if ($this->input->post('search')) {
            $this->leaderboard_validation();
            if ($this->form_validation->run() == true) {
                $branch_id = $this->application_model->get_branch_id();
                $this->data['branch_id'] = $branch_id;
                $class_id = $this->input->post('class_id');
                $section_id = $this->input->post('section_id');
                $exam_type = $this->input->post('exam_type');
                $exam_id = $this->input->post('exam_id');
                $subject_id = $this->input->post('subject_id');
                if ($exam_type == 'offline') {
                    $this->data['leaderboard'] = $this->leaderboard_model->getOfflineExamLeaderboard2($branch_id, $class_id, $section_id, $exam_id, $subject_id);
                } elseif ($exam_type == 'online') {
                    $this->data['leaderboard'] = $this->leaderboard_model->getOnlineExamLeaderboard2($branch_id, $class_id, $section_id, $exam_id, $subject_id);
                    // printVar($this->db->last_query());
                    // die;
                }

            } else {
                $error = $this->form_validation->error_array();
                $array = array('status' => 'fail', 'error' => $error);
            }
        }
        $this->data['filter_exam_type'] = $this->input->post('exam_type');
        $this->data['filter_exam_id'] = $this->input->post('exam_id');
        $this->data['filter_subject_id'] = $this->input->post('subject_id');
        $this->data['branch_id'] = $this->application_model->get_branch_id();
        $this->data['title'] = translate('leaderboard');
        $this->data['sub_page'] = 'leaderboard/index';
        $this->data['main_menu'] = 'leaderboard';
        $this->load->view('layout/index', $this->data);
    }

    protected function leaderboard_validation()
    {
        if (is_superadmin_loggedin()) {
            $this->form_validation->set_rules('branch_id', translate('branch'), 'required');
        }
        $this->form_validation->set_rules('class_id', translate('class'), 'trim|required');
        $this->form_validation->set_rules('section_id', translate('section'), 'trim|required');
        $this->form_validation->set_rules('exam_type', translate('exam_type'), 'trim|required');
        $this->form_validation->set_rules('exam_id', translate('exam'), 'required');
    }

}