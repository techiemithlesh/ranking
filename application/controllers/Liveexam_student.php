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
    // public function getCurrentQuestion()
    // {
    //     $session_id = $this->input->get('session_id');
    //     $session = $this->live_exam_model->getSessionWithStatus($session_id);

    //     if (!$session) {
    //         echo json_encode([
    //             'status' => 0,
    //             'code' => 'invalid',
    //             'message' => 'Invalid session',
    //             'is_published' => 0,
    //             'session_code' => null
    //         ]);
    //         return;
    //     }

    //     // 🔹 Handle session end states first
    //     if ($session->status === 'completed') {
    //         echo json_encode([
    //             'status' => 0,
    //             'code' => 'completed',
    //             'message' => 'Thank you for attending the exam. Your result will be processed soon.',
    //             'is_published' => (int) $session->is_published,
    //             'session_code' => $session->session_code
    //         ]);
    //         return;
    //     }

    //     if ($session->status === 'aborted') {
    //         echo json_encode([
    //             'status' => 0,
    //             'code' => 'aborted',
    //             'message' => 'The exam was aborted by the host.',
    //             'is_published' => (int) $session->is_published,
    //             'session_code' => (int) $session->session_code
    //         ]);
    //         return;
    //     }

    //     if ($session->status !== 'active') {
    //         echo json_encode([
    //             'status' => 0,
    //             'code' => 'inactive',
    //             'message' => 'Session ended or inactive',
    //             'is_published' => (int) $session->is_published,
    //             'session_code' => (int) $session->session_code
    //         ]);
    //         return;
    //     }

    //     // 🔹 Waiting for host to start
    //     if (empty($session->current_question_id)) {
    //         echo json_encode([
    //             'status' => 0,
    //             'code' => 'waiting',
    //             'message' => 'Waiting for host to start...',
    //             'is_published' => (int) $session->is_published,
    //             'session_code' => (int) $session->session_code
    //         ]);
    //         return;
    //     }

    //     // 🔹 Fetch current question
    //     $question = $this->live_exam_model->getQuestionById(
    //         $session->current_question_id,
    //         $session->exam_id
    //     );

    //     if (!$question) {
    //         echo json_encode([
    //             'status' => 0,
    //             'code' => 'no_question',
    //             'message' => 'No question available',
    //             'is_published' => (int) $session->is_published,
    //             'session_code' => (int) $session->session_code
    //         ]);
    //         return;
    //     }


    //     /** 
    //      * STUDENT ANSWER
    //      */

    //     $student_id = get_loggedin_user_id();

    //     $answer = $this->db
    //         ->where([
    //             'session_id' => $session->id,
    //             'question_id' => $question->id,
    //             'student_id' => $student_id
    //         ])
    //         ->get('exam_session_answers')
    //         ->row_array();

    //     log_message('debug', 'The Student Answer'. $answer);

    //     $data = [
    //         'question' => $question,
    //         'exam_id' => $session->exam_id,
    //         'session_id' => $session->id,
    //         'exam' => $this->live_exam_model->getExamDetailsForLive($session->exam_id),
    //         'student_answer' => $answer ? $answer['answer'] : null
    //     ];

    //     $html = $this->load->view('userrole/liveexam/_question', $data, true);

    //     echo json_encode([
    //         'status' => 1,
    //         'code' => 'active',
    //         'current_step' => $session->current_question_id,
    //         'current_index' => $question->question_index,
    //         'is_published' => (int) $session->is_published,
    //         'session_code' => (int) $session->session_code,
    //         'html' => $html
    //     ]);
    // }

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

    // public function submitAnswer()
    // {
    //     if (!is_student_loggedin()) {
    //         echo json_encode(['status' => 0, 'message' => 'Not authorized']);
    //         return;
    //     }

    //     $studentID = get_loggedin_user_id();
    //     $online_examID = $this->input->post('online_exam_id');
    //     $sessionID = $this->input->post('session_id');
    //     $questionID = $this->input->post('question_id');
    //     $answers = $this->input->post('answer');

    //     if (empty($online_examID) || empty($sessionID) || empty($questionID) || empty($answers)) {
    //         echo json_encode(['status' => 0, 'message' => 'Missing parameters']);
    //         return;
    //     }

    //     $session = $this->live_exam_model->getSession($sessionID);

    //     if (!$session || $session->status !== 'active') {
    //         echo json_encode(['status' => 0, 'message' => 'Exam ended or inactive']);
    //         return;
    //     }

    //     $answerValue = null;


    //     if (!empty($answers[$questionID])) {
    //         $qData = $answers[$questionID]; // e.g. [1] => "2" for MCQ
    //         if (isset($qData[1])) {
    //             $answerValue = $qData[1]; // MCQ
    //         } elseif (isset($qData[2])) {
    //             $answerValue = json_encode($qData[2]); // Multi-select
    //         } elseif (isset($qData[3])) {
    //             $answerValue = $qData[3]; // True/False
    //         } elseif (isset($qData[4])) {
    //             $answerValue = $qData[4]; // Text
    //         }
    //     }

    //     if ($answerValue !== null) {
    //         $data = [
    //             'student_id' => $studentID,
    //             'online_exam_id' => $online_examID,
    //             'question_id' => $questionID,
    //             'answer' => $answerValue,
    //             'created_at' => date('Y-m-d H:i:s'),
    //         ];
    //     }

    //     $exists = $this->db->where([
    //         'student_id' => $studentID,
    //         'online_exam_id' => $online_examID,
    //         'question_id' => $questionID,
    //     ])->get('online_exam_answer')->row();

    //     if ($exists) {
    //         $this->db->where('id', $exists->id)->update('online_exam_answer', $data);
    //     } else {
    //         $this->db->insert('online_exam_answer', $data);
    //     }

    //     $liveData = [
    //         'session_id' => $sessionID,
    //         'student_id' => $studentID,
    //         'question_id' => $questionID,
    //         'answer' => $answerValue,
    //         'submitted_at' => date('Y-m-d H:i:s'),
    //     ];

    //     $existsLive = $this->db->where([
    //         'session_id' => $sessionID,
    //         'student_id' => $studentID,
    //         'question_id' => $questionID,
    //     ])->get('exam_session_answers')->row();

    //     if ($existsLive) {
    //         $this->db->where('id', $existsLive->id)->update('exam_session_answers', $liveData);
    //     } else {
    //         $this->db->insert('exam_session_answers', $liveData);
    //     }

    //     echo json_encode(['status' => 1, 'message' => 'Answer submitted successfully']);
    // }


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

    /**
     * ✅ Private helper to generate report PDF
     */
    private function generateReportPdf($sessionCode, $studentId, $isPreview = true)
    {
        $this->db->reset_query();

        // Get data
        $data['student'] = $this->application_model->getStudentDetails($studentId);
        $branch_id = $data['student']['branch_id'];

        $data['branchData'] = $this->db->query("SELECT * FROM branch WHERE id='" . $branch_id . "'")->row_array() ?? [];
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

        $html = $this->load->view('userrole/liveexam/report_pdf', $data, true);

        // PDF
        $this->pdf->loadHtml($html);
        $this->pdf->setPaper('A4', 'portrait');
        $this->pdf->render();

        // File name
        $studentName = $data['student']['first_name'] . ' ' . $data['student']['last_name'];
        $safeStudentName = preg_replace('/[^A-Za-z0-9_-]/', '', str_replace(' ', '_', $studentName));
        $safeExamName = preg_replace('/[^A-Za-z0-9_-]/', '', str_replace(' ', '_', $data['report']['exam_name']));
        $safeSessionCode = preg_replace('/[^A-Za-z0-9_-]/', '', $sessionCode);

        $fileName = $safeStudentName . '_' . $safeExamName . '_' . $safeSessionCode . '.pdf';

        // Stream  (0 preview 1 download)
        $this->pdf->stream($fileName, ["Attachment" => $isPreview ? 0 : 1]);
    }

    /**
     * ✅ For logged-in students (download mode)
     */
    // public function studentReport($sessionCode = null)
    // {
    //     if (!is_student_loggedin()) {
    //         set_alert('info', 'You are not authorised to check this report !');
    //         return redirect(base_url('liveexam_student'));
    //     }

    //     $studentId = get_loggedin_user_id();

    //     $this->generateReportPdf($sessionCode, $studentId, true); // preview
    // }

    public function studentReport($sessionCode = null)
    {
        if (!is_student_loggedin()) {
            set_alert('info', 'You are not authorised to check this report !');
            return redirect(base_url('liveexam_student'));
        }

        $studentId = get_loggedin_user_id();
        $pdfUrl = base_url('Liveexam_student/pdfFile/' . $sessionCode . '/' . $studentId);

        // Detect mobile from User-Agent
        $isMobile = preg_match('/Mobile|Android|iP(hone|od|ad)/i', $_SERVER['HTTP_USER_AGENT']);

        if ($isMobile) {
            // Google Docs Viewer for mobile
            $viewerUrl = "https://docs.google.com/gview?embedded=true&url=" . urlencode($pdfUrl);
            $data['viewerUrl'] = $viewerUrl;
            $data['title'] = translate('exam_report_preview');
            $this->load->view('userrole/student/pdf_viewer', $data);
        } else {
            // Normal inline PDF preview
            $this->generateReportPdf($sessionCode, $studentId, true);
        }
    }


    public function pdfFile($sessionCode = null, $studentId = null)
    {
        if (empty($sessionCode) || empty($studentId)) {
            set_alert('error', 'Invalid report request');
            return redirect(base_url('liveexam_student/myReports'));
        }

        // Always stream inline
        $this->generateReportPdf($sessionCode, $studentId, true);
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


}