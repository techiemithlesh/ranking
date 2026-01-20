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
        $this->load->model('sms_model');
        $this->load->model('subject_model');
        $this->load->model('email_model');
        $this->load->model('leaderboard_model');
        $this->load->model('reward_model');
        $this->load->library('pdf');
        $this->load->library('ciqrcode');
        $this->load->library('reward_lib');
        $this->load->library('whatsapp_lib');

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

        if (!is_superadmin_loggedin()) {
            if (!$this->live_exam_model->isBranchExamAssigned($exam_id)) {
                set_alert('error', translate('You dont have permission to take this exam'));
                redirect(base_url('liveexam'));
            }
        }

        $exam = $this->live_exam_model->getExamDetailsForLive($exam_id);

        $totalStudents = $this->live_exam_model->countTotalStudentsInClass($exam_id);

        if (!$exam) {
            set_alert('error', translate('exam_not_found_or_not_allowed'));
            redirect(base_url('liveExam'));
        }

        // 🔹 ACTIVE SESSION CHECK
        $active_session = $this->db
            ->where([
                'exam_id' => $exam_id,
                'host_id' => get_loggedin_user_id(),
                'status'  => 'active'
            ])
            ->order_by('id', 'DESC')
            ->get('exam_sessions')
            ->row();

        $elapsed_seconds = 0;
        if ($active_session && $active_session->started_at) {
            $elapsed_seconds = time() - strtotime($active_session->started_at);
        }

        $this->data['headerelements'] = [
            'js' => ['js/online-exam.js'],
        ];

        $data['exam'] = $exam;
        $data['student_count'] = $totalStudents ?? 0;
        $data['active_session'] = $active_session;
        $data['elapsed_seconds'] = max(0, $elapsed_seconds);
        $data['grace_seconds'] = LIVE_EXAM_HOST_GRACE_SECONDS;
        $data['title'] = translate('host_live_exam');
        $data['sub_page'] = 'onlineexam/live_exam/host';
        $data['main_menu'] = 'onlineexam';

        $this->load->view('layout/index', $data);
    }

    public function goLive()
    {
        $session_id = $this->input->post('session_id');
        $first_qid  = $this->input->post('first_question_id');

        if (!$session_id || !$first_qid) {
            echo json_encode(['status' => 0]);
            return;
        }

        // 1. Update the database using MySQL's internal clock
        // This ensures go_live_at is exactly 10 seconds from "Now" in the DB
        $this->db->set('status', 'starting');
        $this->db->set('go_live_at', 'DATE_ADD(NOW(), INTERVAL 10 SECOND)', FALSE);
        $this->db->set('current_question_id', $first_qid);
        $this->db->set('current_step_version', 1);

        $this->db->where('id', $session_id);
        $this->db->where('status', 'waiting');
        $this->db->update('exam_sessions');

        // 2. Fetch the newly created time back from the DB to send to JavaScript
        $session = $this->db->select('go_live_at')
            ->where('id', $session_id)
            ->get('exam_sessions')
            ->row();

        $goLiveAt = $session->go_live_at;

        // live_exam_log('debug', "[LiveExam] Session {$session_id} set to 'starting' with go_live_at={$goLiveAt}");

        echo json_encode(['status' => 1, 'go_live_at' => $goLiveAt]);
    }


    public function ajaxGetQuestions()
    {
        // live_exam_log('debug', '[LiveExam] Host requesting exam questions via AJAX');

        $examID = (int) $this->input->post('exam_id');
        $hostID = (int) get_loggedin_user_id();

        $exam = $this->live_exam_model->getExamDetailsForLive($examID);
        // live_exam_log('debug', '[LiveExam] Fetched exam details: ' . json_encode($exam));

        if (!$exam) {
            // live_exam_log('debug', '[LiveExam] Exam not found for ID: ' . $examID);
            echo json_encode(['status' => 0, 'message' => 'Exam not found']);
            return;
        }

        // IMPORTANT: reset builder
        $this->db->reset_query();

        // ONLY valid states
        $active_session = $this->db
            ->where('exam_id', $examID)
            ->where('host_id', $hostID)
            ->where_in('status', ['active', 'starting', 'waiting'])
            ->order_by('id', 'DESC')
            ->limit(1)
            ->get('exam_sessions')
            ->row();

        // live_exam_log('debug', '[LiveExam] Fetched active session: ' . json_encode($active_session));
        // live_exam_log('debug', '[LiveExam] Active session query: ' . $this->db->last_query());

        if (!$active_session) {
            // live_exam_log('debug', '[LiveExam] No active session found for exam ID: ' . $examID);
            echo json_encode(['status' => 0, 'message' => 'Session not found']);
            return;
        }

        if ($active_session->status === 'starting' && (!empty($active_session->go_live_at) && strtotime($active_session->go_live_at) <= time())) {
            // live_exam_log('debug', "[LiveExam] Auto-activating session ID: {$active_session->id}");
            $this->live_exam_model->autoActivateSession($active_session->id);
            $active_session = $this->live_exam_model->getSession($active_session->id);
            // live_exam_log('debug', '[LiveExam] Session after auto activate: ' . json_encode($active_session));
        }


        // --- NEW: TIME SYNC LOGIC ---
        $elapsed_seconds = 0;
        if ($active_session->status === 'active' && !empty($active_session->started_at)) {
            // Calculate exactly how many seconds have passed since the exam started
            $elapsed_seconds = time() - strtotime($active_session->started_at);
        }

        // GRACE
        $active_session = $this->live_exam_model->handleGraceTimeout($active_session);
        if ($active_session->status === 'aborted') {
            echo json_encode([
                'status' => 0,
                'status_code' => 'aborted',
                'session_status' => 'aborted',
                'message' => 'Session aborted'
            ]);
            return;
        }

        $questions = $this->live_exam_model
            ->getExamQuestions($exam->id, $exam->question_type);

        $page = $this->load->view(
            'onlineexam/live_exam/ajax_start',
            ['exam' => $exam, 'questions' => $questions],
            true
        );

        if ($active_session) {
            $current_index = 1;
            foreach ($questions as $i => $q) {
                if ($q->question_id == $active_session->current_question_id) {
                    $current_index = $i + 1;
                    break;
                }
            }

            echo json_encode([
                'status' => 1,
                'resume' => 1,
                'session_id' => $active_session->id,
                'session_code' => $active_session->session_code,
                'session_status' => $active_session->status,
                'go_live_at' => $active_session->go_live_at,
                'elapsed_seconds' => max(0, $elapsed_seconds),
                'join_link' => base_url('Liveexam_student/join/' . $active_session->session_code),
                'current_question_id' => $active_session->current_question_id,
                'current_index' => $current_index,
                'page' => $page
            ]);
            return;
        }

        // 🔹 FRESH START
        $now = time();
        if (strtotime($exam->exam_start) <= $now && $now <= strtotime($exam->exam_end)) {
            echo json_encode([
                'status' => 1,
                'resume' => 0,
                'total_questions' => count($questions),
                'page' => $page
            ]);
        } else {
            echo json_encode([
                'status' => 0,
                'message' => 'Exam expired or inactive'
            ]);
        }
    }


    public function startSession()
    {
        // live_exam_log('debug', '[LiveExam] Starting new session via host request');
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
        // live_exam_log('debug', '[LiveExam] New session created: ' . json_encode($session));

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
        // log_message('debug', '[LiveExam] Activating session via host request');
        $session_id = $this->input->post('session_id');

        $this->db->where('id', $session_id)
            ->where('status', 'waiting')
            ->update('exam_sessions', [
                'started_at' => date('Y-m-d H:i:s')
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

        $ok = $this->live_exam_model->setCurrentQuestionHost($session_id, $question_id);

        if ($ok) {
            echo json_encode(['status' => 1, 'message' => 'Current question updated']);
        } else {
            echo json_encode(['status' => 0, 'message' => 'Failed to update']);
        }
    }

    public function endSession()
    {
        $session_id = $this->input->post('session_id');
        $aborted = (int) $this->input->post('aborted');
        $publish = (int) $this->input->post('publish');

        if (empty($session_id)) {
            echo json_encode(['status' => 0, 'message' => 'Missing session id']);
            return;
        }

        // End session
        $ok = $this->live_exam_model->endSession($session_id, get_loggedin_user_id(), $aborted, $publish);
        if (!$ok) {
            echo json_encode(['status' => 0, 'message' => 'Failed to end session']);
            return;
        }

        // Get session details
        $session = $this->live_exam_model->getSession($session_id);
        if (empty($session)) {
            echo json_encode(['status' => 0, 'message' => 'Session not found']);
            return;
        }

        $session_code = $session->session_code;
        $exam_id = $session->exam_id;
        $exam_type = 'live_exam';

        $exam_name = get_type_tittle_by_id('online_exam', $exam_id);

        // Only compute and reward if published & not aborted
        if ($publish && !$aborted && $session_code) {

            // Compute leaderboard
            // live_exam_log('debug', "[LiveExam] Computing leaderboard for session {$session_code}");
            $this->leaderboard_model->computeLeaderboard($session_code);

            // live_exam_log('debug', "[LiveExam] Processing rewards and WhatsApp notifications for session {$session_code}");

            $leaderboard = $this->leaderboard_model->getAllRankBySession($session_code);

            // live_exam_log('debug', "[LiveExam] Leaderboard data: " . json_encode($leaderboard));

            $rewardCount = 0;
            $sentCount = 0;

            foreach ($leaderboard as $entry) {

                $student_id = $entry['student_id'];
                $performance = [
                    'percentage' => (float) $entry['percentage'],
                    'percentile' => (float) $entry['percentile'],
                    'rank' => (int) $entry['rank_position'],
                ];

                // live_exam_log('debug', "[RewardFlow] Checking student={$student_id} perf=" . json_encode($performance));

                // ✅ Process reward
                $granted = $this->reward_lib->processExamReward(
                    $student_id,
                    $exam_id,
                    $exam_type,
                    $performance,
                    $session_code
                );
                if ($granted) {
                    $rewardCount++;
                }
            }

            // live_exam_log('debug', "[LiveExam] ✅ {$rewardCount} rewards granted for session {$session_code}");
        }

        // Final response
        echo json_encode([
            'status' => 1,
            'message' => 'Session ended successfully',
            'redirect_url' => base_url("LiveExam/leaderboard/" . $session_code)
        ]);
    }

    public function getParticipants()
    {
        $session_id = $this->input->get('session_id');

        $this->live_exam_model->cleanupInactiveStudents($session_id);

        $participants = $this->live_exam_model->getParticipantsBySession($session_id);

        echo json_encode([
            'status' => 1,
            'participants' => $participants,
            'total' => count($participants)
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
    /* ENDPOINT FOR BACKGROUND CHECK EXAM STILL RUNNING OR NOT */
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

    /**
     * REPORTING 
     */
    public function getSessionReport()
    {
        if (!get_permission('live_exam', 'is_view')) {
            access_denied();
        }

        if (isset($_POST['search'])) {
            $branchID = $this->application_model->get_branch_id();
            if (is_superadmin_loggedin() == true) {
                $this->form_validation->set_rules('branch_id', 'Branch', 'trim|required');
            }
            $this->form_validation->set_rules('class_id', 'Class', 'trim|required');
            $this->form_validation->set_rules('section_id', 'Section', 'trim|required');
            $this->form_validation->set_rules('session_code', 'Session', 'trim|required');

            if ($this->form_validation->run() == true) {
                $classID = $this->input->post('class_id');
                $sectionId = $this->input->post('section_id');
                $sessionCode = $this->input->post('session_code');
                $this->data['reports'] = $this->live_exam_model->getSessionReportForAdmin($sessionCode, $branchID, $classID, $sectionId);
            }
        }
        $this->data['session_code'] = $sessionCode;
        $this->data['title'] = translate('live_exam_report');
        $this->data['sub_page'] = 'onlineexam/live_exam/report';
        $this->data['main_menu'] = 'onlineexam';
        $this->load->view('layout/index', $this->data);
    }

    public function getLiveExamByClass()
    {
        $html = '';
        $classID = $this->input->post('class_id');
        $branchID = get_loggedin_branch_id();
        $sessionID = get_session_id();

        if (!empty($classID)) {
            $this->db->select('oe.id, oe.title');
            $this->db->from('online_exam oe');
            $this->db->join('exam_assignment ea', 'ea.exam_id = oe.id', 'left');

            $this->db->where('oe.publish_status', 1);
            $this->db->where('oe.is_live', 1);
            $this->db->where('oe.session_id', $sessionID);
            $this->db->where('oe.class_id', $classID);

            // 🔑 Restrict only if NOT super-Admin
            if (!is_superadmin_loggedin()) {
                $this->db->group_start();
                // Exam created by the same branch
                $this->db->where('oe.created_by_branch', $branchID);
                // OR exam assigned to this branch
                $this->db->or_where('ea.branch_id', $branchID);
                $this->db->group_end();
            }

            $this->db->group_by('oe.id');
            $query = $this->db->get();

            if ($query->num_rows() > 0) {
                $exams = $query->result();
                $html .= '<option value="">' . translate('select') . '</option>';
                foreach ($exams as $row) {
                    $html .= '<option value="' . $row->id . '">' . $row->title . '</option>';
                }
            } else {
                $html .= '<option value="">' . translate('no_information_available') . '</option>';
            }
        } else {
            $html .= '<option value="">' . translate('select') . '</option>';
        }

        echo $html;
    }

    public function getSessionsByExam()
    {
        $exam_id = $this->input->post('exam_id');
        $sessions = $this->live_exam_model->getSessionsByExam($exam_id);

        $options = "<option value=''>" . translate('select') . "</option>";
        foreach ($sessions as $s) {
            $start = date('d M Y - h:i A', strtotime($s['started_at']));
            $end = !empty($s['ended_at']) ? date('h:i A', strtotime($s['ended_at'])) : 'Ongoing';
            $label = $start . " to " . $end;

            // Time slot label
            $hour = date('H', strtotime($s['started_at']));
            if ($hour < 12) {
                $slot = "(Morning)";
            } elseif ($hour < 17) {
                $slot = "(Afternoon)";
            } else {
                $slot = "(Evening)";
            }
            $options .= "<option value='{$s['session_code']}'>{$label} {$slot}</option>";
        }

        echo $options;
    }

    public function getSessionsByExamWithAll()
    {
        $exam_id = $this->input->post('exam_id');
        $sessions = $this->live_exam_model->getSessionsByExam($exam_id);

        // Add "All Sessions" as the first option
        $options = "<option value=''>" . translate('all_sessions') . "</option>";

        foreach ($sessions as $s) {
            $start = date('d M Y - h:i A', strtotime($s['started_at']));
            $end = !empty($s['ended_at']) ? date('h:i A', strtotime($s['ended_at'])) : 'Ongoing';
            $label = $start . " to " . $end;

            // Time slot label
            $hour = date('H', strtotime($s['started_at']));
            if ($hour < 12) {
                $slot = "(" . translate('morning') . ")";
            } elseif ($hour < 17) {
                $slot = "(" . translate('afternoon') . ")";
            } else {
                $slot = "(" . translate('evening') . ")";
            }

            $options .= "<option value='{$s['session_code']}'>{$label} {$slot}</option>";
        }

        echo $options;
    }

    public function leaderboard($sessionCode)
    {
        if (empty($sessionCode)) {
            set_alert('error', 'Invalid session code');
            return redirect(base_url('LiveExam'));
        }

        $limitTopN = 3;
        $this->data['topStudents'] = $this->leaderboard_model->getTopN($sessionCode, $limitTopN);

        $pageLimit = 50;
        $page = (int) $this->input->get('page') ?? 1;
        $offset = ($page - 1) * $pageLimit;

        $this->data['otherStudents'] = $this->leaderboard_model->getRankPage($sessionCode, $offset, $pageLimit, $limitTopN);

        // Total students count for pagination
        $this->data['totalStudents'] = $this->leaderboard_model->countLeaderboard($sessionCode);

        $this->data['pageLimit'] = $pageLimit;
        $this->data['currentPage'] = $page;
        $this->data['session_code'] = $sessionCode;

        $this->data['title'] = translate('leaderboard');
        $this->data['sub_page'] = 'onlineexam/live_exam/leaderboard';
        $this->data['main_menu'] = 'onlineexam';
        $this->load->view('layout/index', $this->data);
    }

    public function leaderboardReport()
    {
        $branchID = $this->application_model->get_branch_id();
        $classID = null;
        $sectionId = null;
        $examId = null;
        $sessionCode = null;

        if (isset($_POST['search'])) {
            if (is_superadmin_loggedin() == true) {
                $this->form_validation->set_rules('branch_id', 'Branch', 'trim|required');
            }
            $this->form_validation->set_rules('class_id', 'Class', 'trim|required');
            $this->form_validation->set_rules('section_id', 'Section', 'trim|required');
            $this->form_validation->set_rules('exam_id', 'Exam', 'trim|required');
            $this->form_validation->set_rules('session_code', 'Session', 'trim');

            if ($this->form_validation->run() == true) {
                $classID = $this->input->post('class_id');
                $sectionId = $this->input->post('section_id');
                $examId = $this->input->post('exam_id');
                $sessionCode = $this->input->post('session_code');
                $sessionCode = !empty($sessionCode) ? $sessionCode : null;

                $this->data['reports'] = $this->leaderboard_model->getAllRank(
                    $branchID,
                    $classID,
                    $sectionId,
                    $examId,
                    $sessionCode
                );
            }
        }

        $this->data['classId'] = $classID;
        $this->data['sectionId'] = $sectionId;
        $this->data['examID'] = $examId;
        $this->data['sessionCode'] = $sessionCode;

        $this->data['title'] = translate('leaderboard_report');
        $this->data['sub_page'] = 'onlineexam/live_exam/leaderboard_report';
        $this->data['main_menu'] = 'onlineexam';
        $this->load->view('layout/index', $this->data);
    }

    /**WHATSAPP INTGERATION */
    public function generateAndSaveReportPdf($sessionCode, $studentId)
    {
        $this->db->reset_query();

        // Fetch student data
        $data['student'] = $this->application_model->getStudentDetails($studentId);
        $branch_id = $data['student']['branch_id'];
        $data['branchData'] = $this->db->where('id', $branch_id)->get('branch')->row_array() ?? [];

        // Get exam report
        $data['report'] = $this->live_exam_model->getLiveExamSessionReport($sessionCode, $studentId);

        // ✅ Generate QR Code
        $qrText = base_url("Liveexam_student/verify?session=" . $sessionCode . "&student=" . $studentId);
        $params = [
            'data' => $qrText,
            'level' => 'H',
            'size' => 5,
            'savename' => FCPATH . "uploads/qrcodes/" . $studentId . "_" . $sessionCode . ".png"
        ];
        $this->ciqrcode->generate($params);
        $data['qr_code'] = base_url("uploads/qrcodes/" . $studentId . "_" . $sessionCode . ".png");

        // ✅ Create Chart URL
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

        // ✅ Render the HTML view
        $html = $this->load->view('userrole/liveexam/report_pdf', $data, true);

        // ✅ Generate PDF
        $this->pdf->loadHtml($html);
        $this->pdf->setPaper('A4', 'portrait');
        $this->pdf->render();

        // ✅ Prepare file name
        $studentName = preg_replace('/[^A-Za-z0-9_-]/', '', str_replace(' ', '_', $data['student']['first_name'] . '_' . $data['student']['last_name']));
        $examName = preg_replace('/[^A-Za-z0-9_-]/', '', str_replace(' ', '_', $data['report']['exam_name']));
        $fileName = "{$studentName}_{$examName}_{$sessionCode}.pdf";

        // ✅ Save path
        $saveDir = FCPATH . "uploads/reportcards/";
        if (!file_exists($saveDir)) {
            mkdir($saveDir, 0777, true);
        }

        $savePath = $saveDir . $fileName;

        // ✅ Save the file
        file_put_contents($savePath, $this->pdf->output());

        // ✅ Return useful info
        return [
            'path' => $savePath,
            'url' => base_url('uploads/reportcards/' . $fileName),
            'file' => $fileName
        ];
    }
    public function test_whatsapp()
    {
        $this->load->library('whatsapp_lib');
        $response = $this->whatsapp_lib->send_text('917667043372', 'Hi Mithlesh Your Coding is awesome', 'test', 0);

        echo '<pre>';
        print_r($response);
    }

    public function test_media_send()
    {
        $number = '917667043372';
        $caption = 'Hi! Please check your attached test report card.';
        $pdf_url = base_url('uploads/reportcards/sample.pdf');

        $response = $this->whatsapp_lib->send_media(
            $number,
            $caption,
            $pdf_url,
            'test_media',
            0
        );

        echo '<pre>';
        print_r($response);
    }
}
