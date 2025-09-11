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
            $whereClause = " WHERE `online_exam`.`session_id` = '$sessionID' ";
        } else {
            $branchID = $this->db->escape(get_loggedin_branch_id());
            $whereClause = " WHERE `online_exam`.`session_id` = '$sessionID' AND (
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
            $countWhere = " WHERE `online_exam`.`session_id` = '$sessionID' ";
        } else {
            $branchID = $this->db->escape(get_loggedin_branch_id());
            $countWhere = " WHERE `online_exam`.`session_id` = '$sessionID' AND (
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

        log_message('debug', $this->db->last_query());

        $data = array();
        $count = $start + 1;

        foreach ($records as $record) {
            $status = ($record->publish_status == 1) ? 'checked' : '';
            $row = array();
            $action = "";

            $action .= '<a href="' . base_url('onlineexam/question_list/' . $record->id) . '" class="btn btn-circle btn-default icon" data-toggle="tooltip" data-original-title="' . translate('view') . " " . translate('question') . '"> <i class="fa fa-list"></i></a>';

            if ($record->publish_status == 0) {
                $action .= '<a href="' . base_url('onlineexam/manage_question/' . $record->id) . '" class="btn btn-circle btn-default icon" data-toggle="tooltip" data-original-title="' . translate('add_questions') . '"> <i class="fas fa-question"></i></a>';
            }

            // $action .= '<button class="btn btn-circle btn-success icon" data-toggle="tooltip" 
            //             title="Host Live Exam" onclick="hostLiveExam(' . $record->id . ')">
            //             <i class="fas fa-broadcast-tower"></i></button>';

            if ($record->publish_status == 1) {
                $action .= '<a href="' . base_url('liveexam/host/' . $record->id) . '" class="btn btn-circle btn-success icon" data-toggle="tooltip" data-original-title="' . translate('Host Live Exam') . '"> <i class="fas fa-broadcast-tower"></i></a>';
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

    public function getSessionStudents($session_id)
    {
        if (empty($session_id))
            return [];

        $this->db->select('ess.student_id, ess.joined_at, s.name, s.roll_no, s.admission_no');
        $this->db->from('exam_session_students as ess');
        $this->db->join('student as s', 's.id = ess.student_id', 'left');
        $this->db->where('ess.session_id', intval($session_id));
        $this->db->order_by('ess.joined_at', 'ASC');
        return $this->db->get()->result();
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
            'status' => 'active',
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

    public function getSessionByCode($session_code)
    {
        return $this->db->where('session_code', $session_code)
            ->where('status', 'active')
            ->get('exam_sessions')
            ->row();
    }


    public function addStudentToSession($session_id, $student_id)
    {
        $exists = $this->db->where('session_id', $session_id)
            ->where('student_id', $student_id)
            ->get('exam_session_students')
            ->row();
        if (!$exists) {
            $this->db->insert('exam_session_students', [
                'session_id' => $session_id,
                'student_id' => $student_id,
                'joined_at' => date('Y-m-d H:i:s')
            ]);
        }
    }

    public function endSession($session_id, $host_id)
    {
        // Verify ownership
        $this->db->where('id', $session_id);
        $this->db->where('host_id', $host_id);
        $session = $this->db->get('exam_sessions')->row();

        if (!$session) {
            return false; // not found or not owned by this host
        }

        $this->db->where('id', $session_id);
        return $this->db->update('exam_sessions', [
            'status' => 'completed',
            'ended_at' => date('Y-m-d H:i:s')
        ]);
    }


    // STUDENT
    public function liveExamListForStudentDT($postData, $currency_symbol = '')
    {
        $response = array();
        $sessionID = get_session_id();

        // Read datatable params
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
        $this->db->select('questions_manage.*, questions.id as qus_id, questions.*')
            ->from('questions_manage')
            ->join('questions', 'questions.id = questions_manage.question_id')
            ->where('questions_manage.onlineexam_id', $exam_id)
            ->where('questions_manage.question_id', $question_id)
            ->limit(1);

        return $this->db->get()->row();
    }



}