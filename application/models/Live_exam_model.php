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


    public function getExamQuestions($exam_id)
    {
        $exam_id = intval($exam_id);
        $this->db->select('id, question, option1, option2, option3, option4');
        $this->db->from('questions_manage');
        $this->db->where('onlineexam_id', $exam_id);
        $this->db->order_by('id', 'ASC');
        return $this->db->get()->result_array();
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


    public function createSession($examID, $hostID, $hostRole)
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


    private function getSectionDetails($section_json)
    {
        $arr = json_decode($section_json, true);
        $nameList = [];
        if (json_last_error() == JSON_ERROR_NONE && is_array($arr)) {
            foreach ($arr as $sec) {
                $nameList[] = get_type_name_by_id('section', $sec);
            }
        }
        return implode(', ', $nameList);
    }

}