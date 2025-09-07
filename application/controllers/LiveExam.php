<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @package : SchoolExcel school management system
 * @version : 4.0
 * @developed by : Mithlesh Patel
 * @support : techie.mithlesh@gmail.com
 * @author url : http://codewithmithlesh.com
 * @filename : LiveExam.php
 * @copyright : Reserved SchoolExcel Team
 */

class LiveExam extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('onlineexam_model');
        $this->load->model('live_exam_model');
        $this->load->model('email_model');
        $this->load->model('sms_model');
        $this->load->model('subject_model');
        $this->data['headerelements'] = array(
            'css' => array(
                'vendor/summernote/summernote.css',
                'vendor/bootstrap-timepicker/css/bootstrap-timepicker.css',
            ),
            'js' => array(
                'vendor/summernote/summernote.js',
                'vendor/bootstrap-timepicker/bootstrap-timepicker.js',
                'js/online-exam.js',
            ),
        );

    }


    public function index()
    {

        if (!get_permission('live_exam', 'is_view')) {
            access_denied();
        }

        $this->data['title'] = translate('live_exam');
        $this->data['sub_page'] = 'onlineexam/live_exam/index';
        $this->data['main_menu'] = 'onlineexam';
        $this->load->view('layout/index', $this->data);
    }

    public function getLiveExamListDT()
    {
        if ($_POST) {
            $postData = $this->input->post();
            $currencySymbol = $this->data['global_config']['currency_symbol'];
            echo $this->live_exam_model->examListLiveDT($postData, $currencySymbol);
        }
    }


    public function host($exam_id)
    {
        if (!get_permission('live_exam', 'is_add')) {
            access_denied();
        }

        // If non-superadmin, ensure branch is allowed
        if (!is_superadmin_loggedin()) {
            $isPerm = $this->live_exam_model->isBranchExamAssigned($exam_id);
            if (!$isPerm) {
                set_alert('error', translate('You dont have permission to take this exam'));
                redirect(base_url('liveexam'));
            }
        }


        $data['title'] = translate('host_live_exam');
        $data['exam'] = $this->live_exam_model->getExamDetailsForLive($exam_id);

        if (empty($data['exam'])) {
            set_alert('error', translate('exam_not_found_or_not_allowed'));
            redirect(base_url('liveexam'));
        }

        $data['sub_page'] = 'onlineexam/live_exam/host';
        $data['main_menu'] = 'onlineexam';
        $this->load->view('layout/index', $data);
    }


    public function ajaxGetQuestions()
    {
        $status = 0;
        $totalQuestions = 0;
        $message = "";
        $this->load->model('onlineexam_model');
        $examID = $this->input->post('exam_id');
        $exam = $this->live_exam_model->getExamDetailsForLive($examID);
        $totalQuestions = $exam->questions_qty;
        $studentAttempt = $this->onlineexam_model->getStudentAttempt($exam->id);
        $examSubmitted = $this->onlineexam_model->getStudentSubmitted($exam->id);
        if (!empty($exam)) {
            $startTime = strtotime($exam->exam_start);
            $endTime = strtotime($exam->exam_end);
            $now = strtotime("now");
            if (($startTime <= $now && $now <= $endTime) && (empty($examSubmitted)) && $exam->publish_status == 1) {
                if ($exam->limits_participation > $studentAttempt) {
                    // $this->onlineexam_model->addStudentAttemts($exam->id);
                    $message = "";
                    $status = 1;
                } else {
                    $status = 0;
                    $message = "You already reach max exam attempt.";
                }
            } else {
                $message = "Maybe the test has expired or something wrong.";
            }
        }
        $data['exam'] = $exam;
        $data['questions'] = $this->onlineexam_model->getExamQuestions($exam->id, $exam->question_type);

        // $activeSession = $this->live_exam_model->getActiveSessionByExam($exam->id);
        // $participants = [];
        // if (!empty($activeSession)) {
        //     $participants = $this->live_exam_model->getSessionStudents($activeSession->id);
        // }
        // $data['participants'] = $participants;
        // $data['active_session'] = $activeSession;

        $pag_content = $this->load->view('onlineexam/live_exam/ajax_start', $data, true);
        echo json_encode(array(
            'status' => $status,
            'total_questions' => $totalQuestions,
            'message' => $message,
            'page' => $pag_content,
            // 'participants_count' => count($participants),
            // 'participants' => $participants
        ));
    }


}