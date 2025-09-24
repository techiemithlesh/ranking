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

    public function studentReport_f($sessionCode = null)
    {

        if (!is_student_loggedin()) {
            set_alert('info', 'You are not authorised to check this report !');
            return redirect(base_url('liveexam_student'));
        }


        $studentId = get_loggedin_user_id();
        $branch_id = get_loggedin_branch_id();

        $data['student'] = $this->application_model->getStudentDetails($studentId);

        $data['branchData'] = $this->db->query("SELECT * FROM branch WHERE id='" . $branch_id . "'")->row_array() ?? [];

        $data['report'] = $this->live_exam_model->getLiveExamSessionReport($sessionCode, $studentId);
        // printVar($data['report']);
        // die;
        $html = $this->load->view('userrole/liveexam/report_pdf', $data, true);

        // Generate PDF
        $this->load->library('pdf');
        $this->pdf->loadHtml($html);
        $this->pdf->setPaper('A4', 'portrait');
        $this->pdf->render();

        // ✅ Build unique file name
        $studentName = isset($data['student']['first_name']) ?
            $data['student']['first_name'] . ' ' . $data['student']['last_name'] : 'Student';

        $examName = !empty($data['report']['exam_name']) ? $data['report']['exam_name'] : 'Exam';

        // Remove spaces/special chars for file safety
        $safeStudentName = preg_replace('/[^A-Za-z0-9_-]/', '', str_replace(' ', '_', $studentName));
        $safeExamName = preg_replace('/[^A-Za-z0-9_-]/', '', str_replace(' ', '_', $examName));
        $safeSessionCode = preg_replace('/[^A-Za-z0-9_-]/', '', $sessionCode);

        $fileName = $safeStudentName . '_' . $safeExamName . '_' . $safeSessionCode . '.pdf';

        // Preview in browser (0 = preview, 1 = download)
        $this->pdf->stream($fileName, array("Attachment" => 1));
    }


    // public function studentReport($sessionCode = null)
    // {
    //     if (!is_student_loggedin()) {
    //         set_alert('info', 'You are not authorised to check this report !');
    //         return redirect(base_url('liveexam_student'));
    //     }

    //     $studentId = get_loggedin_user_id();
    //     $branch_id = get_loggedin_branch_id();

    //     $data['student'] = $this->application_model->getStudentDetails($studentId);
    //     $data['branchData'] = $this->db->query("SELECT * FROM branch WHERE id='" . $branch_id . "'")->row_array() ?? [];
    //     $data['report'] = $this->live_exam_model->getLiveExamSessionReport($sessionCode, $studentId);

    //     // ✅ QR Code
    //     $this->load->library('ciqrcode');
    //     $qrText = base_url("report/verify?session=" . $sessionCode . "&student=" . $studentId);
    //     $params['data'] = $qrText;
    //     $params['level'] = 'H';
    //     $params['size'] = 5;
    //     $params['savename'] = FCPATH . "uploads/qrcodes/" . $studentId . "_" . $sessionCode . ".png";
    //     $this->ciqrcode->generate($params);
    //     $data['qr_code'] = base_url("uploads/qrcodes/" . $studentId . "_" . $sessionCode . ".png");

    //     // ✅ Chart
    //     $chartUrl = "https://quickchart.io/chart?c=" . urlencode(json_encode([
    //         'type' => 'pie',
    //         'data' => [
    //             'labels' => ['Correct', 'Wrong', 'Unanswered'],
    //             'datasets' => [
    //                 [
    //                     'data' => [
    //                         $data['report']['correct_ans'],
    //                         $data['report']['wrong_ans'],
    //                         $data['report']['total_question'] - $data['report']['total_answered']
    //                     ]
    //                 ]
    //             ]
    //         ]
    //     ]));
    //     $data['chart_url'] = $chartUrl;

    //     // Generate HTML
    //     $html = $this->load->view('userrole/liveexam/report_pdf', $data, true);

    //     // PDF
    //     $this->load->library('pdf');
    //     $this->pdf->loadHtml($html);
    //     $this->pdf->setPaper('A4', 'portrait');
    //     $this->pdf->render();

    //     // File name
    //     $studentName = $data['student']['first_name'] . ' ' . $data['student']['last_name'];
    //     $safeStudentName = preg_replace('/[^A-Za-z0-9_-]/', '', str_replace(' ', '_', $studentName));
    //     $safeExamName = preg_replace('/[^A-Za-z0-9_-]/', '', str_replace(' ', '_', $data['report']['exam_name']));
    //     $safeSessionCode = preg_replace('/[^A-Za-z0-9_-]/', '', $sessionCode);

    //     $fileName = $safeStudentName . '_' . $safeExamName . '_' . $safeSessionCode . '.pdf';

    //     $this->pdf->stream($fileName, ["Attachment" => 0]);
    // }


    /**
     * ✅ Private helper to generate report PDF
     */
    private function generateReportPdf($sessionCode, $studentId, $isPreview = true)
    {

        // log_message('debug', 'The student id: ' . $studentId);
        // log_message('debug', 'The session code: ' . $sessionCode);

        $branch_id = get_loggedin_branch_id();

        $this->db->reset_query();

        // Get data
        $data['student'] = $this->application_model->getStudentDetails($studentId);
        $data['branchData'] = $this->db->query("SELECT * FROM branch WHERE id='" . $branch_id . "'")->row_array() ?? [];
        $data['report'] = $this->live_exam_model->getLiveExamSessionReport($sessionCode, $studentId);

        // log_message('debug', 'The Query Build' . $this->db->last_query());

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

        // Load HTML view
        $html = $this->load->view('userrole/liveexam/report_pdf', $data, true);

        // PDF
        $this->pdf->loadHtml($html);
        $this->pdf->setPaper('A4', 'portrait');

        // $options = $this->pdf->getOptions();
        // $options->set('debugPng', true);
        // $options->set('debugKeepTemp', true);
        // $options->set('debugCss', true);
        // $options->set('debugLayout', true);
        // $options->set('debugLayoutLines', true);
        // $options->set('debugLayoutBlocks', true);
        // $options->set('debugLayoutInline', true);
        // $options->set('debugLayoutPaddingBox', true);

        // $this->pdf->setOptions($options);

        file_put_contents(FCPATH . "uploads/debug_report.html", $html);



        $this->pdf->render();

        // File name
        $studentName = $data['student']['first_name'] . ' ' . $data['student']['last_name'];
        $safeStudentName = preg_replace('/[^A-Za-z0-9_-]/', '', str_replace(' ', '_', $studentName));
        $safeExamName = preg_replace('/[^A-Za-z0-9_-]/', '', str_replace(' ', '_', $data['report']['exam_name']));
        $safeSessionCode = preg_replace('/[^A-Za-z0-9_-]/', '', $sessionCode);

        $fileName = $safeStudentName . '_' . $safeExamName . '_' . $safeSessionCode . '.pdf';

        // Stream
        // $this->pdf->stream($fileName, ["Attachment" => $isPreview ? 0 : 1]);

        try {
            $this->pdf->render();
            $this->pdf->stream($fileName, ["Attachment" => $isPreview ? 0 : 1]);
        } catch (Exception $e) {
            log_message('error', 'DOMPDF error: ' . $e->getMessage());
            echo "<pre>DOMPDF crashed:\n" . $e->getMessage() . "\n</pre>";
        }

    }

    /**
     * ✅ For logged-in students (download mode)
     */
    public function studentReport($sessionCode = null)
    {
        log_message('debug', 'Controller Reached');
        if (!is_student_loggedin()) {
            set_alert('info', 'You are not authorised to check this report !');
            return redirect(base_url('liveexam_student'));
        }

        $studentId = get_loggedin_user_id();
        log_message('debug', 'The student Report Method' . $studentId);
        $this->generateReportPdf($sessionCode, $studentId, false); // force download
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



}