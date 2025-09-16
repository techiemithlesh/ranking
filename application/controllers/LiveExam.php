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

        $this->data['headerelements'] = array(
            'js' => array(
                'js/online-exam.js',
            ),
        );

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
        $examID = $this->input->post('exam_id');
        $exam = $this->live_exam_model->getExamDetailsForLive($examID);

        $totalQuestions = $exam->questions_qty;
        if (!empty($exam)) {
            $startTime = strtotime($exam->exam_start);
            $endTime = strtotime($exam->exam_end);
            $now = strtotime("now");
            if (($startTime <= $now && $now <= $endTime) && $exam->publish_status == 1) {
                $message = "";
                $status = 1;

            } else {
                $message = "Maybe the test has expired or something wrong.";
            }
        }
        $data['exam'] = $exam;
        $data['questions'] = $this->onlineexam_model->getExamQuestions($exam->id, $exam->question_type);
        $pag_content = $this->load->view('onlineexam/live_exam/ajax_start', $data, true);
        echo json_encode(array(
            'status' => $status,
            'total_questions' => $totalQuestions,
            'message' => $message,
            'page' => $pag_content
        ));
    }

    public function startSession()
    {
        if (!get_permission('live_exam', 'is_add')) {
            echo json_encode(['status' => 0, 'message' => 'Permission denied']);
            return;
        }

        $examID = $this->input->post('exam_id');
        if (empty($examID)) {
            echo json_encode(['status' => 0, 'message' => 'Invalid Exam']);
            return;
        }

        $hostID = get_loggedin_user_id();
        $hostRole = loggedin_role_name();
        $currentQuestionId = $this->input->post('current_question_id');

        $session = $this->live_exam_model->createSession($examID, $hostID, $hostRole, $currentQuestionId);

        if ($session) {
            echo json_encode([
                'status' => 1,
                'message' => 'Session started successfully',
                'session_id' => $session->id,
                'session_code' => $session->session_code,
                'join_link' => base_url('Liveexam_student/join/' . $session->session_token)
            ]);
        } else {
            echo json_encode(['status' => 0, 'message' => 'Failed to start session']);
        }
    }

    public function activateSession()
    {
        $session_id = $this->input->post('session_id');

        $this->db->where('id', $session_id)
            ->update('exam_sessions', [
                'status' => 'active',
                'last_ping_at' => date('Y-m-d H:i:s')
            ]);

        echo json_encode(['status' => 1]);
    }

    public function sessionHeartbeat()
    {
        $session_id = $this->input->post('session_id');

        $this->db->where('id', $session_id)
            ->where('status', 'active')
            ->update('exam_sessions', [
                'last_ping_at' => date('Y-m-d H:i:s')
            ]);

        echo json_encode(['status' => 1]);
    }


    public function setCurrentQuestion()
    {
        if (!get_permission('live_exam', 'is_add')) {
            echo json_encode(['status' => 0, 'message' => 'Permission denied']);
            return;
        }

        $session_id = $this->input->post('session_id');
        $question_id = $this->input->post('question_id');

        if (empty($session_id) || $question_id === null) {
            echo json_encode(['status' => 0, 'message' => 'Missing parameters']);
            return;
        }

        $ok = $this->live_exam_model->setCurrentQuestion($session_id, $question_id);

        if ($ok) {
            echo json_encode(['status' => 1, 'message' => 'Current question updated']);
        } else {
            echo json_encode(['status' => 0, 'message' => 'Failed to update']);
        }
    }


    public function endSession()
    {
        $session_id = $this->input->post('session_id');

        if (empty($session_id)) {
            echo json_encode(['status' => 0, 'message' => 'Missing session id']);
            exit;
        }
        $ok = $this->live_exam_model->endSession($session_id, get_loggedin_user_id());
        if ($ok)
            echo json_encode(['status' => 1, 'message' => 'Session ended']);
        else
            echo json_encode(['status' => 0, 'message' => 'Failed to end session']);
    }

    public function getParticipants()
    {
        $session_id = $this->input->get('session_id');

        $participants = $this->live_exam_model->getParticipantsBySession($session_id);

        echo json_encode([
            'status' => 1,
            'participants' => $participants
        ]);
    }

    public function getSessionAnswers()
    {
        $session_id = $this->input->get('session_id');
        $question_id = $this->input->get('question_id');

        if (empty($session_id) || empty($question_id)) {
            echo json_encode([
                'status' => 0,
                'message' => 'Invalid request'
            ]);
            return;
        }

        $answers = $this->live_exam_model->getAnswersBySession($session_id, $question_id);

        echo json_encode([
            'status' => 1,
            'data' => $answers
        ]);
    }



    // ENDPOIN FOR BACKGROUND CHECK EXAM STILL RUNNING OR NOT

    public function cleanupSessions()
    {
        $threshold = date('Y-m-d H:i:s', strtotime('-2 minutes'));
        $this->db->where('status', 'active')
            ->where('last_ping_at <', $threshold)
            ->update('exam_sessions', [
                'status' => 'aborted',
                'status_reason' => 'timeout',
                'ended_at' => date('Y-m-d H:i:s')
            ]);
    }


}