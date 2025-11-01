<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Onlineexam_model extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    // online exam save and update function
    public function saveExam($data, $branchID)
    {
        $onlineExam = array(
            'title' => $data['title'],
            'class_id' => $data['class_id'],
            'section_id' => json_encode($this->input->post('section')),
            'subject_id' => json_encode($this->input->post('subject')),
            'limits_participation' => $data['participation_limit'],
            'exam_start' => date('Y-m-d H:i:s', strtotime($data['start_date'] . " " . $data['start_time'])),
            'exam_end' => date('Y-m-d H:i:s', strtotime($data['end_date'] . " " . $data['end_time'])),
            'duration' => date('H:i:s', strtotime($data['duration'])),
            'mark_type' => $data['mark_type'],
            'passing_mark' => $data['passing_mark'],
            'instruction' => $data['instruction'],
            'session_id' => get_session_id(),
            'publish_result' => $data['publish_result'],
            'neg_mark' => (isset($data['negative_marking']) ? 1 : 0),
            'marks_display' => (isset($data['marks_display']) ? 1 : 0),
            'question_type' => $data['question_type'],
            'fee' => ($data['exam_type'] == 1 ? $data['exam_fee'] : 0),
            'exam_type' => $data['exam_type'],
            'is_live' => $data['is_live'],
            'created_by_branch' => $branchID,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        );
        if (!isset($_POST['id'])) {
            $onlineExam['publish_status'] = 0;
            $onlineExam['created_by'] = get_loggedin_user_id();
            $this->db->insert('online_exam', $onlineExam);
        } else {
            $this->db->where('id', $data['id']);
            $this->db->update('online_exam', $onlineExam);
        }
    }

    public function examList()
    {
        $this->db->select('online_exam.*,class.name as class_name,branch.name as branchname');
        $this->db->from('online_exam');
        $this->db->join('branch', 'branch.id = online_exam.branch_id', 'inner');
        $this->db->join('class', 'class.id = online_exam.class_id', 'left');
        if (!is_superadmin_loggedin()) {
            $this->db->where('online_exam.branch_id', get_loggedin_branch_id());
        }
        $this->db->order_by('online_exam.id', 'DESC');
        $result = $this->db->get()->result_array();
        foreach ($result as $key => $value) {
            $result[$key]['section_details'] = $this->getSectionDetails($value['section_id']);
        }
        return $result;
    }

    public function examListDT_old($postData, $currency_symbol = '')
    {
        $response = array();
        $sessionID = get_session_id();
        // read value
        $draw = $postData['draw'];
        $start = $postData['start'];
        $rowperpage = $postData['length']; // Rows display per page
        $searchValue = $postData['search']['value']; // Search value

        // order
        $columnIndex = empty($postData['order'][0]['column']) ? 0 : $postData['order'][0]['column']; // Column index
        $columnSortOrder = empty($postData['order'][0]['dir']) ? 'DESC' : $postData['order'][0]['dir']; // asc or desc
        $column_order = array('`online_exam`.`id`');

        $search_arr = array();
        $searchQuery = "";
        if ($searchValue != '') {
            $search_arr[] = " (`online_exam`.`title` like '%" . $searchValue . "%' OR `online_exam`.`exam_start` like '%" . $searchValue . "%' OR `online_exam`.`exam_end` like '%" . $searchValue . "%') ";
        }

        if (!is_superadmin_loggedin()) {
            $branchID = $this->db->escape(get_loggedin_branch_id());
            $search_arr[] = " `online_exam`.`branch_id` = $branchID ";
            if (!is_superadmin_loggedin() && !is_admin_loggedin()) {
                $search_arr[] = " `online_exam`.`created_by` = " . $this->db->escape(get_loggedin_user_id());
            }
        } else {
            $column_order[] = '`online_exam`.`id`';
        }

        // order
        $column_order[] = '`online_exam`.`title`';
        $column_order[] = '`class`.`id`';
        $column_order[] = '`questions_qty`';
        $column_order[] = '`online_exam`.`exam_start`';
        $column_order[] = '`online_exam`.`exam_end`';
        $column_order[] = '`online_exam`.`duration`';

        if (count($search_arr) > 0) {
            $searchQuery = implode("AND", $search_arr);
        }

        // Total number of records without filtering
        if (is_superadmin_loggedin()) {
            $sql = "SELECT `id` FROM `online_exam` WHERE `session_id` = '$sessionID'";
        } else {
            $branchID = $this->db->escape(get_loggedin_branch_id());
            $sql = "SELECT `id` FROM `online_exam` WHERE `branch_id` = $branchID AND `session_id` = '$sessionID'";
            if (!is_superadmin_loggedin() && !is_admin_loggedin()) {
                $sql .= " AND `created_by` = " . $this->db->escape(get_loggedin_user_id());
            }
        }
        $records = $this->db->query($sql)->result();
        $totalRecords = count($records);

        // Total number of record with filtering
        $sql = "SELECT `id` FROM `online_exam` WHERE `session_id` = '$sessionID'";
        if (!empty($searchQuery)) {
            $sql .= " AND " . $searchQuery;
        }
        $records = $this->db->query($sql)->result();
        $totalRecordwithFilter = count($records);

        // Fetch records
        $sql = "SELECT `online_exam`.*, `class`.`name` as `class_name`,(SELECT COUNT(`id`) FROM `questions_manage` WHERE `questions_manage`.`onlineexam_id`=`online_exam`.`id`) as `questions_qty`, `branch`.`name` as `branchname` FROM `online_exam` INNER JOIN `branch` ON `branch`.`id` = `online_exam`.`branch_id` LEFT JOIN `class` ON `class`.`id` = `online_exam`.`class_id` WHERE `online_exam`.`session_id` = '$sessionID'";
        if (!empty($searchQuery)) {
            $sql .= " AND " . $searchQuery;
        }
        $sql .= " ORDER BY " . $column_order[$columnIndex] . " $columnSortOrder LIMIT $start, $rowperpage";
        $records = $this->db->query($sql)->result();

        $data = array();
        $count = $start + 1;
        foreach ($records as $record) {
            if ($record->publish_status == 0) {
                $status = '';
            } else {
                $status = 'checked';
            }
            $row = array();
            $action = "";
            if (get_permission('add_questions', 'is_add')) {
                if ($record->publish_result == 0 && $record->publish_status == 1) {
                    $action .= '<button onclick="confirmModal(' . $this->db->escape(base_url('onlineexam/make_result_publish/' . $record->id)) . ')" class="btn btn-circle btn-default icon" data-toggle="tooltip" data-original-title="' . translate('make') . " " . translate('result_publish') . '"> <i class="fas fa-square-poll-vertical"></i></button>';
                }
            }
            $action .= '<a href="' . base_url('onlineexam/question_list/' . $record->id) . '" class="btn btn-circle btn-default icon" data-toggle="tooltip" data-original-title="' . translate('view') . " " . translate('question') . '"> <i class="fas fa-list-check"></i></a>';
            if ($record->publish_status == 0) {
                $action .= '<a href="' . base_url('onlineexam/manage_question/' . $record->id) . '" class="btn btn-circle btn-default icon" data-toggle="tooltip" data-original-title="' . translate('add_questions') . '"> <i class="fas fa-question"></i></a>';
            }
            if (get_permission('online_exam', 'is_edit')) {
                $action .= '<a href="' . base_url('onlineexam/edit/' . $record->id) . '" class="btn btn-circle btn-default icon" data-toggle="tooltip" data-original-title="' . translate('edit') . '"> <i class="fas fa-pen-nib"></i></a>';
            }
            if (get_permission('online_exam', 'is_delete')) {
                $action .= btn_delete('onlineexam/delete/' . $record->id);
            }

            // EXAM LINK SHARE
            if (get_permission('exam_link_share', 'is_view')) {
                $examURL = base_url('userrole/onlineexam_take/' . $record->id);
                $action .= '<button class="btn btn-circle btn-info icon" data-toggle="tooltip" title="Share Link" onclick="shareExamLink(\'' . $examURL . '\')"><i class="fas fa-share-alt"></i></button>';
            }


            $row[] = $count++;
            if (is_superadmin_loggedin()) {
                $row[] = $record->branchname;
            }
            $row[] = $record->title;
            $row[] = $record->class_name . " (" . $this->getSectionDetails($record->section_id) . ")";
            $row[] = $record->questions_qty;
            $row[] = _d($record->exam_start) . "<p class='text-muted'>" . date("h:i A", strtotime($record->exam_start)) . "</p>";
            $row[] = _d($record->exam_end) . "<p class='text-muted'>" . date("h:i A", strtotime($record->exam_end)) . "</p>";
            $row[] = $record->duration;
            $row[] = $record->exam_type == 0 ? translate('free') : $currency_symbol . $record->fee;
            $row[] = '<div class="material-switch ml-xs">
                        <input class="exam-status" id="examstatus_' . $record->id . '" data-id="' . $record->id . '" name="exam_status' . $record->id . '"
                        type="checkbox" ' . $status . ' />
                        <label for="examstatus_' . $record->id . '" class="label-primary"></label>
                    </div>';
            $row[] = get_type_name_by_id('staff', $record->created_by);
            $row[] = $action;
            $data[] = $row;
        }

        // Response
        $response = array(
            "draw" => intval($draw),
            "recordsTotal" => $totalRecords,
            "recordsFiltered" => $totalRecordwithFilter,
            "data" => $data,
        );
        return json_encode($response);
    }

    public function examListDT($postData, $currency_symbol = '')
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
            $whereClause = " WHERE `online_exam`.`session_id` = '$sessionID' AND is_live = 0 ";
        } else {
            $branchID = $this->db->escape(get_loggedin_branch_id());
            $whereClause = " WHERE `online_exam`.`session_id` = '$sessionID' AND is_live = 0 AND (
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
            $countWhere = " WHERE `online_exam`.`session_id` = '$sessionID' AND is_live = 0 ";
        } else {
            $branchID = $this->db->escape(get_loggedin_branch_id());
            $countWhere = " WHERE `online_exam`.`session_id` = '$sessionID' AND is_live = 0 AND (
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

        $data = array();
        $count = $start + 1;

        foreach ($records as $record) {
            $status = ($record->publish_status == 1) ? 'checked' : '';
            $row = array();
            $action = "";

            if (get_permission('add_questions', 'is_add')) {
                if ($record->publish_result == 0 && $record->publish_status == 1) {
                    $action .= '<button onclick="confirmModal(' . $this->db->escape(base_url('onlineexam/make_result_publish/' . $record->id)) . ')" class="btn btn-circle btn-default icon" data-toggle="tooltip" data-original-title="' . translate('make') . " " . translate('result_publish') . '"> <i class="fas fa-square-poll-vertical"></i></button>';
                }
            }

            $action .= '<a href="' . base_url('onlineexam/question_list/' . $record->id) . '" class="btn btn-circle btn-default icon" data-toggle="tooltip" data-original-title="' . translate('view') . " " . translate('question') . '"> <i class="fas fa-clipboard-list"></i></a>';


            if ($record->publish_status == 0) {
                $action .= '<a href="' . base_url('onlineexam/manage_question/' . $record->id) . '" class="btn btn-circle btn-default icon" data-toggle="tooltip" data-original-title="' . translate('add_questions') . '"> <i class="fas fa-question"></i></a>';
            }

            if (get_permission('online_exam', 'is_edit')) {
                $action .= '<a href="' . base_url('onlineexam/edit/' . $record->id) . '" class="btn btn-circle btn-default icon" data-toggle="tooltip" data-original-title="' . translate('edit') . '"> <i class="fas fa-pen-nib"></i></a>';
            }

            if (get_permission('online_exam', 'is_delete')) {
                $action .= btn_delete('onlineexam/delete/' . $record->id);
            }

            // ASSIGN BRANCH
            if (is_superadmin_loggedin() && (empty($record->created_by_branch) || $record->created_by_branch == 0)) {
                $action .= '<button class="btn btn-circle btn-info icon" data-toggle="tooltip" title="Assign Branch" onclick="openAssignBranchModal(' . $record->id . ')"><i class="fas fa-building"></i></button>';
            }

            // Exam Link Share
            if (get_permission('exam_link_share', 'is_view')) {
                $examURL = base_url('userrole/onlineexam_take/' . $record->id);
                $action .= '<button class="btn btn-circle btn-info icon" data-toggle="tooltip" title="Share Link" onclick="shareExamLink(\'' . $examURL . '\')"><i class="fas fa-share-alt"></i></button>';
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
            $row[] = $record->class_name . " (" . $this->getSectionDetails($record->section_id) . ")";
            $row[] = $record->questions_qty;
            $row[] = _d($record->exam_start) . "<p class='text-muted'>" . date("h:i A", strtotime($record->exam_start)) . "</p>";
            $row[] = _d($record->exam_end) . "<p class='text-muted'>" . date("h:i A", strtotime($record->exam_end)) . "</p>";
            $row[] = $record->duration;
            $row[] = $record->exam_type == 0 ? translate('free') : $currency_symbol . $record->fee;

            $row[] = '<div class="material-switch ml-xs">
                <input class="exam-status" id="examstatus_' . $record->id . '" data-id="' . $record->id . '" name="exam_status' . $record->id . '" type="checkbox" ' . $status . ' />
                <label for="examstatus_' . $record->id . '" class="label-primary"></label>
              </div>';
            $row[] = get_type_name_by_id('staff', $record->created_by);
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
    public function getSelectExamList($class_id)
    {
        $arrayData = array("" => translate('select'));
        if (!is_superadmin_loggedin() && !is_admin_loggedin()) {
            $this->db->where('created_by', get_loggedin_user_id());
        }
        $this->db->where('class_id', $class_id);
        $this->db->where('session_id', get_session_id());
        $this->db->where('publish_status', 1);
        $this->db->where('publish_result', 1);
        $result = $this->db->get('online_exam')->result();
        foreach ($result as $row) {
            $arrayData[$row->id] = $row->title;
        }
        return $arrayData;
    }

    public function getSectionDetails($data)
    {
        $array = json_decode($data, true);
        $nameList = [];
        if (json_last_error() == JSON_ERROR_NONE) {
            foreach ($array as $key => $value) {
                $nameList[] = get_type_name_by_id('section', $value);
            }
        }
        $nameList = implode(', ', $nameList);
        return $nameList;
    }

    public function getSubjectDetails($data)
    {
        $array = json_decode($data, true);
        $nameList = [];
        if (json_last_error() == JSON_ERROR_NONE) {
            foreach ($array as $key => $value) {
                $nameList[] = get_type_name_by_id('subject', $value);
            }
        }
        $nameList = implode(',<br>', $nameList);
        return $nameList;
    }

    // questions save and update function
    public function saveQuestions()
    {
        $branchId = is_superadmin_loggedin() ? NULL : get_loggedin_branch_id();
        $questionType = $this->input->post('question_type');
        if ($questionType == 2) {
            $answer = json_encode($this->input->post('answer'));
        } else {
            $answer = $this->input->post('answer');
        }

        $questionID = $this->input->post('question_id');
        $classID = $this->input->post('class_id');
        $sectionID = $this->input->post('section_id');
        $subjectID = $this->input->post('subject_id');
        $groupID = $this->input->post('group_id');

        $questionsExam = array(
            'type' => $questionType,
            'level' => $this->input->post('question_level'),
            'group_id' => $groupID,
            'question' => $this->input->post('question', false),
            'opt_1' => $this->tagRemove($this->input->post('option1', false)),
            'opt_2' => $this->tagRemove($this->input->post('option2', false)),
            'opt_3' => $this->tagRemove($this->input->post('option3', false)),
            'opt_4' => $this->tagRemove($this->input->post('option4', false)),
            'answer' => $answer,
            'mark' => $this->input->post('mark'),
        );

        if (!empty($classID)) {
            $questionsExam['class_id'] = $classID;
        }
        if (!empty($sectionID)) {
            $questionsExam['section_id'] = $sectionID;
        }
        if (!empty($subjectID)) {
            $questionsExam['subject_id'] = $subjectID;
        }

        if (empty($questionID)) {
            $questionsExam['created_by_branch'] = $branchId;
            $this->db->insert('questions', $questionsExam);
        } else {
            $this->db->where('id', $questionID);
            $this->db->update('questions', $questionsExam);
        }
    }

    private function tagRemove($text = "")
    {
        if ($text != "") {
            $text = str_replace("<p>", "", $text);
            $text = str_replace("</p>", "", $text);
        }
        return $text;
    }

    public function question_level()
    {
        $arrayLevel = array(
            '' => translate("select"),
            '1' => translate("easy"),
            '2' => translate("medium"),
            '3' => translate("hard"),
        );
        return $arrayLevel;
    }

    // public function question_group($branch_id = '')
    // {
    //     if (empty($branch_id)) {
    //         $array = array('' => translate('select_branch_first'));
    //     } else {
    //         $this->db->where('branch_id', $branch_id);
    //         $result = $this->db->get('question_group')->result();
    //         $array = array('' => translate('select'));
    //         foreach ($result as $row) {
    //             $array[$row->id] = $row->name;
    //         }
    //     }
    //     return $array;
    // }

    public function question_group()
    {
        $this->db->select('id,name,created_by_branch')
            ->from('question_group');
        if (!is_superadmin_loggedin()) {
            // $this->db->where('created_by_branch', get_loggedin_branch_id());
        }
        $rows = $this->db->order_by('name', 'asc')->get()->result();
        $list = ['' => translate('select')];
        foreach ($rows as $r) {
            $label = $r->created_by_branch === NULL
                ? 'Global — ' . $r->name
                : get_type_name_by_id('branch', $r->created_by_branch) . ' — ' . $r->name;
            $list[$r->id] = $label;
        }
        return $list;
    }

    public function question_type()
    {
        $arrayType = array(
            '1' => translate("single_choice"),
            '2' => translate("multiple_choice"),
            '3' => translate("true/false"),
            '4' => translate("descriptive"),
        );
        return $arrayType;
    }

    // public function questionList_old($postData)
    // {
    //     $response = array();

    //     // read value
    //     $draw = $postData['draw'];
    //     $start = $postData['start'];
    //     $rowperpage = $postData['length']; // Rows display per page
    //     $searchValue = $postData['search']['value']; // Search value

    //     // Search
    //     $branchID = $this->db->escape($postData['branch_id']);
    //     $examID = $this->db->escape($postData['examID']);
    //     $negMark = $postData['negMark'];

    //     $search_arr = array();
    //     $searchQuery = "";
    //     if ($searchValue != '') {
    //         $search_arr[] = " (`questions`.`question` like '%" . $searchValue . "%' OR `question_group`.`name` like '%" . $searchValue . "%') ";
    //     }

    //     if (!is_superadmin_loggedin()) {
    //         $showOwnquestion = $this->app_lib->getSchoolConfig($postData['branch_id'], 'show_own_question');
    //         if (!empty($showOwnquestion->show_own_question) && $showOwnquestion->show_own_question == 1) {
    //             $search_arr[] = " `questions`.`created_by` = " . $this->db->escape(get_loggedin_user_id());
    //         }
    //     }

    //     $questionGroup = $postData['questionGroup'];
    //     if ($questionGroup != '') {
    //         $questionGroup = $this->db->escape($questionGroup);
    //         $search_arr[] = " `questions`.`group_id` = $questionGroup ";
    //     }

    //     $questionType = $postData['questionType'];
    //     if ($questionType != '') {
    //         $questionType = $this->db->escape($questionType);
    //         $search_arr[] = " `questions`.`type` = $questionType ";
    //     }

    //     $questionLevel = $postData['questionLevel'];
    //     if ($questionLevel != '') {
    //         $questionLevel = $this->db->escape($questionLevel);
    //         $search_arr[] = " `questions`.`level` = $questionLevel ";
    //     }

    //     $classID = $postData['classID'];
    //     if ($classID != '') {
    //         $classID = $this->db->escape($classID);
    //         $search_arr[] = " `questions`.`class_id` = $classID ";
    //     }
    //     $sectionID = $postData['sectionID'];
    //     if ($sectionID != '') {
    //         $sectionID = $this->db->escape($sectionID);
    //         $search_arr[] = " `questions`.`section_id` = $sectionID ";
    //     }
    //     $subjectID = $postData['subjectID'];
    //     if ($subjectID != '') {
    //         $subjectID = $this->db->escape($subjectID);
    //         $search_arr[] = " `questions`.`subject_id` = $subjectID ";
    //     }

    //     if (count($search_arr) > 0) {
    //         $searchQuery = implode("AND", $search_arr);
    //     }

    //     // Total number of records without filtering
    //     $userID = $this->db->escape(get_loggedin_user_id());
    //     $sql = "SELECT `questions`.`id` FROM `questions` WHERE `questions`.`branch_id` = $branchID";
    //     if (!is_superadmin_loggedin()) {
    //         if (!empty($showOwnquestion->show_own_question) && $showOwnquestion->show_own_question == 1) {
    //             $sql .= " AND `questions`.`created_by` = " . $this->db->escape(get_loggedin_user_id());
    //         }
    //     }
    //     $records = $this->db->query($sql)->result();
    //     $totalRecords = count($records);

    //     // Total number of record with filtering
    //     $sql = "SELECT `questions`.`id`,`question_group`.`name` as `group_name` FROM `questions` LEFT JOIN `question_group` ON `question_group`.`id` = `questions`.`group_id` WHERE `questions`.`branch_id` = $branchID";
    //     if (!empty($searchQuery)) {
    //         $sql .= " AND " . $searchQuery;
    //     }
    //     $records = $this->db->query($sql)->result();
    //     $totalRecordwithFilter = count($records);

    //     // Fetch records
    //     $sql = "SELECT `questions`.*, IFNULL(`questions_manage`.`marks`, `questions`.`mark`) as `marks`, IFNULL(`questions_manage`.`neg_marks`, 1) as `neg_marks`, `questions_manage`.`id` as `manage_id`, `branch`.`name`, `subject`.`name` as `subject_name`, `class`.`name` as `class_name`, `section`.`name` as `section_name`, `question_group`.`name` as `group_name` FROM `questions` INNER JOIN `branch` ON `branch`.`id` = `questions`.`branch_id` LEFT JOIN `questions_manage` ON `questions_manage`.`question_id` = `questions`.`id` and `questions_manage`.`onlineexam_id` = $examID LEFT JOIN `class` ON `class`.`id` = `questions`.`class_id` LEFT JOIN `section` ON `section`.`id` = `questions`.`section_id` LEFT JOIN `subject` ON `subject`.`id` = `questions`.`subject_id` LEFT JOIN `question_group` ON `question_group`.`id` = `questions`.`group_id` WHERE `questions`.`branch_id` = $branchID";
    //     if (!empty($searchQuery)) {
    //         $sql .= " AND " . $searchQuery;
    //     }
    //     $sql .= " ORDER BY `questions`.`id` ASC LIMIT $start, $rowperpage";
    //     $records = $this->db->query($sql)->result();

    //     $data = array();
    //     $count = $start + 1;
    //     $question_type = $this->onlineexam_model->question_type();
    //     $arrayLevel = $this->onlineexam_model->question_level();
    //     foreach ($records as $key => $record) {
    //         $checkbox_status = "";
    //         if (!empty($record->manage_id)) {
    //             $checkbox_status = "checked";
    //         }

    //         // Question preview logic
    //         $questionContent = $record->question;
    //         $hasImage = preg_match('/<img[^>]+src="([^">]+)"/', $questionContent, $imgMatch);
    //         $hasIframe = preg_match('/<iframe[^>]+src="([^">]+)"/', $questionContent, $iframeMatch);

    //         $preview = '';
    //         if ($hasImage) {
    //             $preview .= '<img src="' . htmlspecialchars($imgMatch[1]) . '" style="max-height:200px; width: 200px;"> ';
    //         }

    //         if ($hasIframe) {
    //             $iframe = preg_replace(
    //                 '/width="[^"]*"|height="[^"]*"/i',
    //                 '',
    //                 $iframeMatch[0]
    //             );
    //             $preview .= '<div style="max-width:300px; max-height:180px;">' . str_replace('<iframe', '<iframe width="300" height="180"', $iframe) . '</div>';
    //         }

    //         $textOnly = trim(strip_tags($questionContent));
    //         if ($textOnly) {
    //             $preview .= '<br><small>' . htmlspecialchars($textOnly) . '</small>';
    //         }


    //         $row = array();
    //         $cb_row = '';
    //         $cb_row .= '<input type="hidden" name="question[' . $key . '][id]" value="' . $record->id . '">';
    //         $cb_row .= '<div class="checkbox-replace"><label class="i-checks">';
    //         $cb_row .= '<input type="checkbox" class="cb_question" name="question[' . $key . '][cb_id]" value="' . $record->id . '"' . $checkbox_status . '><i></i>';
    //         $cb_row .= '</label></div>';
    //         $row[] = $cb_row;
    //         $row[] = $count++;
    //         $row[] = $preview;
    //         $row[] = $record->group_name;
    //         $row[] = $record->class_name . " (" . $record->section_name . ")";
    //         $row[] = $record->subject_name;
    //         $row[] = $question_type[$record->type];
    //         $row[] = $arrayLevel[$record->level];
    //         $row[] = '<div class="form-group"><input type="text" class="form-control" name="question[' . $key . '][marks]" value="' . $record->marks . '"><span class="error"></span></div>';
    //         if ($negMark == 1) {
    //             $row[] = '<div class="form-group"><input type="text" class="form-control" name="question[' . $key . '][negative_marks]" value="' . $record->neg_marks . '"><span class="error"></span></div>';
    //         }
    //         $data[] = $row;
    //     }

    //     // Response
    //     $response = array(
    //         "draw" => intval($draw),
    //         "recordsTotal" => $totalRecords,
    //         "recordsFiltered" => $totalRecordwithFilter,
    //         "data" => $data,
    //     );
    //     return json_encode($response);
    // }



    // public function questionListDT($postData)
    // {
    //     $response = array();

    //     // read value
    //     $draw = $postData['draw'];
    //     $start = $postData['start'];
    //     $rowperpage = $postData['length']; // Rows display per page
    //     $searchValue = $postData['search']['value']; // Search value

    //     // order
    //     $columnIndex = empty($postData['order'][0]['column']) ? 0 : $postData['order'][0]['column']; // Column index
    //     $columnSortOrder = empty($postData['order'][0]['dir']) ? 'asc' : $postData['order'][0]['dir']; // asc or desc
    //     $column_order = array('`questions`.`id`');

    //     $search_arr = array();
    //     $searchQuery = "";
    //     if ($searchValue != '') {
    //         $search_arr[] = " (`questions`.`question` like '%" . $searchValue . "%') ";
    //     }

    //     if (!is_superadmin_loggedin()) {
    //         $showOwnquestion = $this->app_lib->getSchoolConfig('', 'show_own_question');
    //         $branch_id = $this->db->escape(get_loggedin_branch_id());
    //         $search_arr[] = " `questions`.`branch_id` = $branch_id ";
    //         if (!empty($showOwnquestion->show_own_question) && $showOwnquestion->show_own_question == 1) {
    //             $search_arr[] = " `questions`.`created_by` = " . $this->db->escape(get_loggedin_user_id());
    //         }
    //     } else {
    //         $column_order[] = '`questions`.`branch_id`';
    //     }

    //     // order
    //     $column_order[] = '`questions`.`question`';
    //     $column_order[] = '`group_name`';
    //     $column_order[] = '`class`.`id`';
    //     $column_order[] = '`subject`.`id`';
    //     $column_order[] = '`questions`.`type`';
    //     $column_order[] = '`questions`.`level`';

    //     if (count($search_arr) > 0) {
    //         $searchQuery = implode("AND", $search_arr);
    //     }

    //     // Total number of records without filtering
    //     if (is_superadmin_loggedin()) {
    //         $sql = "SELECT `questions`.`id` FROM `questions`";
    //     } else {
    //         $branchID = $this->db->escape(get_loggedin_branch_id());
    //         $sql = "SELECT `questions`.`id` FROM `questions` WHERE `questions`.`branch_id` = $branchID";
    //         if (!empty($showOwnquestion->show_own_question) && $showOwnquestion->show_own_question == 1) {
    //             $sql .= " AND `questions`.`created_by` = " . $this->db->escape(get_loggedin_user_id());
    //         }
    //     }
    //     $records = $this->db->query($sql)->result();
    //     $totalRecords = count($records);

    //     // Total number of record with filtering
    //     $sql = "SELECT `questions`.`id` FROM `questions`";
    //     if (!empty($searchQuery)) {
    //         $sql .= " WHERE " . $searchQuery;
    //     }
    //     $records = $this->db->query($sql)->result();
    //     $totalRecordwithFilter = count($records);

    //     // Fetch records
    //     $sql = "SELECT `questions`.`id`, `questions`.`question`, `questions`.`type`, `questions`.`level`, `branch`.`name`, `subject`.`name` as `subject_name`, `class`.`name` as `class_name`, `section`.`name` as `section_name`, `question_group`.`name` as `group_name` FROM `questions` INNER JOIN `branch` ON `branch`.`id` = `questions`.`branch_id` LEFT JOIN `class` ON `class`.`id` = `questions`.`class_id` LEFT JOIN `section` ON `section`.`id` = `questions`.`section_id` LEFT JOIN `subject` ON `subject`.`id` = `questions`.`subject_id` LEFT JOIN `question_group` ON `question_group`.`id` = `questions`.`group_id`";
    //     if (!empty($searchQuery)) {
    //         $sql .= " WHERE " . $searchQuery;
    //     }
    //     $sql .= " ORDER BY " . $column_order[$columnIndex] . " $columnSortOrder LIMIT $start, $rowperpage";
    //     $records = $this->db->query($sql)->result();

    //     $data = array();
    //     $count = $start + 1;
    //     $question_type = $this->onlineexam_model->question_type();
    //     $arrayLevel = $this->onlineexam_model->question_level();
    //     foreach ($records as $record) {

    //         $row = array();
    //         $action = "";
    //         $action .= '<button data-loading-text="<i class=' . "'fas fa-spinner fa-spin'" . '></i>" class="btn btn-circle btn-default icon" data-toggle="tooltip" data-original-title="' . translate('view') . '" onclick="getQuestion(' . $this->db->escape($record->id) . ', this);"><i class="fas fa-bars"></i></button>';
    //         $action .= '<a href="' . base_url('onlineexam/question_edit/' . $record->id) . '" class="btn btn-circle btn-default icon" data-toggle="tooltip" data-original-title="' . translate('edit') . '"><i class="fas fa-pen-nib"></i></a>';
    //         $action .= btn_delete('onlineexam/question_delete/' . $record->id);

    //         $row[] = $count++;
    //         if (is_superadmin_loggedin()) {
    //             $row[] = $record->name;
    //         }
    //         // $row[] = strip_tags($record->question);

    //         // Question preview logic
    //         $questionContent = $record->question;
    //         $hasImage = preg_match('/<img[^>]+src="([^">]+)"/', $questionContent, $imgMatch);
    //         $hasIframe = preg_match('/<iframe[^>]+src="([^">]+)"/', $questionContent, $iframeMatch);

    //         $preview = '';
    //         if ($hasImage) {
    //             $preview .= '<img src="' . htmlspecialchars($imgMatch[1]) . '" style="max-height:200px; width: 200px;"> ';
    //         }

    //         if ($hasIframe) {
    //             $iframe = preg_replace(
    //                 '/width="[^"]*"|height="[^"]*"/i',
    //                 '',
    //                 $iframeMatch[0]
    //             );
    //             $preview .= '<div style="max-width:300px; max-height:180px;">' . str_replace('<iframe', '<iframe width="300" height="180"', $iframe) . '</div>';
    //         }

    //         $textOnly = trim(strip_tags($questionContent));
    //         if ($textOnly) {
    //             $preview .= '<br><small>' . htmlspecialchars($textOnly) . '</small>';
    //         }


    //         $row[] = $preview;
    //         $row[] = $record->group_name;
    //         $row[] = $record->class_name . " (" . $record->section_name . ")";
    //         $row[] = $record->subject_name;
    //         $row[] = $question_type[$record->type];
    //         $row[] = $arrayLevel[$record->level];
    //         $row[] = $action;
    //         $data[] = $row;
    //     }

    //     // Response
    //     $response = array(
    //         "draw" => intval($draw),
    //         "recordsTotal" => $totalRecords,
    //         "recordsFiltered" => $totalRecordwithFilter,
    //         "data" => $data,
    //     );
    //     return json_encode($response);
    // }


    public function questionList($postData)
    {
        $response = array();

        // read value
        $draw = $postData['draw'];
        $start = $postData['start'];
        $rowperpage = $postData['length']; // Rows display per page
        $searchValue = $postData['search']['value']; // Search value

        $examID = $this->db->escape($postData['examID']);
        $negMark = $postData['negMark'];

        $branchID = get_loggedin_branch_id();

        $search_arr = array();
        $searchQuery = "";

        if ($searchValue != '') {
            $search_arr[] = " (`questions`.`question` like '%" . $searchValue . "%' OR `question_group`.`name` like '%" . $searchValue . "%') ";
        }

        $questionGroup = $postData['questionGroup'];
        if ($questionGroup != '') {
            $questionGroup = $this->db->escape($questionGroup);
            $search_arr[] = " `questions`.`group_id` = $questionGroup ";
        }

        $questionType = $postData['questionType'];
        if ($questionType != '') {
            $questionType = $this->db->escape($questionType);
            $search_arr[] = " `questions`.`type` = $questionType ";
        }

        $questionLevel = $postData['questionLevel'];
        if ($questionLevel != '') {
            $questionLevel = $this->db->escape($questionLevel);
            $search_arr[] = " `questions`.`level` = $questionLevel ";
        }

        $classID = $postData['classID'];
        if ($classID != '' && $classID != '0') {
            $classID = $this->db->escape($classID);
            $search_arr[] = " `questions`.`class_id` = $classID ";
        }

        $sectionID = $postData['sectionID'];
        if ($sectionID != '' && $sectionID != '0') {
            $sectionID = $this->db->escape($sectionID);
            $search_arr[] = " `questions`.`section_id` = $sectionID ";
        }

        $subjectID = $postData['subjectID'];
        if ($subjectID != '' && $subjectID != '0') {
            $subjectID = $this->db->escape($subjectID);
            $search_arr[] = " `questions`.`subject_id` = $subjectID ";
        }

        if (count($search_arr) > 0) {
            $searchQuery = implode(" AND ", $search_arr);
        }

        // Build WHERE clause based on login type
        if (is_superadmin_loggedin()) {
            // Superadmin sees all questions
            $whereClause = "WHERE 1=1";
        } else {
            $branchID_escaped = $this->db->escape($branchID);
            $whereClause = "WHERE (
            `questions`.`created_by_branch` = $branchID_escaped
            OR `questions`.`id` IN (
                SELECT `question_id` FROM `question_assignments` WHERE `branch_id` = $branchID_escaped
            )
        )";
        }

        // Append search query
        if (!empty($searchQuery)) {
            $whereClause .= " AND " . $searchQuery;
        }

        // Total records without filtering
        $sql = "SELECT `questions`.`id` FROM `questions` $whereClause";
        $records = $this->db->query($sql)->result();
        $totalRecords = count($records);

        // Total records with filtering (same as above here)
        $totalRecordwithFilter = $totalRecords;

        // Fetch records with JOINs
        $sql = "SELECT `questions`.*, 
            IFNULL(`questions_manage`.`marks`, `questions`.`mark`) as `marks`,
            IFNULL(`questions_manage`.`neg_marks`, 1) as `neg_marks`,
            `questions_manage`.`id` as `manage_id`,
            `subject`.`name` as `subject_name`, 
            `class`.`name` as `class_name`, 
            `section`.`name` as `section_name`, 
            `question_group`.`name` as `group_name`
        FROM `questions`
        LEFT JOIN `questions_manage` ON `questions_manage`.`question_id` = `questions`.`id` AND `questions_manage`.`onlineexam_id` = $examID
        LEFT JOIN `class` ON `class`.`id` = `questions`.`class_id`
        LEFT JOIN `section` ON `section`.`id` = `questions`.`section_id`
        LEFT JOIN `subject` ON `subject`.`id` = `questions`.`subject_id`
        LEFT JOIN `question_group` ON `question_group`.`id` = `questions`.`group_id`
        $whereClause
        ORDER BY `questions`.`id` ASC
        LIMIT $start, $rowperpage";

        $records = $this->db->query($sql)->result();

        $data = array();
        $count = $start + 1;
        $question_type = $this->onlineexam_model->question_type();
        $arrayLevel = $this->onlineexam_model->question_level();

        foreach ($records as $key => $record) {
            $checkbox_status = !empty($record->manage_id) ? "checked" : "";

            $questionContent = $record->question;
            $preview = '';

            if (preg_match('/<img[^>]+src="([^">]+)"/', $questionContent, $imgMatch)) {
                $preview .= '<img src="' . htmlspecialchars($imgMatch[1]) . '" style="max-height:200px; width: 200px;"> ';
            }
            if (preg_match('/<iframe[^>]+src="([^">]+)"/', $questionContent, $iframeMatch)) {
                $iframe = preg_replace('/width="[^"]*"|height="[^"]*"/i', '', $iframeMatch[0]);
                $preview .= '<div style="max-width:300px; max-height:180px;">' . str_replace('<iframe', '<iframe width="300" height="180"', $iframe) . '</div>';
            }

            $textOnly = trim(strip_tags($questionContent));
            if ($textOnly) {
                $preview .= '<br><small>' . htmlspecialchars($textOnly) . '</small>';
            }

            $row = array();
            $row[] = '<input type="hidden" name="question[' . $key . '][id]" value="' . $record->id . '">
            <div class="checkbox-replace"><label class="i-checks">
            <input type="checkbox" class="cb_question" name="question[' . $key . '][cb_id]" value="' . $record->id . '"' . $checkbox_status . '><i></i>
            </label></div>';
            $row[] = $count++;
            $row[] = $preview;
            $row[] = $record->group_name;
            $row[] = $record->class_name . " (" . $record->section_name . ")";
            $row[] = $record->subject_name;
            $row[] = $question_type[$record->type];
            $row[] = $arrayLevel[$record->level];
            $row[] = '<div class="form-group"><input type="text" class="form-control" name="question[' . $key . '][marks]" value="' . $record->marks . '"><span class="error"></span></div>';

            if ($negMark == 1) {
                $row[] = '<div class="form-group"><input type="text" class="form-control" name="question[' . $key . '][negative_marks]" value="' . $record->neg_marks . '"><span class="error"></span></div>';
            }

            $data[] = $row;
        }

        return json_encode(array(
            "draw" => intval($draw),
            "recordsTotal" => $totalRecords,
            "recordsFiltered" => $totalRecordwithFilter,
            "data" => $data,
        ));
    }
    public function questionListDT($postData)
    {
        $response = array();

        $draw = $postData['draw'];
        $start = $postData['start'];
        $rowperpage = $postData['length'];
        $searchValue = $postData['search']['value'];

        $columnIndex = empty($postData['order'][0]['column']) ? 0 : $postData['order'][0]['column'];
        $columnSortOrder = empty($postData['order'][0]['dir']) ? 'asc' : $postData['order'][0]['dir'];

        // Default order columns
        $column_order = array('questions.id', 'questions.question', 'question_group.name', 'class.name', 'subject.name', 'questions.type', 'questions.level');

        $search_arr = array();
        if ($searchValue != '') {
            $search_arr[] = " (questions.question LIKE '%" . $searchValue . "%') ";
        }
        $searchQuery = count($search_arr) > 0 ? implode(" AND ", $search_arr) : "";

        // Fetch data based on role
        if (is_superadmin_loggedin()) {
            // Super Admin: Fetch global questions
            $this->db->select('questions.id, questions.question, questions.type, questions.level, 
            "Global" as branch_name, subject.name as subject_name, class.name as class_name, 
            section.name as section_name, question_group.name as group_name');
            $this->db->from('questions');
            $this->db->join('class', 'class.id = questions.class_id', 'left');
            $this->db->join('section', 'section.id = questions.section_id', 'left');
            $this->db->join('subject', 'subject.id = questions.subject_id', 'left');
            $this->db->join('question_group', 'question_group.id = questions.group_id', 'left');
            $this->db->where('questions.created_by_branch IS NULL', null, false);

            if (!empty($searchQuery)) {
                $this->db->where($searchQuery, null, false);
            }

            $totalRecords = $this->db->count_all_results('', false);
            $totalRecordwithFilter = $totalRecords;

            $this->db->order_by($column_order[$columnIndex], $columnSortOrder);
            $this->db->limit($rowperpage, $start);
            $records = $this->db->get()->result();

        } else {
            // Branch user: Fetch assigned questions
            $branchID = get_loggedin_branch_id();

            $this->db->select('questions.id, questions.question, questions.type, questions.level, 
                branch.name as branch_name, subject.name as subject_name, class.name as class_name, 
                section.name as section_name, question_group.name as group_name');
            $this->db->from('question_assignments');
            $this->db->join('questions', 'questions.id = question_assignments.question_id', 'inner');
            $this->db->join('branch', 'branch.id = question_assignments.branch_id', 'inner');
            $this->db->join('class', 'class.id = questions.class_id', 'left');
            $this->db->join('section', 'section.id = questions.section_id', 'left');
            $this->db->join('subject', 'subject.id = questions.subject_id', 'left');
            $this->db->join('question_group', 'question_group.id = questions.group_id', 'left');
            $this->db->where('question_assignments.branch_id', $branchID);

            if (!empty($searchQuery)) {
                $this->db->where($searchQuery, null, false);
            }

            $totalRecords = $this->db->count_all_results('', false);
            $totalRecordwithFilter = $totalRecords;

            $this->db->order_by($column_order[$columnIndex], $columnSortOrder);
            $this->db->limit($rowperpage, $start);
            $records = $this->db->get()->result();
        }

        // Prepare data
        $data = array();
        $count = $start + 1;
        $question_type = $this->onlineexam_model->question_type();
        $arrayLevel = $this->onlineexam_model->question_level();

        foreach ($records as $record) {
            $row = array();

            $action = "";
            $action .= '<button data-loading-text="<i class=\'fas fa-spinner fa-spin\'></i>" class="btn btn-circle btn-default icon" data-toggle="tooltip" data-original-title="View" onclick="getQuestion(' . $record->id . ', this);"><i class="fas fa-bars"></i></button>';
            $action .= '<a href="' . base_url('onlineexam/question_edit/' . $record->id) . '" class="btn btn-circle btn-default icon" data-toggle="tooltip" data-original-title="Edit"><i class="fas fa-pen-nib"></i></a>';
            $action .= btn_delete('onlineexam/question_delete/' . $record->id);

            $row[] = $count++;
            if (is_superadmin_loggedin()) {
                $row[] = $record->branch_name;
            }

            // Question preview
            $questionContent = $record->question;
            $hasImage = preg_match('/<img[^>]+src="([^">]+)"/', $questionContent, $imgMatch);
            $hasIframe = preg_match('/<iframe[^>]+src="([^">]+)"/', $questionContent, $iframeMatch);

            $preview = '';
            if ($hasImage) {
                $preview .= '<img src="' . htmlspecialchars($imgMatch[1]) . '" style="max-height:200px; width:200px;"> ';
            }
            if ($hasIframe) {
                $iframe = preg_replace('/width="[^"]*"|height="[^"]*"/i', '', $iframeMatch[0]);
                $preview .= '<div style="max-width:300px; max-height:180px;">' . str_replace('<iframe', '<iframe width="300" height="180"', $iframe) . '</div>';
            }
            $textOnly = trim(strip_tags($questionContent));
            if ($textOnly) {
                $preview .= '<br><small>' . htmlspecialchars($textOnly) . '</small>';
            }

            $row[] = $preview;
            $row[] = $record->group_name;
            $row[] = $record->class_name . " (" . $record->section_name . ")";
            $row[] = $record->subject_name;
            $row[] = $question_type[$record->type];
            $row[] = $arrayLevel[$record->level];
            $row[] = $action;

            $data[] = $row;
        }

        // Response
        $response = array(
            "draw" => intval($draw),
            "recordsTotal" => $totalRecords,
            "recordsFiltered" => $totalRecordwithFilter,
            "data" => $data,
        );

        return json_encode($response);
    }
    public function getStudentSubmitted($online_examID = null)
    {
        $r = $this->db->select('id')->where(array('student_id' => get_loggedin_user_id(), 'online_exam_id' => $online_examID))->get('online_exam_submitted')->row();
        return $r;
    }

    public function getExamQuestions($onlineexamID = null, $random_type = 0)
    {
        $this->db->select('questions_manage.*,questions.id as qus_id,questions.*')->from('questions_manage');
        $this->db->join('questions', 'questions.id = questions_manage.question_id');
        $this->db->where('questions_manage.onlineexam_id', $onlineexamID);
        if ($random_type == 1) {
            $this->db->order_by('rand()');
        } else {
            $this->db->order_by('questions_manage.id', 'ASC');
        }
        $query = $this->db->get();
        return $query->result();
    }

    public function getExamResults($onlineexamID = null, $studentID = 0)
    {
        $sql = "SELECT `questions_manage`.*, `questions`.`id` as `qus_id`, `questions`.*, `online_exam_answer`.`answer` as `sb_ans`, `online_exam_answer`.`id` as `ans_id` FROM `questions_manage` INNER JOIN `questions` ON `questions`.`id` = `questions_manage`.`question_id` LEFT JOIN `online_exam_answer` ON `online_exam_answer`.`online_exam_id` = `questions_manage`.`onlineexam_id` and `online_exam_answer`.`question_id` = `questions`.`id` and `online_exam_answer`.`student_id` = " . $this->db->escape($studentID) . " WHERE `questions_manage`.`onlineexam_id` = " . $this->db->escape($onlineexamID) . " ORDER BY `questions_manage`.`id` ASC";
        $query = $this->db->query($sql);

        return $query->result();
    }

    // public function getExamDetails($onlineexamID, $status = true)
    // {
    //     $onlineexamID = $this->db->escape($onlineexamID);
    //     $sessionID = $this->db->escape(get_session_id());
    //     $branchID = $this->db->escape(get_loggedin_branch_id());
    //     $sql = "SELECT `online_exam`.*, `class`.`name` as `class_name`,(SELECT COUNT(`id`) FROM `questions_manage` WHERE `questions_manage`.`onlineexam_id`=`online_exam`.`id`) as `questions_qty`, `branch`.`name` as `branchname` FROM `online_exam` INNER JOIN `branch` ON `branch`.`id` = `online_exam`.`branch_id` LEFT JOIN `class` ON `class`.`id` = `online_exam`.`class_id` WHERE `online_exam`.`session_id` = $sessionID AND `online_exam`.`id` = " . $onlineexamID;
    //     if ($status == true) {
    //         $sql .= " AND `online_exam`.`publish_status` = '1'";
    //     }
    //     if (!is_superadmin_loggedin()) {
    //         $sql .= " AND `online_exam`.`branch_id` = $branchID";
    //     }
    //     $records = $this->db->query($sql)->row();
    //     return $records;
    // }

    public function getExamDetails($onlineexamID, $status = true)
    {
        $onlineexamID = $this->db->escape($onlineexamID);
        $sessionID = $this->db->escape(get_session_id());
        $branchID = $this->db->escape(get_loggedin_branch_id());

        $sql = "SELECT `online_exam`.*, 
                `class`.`name` as `class_name`,
                (SELECT COUNT(`id`) 
                 FROM `questions_manage` 
                 WHERE `questions_manage`.`onlineexam_id`=`online_exam`.`id`) as `questions_qty`, 
                `branch`.`name` as `branchname` 
            FROM `online_exam` 
            LEFT JOIN `branch` ON `branch`.`id` = `online_exam`.`created_by_branch` 
            LEFT JOIN `class` ON `class`.`id` = `online_exam`.`class_id` 
            WHERE `online_exam`.`session_id` = $sessionID 
            AND `online_exam`.`id` = " . $onlineexamID;

        if ($status == true) {
            $sql .= " AND `online_exam`.`publish_status` = '1'";
        }

        if (!is_superadmin_loggedin()) {
            // Restrict for branch admins: show if created by their branch or assigned to them
            $sql .= " AND (
            `online_exam`.`created_by_branch` = $branchID 
            OR `online_exam`.`id` IN (
                SELECT `exam_id` FROM `exam_assignment` WHERE `branch_id` = $branchID
            )
        )";
        }

        $records = $this->db->query($sql)->row();
        return $records;
    }

    public function getStudentAttempt($onlineexamID)
    {
        $this->db->select('IFNULL(SUM(count), 0) as att');
        $this->db->where(array('student_id' => get_loggedin_user_id(), 'online_exam_id' => $onlineexamID));
        $r = $this->db->get('online_exam_attempts')->row();
        return $r->att;
    }

    public function addStudentAttemts($onlineexamID)
    {
        $query = $this->db->where(array('student_id' => get_loggedin_user_id(), 'online_exam_id' => $onlineexamID))->get('online_exam_attempts');
        if ($query->num_rows() > 0) {
            $this->db->set('count', 'count+1', false);
            $this->db->where('id', $query->row()->id);
            $this->db->update('online_exam_attempts');
        } else {
            $this->db->insert('online_exam_attempts', ['student_id' => get_loggedin_user_id(), 'online_exam_id' => $onlineexamID, 'count' => 1]);
        }
    }

    public function getSubjectByClass($classID = '', $sectionID = '')
    {
        if (loggedin_role_id() == 3) {
            $restricted = $this->getSingle('branch', get_loggedin_branch_id(), true)->teacher_restricted;
            if ($restricted == 1) {
                $getClassTeacher = $this->getClassTeacherByClassSection($classID);
                if ($getClassTeacher == true) {
                    $query = $this->getSubjectList($classID);
                } else {
                    $this->db->select('timetable_class.subject_id,subject.name as subjectname');
                    $this->db->from('timetable_class');
                    $this->db->join('section', 'section.id = timetable_class.section_id', 'left');
                    $this->db->join('subject', 'subject.id = timetable_class.subject_id', 'left');
                    $this->db->where(array('timetable_class.teacher_id' => get_loggedin_user_id(), 'timetable_class.session_id' => get_session_id(), 'timetable_class.class_id' => $classID));
                    $this->db->group_by('timetable_class.subject_id');
                    $query = $this->db->get();
                }
            } else {
                $query = $this->getSubjectList($classID);
            }
        } else {
            $query = $this->getSubjectList($classID);
        }
        return $query;
    }

    public function getSubjectList($classID = '')
    {
        $this->db->select('subject_assign.subject_id, subject.name as subjectname');
        $this->db->from('subject_assign');
        $this->db->join('subject', 'subject.id = subject_assign.subject_id', 'left');
        $this->db->where('class_id', $classID);
        $this->db->where('session_id', get_session_id());
        $query = $this->db->get();
        return $query;
    }

    public function getClassTeacherByClassSection($classID = '')
    {
        $this->db->select('teacher_allocation.id');
        $this->db->from('teacher_allocation');
        $this->db->where('teacher_allocation.teacher_id', get_loggedin_user_id());
        $this->db->where('teacher_allocation.session_id', get_session_id());
        $this->db->where('teacher_allocation.class_id', $classID);
        $q = $this->db->get()->num_rows();
        if ($q > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function examReport($examID = '', $classID = '', $branchID = '', $order_by_position = 0)
    {
        $this->db->select('online_exam_submitted.student_id,online_exam_submitted.remark,online_exam_submitted.position,online_exam.id,online_exam.neg_mark,online_exam.mark_type,online_exam.passing_mark,student.first_name,student.last_name,student.register_no,student.mobileno,online_exam.title,online_exam.section_id,online_exam.subject_id,class.name as class_name');
        $this->db->from('online_exam_submitted');
        $this->db->join('online_exam', 'online_exam.id = online_exam_submitted.online_exam_id', 'inner');
        $this->db->join('class', 'class.id = online_exam.class_id', 'left');
        $this->db->join('student', 'student.id = online_exam_submitted.student_id', 'left');
        $this->db->where('online_exam_submitted.online_exam_id', $examID);
        $this->db->where('online_exam.session_id', get_session_id());
        $this->db->where('online_exam.class_id', $classID);
        $result = $this->db->get()->result_array();
        foreach ($result as $key => $value) {
            $examResult = $this->examResult($value['id'], $value['student_id']);
            $status = 0;
            $mark = 0;
            $score = 0;
            $total_neg_marks = $value['neg_mark'] == 0 ? 0 : $examResult['total_neg_marks'];
            if ($examResult['total_obtain_marks'] != 0) {
                if ($value['mark_type'] == 1) {
                    $obtain = ((($examResult['total_obtain_marks'] - $total_neg_marks) * 100) / $examResult['total_marks']);
                    if ($obtain >= $value['passing_mark']) {
                        $status = 1;
                    } else {
                        $status = 0;
                    }
                } else {
                    $obtain = ($examResult['total_obtain_marks'] - $total_neg_marks);
                    if ($obtain >= $value['passing_mark']) {
                        $status = 1;
                    } else {
                        $status = 0;
                    }
                }
                $mark = ($examResult['total_obtain_marks'] - $total_neg_marks);
            }
            $score = ($examResult['total_marks'] === 0) ? '0.00' : number_format(((($examResult['total_obtain_marks'] - $total_neg_marks) * 100) / $examResult['total_marks']), 2, '.', '');
            $result[$key]['result'] = $status;
            $result[$key]['mark'] = $mark;
            $result[$key]['totalmark'] = $examResult['total_marks'];
            $result[$key]['score'] = $score;
        }
        if ($order_by_position == 1) {
            array_multisort(array_column($result, 'position'), SORT_ASC, $result);
        } else {
            array_multisort(array_column($result, 'score'), SORT_DESC, $result);
        }
        return $result;
    }

    public function examResult($examID, $studentID)
    {
        $result = $this->getExamResults($examID, $studentID);
        $correct_ans = 0;
        $total_question = 0;
        $total_neg_marks = 0;
        $total_marks = 0;
        $total_obtain_marks = 0;
        $wrong_ans = 0;
        $total_answered = 0;
        if (!empty($result)) {
            $total_question = count($result);
            foreach ($result as $key => $value) {
                $total_marks = $total_marks + $value->marks;
                if (!empty($value->ans_id)) {
                    $total_answered++;
                    if ($value->type == 1 || $value->type == 3) {
                        if ($value->sb_ans == $value->answer) {
                            $correct_ans++;
                            $total_obtain_marks = $total_obtain_marks + $value->marks;
                        } else {
                            $total_neg_marks = $total_neg_marks + $value->neg_marks;
                            $wrong_ans++;
                        }
                    } elseif ($value->type == 2) {
                        if ($this->array_equal(json_decode($value->answer), json_decode($value->sb_ans))) {
                            $correct_ans++;
                            $total_obtain_marks = $total_obtain_marks + $value->marks;
                        } else {
                            $total_neg_marks = $total_neg_marks + $value->neg_marks;
                            $wrong_ans++;
                        }
                    } elseif ($value->type == 4) {
                        $correctAns = str_replace(" ", "_", $value->answer);
                        $studentAns = str_replace(" ", "_", $value->sb_ans);
                        if (strtolower($correctAns) == strtolower($studentAns)) {
                            $correct_ans++;
                            $total_obtain_marks = $total_obtain_marks + $value->marks;
                        } else {
                            $total_neg_marks = $total_neg_marks + $value->neg_marks;
                            $wrong_ans++;
                        }
                    }
                }
            }
        }
        return ['total_marks' => $total_marks, 'total_obtain_marks' => $total_obtain_marks, 'correct_ans' => $correct_ans, 'total_question' => $total_question, 'total_neg_marks' => $total_neg_marks, 'wrong_ans' => $wrong_ans, 'total_answered' => $total_answered];
    }

    public function array_equal($a, $b)
    {
        return (
            is_array($a) && is_array($b) && count($a) == count($b) && array_diff($a, $b) === array_diff($b, $a)
        );
    }
    public function getQuestionsFilteredDT($postData)
    {
        $response = array();

        // Read DataTable params
        $draw = $postData['draw'];
        $start = $postData['start'];
        $rowperpage = $postData['length'];
        $searchValue = $postData['search']['value'];

        // Filters
        $classId = $postData['class_id'];
        $sectionId = $postData['section_id'];
        $subjectId = $postData['subject_id'];

        // Order
        $columnIndex = empty($postData['order'][0]['column']) ? 0 : $postData['order'][0]['column'];
        $columnSortOrder = empty($postData['order'][0]['dir']) ? 'ASC' : $postData['order'][0]['dir'];
        $column_order = array('questions.id', 'questions.question', 'question_group.name', 'class.name', 'subject.name', 'questions.type', 'questions.level');

        // Search
        $searchQuery = "";
        if ($searchValue != '') {
            $searchQuery = "(questions.question LIKE '%" . $searchValue . "%')";
        }

        // Total records without filtering
        $this->db->from('questions');
        $this->db->where('class_id', $classId);
        if (!empty($sectionId)) {
            $this->db->where('section_id', $sectionId);
        }
        if (!empty($subjectId)) {
            $this->db->where('subject_id', $subjectId);
        }
        $this->db->where('questions.created_by_branch IS NULL', null, false);
        $totalRecords = $this->db->count_all_results();

        // Total records with filtering
        $this->db->from('questions');
        $this->db->join('question_group', 'question_group.id = questions.group_id', 'left');
        $this->db->join('class', 'class.id = questions.class_id', 'left');
        $this->db->join('section', 'section.id = questions.section_id', 'left');
        $this->db->join('subject', 'subject.id = questions.subject_id', 'left');
        $this->db->where('questions.class_id', $classId);
        if (!empty($sectionId)) {
            $this->db->where('questions.section_id', $sectionId);
        }
        if (!empty($subjectId)) {
            $this->db->where('questions.subject_id', $subjectId);
        }
        $this->db->where('questions.created_by_branch IS NULL', null, false);
        if (!empty($searchQuery)) {
            $this->db->where($searchQuery, null, false);
        }
        $totalRecordwithFilter = $this->db->count_all_results();

        // Fetch records
        $this->db->select('
        questions.id,
        questions.question,
        questions.type,
        questions.level,
        subject.name as subject_name,
        class.name as class_name,
        section.name as section_name,
        question_group.name as group_name
    ');
        $this->db->from('questions');
        $this->db->join('question_group', 'question_group.id = questions.group_id', 'left');
        $this->db->join('class', 'class.id = questions.class_id', 'left');
        $this->db->join('section', 'section.id = questions.section_id', 'left');
        $this->db->join('subject', 'subject.id = questions.subject_id', 'left');
        $this->db->where('questions.class_id', $classId);
        if (!empty($sectionId)) {
            $this->db->where('questions.section_id', $sectionId);
        }
        if (!empty($subjectId)) {
            $this->db->where('questions.subject_id', $subjectId);
        }
        $this->db->where('questions.created_by_branch IS NULL', null, false);
        if (!empty($searchQuery)) {
            $this->db->where($searchQuery, null, false);
        }
        $this->db->order_by($column_order[$columnIndex], $columnSortOrder);
        $this->db->limit($rowperpage, $start);
        $records = $this->db->get()->result();

        // Prepare data
        $data = array();
        $count = $start + 1;
        $question_type = $this->onlineexam_model->question_type();
        $arrayLevel = $this->onlineexam_model->question_level();

        foreach ($records as $record) {
            $row = array();

            $checkbox = '<div class="material-switch ml-xs">
            <input class="question-assign" id="qassign_' . $record->id . '" data-id="' . $record->id . '" name="question_assign[' . $record->id . ']" type="checkbox" />
                <label for="qassign_' . $record->id . '" class="label-primary"></label>
            </div>';
            $row[] = $checkbox;

            // SL
            $row[] = $count++;

            // Question preview
            $questionContent = $record->question;
            $hasImage = preg_match('/<img[^>]+src="([^">]+)"/', $questionContent, $imgMatch);
            $hasIframe = preg_match('/<iframe[^>]+src="([^">]+)"/', $questionContent, $iframeMatch);

            $preview = '';
            if ($hasImage) {
                $preview .= '<img src="' . htmlspecialchars($imgMatch[1]) . '" style="max-height:200px; width: 200px;"> ';
            }
            if ($hasIframe) {
                $iframe = preg_replace('/width="[^"]*"|height="[^"]*"/i', '', $iframeMatch[0]);
                $preview .= '<div style="max-width:300px; max-height:180px;">' . str_replace('<iframe', '<iframe width="300" height="180"', $iframe) . '</div>';
            }
            $textOnly = trim(strip_tags($questionContent));
            if ($textOnly) {
                $preview .= '<br><small>' . htmlspecialchars($textOnly) . '</small>';
            }
            $row[] = $preview;

            // Other columns
            $row[] = $record->group_name;
            $row[] = $record->class_name . " (" . $record->section_name . ")";
            $row[] = $record->subject_name;
            $row[] = $question_type[$record->type];
            $row[] = $arrayLevel[$record->level];

            $data[] = $row;
        }

        // Final response
        $response = array(
            "draw" => intval($draw),
            "recordsTotal" => $totalRecords,
            "recordsFiltered" => $totalRecordwithFilter,
            "data" => $data,
        );
        return $response;
    }
    public function assignQuestionToBranch($question_id, $branch_id, $created_by = null)
    {
        $data = [
            'question_id' => $question_id,
            'branch_id' => $branch_id,
            'created_by' => $created_by,
        ];

        // Check if already assigned to avoid duplicates
        $exists = $this->db->get_where('question_assignments', [
            'question_id' => $question_id,
            'branch_id' => $branch_id
        ])->num_rows();

        if ($exists == 0) {
            $this->db->insert('question_assignments', $data);
        }
    }
    public function getAllExams()
    {
        return $this->db->get('online_exam')->result_array();
    }

    public function getBranchExams($branch_id)
    {
        $this->db->select('online_exam.*');
        $this->db->from('online_exam');
        $this->db->join('exam_assignment', 'exam_assignment.exam_id = online_exam.id', 'left');
        $this->db->where('exam_assignment.branch_id', $branch_id);
        $this->db->or_where('online_exam.created_by_branch', $branch_id);
        $this->db->group_by('online_exam.id');
        return $this->db->get()->result_array();
    }

}