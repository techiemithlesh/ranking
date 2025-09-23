<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Live_exam_model extends MY_Model
{

    public function __construct()
    {
        parent::__construct();
    }


    public function examListLiveDT($postData, $currency_symbol = '')
    {
        $response = array();
        $sessionID = get_session_id();

        // read value
        $draw = $postData['draw'];
        $start = $postData['start'];
        $rowperpage = $postData['length'];
        $searchValue = $postData['search']['value'];

        // order
        $columnIndex = empty($postData['order'][0]['column']) ? 0 : $postData['order'][0]['column'];
        $columnSortOrder = empty($postData['order'][0]['dir']) ? 'DESC' : $postData['order'][0]['dir'];
        $column_order = array('`online_exam`.`id`');

        $search_arr = array();
        $searchQuery = "";

        if ($searchValue != '') {
            $search_arr[] = " (`online_exam`.`title` like '%" . $searchValue . "%' OR `online_exam`.`exam_start` like '%" . $searchValue . "%' OR `online_exam`.`exam_end` like '%" . $searchValue . "%') ";
        }

        // WHERE CLAUSE BUILD
        if (is_superadmin_loggedin()) {
            $whereClause = " WHERE `online_exam`.`session_id` = '$sessionID' AND is_live = 1";
        } else {
            $branchID = $this->db->escape(get_loggedin_branch_id());
            $whereClause = " WHERE `online_exam`.`session_id` = '$sessionID' AND is_live = 1 AND (
            `online_exam`.`created_by_branch` = $branchID 
            OR `online_exam`.`id` IN (
                SELECT `exam_id` FROM `exam_assignment` WHERE `branch_id` = $branchID
            )
         )";
        }

        // Append search filter
        if (!empty($search_arr)) {
            $searchQuery = implode(" AND ", $search_arr);
            $whereClause .= " AND " . $searchQuery;
        }

        // Total records without filtering (build fresh WHERE clause without search filter)
        if (is_superadmin_loggedin()) {
            $countWhere = " WHERE `online_exam`.`session_id` = '$sessionID' AND  is_live=1";
        } else {
            $branchID = $this->db->escape(get_loggedin_branch_id());
            $countWhere = " WHERE `online_exam`.`session_id` = '$sessionID' AND is_live=1 AND (
            `online_exam`.`created_by_branch` = $branchID 
            OR `online_exam`.`id` IN (
                SELECT `exam_id` FROM `exam_assignment` WHERE `branch_id` = $branchID
            )
        )";
        }

        // Total records without filter
        $sql = "SELECT `id` FROM `online_exam` " . $countWhere;
        $records = $this->db->query($sql)->result();
        $totalRecords = count($records);

        // Total records with filtering
        $sql = "SELECT `id` FROM `online_exam` " . $whereClause;
        $records = $this->db->query($sql)->result();
        $totalRecordwithFilter = count($records);

        // Fetch paginated records
        $sql = "SELECT `online_exam`.*, `class`.`name` as `class_name`,
        (SELECT COUNT(`id`) FROM `questions_manage` WHERE `questions_manage`.`onlineexam_id`=`online_exam`.`id`) as `questions_qty`,
        `branch`.`name` as `branchname`
        FROM `online_exam`
        LEFT JOIN `branch` ON `branch`.`id` = `online_exam`.`created_by_branch`
        LEFT JOIN `class` ON `class`.`id` = `online_exam`.`class_id`
        $whereClause
        ORDER BY " . $column_order[$columnIndex] . " $columnSortOrder
        LIMIT $start, $rowperpage";

        $records = $this->db->query($sql)->result();

        // log_message('debug', $this->db->last_query());

        $data = array();
        $count = $start + 1;

        foreach ($records as $record) {
            $status = ($record->publish_status == 1) ? 'checked' : '';
            $row = array();
            $action = "";

            $action .= '<a href="' . base_url('onlineexam/question_list/' . $record->id) . '" class="btn btn-circle btn-default icon" data-toggle="tooltip" data-original-title="' . translate('view') . " " . translate('question') . '"> <i class="fa fa-list"></i></a>';

            if ($record->publish_status == 0) {
                $action .= '<a href="' . base_url('onlineexam/manage_question/' . $record->id) . '" class="btn btn-circle btn-default icon" data-toggle="tooltip" data-original-title="' . translate('add_questions') . '"> <i class="fas fa-question"></i></a>';
                /**
                 * Branch Assignment.
                 */
                $action .= '<button class="btn btn-circle btn-info icon" data-toggle="tooltip" title="Assign Branch" onclick="openAssignBranchModal(' . $record->id . ')"><i class="fas fa-code-branch"></i></button>';
            }

            if ($record->publish_status == 1) {
                $action .= '<a href="' . base_url('LiveExam/host/' . $record->id) . '" class="btn btn-circle btn-success icon" data-toggle="tooltip" data-original-title="' . translate('Host Live Exam') . '"> <i class="fas fa-broadcast-tower"></i></a>';
            }

            $row[] = $count++;
            if (is_superadmin_loggedin()) {
                if (empty($record->created_by_branch)) {
                    $row[] = '<span class="label label-success">Global</span>';
                } else {
                    $row[] = $record->branchname;
                }
            }
            $row[] = $record->title;
            $row[] = $record->class_name . " (" . getSectionDetails($record->section_id) . ")";
            $row[] = $record->questions_qty;
            $row[] = _d($record->exam_start) . "<p class='text-muted'>" . date("h:i A", strtotime($record->exam_start)) . "</p>";
            $row[] = _d($record->exam_end) . "<p class='text-muted'>" . date("h:i A", strtotime($record->exam_end)) . "</p>";
            $row[] = $record->duration;
            $row[] = $record->exam_type == 0 ? translate('free') : $currency_symbol . $record->fee;
            $row[] = '<div class="material-switch ml-xs">
                <input class="exam-status" id="examstatus_' . $record->id . '" data-id="' . $record->id . '" name="exam_status' . $record->id . '" type="checkbox" ' . $status . ' />
                <label for="examstatus_' . $record->id . '" class="label-primary"></label>
              </div>';
            $row[] = $action;
            $data[] = $row;
        }

        return json_encode(array(
            "draw" => intval($draw),
            "recordsTotal" => $totalRecords,
            "recordsFiltered" => $totalRecordwithFilter,
            "data" => $data,
        ));
    }


    // Model Method to check if branch has exam assigned
    public function isBranchExamAssigned($examID)
    {
        $branchID = get_loggedin_branch_id(); // returns int or null
        if (empty($branchID)) {
            return false;
        }

        // Use Query Builder and ensure examID is integer
        $this->db->select('id');
        $this->db->from('exam_assignment');
        $this->db->where('exam_id', $examID);
        $this->db->where('branch_id', $branchID);
        $row = $this->db->get()->row();

        // Also allow if exam is created for this branch
        if (!empty($row)) {
            return true;
        }

        $this->db->select('id');
        $this->db->from('online_exam');
        $this->db->where('id', $examID);
        $this->db->where('created_by_branch', $branchID);
        $row2 = $this->db->get()->row();

        return !empty($row2);
    }

    public function getExamDetailsForLive($onlineexamID)
    {
        // We expect $onlineexamID as integer (from controller). Validate.
        $onlineexamID = intval($onlineexamID);
        $sessionID = get_session_id();

        // If superadmin, we will allow host only if exam is global or has assignments/branch.
        $isSuper = is_superadmin_loggedin();
        $branchID = get_loggedin_branch_id();

        // Build base query
        $this->db->select('online_exam.*,
        class.name as class_name,
        branch.name as branchname,
        (SELECT COUNT(id) FROM questions_manage WHERE questions_manage.onlineexam_id = online_exam.id) as questions_qty');
        $this->db->from('online_exam');
        $this->db->join('class', 'class.id = online_exam.class_id', 'left');
        $this->db->join('branch', 'branch.id = online_exam.created_by_branch', 'left');
        $this->db->where('online_exam.session_id', $sessionID);
        $this->db->where('online_exam.publish_status', 1);
        $this->db->where('online_exam.id', $onlineexamID);

        // For non-superadmin: restrict to branch or assignment
        if (!$isSuper) {
            // Branch must match created_by_branch OR the exam is assigned to this branch
            $this->db->group_start();
            $this->db->where('online_exam.created_by_branch', $branchID);
            $this->db->or_where("online_exam.id IN (SELECT exam_id FROM exam_assignment WHERE branch_id = {$this->db->escape($branchID)})", null, false);
            $this->db->group_end();
        } else {
            // For superadmin: allow, but later we will check that the exam is either global or assigned somewhere
        }

        $record = $this->db->get()->row();

        if (!$record) {
            return [];
        }

        // If caller is superadmin, ensure the exam is either global or assigned to at least one branch
        if ($isSuper) {
            $hasCreatedBranch = !empty($record->created_by_branch);
            $assignedCount = $this->db->select('COUNT(*) as cnt')
                ->from('exam_assignment')
                ->where('exam_id', $onlineexamID)
                ->get()
                ->row()->cnt;

            if (!$hasCreatedBranch && intval($assignedCount) === 0) {
                // Not allowed for host: exam is orphan (neither created for a branch nor assigned)
                return [];
            }
        }

        // No section check here (host side). Return the record object.
        return $record;
    }

    public function getActiveSessionByExam($exam_id)
    {
        return $this->db->from('live_exam_sessions')
            ->where('exam_id', intval($exam_id))
            ->where('status', 'active')
            ->order_by('started_at', 'DESC')
            ->get()->row();
    }

    // public function getParticipantsBySession($session_id)
    // {
    //     $threshold = date('Y-m-d H:i:s', strtotime('-15 seconds'));

    //     return $this->db->select('
    //         s.id as student_id,
    //         CONCAT(s.first_name, " ", s.last_name) as student_name,
    //         s.register_no,
    //         ess.status as live_status,
    //         ess.joined_at')
    //         ->from('exam_session_students ess')
    //         ->join('student s', 's.id = ess.student_id')
    //         ->where('ess.session_id', $session_id)
    //         ->where('ess.last_ping_at >=', $threshold)
    //         ->order_by('ess.joined_at', 'ASC')
    //         ->get()
    //         ->result();
    // }

    public function getParticipantsBySession($session_id)
    {
        return $this->db->select('
            s.id as student_id,
            CONCAT(s.first_name, " ", s.last_name) as student_name,
            s.register_no,
            ess.status as live_status,
            ess.joined_at,
            ess.last_ping_at')
            ->from('exam_session_students ess')
            ->join('student s', 's.id = ess.student_id')
            ->where('ess.session_id', $session_id)
            ->order_by('ess.joined_at', 'ASC')
            ->get()
            ->result();
    }

    public function createSession($examID, $hostID, $hostRole, $question_id)
    {
        // generate codes
        $sessionCode = strtoupper(substr(md5(uniqid(rand(), true)), 0, 6));
        $sessionToken = bin2hex(random_bytes(16)); // 32 chars  

        $data = [
            'exam_id' => intval($examID),
            'host_id' => intval($hostID),
            'host_role' => $hostRole,
            'session_code' => $sessionCode,
            'session_token' => $sessionToken,
            'current_question_id' => $question_id,
            'status' => 'waiting',
            'started_at' => date('Y-m-d H:i:s')
        ];

        $this->db->insert('exam_sessions', $data);

        if ($this->db->affected_rows() > 0) {
            $data['id'] = $this->db->insert_id();
            return (object) $data; // return session object
        }

        return false;
    }

    public function setCurrentQuestion($session_id, $question_id)
    {
        $this->db->where('id', intval($session_id));
        $this->db->update('exam_sessions', [
            'current_question_id' => intval($question_id),
        ]);

        return $this->db->affected_rows() > 0;
    }


    public function getSession($session_id)
    {
        return $this->db->get_where('exam_sessions', ['id' => intval($session_id)])->row();
    }

    public function getSessionWithStatus($session_id)
    {
        return $this->db
            ->select('id, exam_id, host_id, session_code, status, status_reason, started_at, ended_at, current_question_id, is_published')
            ->from('exam_sessions')
            ->where('id', intval($session_id))
            ->get()
            ->row();
    }

    public function getSessionByCode($session_code)
    {
        return $this->db->where('session_code', $session_code)
            ->where('status', 'active')
            ->get('exam_sessions')
            ->row();
    }


    public function addStudentToSession($session_id, $student_id)
    {
        $existing = $this->db
            ->where('session_id', $session_id)
            ->where('student_id', $student_id)
            ->get('exam_session_students')
            ->row();

        if ($existing) {
            // Resume: just mark active again
            $this->db->where('id', $existing->id)
                ->update('exam_session_students', [
                    'last_ping_at' => date('Y-m-d H:i:s'),
                    'status' => 'active'
                ]);
            return $existing->id;
        } else {
            // First join
            $this->db->insert('exam_session_students', [
                'session_id' => $session_id,
                'student_id' => $student_id,
                'joined_at' => date('Y-m-d H:i:s'),
                'last_ping_at' => date('Y-m-d H:i:s'),
                'status' => 'active'
            ]);
            return $this->db->insert_id();
        }
    }
    // public function endSession($session_id, $host_id)
    // {
    //     // Verify ownership
    //     $this->db->where('id', $session_id);
    //     $this->db->where('host_id', $host_id);
    //     $session = $this->db->get('exam_sessions')->row();

    //     if (!$session) {
    //         return false; // not found or not owned by this host
    //     }

    //     $this->db->where('id', $session_id);
    //     return $this->db->update('exam_sessions', [
    //         'status' => 'completed',
    //         'status_reason' => 'normal_end',
    //         'ended_at' => date('Y-m-d H:i:s')
    //     ]);
    // }

    public function endSession($session_id, $host_id, $aborted = 0, $publish = 0)
    {
        // Verify ownership
        $this->db->where('id', $session_id);
        $this->db->where('host_id', $host_id);
        $session = $this->db->get('exam_sessions')->row();

        if (!$session) {
            return false;
        }

        $update = [
            'status' => $aborted ? 'aborted' : 'completed',
            'status_reason' => $aborted ? 'aborted_by_host' : 'normal_end',
            'ended_at' => date('Y-m-d H:i:s'),
            'is_published' => $aborted ? 0 : $publish,
        ];

        $this->db->where('id', $session_id);
        return $this->db->update('exam_sessions', $update);
    }



    // STUDENT
    public function liveExamListForStudentDT($postData, $currency_symbol = '')
    {
        $response = array();
        $sessionID = get_session_id();

        $draw = intval($postData['draw'] ?? 1);
        $start = intval($postData['start'] ?? 0);
        $rowperpage = intval($postData['length'] ?? 10);
        $searchValue = $postData['search']['value'] ?? '';

        $columnIndex = $postData['order'][0]['column'] ?? 0;
        $columnSortOrder = $postData['order'][0]['dir'] ?? 'DESC';

        $columns = [
            0 => 'oe.id',
            1 => 'oe.title',
            2 => 'class.name',
            3 => 'subject.name',
            4 => 'oe.questions_qty',
            5 => 'oe.exam_start',
            6 => 'oe.exam_end',
            7 => 'oe.duration',
            8 => 'sess.status'
        ];
        $orderBy = $columns[$columnIndex] ?? 'oe.id';

        // Student details
        $enrollID = $this->session->userdata('enrollID');
        $enroll = $this->db->where('id', $enrollID)->get('enroll')->row();
        $branch_id = get_loggedin_branch_id();
        $class_id = $enroll->class_id;
        $section_id = $enroll->section_id;

        // Exams assigned to branch
        $assignedExamIDs = $this->db->select('exam_id')
            ->where('branch_id', $branch_id)
            ->get('exam_assignment')
            ->result_array();
        $assignedExamIDs = array_column($assignedExamIDs, 'exam_id');

        // ---- Base Query ----
        $this->db->select('
        oe.id as exam_id,
        oe.title,
        oe.exam_start,
        oe.exam_end,
        oe.duration,
        oe.section_id,
        oe.subject_id,
        class.name as class_name,
        subj.name as subject_name,
        (SELECT COUNT(id) FROM questions_manage WHERE onlineexam_id = oe.id) as questions_qty,
        sess.id as session_id,
        sess.session_code,
        sess.status as session_status
        ');
        $this->db->from('online_exam as oe');
        $this->db->join('class', 'class.id = oe.class_id', 'left');
        $this->db->join('subject as subj', 'subj.id = oe.subject_id', 'left');
        $this->db->join('exam_sessions as sess', 'sess.exam_id = oe.id AND sess.status="active"', 'left');

        $this->db->where('oe.session_id', $sessionID);
        $this->db->where('oe.publish_status', 1);
        $this->db->where('oe.is_live', 1);
        $this->db->where('oe.class_id', $class_id);
        $this->db->group_start();
        $this->db->where('oe.created_by_branch', $branch_id);
        if (!empty($assignedExamIDs)) {
            $this->db->or_where_in('oe.id', $assignedExamIDs);
        }
        $this->db->group_end();

        // Search
        if (!empty($searchValue)) {
            $this->db->group_start();
            $this->db->like('oe.title', $searchValue);
            $this->db->or_like('oe.exam_start', $searchValue);
            $this->db->or_like('oe.exam_end', $searchValue);
            $this->db->group_end();
        }

        // Count filtered
        $totalRecordwithFilter = $this->db->count_all_results('', false);

        // Order + Limit
        $this->db->order_by($orderBy, $columnSortOrder);
        if ($rowperpage != -1) {
            $this->db->limit($rowperpage, $start);
        }

        $query = $this->db->get();
        $records = $query->result();

        // log_message('debug', 'the query is: '. $this->db->last_query());

        // Count total
        $totalRecords = $totalRecordwithFilter;

        // ---- Build Data ----
        $data = [];
        $count = $start + 1;

        foreach ($records as $record) {
            // Filter by section
            $array = json_decode($record->section_id, true);
            if ((is_array($array) && !in_array($section_id, $array)) && $record->section_id != $section_id) {
                continue;
            }

            // Status
            $status = '<span class="label label-danger">' . translate('inactive') . '</span>';
            $action = '';
            if ($record->session_status === 'active') {
                $status = '<span class="label label-success">' . translate('active') . '</span>';
                $action = '<a href="' . base_url('liveexam_student/join/' . $record->session_code) . '" 
                          class="btn btn-circle btn-success btn-sm" 
                          title="' . translate('join_exam') . '">
                          <i class="fas fa-sign-in-alt"></i></a>';
            }

            $row = [];
            $row[] = $count++;
            $row[] = $record->title;
            $row[] = $record->class_name . " (" . $this->onlineexam_model->getSectionDetails($record->section_id) . ")";
            $row[] = $this->onlineexam_model->getSubjectDetails($record->subject_id);
            $row[] = $record->questions_qty;
            $row[] = _d($record->exam_start) . "<p class='text-muted'>" . date("h:i A", strtotime($record->exam_start)) . "</p>";
            $row[] = _d($record->exam_end) . "<p class='text-muted'>" . date("h:i A", strtotime($record->exam_end)) . "</p>";
            $row[] = $record->duration;
            $row[] = $status;
            $row[] = $action;

            $data[] = $row;
        }

        // Response
        $response = [
            "draw" => $draw,
            "recordsTotal" => $totalRecords,
            "recordsFiltered" => $totalRecordwithFilter,
            "data" => $data,
        ];

        return json_encode($response);
    }

    public function getQuestionById($question_id, $exam_id)
    {
        $sql = "
        SELECT qm.*, q.*, q.id AS qus_id, ranked.question_index
        FROM questions_manage qm
        JOIN questions q ON q.id = qm.question_id
        JOIN (
            SELECT question_id, ROW_NUMBER() OVER (ORDER BY qm.id ASC) AS question_index
            FROM questions_manage qm
            WHERE qm.onlineexam_id = ?
        ) ranked ON ranked.question_id = qm.question_id
        WHERE qm.onlineexam_id = ? AND qm.question_id = ?
        LIMIT 1
    ";

        return $this->db->query($sql, [$exam_id, $exam_id, $question_id])->row();
    }

    public function getExamQuestions($exam_id)
    {
        return $this->db->select('qm.id as qm_id, qm.question_id, q.question, q.question_type')
            ->from('questions_manage qm')
            ->join('questions q', 'q.id = qm.question_id')
            ->where('qm.onlineexam_id', $exam_id)
            ->order_by('qm.id', 'ASC')
            ->get()
            ->result();
    }

    public function getAnswersBySession($session_id, $question_id)
    {
        return $this->db->select("
            esa.*, 
            CONCAT(s.first_name, ' ', s.last_name) as student_name, 
            s.register_no, 
            q.question as question_text
        ")
            ->from('exam_session_answers esa')
            ->join('student s', 's.id = esa.student_id')
            ->join('questions q', 'q.id = esa.question_id')
            ->where('esa.session_id', $session_id)
            ->where('esa.question_id', $question_id)
            ->order_by('esa.submitted_at', 'DESC')
            ->get()
            ->result();
    }
    public function cleanupInactiveStudents($session_id)
    {
        $threshold = date("Y-m-d H:i:s", strtotime("-15 seconds"));

        $this->db->where('session_id', $session_id)
            ->where('status', 'active')
            ->where('last_ping_at <', $threshold)
            ->update('exam_session_students', ['status' => 'offline']);
    }

    /**
     * Helper to compare arrays for multiple-choice answers
     */
    private function array_equal($a, $b)
    {
        if (is_array($a) && is_array($b)) {
            sort($a);
            sort($b);
            return $a == $b;
        }
        return false;
    }


    public function getLiveExamSessionReport_F($session_code, $studentID)
    {
        // 1. Get exam + session + student info
        $exam = $this->db->select('
            oe.id as exam_id, 
            oe.title as exam_name, 
            oe.neg_mark, 
            oe.passing_mark, 
            es.started_at, 
            es.ended_at, 
            ess.joined_at, 
            ess.last_ping_at
        ')
            ->from('exam_sessions es')
            ->join('online_exam oe', 'oe.id = es.exam_id', 'inner')
            ->join('exam_session_students ess', 'ess.session_id = es.id AND ess.student_id = ' . $this->db->escape($studentID), 'inner')
            ->where('es.session_code', $session_code)
            ->get()
            ->row_array();

        if (empty($exam)) {
            return [];
        }

        $examID = $exam['exam_id'];
        $examTitle = $exam['exam_name'];
        $neg_mark_enabled = (int) $exam['neg_mark'] === 1;
        $passing_mark = (float) $exam['passing_mark'];

        // 2. Fetch all questions & answers
        $sql = "
        SELECT 
            qm.*, 
            q.id as qus_id, 
            q.question, 
            q.type, 
            q.mark as marks,
            q.answer,
            esa.answer as sb_ans, 
            esa.id as ans_id
        FROM exam_sessions es
        INNER JOIN questions_manage qm 
            ON qm.onlineexam_id = es.exam_id
        INNER JOIN questions q 
            ON q.id = qm.question_id
        LEFT JOIN exam_session_answers esa 
            ON esa.session_id = es.id 
           AND esa.question_id = q.id 
           AND esa.student_id = " . $this->db->escape($studentID) . "
        WHERE es.session_code = " . $this->db->escape($session_code) . "
        ORDER BY qm.id ASC
    ";

        $result = $this->db->query($sql)->result();

        // log_message('debug', 'The Query is: ' . $this->db->last_query());

        // 3. Initialize counters
        $total_marks = 0;
        $total_obtain_marks = 0;
        $total_neg_marks = 0;
        $correct_ans = 0;
        $wrong_ans = 0;
        $total_answered = 0;
        $total_question = 0;

        // 4. Process results
        if (!empty($result)) {
            $total_question = count($result);

            foreach ($result as $value) {
                $marks = (float) $value->marks;
                $total_marks += $marks;

                if (!empty($value->ans_id)) {
                    $total_answered++;

                    $isCorrect = false;

                    if ($value->type == 1 || $value->type == 3) {
                        // Single choice / True-False
                        $isCorrect = ($value->sb_ans == $value->answer);
                    } elseif ($value->type == 2) {
                        // Multiple choice
                        $isCorrect = $this->array_equal(json_decode($value->answer), json_decode($value->sb_ans));
                    } elseif ($value->type == 4) {
                        // Fill in the blank
                        $correctAns = strtolower(trim(str_replace(" ", "_", $value->answer)));
                        $studentAns = strtolower(trim(str_replace(" ", "_", $value->sb_ans)));
                        $isCorrect = ($correctAns == $studentAns);
                    }

                    if ($isCorrect) {
                        $correct_ans++;
                        $total_obtain_marks += $marks;
                    } else {
                        $wrong_ans++;
                        if ($neg_mark_enabled) {
                            $total_neg_marks += 1;   // ❗ adjust if penalty is % of marks
                            $total_obtain_marks -= 1;
                        }
                    }
                }
            }
        }

        // Prevent negative marks
        if ($total_obtain_marks < 0) {
            $total_obtain_marks = 0;
        }

        // 5. Time taken (student-specific)
        $time_taken = "N/A";
        if (!empty($exam['joined_at']) && !empty($exam['last_ping_at'])) {
            $start = strtotime($exam['joined_at']);
            $end = strtotime($exam['last_ping_at']);
            if ($end > $start) {
                $diff = $end - $start;
                $minutes = floor($diff / 60);
                $seconds = $diff % 60;
                $time_taken = $minutes . " min " . $seconds . " sec";
            }
        }

        // 6. Percentage & result
        $percentage = $total_marks > 0 ? round(($total_obtain_marks / $total_marks) * 100, 2) : 0;
        $result_status = ($total_obtain_marks >= $passing_mark) ? 'Pass' : 'Fail';

        // 7. Return report
        return [
            'exam_name' => $examTitle,
            'exam_date' => !empty($exam['started_at']) ? date("d M Y", strtotime($exam['started_at'])) : "N/A",
            'time_taken' => $time_taken,
            'total_marks' => $total_marks,
            'total_obtain_marks' => $total_obtain_marks,
            'total_neg_marks' => $total_neg_marks,
            'correct_ans' => $correct_ans,
            'wrong_ans' => $wrong_ans,
            'total_answered' => $total_answered,
            'total_question' => $total_question,
            'percentage' => $percentage,
            'result_status' => $result_status
        ];
    }

    public function getLiveExamSessionReport($session_code, $studentID)
    {
        // 1. Get exam + session + student info
        $exam = $this->db->select('
            oe.id as exam_id, 
            oe.title as exam_name, 
            oe.neg_mark, 
            oe.passing_mark, 
            es.started_at, 
            es.ended_at, 
            ess.joined_at, 
            ess.last_ping_at
        ')
            ->from('exam_sessions es')
            ->join('online_exam oe', 'oe.id = es.exam_id', 'inner')
            ->join('exam_session_students ess', 'ess.session_id = es.id AND ess.student_id = ' . $this->db->escape($studentID), 'inner')
            ->where('es.session_code', $session_code)
            ->get()
            ->row_array();

        if (empty($exam)) {
            return [];
        }

        $examID = $exam['exam_id'];
        $examTitle = $exam['exam_name'];
        $neg_mark_enabled = (int) $exam['neg_mark'] === 1;
        $passing_mark = (float) $exam['passing_mark'];

        // 2. Fetch all questions & answers
        $sql = "
        SELECT 
            qm.*, 
            q.id as qus_id, 
            q.question, 
            q.type, 
            q.mark as marks, 
            q.answer, 
            esa.answer as sb_ans, 
            esa.id as ans_id
        FROM exam_sessions es
        INNER JOIN questions_manage qm 
            ON qm.onlineexam_id = es.exam_id
        INNER JOIN questions q 
            ON q.id = qm.question_id
        LEFT JOIN exam_session_answers esa 
            ON esa.session_id = es.id 
           AND esa.question_id = q.id 
           AND esa.student_id = " . $this->db->escape($studentID) . "
        WHERE es.session_code = " . $this->db->escape($session_code) . "
        ORDER BY qm.id ASC
    ";

        $result = $this->db->query($sql)->result();

        // 3. Initialize counters
        $total_marks = 0;
        $total_obtain_marks = 0;
        $total_neg_marks = 0;
        $correct_ans = 0;
        $wrong_ans = 0;
        $total_answered = 0;
        $total_question = 0;

        // 4. Process results
        if (!empty($result)) {
            $total_question = count($result);

            foreach ($result as $value) {
                $marks = (float) $value->marks;
                $total_marks += $marks;

                if (!empty($value->ans_id)) {
                    $total_answered++;

                    $isCorrect = false;

                    if ($value->type == 1 || $value->type == 3) {
                        $isCorrect = ($value->sb_ans == $value->answer);
                    } elseif ($value->type == 2) {
                        $isCorrect = $this->array_equal(json_decode($value->answer), json_decode($value->sb_ans));
                    } elseif ($value->type == 4) {
                        $correctAns = strtolower(trim(str_replace(" ", "_", $value->answer)));
                        $studentAns = strtolower(trim(str_replace(" ", "_", $value->sb_ans)));
                        $isCorrect = ($correctAns == $studentAns);
                    }

                    if ($isCorrect) {
                        $correct_ans++;
                        $total_obtain_marks += $marks;
                    } else {
                        $wrong_ans++;
                        if ($neg_mark_enabled) {
                            $total_neg_marks += 1;
                            $total_obtain_marks -= 1;
                        }
                    }
                }
            }
        }

        if ($total_obtain_marks < 0) {
            $total_obtain_marks = 0;
        }

        // 5. Time taken
        $time_taken = "N/A";
        if (!empty($exam['joined_at']) && !empty($exam['last_ping_at'])) {
            $start = strtotime($exam['joined_at']);
            $end = strtotime($exam['last_ping_at']);
            if ($end > $start) {
                $diff = $end - $start;
                $minutes = floor($diff / 60);
                $seconds = $diff % 60;
                $time_taken = $minutes . " min " . $seconds . " sec";
            }
        }

        // 6. Percentage & result
        $percentage = $total_marks > 0 ? round(($total_obtain_marks / $total_marks) * 100, 2) : 0;
        $result_status = ($total_obtain_marks >= $passing_mark) ? 'Pass' : 'Fail';

        // 7. Rank calculation
        $rankData = $this->getStudentRank($session_code, $studentID);

        // 8. Return
        return [
            'exam_name' => $examTitle,
            'exam_date' => !empty($exam['started_at']) ? date("d M Y", strtotime($exam['started_at'])) : "N/A",
            'time_taken' => $time_taken,
            'total_marks' => $total_marks,
            'total_obtain_marks' => $total_obtain_marks,
            'total_neg_marks' => $total_neg_marks,
            'correct_ans' => $correct_ans,
            'wrong_ans' => $wrong_ans,
            'total_answered' => $total_answered,
            'total_question' => $total_question,
            'percentage' => $percentage,
            'result_status' => $result_status,
            'rank' => $rankData['rank'],
            'total_students' => $rankData['total_students']
        ];
    }

    public function getStudentRank($session_code, $studentID)
    {
        $sql = "
        SELECT esa.student_id, SUM(CASE WHEN esa.answer = q.answer THEN q.mark ELSE 0 END) as obtain_marks
        FROM exam_sessions es
        INNER JOIN questions_manage qm ON qm.onlineexam_id = es.exam_id
        INNER JOIN questions q ON q.id = qm.question_id
        LEFT JOIN exam_session_answers esa ON esa.session_id = es.id AND esa.question_id = q.id
        WHERE es.session_code = " . $this->db->escape($session_code) . "
        GROUP BY esa.student_id
        ORDER BY obtain_marks DESC
    ";

        $students = $this->db->query($sql)->result_array();
        $rank = null;
        $total_students = count($students);

        foreach ($students as $i => $s) {
            if ($s['student_id'] == $studentID) {
                $rank = $i + 1;
                break;
            }
        }

        return [
            'rank' => $rank,
            'total_students' => $total_students
        ];
    }






}