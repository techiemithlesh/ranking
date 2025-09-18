<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @package : SchoolExcel School Management System
 * @version : 4.0
 * @developed by : Mithlesh Patel
 * @support : techie.mithlesh@gmail.com
 * @author url : http://codewithmithlesh.com
 * @filename : Liveexam_student.php
 * @copyright : Reserved Eduproject GlobalTech Team
 */

class Liveexam_student extends User_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('live_exam_model');
        $this->load->model('onlineexam_model');
    }

    public function index()
    {
        if (!is_student_loggedin()) {
            access_denied();
        }

        $this->data['headerelements'] = array(
            'js' => array(
                'js/online-exam.js',
            ),
        );

        $this->cleanupSessions();
        $this->data['title'] = translate('live_exams');
        $this->data['sub_page'] = 'userrole/liveexam/index';
        $this->data['main_menu'] = 'onlineexam';
        $this->load->view('layout/index', $this->data);
    }

    public function cleanupSessions()
    {
        $threshold = date('Y-m-d H:i:s', strtotime('-2 minutes'));

        // Clean active sessions with no heartbeat
        $this->db->where('status', 'active')
            ->where('last_ping_at <', $threshold)
            ->update('exam_sessions', [
                'status' => 'aborted',
                'status_reason' => 'timeout',
                'ended_at' => date('Y-m-d H:i:s')
            ]);

        // Clean waiting sessions never activated
        $this->db->where('status', 'waiting')
            ->where('created_at <', $threshold)
            ->update('exam_sessions', [
                'status' => 'aborted',
                'status_reason' => 'never_started',
                'ended_at' => date('Y-m-d H:i:s')
            ]);
    }

    public function getExamListDT()
    {
        if ($_POST) {
            $this->load->model('onlineexam_model');
            $postData = $this->input->post();
            $currencySymbol = $this->data['global_config']['currency_symbol'];
            echo $this->live_exam_model->liveExamListForStudentDT($postData, $currencySymbol);

        }
    }

    public function join($session_code)
    {
        if (!is_student_loggedin()) {
            access_denied();
        }

        ini_set('display_errors', 1);
        error_reporting(E_ALL);

        // 🔹 Find the live session
        $session = $this->live_exam_model->getSessionByCode($session_code);
        if (empty($session) || $session->status !== 'active') {
            set_alert('error', translate('invalid_or_expired_session'));
            redirect(base_url('liveexam_student'));
        }

        // 🔹 Fetch the exam details
        $exam = $this->live_exam_model->getExamDetailsForLive($session->exam_id);
        if (empty($exam)) {
            set_alert('error', translate('exam_not_found_or_not_allowed'));
            redirect(base_url('liveexam_student'));
        }

        // 🔹 Register the student in this session (if not already joined)
        $student_id = get_loggedin_user_id();
        $this->live_exam_model->addStudentToSession($session->id, $student_id);

        // 🔹 Prepare data for view
        $this->data['exam'] = $exam;
        $this->data['session'] = $session;
        $this->data['title'] = translate('join_live_exam');
        $this->data['sub_page'] = 'userrole/liveexam/take';
        $this->data['main_menu'] = 'onlineexam';

        $this->load->view('layout/index', $this->data);
    }

    public function getCurrentQuestion()
    {
        $session_id = $this->input->get('session_id');
        $session = $this->live_exam_model->getSession($session_id);

        if (!$session || $session->status !== 'active') {
            echo json_encode(['status' => 0, 'message' => 'Session ended or invalid']);
            return;
        }

        if (empty($session->current_question_id)) {
            echo json_encode(['status' => 0, 'message' => 'Waiting for host to start...']);
            return;
        }

        // Fetch single question
        $question = $this->live_exam_model->getQuestionById(
            $session->current_question_id,
            $session->exam_id
        );

        if (!$question) {
            echo json_encode(['status' => 0, 'message' => 'No question available']);
            return;
        }

        $data = [
            'question' => $question,
            'exam_id' => $session->exam_id,
            'session_id' => $session->id,
            'exam' => $this->live_exam_model->getExamDetailsForLive($session->exam_id)
        ];

        $html = $this->load->view('userrole/liveexam/_question', $data, true);

        echo json_encode(['status' => 1, 'current_step' => $session->current_question_id, 'current_index' => $question->question_index, 'html' => $html]);
    }

    public function submitAnswer()
    {
        if (!is_student_loggedin()) {
            echo json_encode(['status' => 0, 'message' => 'Not authorized']);
            return;
        }

        $studentID = get_loggedin_user_id();
        $online_examID = $this->input->post('online_exam_id');
        $sessionID = $this->input->post('session_id');
        $questionID = $this->input->post('question_id');
        $answers = $this->input->post('answer');

        if (empty($online_examID) || empty($sessionID) || empty($questionID) || empty($answers)) {
            echo json_encode(['status' => 0, 'message' => 'Missing parameters']);
            return;
        }

        $answerValue = null;


        if (!empty($answers[$questionID])) {
            $qData = $answers[$questionID]; // e.g. [1] => "2" for MCQ
            if (isset($qData[1])) {
                $answerValue = $qData[1]; // MCQ
            } elseif (isset($qData[2])) {
                $answerValue = json_encode($qData[2]); // Multi-select
            } elseif (isset($qData[3])) {
                $answerValue = $qData[3]; // True/False
            } elseif (isset($qData[4])) {
                $answerValue = $qData[4]; // Text
            }
        }

        if ($answerValue !== null) {
            $data = [
                'student_id' => $studentID,
                'online_exam_id' => $online_examID,
                'question_id' => $questionID,
                'answer' => $answerValue,
                'created_at' => date('Y-m-d H:i:s'),
            ];
        }

        $exists = $this->db->where([
            'student_id' => $studentID,
            'online_exam_id' => $online_examID,
            'question_id' => $questionID,
        ])->get('online_exam_answer')->row();

        if ($exists) {
            $this->db->where('id', $exists->id)->update('online_exam_answer', $data);
        } else {
            $this->db->insert('online_exam_answer', $data);
        }

        $liveData = [
            'session_id' => $sessionID,
            'student_id' => $studentID,
            'question_id' => $questionID,
            'answer' => $answerValue,
            'submitted_at' => date('Y-m-d H:i:s'),
        ];

        $existsLive = $this->db->where([
            'session_id' => $sessionID,
            'student_id' => $studentID,
            'question_id' => $questionID,
        ])->get('exam_session_answers')->row();

        if ($existsLive) {
            $this->db->where('id', $existsLive->id)->update('exam_session_answers', $liveData);
        } else {
            $this->db->insert('exam_session_answers', $liveData);
        }

        echo json_encode(['status' => 1, 'message' => 'Answer submitted successfully']);
    }

    public function studentHeartbeat()
    {
        $session_id = $this->input->post('session_id');
        $student_id = get_loggedin_user_id();

        if (!$session_id || !$student_id) {
            echo json_encode(['status' => 0, 'message' => 'Invalid data']);
            return;
        }

        $this->db->where([
            'session_id' => $session_id,
            'student_id' => $student_id
        ])->update('exam_session_students', [
                    'last_ping_at' => date('Y-m-d H:i:s')
                ]);

        echo json_encode(['status' => 1]);
    }



}