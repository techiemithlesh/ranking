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

        $sessionCode = null;
        $this->data['leaderboard'] = [];

        if ($this->input->post('search')) {

            $exam_type = $this->input->post('exam_type');
            $branch_id = $this->application_model->get_branch_id();
            $class_id = $this->input->post('class_id');
            $section_id = $this->input->post('section_id');
            $exam_id = $this->input->post('exam_id');
            $subject_id = $this->input->post('subject_id');
            $sessionCode = $this->input->post('session_code');
            $sessionCode = !empty($sessionCode) ? $sessionCode : null;

            // VALIDATION
            $this->leaderboard_validation();

            if ($this->form_validation->run() == true) {

                if ($exam_type == 'offline') {
                    $this->data['leaderboard'] = $this->leaderboard_model
                        ->getOfflineExamLeaderboard($branch_id, $class_id, $section_id, $exam_id, $subject_id);
                } elseif ($exam_type == 'online') {
                    $this->data['leaderboard'] = $this->leaderboard_model
                        ->getOnlineExamLeaderboard($branch_id, $class_id, $section_id, $exam_id, $subject_id);
                } elseif ($exam_type == 'live_exam') {

                    if (!empty($subject_id)) {
                        // ⭐ SUBJECT-WISE LIVE EXAM (all live exams)
                        $this->data['leaderboard'] = $this->leaderboard_model
                            ->getLiveExamSubjectRank($branch_id, $class_id, $section_id, $subject_id);

                    } else {
                        // ⭐ EXAM-WISE LIVE EXAM
                        $this->data['leaderboard'] = $this->leaderboard_model
                            ->getAllRank($branch_id, $class_id, $section_id, $exam_id, $sessionCode);
                    }
                }
                
            } else {
                $this->data['form_error'] = $this->form_validation->error_array();
            }
        }

        // VIEW DATA
        $this->data['exam_type'] = $this->input->post('exam_type');
        $this->data['filter_exam_id'] = $this->input->post('exam_id');
        $this->data['filter_subject_id'] = $this->input->post('subject_id');
        $this->data['branch_id'] = $this->application_model->get_branch_id();
        $this->data['sessionCode'] = $sessionCode;

        $this->data['title'] = translate('leaderboard');
        $this->data['sub_page'] = 'leaderboard/index';
        $this->data['main_menu'] = 'leaderboard';

        $this->load->view('layout/index', $this->data);
    }


    private function leaderboard_validation()
    {
        if (is_superadmin_loggedin()) {
            $this->form_validation->set_rules('branch_id', 'Branch', 'trim|required');
        }

        $this->form_validation->set_rules('class_id', 'Class', 'trim|required');
        $this->form_validation->set_rules('section_id', 'Section', 'trim|required');
        $this->form_validation->set_rules('exam_type', 'Exam Type', 'trim|required');

        $exam_type = $this->input->post('exam_type');

        // OFFLINE & ONLINE both require exam selection
        if ($exam_type == 'offline' || $exam_type == 'online') {
            $this->form_validation->set_rules('exam_id', 'Exam', 'trim|required');
        }

        // LIVE EXAM (Exam-wise requires exam_id)
        if ($exam_type == 'live_exam' && empty($this->input->post('subject_id'))) {
            // exam wise live exam
            $this->form_validation->set_rules('exam_id', 'Exam', 'trim|required');
        }

        // LIVE EXAM (Subject-wise requires subject_id)
        if ($exam_type == 'live_exam' && !empty($this->input->post('subject_id'))) {
            // subject wise ranking across all exams
            $this->form_validation->set_rules('subject_id', 'Subject', 'trim|required');
        }
    }


}