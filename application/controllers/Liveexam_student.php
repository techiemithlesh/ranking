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
        $this->data['title'] = translate('live_exams');
        $this->data['sub_page'] = 'userrole/liveexam/index';
        $this->data['main_menu'] = 'onlineexam';

        $this->load->view('layout/index', $this->data);
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

        echo json_encode(['status' => 1, 'current_step' => $session->current_question_id, 'html' => $html]);
    }



}