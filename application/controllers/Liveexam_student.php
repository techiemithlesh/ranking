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

class Liveexam_student extends Public_Controller
{
    public function __construct()
    {
        parent::__construct();

        // figure out which method is being called
        $method = strtolower($this->router->fetch_method() ?? '');

        // ✅ Only enforce login if not "verify"
        if ($method !== 'verify') {
            if (!is_student_loggedin() && !is_parent_loggedin()) {
                $this->session->set_userdata('redirect_url', current_url());
                redirect(base_url('authentication'), 'refresh');
            }
        }

        $this->load->model('live_exam_model');
        $this->load->model('onlineexam_model');
        $this->load->model('leaderboard_model');
        $this->load->library('pdf');
        $this->load->library('ciqrcode');


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

    public function getExamListDT()
    {
        if ($_POST) {
            $this->load->model('onlineexam_model');
            $postData = $this->input->post();
            $currencySymbol = $this->data['global_config']['currency_symbol'];
            echo $this->live_exam_model->liveExamListForStudentDT($postData, $currencySymbol);

        }
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

    public function join($session_code)
    {
        if (!is_student_loggedin()) {
            access_denied();
        }

        // 🔹 Find the live session
        $session = $this->live_exam_model->getSessionByCode($session_code);
        if (empty($session) || !in_array($session->status, ['active', 'waiting'])) {
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
        $session = $this->live_exam_model->getSessionWithStatus($session_id);

        if (!$session) {
            echo json_encode([
                'status' => 0,
                'code' => 'invalid',
                'message' => 'Invalid session',
                'is_published' => 0,
                'session_code' => null
            ]);
            return;
        }

        // 🔹 End states
        if ($session->status === 'completed') {
            echo json_encode([
                'status' => 0,
                'code' => 'completed',
                'message' => 'Thank you for attending the exam. Your result will be processed soon.',
                'is_published' => (int) $session->is_published,
                'session_code' => $session->session_code
            ]);
            return;
        }

        if ($session->status === 'aborted') {
            echo json_encode([
                'status' => 0,
                'code' => 'aborted',
                'message' => 'The exam was aborted by the host.',
                'is_published' => (int) $session->is_published,
                'session_code' => $session->session_code
            ]);
            return;
        }

        if ($session->status !== 'active') {
            echo json_encode([
                'status' => 0,
                'code' => 'inactive',
                'message' => 'Session ended or inactive',
                'is_published' => (int) $session->is_published,
                'session_code' => $session->session_code
            ]);
            return;
        }

        // 🔹 Waiting for host
        if (empty($session->current_question_id)) {
            echo json_encode([
                'status' => 0,
                'code' => 'waiting',
                'message' => 'Waiting for host to start...',
                'is_published' => (int) $session->is_published,
                'session_code' => $session->session_code
            ]);
            return;
        }

        // 🔹 Fetch current question
        $question = $this->live_exam_model->getQuestionById(
            $session->current_question_id,
            $session->exam_id
        );

        if (!$question) {
            echo json_encode([
                'status' => 0,
                'code' => 'no_question',
                'message' => 'No question available',
                'is_published' => (int) $session->is_published,
                'session_code' => $session->session_code
            ]);
            return;
        }

        // 🔹 Fetch student's answer
        $student_id = get_loggedin_user_id();
        $answerRow = $this->db
            ->where([
                'session_id' => $session->id,
                'question_id' => $question->id,
                'student_id' => $student_id
            ])
            ->get('exam_session_answers')
            ->row_array();

        $student_answer = null;
        if ($answerRow) {
            if ($question->type == 2) { // multi-select
                $student_answer = json_decode($answerRow['answer'], true);
            } else {
                $student_answer = $answerRow['answer'];
            }
        }

        $data = [
            'question' => $question,
            'exam_id' => $session->exam_id,
            'session_id' => $session->id,
            'exam' => $this->live_exam_model->getExamDetailsForLive($session->exam_id),
            'student_answer' => $student_answer
        ];

        $html = $this->load->view('userrole/liveexam/_question', $data, true);

        echo json_encode([
            'status' => 1,
            'code' => 'active',
            'current_step' => $session->current_question_id,
            'current_index' => $question->question_index,
            'is_published' => (int) $session->is_published,
            'session_code' => $session->session_code,
            'html' => $html
        ]);
    }

    /**
     * LIVE EXAM QUESTION ANSER SUBMIT
     */

    public function submitAnswer()
    {
        if (!is_student_loggedin()) {
            echo json_encode(['status' => 0, 'message' => 'Not authorized']);
            return;
        }

        $studentID = get_loggedin_user_id();
        $sessionID = $this->input->post('session_id');
        $questionID = $this->input->post('question_id');
        $answers = $this->input->post('answer');

        if (empty($sessionID) || empty($questionID) || empty($answers)) {
            echo json_encode(['status' => 0, 'message' => 'Missing parameters']);
            return;
        }

        $session = $this->live_exam_model->getSession($sessionID);
        if (!$session || $session->status !== 'active') {
            echo json_encode(['status' => 0, 'message' => 'Exam ended or inactive']);
            return;
        }

        // determine answer value
        $answerValue = null;
        if (!empty($answers[$questionID])) {
            $qData = $answers[$questionID];
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

        if ($answerValue === null) {
            echo json_encode(['status' => 0, 'message' => 'No valid answer found']);
            return;
        }

        // save only in live exam session table
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

    /**
     * Heartbeat api for checking whether student is in exam or have left
     * @return void
     */
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
                    'last_ping_at' => date('Y-m-d H:i:s'),
                    'status' => 'active'
                ]);

        echo json_encode(['status' => 1]);
    }

    /**
     * Exam Leave API
     * @return void
     */
    public function leaveSession()
    {
        $session_id = $this->input->post('session_id');
        $student_id = get_loggedin_user_id();

        if (!$session_id || !$student_id) {
            echo json_encode(['status' => 0, 'message' => 'Missing parameters']);
            return;
        }

        $this->db->where([
            'session_id' => $session_id,
            'student_id' => $student_id
        ])->update('exam_session_students', [
                    'status' => 'left',
                    'last_ping_at' => date('Y-m-d H:i:s')
                ]);

        echo json_encode(['status' => 1]);
    }

    public function studentReport($sessionCode = null)
    {
        if (!is_student_loggedin()) {
            set_alert('info', 'You are not authorised to check this report !');
            return redirect(base_url('liveexam_student'));
        }

        $studentId = get_loggedin_user_id();

        // Generate secure token
        $token = hash_hmac('sha256', $sessionCode . $studentId, $this->config->item('encryption_key'));

        $pdfUrl = base_url("Liveexam_student/pdfFile/$sessionCode/$studentId?token=$token");

        $viewerUrl = base_url("assets/pdfjs/web/viewer.html?file=" . urlencode($pdfUrl));
        $data['viewerUrl'] = $viewerUrl;
        $data['title'] = translate('exam_report_preview');

        $this->load->view('userrole/student/pdf_viewer', $data);
    }


    /**
     * Direct PDF Stream (used by pdfjs(mozilla))
     */
    public function pdfFile($sessionCode = null, $studentId = null)
    {
        $token = $this->input->get('token');
        $expected = hash_hmac('sha256', $sessionCode . $studentId, $this->config->item('encryption_key'));

        if ($token !== $expected) {
            show_error("Unauthorized access", 403);
        }

        // Clear output
        while (ob_get_level() > 0)
            ob_end_clean();

        $this->generateReportPdf($sessionCode, $studentId, true);
        exit;
    }

    /**
     * Generate & Stream PDF
     */
    private function generateReportPdf($sessionCode, $studentId, $isPreview = true)
    {
        $this->db->reset_query();

        // Get data
        $data['student'] = $this->application_model->getStudentDetails($studentId);
        $branch_id = $data['student']['branch_id'];

        $data['branchData'] = $this->db
            ->query("SELECT * FROM branch WHERE id='" . $branch_id . "'")
            ->row_array() ?? [];

        $data['report'] = $this->live_exam_model->getLiveExamSessionReport($sessionCode, $studentId);

        // ✅ QR Code
        $qrText = base_url("Liveexam_student/verify?session=" . $sessionCode . "&student=" . $studentId);
        $params['data'] = $qrText;
        $params['level'] = 'H';
        $params['size'] = 5;
        $params['savename'] = FCPATH . "uploads/qrcodes/" . $studentId . "_" . $sessionCode . ".png";
        $this->ciqrcode->generate($params);
        $data['qr_code'] = base_url("uploads/qrcodes/" . $studentId . "_" . $sessionCode . ".png");

        // ✅ Chart
        $chartUrl = "https://quickchart.io/chart?c=" . urlencode(json_encode([
            'type' => 'pie',
            'data' => [
                'labels' => ['Correct', 'Wrong', 'Unanswered'],
                'datasets' => [
                    [
                        'data' => [
                            $data['report']['correct_ans'],
                            $data['report']['wrong_ans'],
                            $data['report']['total_question'] - $data['report']['total_answered']
                        ]
                    ]
                ]
            ]
        ]));
        $data['chart_url'] = $chartUrl;

        // Render PDF HTML
        $html = $this->load->view('userrole/liveexam/report_pdf', $data, true);

        // PDF Setup
        $this->pdf->loadHtml($html);
        $this->pdf->setPaper('A4', 'portrait');
        $this->pdf->render();

        // File name
        $studentName = $data['student']['first_name'] . ' ' . $data['student']['last_name'];
        $safeStudentName = preg_replace('/[^A-Za-z0-9_-]/', '', str_replace(' ', '_', $studentName));
        $safeExamName = preg_replace('/[^A-Za-z0-9_-]/', '', str_replace(' ', '_', $data['report']['exam_name']));
        $safeSessionCode = preg_replace('/[^A-Za-z0-9_-]/', '', $sessionCode);
        $fileName = $safeStudentName . '_' . $safeExamName . '_' . $safeSessionCode . '.pdf';

        // ✅ Clear buffers
        if (ob_get_length()) {
            ob_end_clean();
        }

        // ✅ Force headers
        header("Content-Type: application/pdf");
        header("Cache-Control: public, must-revalidate, max-age=0");
        header("Pragma: public");
        header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");

        // ✅ Stream PDF
        $this->pdf->stream($fileName, ["Attachment" => $isPreview ? 0 : 1]);

        // ✅ Stop execution
        exit;
    }

    public function preview()
    {
        $sessionCode = $this->input->get('session');
        $studentId = $this->input->get('student');

        if (empty($sessionCode) || empty($studentId)) {
            set_alert('info', 'Invalid verification link.');
            return redirect(base_url('student/dashboard'));
        }

        $this->generateReportPdf($sessionCode, $studentId, true);
    }

    /**
     * ✅ FOR DOWNLOAD REPORT CARD
     */

    public function download()
    {
        $sessionCode = $this->input->get('session');
        $studentId = $this->input->get('student');
        $this->generateReportPdf($sessionCode, $studentId, false);
    }

    /**
     * ✅ For QR verification (preview mode, no login required)
     */
    public function verify()
    {
        $sessionCode = $this->input->get('session');
        $studentId = $this->input->get('student');

        if (empty($sessionCode) || empty($studentId)) {
            show_error("Invalid verification link.", 400);
        }
        $this->generateReportPdf($sessionCode, $studentId, true); // always preview
    }

    /**
     * Live exam session report card (Per Session)
     */
    public function myReports()
    {
        if (!is_student_loggedin() && !is_parent_loggedin()) {
            set_alert('error', 'You are not authorised to access this report');
            return redirect(base_url('dashboard'));
        }

        if (is_student_loggedin()) {
            $studentID = get_loggedin_user_id();
        } elseif (is_parent_loggedin()) {
            $studentID = get_activeChildren_id();
        }

        $examID = null;
        $sessionCode = null;
        $reports = [];

        if ($this->input->post('search')) {
            $this->form_validation->set_rules('exam_id', translate('Exam'), 'trim|required');
            $this->form_validation->set_rules('session_code', translate('Session'), 'trim|required');

            if ($this->form_validation->run() == true) {
                $examID = $this->input->post('exam_id');
                $sessionCode = $this->input->post('session_code');

                // get report
                $report = $this->live_exam_model->getSessionReportForStudent($studentID, $sessionCode);

                // make it iterable for view
                if (!empty($report)) {
                    $reports[] = $report;
                }
            }
        }

        $this->data['studentDetails'] = $this->application_model->getStudentDetails($studentID);
        $this->data['examID'] = $examID;
        $this->data['sessionCode'] = $sessionCode;
        $this->data['reports'] = $reports;
        $this->data['title'] = translate('live_exam');
        $this->data['main_menu'] = 'Live_exam';
        $this->data['sub_page'] = 'userrole/liveexam/reports/index';
        $this->load->view('layout/index', $this->data);
    }

    public function leaderboard($sessionCode)
    {
        if (empty($sessionCode)) {
            set_alert('error', 'Invalid session code');
            return redirect(base_url('dashboard'));
        }

        $studentId = get_loggedin_user_id();

        // Top N (3 by default)
        $limitTopN = 3;
        $this->data['topStudents'] = $this->leaderboard_model->getTopN($sessionCode, $limitTopN);

        // Current student’s rank
        $studentRank = $this->leaderboard_model->getStudentRank($sessionCode, $studentId);

        // Nearby students (+/- 5 positions around me)
        $nearbyStudents = [];
        if (!empty($studentRank)) {
            $nearbyStudents = $this->leaderboard_model->getNearbyStudents(
                $sessionCode,
                $studentRank['rank_position'],
                5
            );
        }

        $this->data['studentRank'] = $studentRank;
        $this->data['nearbyStudents'] = $nearbyStudents;
        $this->data['totalStudents'] = $this->leaderboard_model->countLeaderboard($sessionCode);

        $this->data['title'] = translate('leaderboard');
        $this->data['sub_page'] = 'userrole/liveexam/leaderboard';
        $this->data['main_menu'] = 'onlineexam';
        $this->load->view('layout/index', $this->data);
    }

    public function subjectLeaderboard()
    {
        if (!is_student_loggedin() && !is_parent_loggedin()) {
            set_alert('error', 'You are not authorised to access this report');
            return redirect(base_url('dashboard'));
        }

        if (is_student_loggedin()) {
            $studentID = get_loggedin_user_id();
        } else {
            $studentID = get_activeChildren_id();
        }

        $studentDetails = $this->application_model->getStudentDetails($studentID);

        $branchId = $studentDetails['branch_id'];
        $classId = $studentDetails['class_id'];
        $sectionId = $studentDetails['section_id'];

        $this->data['leaderboard'] = [];
        $subjectId = null;

        if ($this->input->post('search')) {

            $this->form_validation->set_rules('subject_id', translate('Subject'), 'trim|required');

            if ($this->form_validation->run() == true) {

                $subjectId = $this->input->post('subject_id');

                $this->data['leaderboard'] =
                    $this->leaderboard_model->getLiveExamSubjectRank(
                        $branchId,
                        $classId,
                        $sectionId,
                        $subjectId
                    );
            }
        }

        $this->data['studentDetails'] = $studentDetails;
        $this->data['subjectId'] = $subjectId;
        $this->data['title'] = translate('leaderboard');
        $this->data['sub_page'] = 'userrole/liveexam/reports/subjectranking';
        $this->data['main_menu'] = 'onlineexam';

        $this->load->view('layout/index', $this->data);
    }



}