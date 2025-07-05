<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Userrole_model extends MY_Model
{

    public function __construct()
    {
        parent::__construct();
    }

    public function getTeachersList($branchID = '')
    {
        $this->db->select('staff.*,staff_designation.name as designation_name,staff_department.name as department_name,login_credential.role as role_id, roles.name as role');
        $this->db->from('staff');
        $this->db->join('login_credential', 'login_credential.user_id = staff.id and login_credential.role != "6" and login_credential.role != "7"', 'inner');
        $this->db->join('roles', 'roles.id = login_credential.role', 'left');
        $this->db->join('staff_designation', 'staff_designation.id = staff.designation', 'left');
        $this->db->join('staff_department', 'staff_department.id = staff.department', 'left');
        if ($branchID != "") {
            $this->db->where('staff.branch_id', $branchID);
        }
        $this->db->where('login_credential.role', 3);
        $this->db->where('login_credential.active', 1);
        $this->db->order_by('staff.id', 'ASC');
        return $this->db->get()->result();
    }

    // get route information by route id and vehicle id
    public function getRouteDetails($routeID, $vehicleID)
    {
        $this->db->select('ta.route_id,ta.stoppage_id,ta.vehicle_id,r.name as route_name,r.start_place,r.stop_place,sp.stop_position,sp.stop_time,sp.route_fare,v.vehicle_no,v.driver_name,v.driver_phone');
        $this->db->from('transport_assign as ta');
        $this->db->join('transport_route as r', 'r.id = ta.route_id', 'left');
        $this->db->join('transport_vehicle as v', 'v.id = ta.vehicle_id', 'left');
        $this->db->join('transport_stoppage as sp', 'sp.id = ta.stoppage_id', 'left');
        $this->db->where('ta.route_id', $routeID);
        $this->db->where('ta.vehicle_id', $vehicleID);
        return $this->db->get()->row_array();
    }

    public function getAssignList($branch_id = '')
    {
        $this->db->select('ta.route_id,ta.stoppage_id,ta.branch_id,r.name,r.start_place,r.stop_place,sp.stop_position,sp.stop_time,sp.route_fare');
        $this->db->from('transport_assign as ta');
        $this->db->join('transport_route as r', 'r.id = ta.route_id', 'left');
        $this->db->join('transport_stoppage as sp', 'sp.id = ta.stoppage_id', 'left');
        $this->db->group_by(array('ta.route_id', 'ta.stoppage_id', 'ta.branch_id'));
        if (!empty($branch_id)) {
            $this->db->where('ta.branch_id', $branch_id);
        }
        return $this->db->get()->result_array();
    }

    // get vehicle list by route_id
    public function getVehicleList($route_id)
    {
        $this->db->select('ta.vehicle_id,v.vehicle_no');
        $this->db->from('transport_assign as ta');
        $this->db->join('transport_vehicle as v', 'v.id = ta.vehicle_id', 'left');
        $this->db->where('ta.route_id', $route_id);
        $vehicles = $this->db->get()->result();
        $name_list = '';
        foreach ($vehicles as $row) {
            $name_list .= '- ' . $row->vehicle_no . '<br>';
        }
        return $name_list;
    }

    // get hostel information by hostel id and room id
    public function getHostelDetails($hostelID, $roomID)
    {
        $this->db->select('h.name as hostel_name,h.watchman,h.category_id,h.address,hc.name as hcategory_name,rc.name as rcategory_name,hr.name as room_name,hr.no_beds,hr.bed_fee');
        $this->db->from('hostel as h');
        $this->db->join('hostel_category as hc', 'hc.id = h.category_id', 'left');
        $this->db->join('hostel_room as hr', 'hr.hostel_id = h.id', 'left');
        $this->db->join('hostel_category as rc', 'rc.id = hr.category_id', 'left');
        $this->db->where('hr.id', $roomID);
        $this->db->where('h.id', $hostelID);
        return $this->db->get()->row();
    }

    // check attendance by staff id and date
    public function get_attendance_by_date($studentID, $date)
    {
        $sql = "SELECT student_attendance.* FROM student_attendance WHERE student_id = " . $this->db->escape($studentID) . " AND date = " . $this->db->escape($date);
        return $this->db->query($sql)->row_array();
    }


    public function getStudentDetails()
    {
        if (is_student_loggedin()) {
            $studentID = get_loggedin_user_id();
        } elseif (is_parent_loggedin()) {
            $studentID = get_activeChildren_id();
        }
        $this->db->select('CONCAT(s.first_name, " ", s.last_name) as fullname,s.email as student_email,e.branch_id,e.student_id,s.hostel_id,s.room_id,s.route_id,s.vehicle_id,e.class_id,e.section_id,c.name as class_name,se.name as section_name,b.school_name,b.email as school_email,b.mobileno as school_mobileno,b.address as school_address');
        $this->db->from('enroll as e');
        $this->db->join('student as s', 's.id = e.student_id', 'inner');
        $this->db->join('branch as b', 'b.id = e.branch_id', 'left');
        $this->db->join('class as c', 'c.id = e.class_id', 'left');
        $this->db->join('section as se', 'se.id = e.section_id', 'left');
        $this->db->where('s.id', $studentID);
        return $this->db->get()->row_array();
    }

    public function getHomeworkList($studentID)
    {
        $this->db->select('homework.*,CONCAT(s.first_name, " ",s.last_name) as fullname,s.register_no,e.student_id, e.roll,subject.name as subject_name,class.name as class_name,section.name as section_name,he.id as ev_id,he.status as ev_status,he.remark as ev_remarks,he.rank');
        $this->db->from('homework');
        $this->db->join('enroll as e', 'e.class_id=homework.class_id and e.section_id = homework.section_id and e.session_id = homework.session_id', 'inner');
        $this->db->join('student as s', 'e.student_id = s.id', 'inner');
        $this->db->join('homework_evaluation as he', 'he.homework_id = homework.id and he.student_id = e.student_id', 'left');
        $this->db->join('subject', 'subject.id = homework.subject_id', 'left');
        $this->db->join('class', 'class.id = homework.class_id', 'left');
        $this->db->join('section', 'section.id = homework.section_id', 'left');
        $this->db->where('e.student_id', $studentID);
        if (!is_superadmin_loggedin()) {
            $this->db->where('homework.branch_id', get_loggedin_branch_id());
        }
        $this->db->where('homework.status', 0);
        $this->db->where('homework.session_id', get_session_id());
        $this->db->order_by('homework.id', 'desc');
        return $this->db->get()->result_array();
    }

    public function getGallery($branchId, $classId, $sectionId)
    {
        $studentID = null;
        if (is_student_loggedin()) {
            $studentID = get_loggedin_user_id();
        } elseif (is_parent_loggedin()) {
            $studentID = get_activeChildren_id();
        }

        $this->db->select('g.*');
        $this->db->from('tbl_gallery g');
        $this->db->where('g.status', 1);

        if (!empty($branchId)) {
            $this->db->where('g.branch_id', $branchId);
        }
        if (!empty($classId)) {
            $this->db->where('g.class_id', $classId);
        }

        $this->db->group_start();

        // Condition 1: Section is NULL, but class_id and branch_id must match
        $this->db->where('g.section_id IS NULL');

        // Condition 2: The gallery is assigned to the student in tbl_gallery_student
        if (!empty($studentID)) {
            $existsQuery = sprintf(
                "EXISTS (SELECT 1 FROM tbl_gallery_student gs WHERE gs.gallery_id = g.id AND gs.student_id = %d)",
                intval($studentID)
            );
            $this->db->or_where($existsQuery, NULL, FALSE);
        }

        $this->db->group_end();

        $query = $this->db->get();
        return $query->result_array();
    }

    public function getSubjectByClass($studentDetail)
    {
        $this->db->select('s.id, s.name');
        $this->db->from('subject_assign as sa');
        $this->db->join('subject as s', 's.id = sa.subject_id', 'inner');
        $this->db->where([
            'sa.class_id' => $studentDetail['class_id'],
            'sa.section_id' => $studentDetail['section_id'],
            'sa.branch_id' => $studentDetail['branch_id'],
            'sa.session_id' => $studentDetail['session_id'],
        ]);
        $this->db->group_by('s.id');
        $query = $this->db->get();

        return $query->result_array();
    }

    public function get_subject_progress($branchID, $classID, $sectionID, $studentID, $subjectID)
    {
        $sql = "SELECT 
            s.name AS subject_name,
            SUM(
                CAST(
                    TRIM(BOTH '\"' FROM SUBSTRING_INDEX(SUBSTRING_INDEX(m.mark, ':', -1), '\"', 2))
                AS DECIMAL)
            ) AS marks_obtained,
            SUM(
                CAST(
                    TRIM(BOTH '\"' FROM SUBSTRING_INDEX(SUBSTRING_INDEX(te.mark_distribution, 'full_mark\":\"', -1), '\"', 1))
                AS DECIMAL)
            ) AS total_marks,
            (SUM(
                CAST(
                    TRIM(BOTH '\"' FROM SUBSTRING_INDEX(SUBSTRING_INDEX(m.mark, ':', -1), '\"', 2))
                AS DECIMAL)
            ) / 
            SUM(
                CAST(
                    TRIM(BOTH '\"' FROM SUBSTRING_INDEX(SUBSTRING_INDEX(te.mark_distribution, 'full_mark\":\"', -1), '\"', 1))
                AS DECIMAL)
            ) * 100) AS percentage
        FROM mark m
        JOIN subject s ON m.subject_id = s.id
        JOIN timetable_exam te ON m.exam_id = te.id
        WHERE m.student_id = ?
            AND m.class_id = ?
            AND m.branch_id = ?
            AND m.section_id = ?
            AND m.subject_id = ?
        GROUP BY 
            m.student_id,
            m.class_id,
            m.branch_id,
            m.section_id,
            m.subject_id,
            s.name";

        return $this->db->query($sql, [$studentID, $classID, $branchID, $sectionID, $subjectID])->row_array();
    }


    // ONLINE EXAM


    public function getUserDetails()
    {
        if (is_student_loggedin()) {
            $studentID = get_loggedin_user_id();
            $this->db->select('*,CONCAT_WS(" ",first_name, last_name) as name, current_address as address');
            $this->db->from('student');
        } elseif (is_parent_loggedin()) {
            $this->db->select('*');
            $this->db->from('parent');
        }
        $this->db->where('id', get_loggedin_user_id());
        return $this->db->get()->row_array();
    }

    public function examListDT_Old($postData, $currency_symbol = '')
    {
        date_default_timezone_set('Asia/Kolkata');

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

        $enrollID = $this->session->userdata('enrollID');
        $enroll = $this->db->select('*')->where(array('id' => $enrollID))->get('enroll')->row();

        $branch_id = $this->db->escape(get_loggedin_branch_id());
        $search_arr[] = " `online_exam`.`branch_id` = $branch_id AND `online_exam`.`class_id` = " . $this->db->escape($enroll->class_id);

        // order
        $column_order[] = '`online_exam`.`title`';
        $column_order[] = '`class`.`id`';
        $column_order[] = '';
        $column_order[] = '`questions_qty`';
        $column_order[] = '`online_exam`.`exam_start`';
        $column_order[] = '`online_exam`.`exam_end`';
        $column_order[] = '`online_exam`.`duration`';

        if (count($search_arr) > 0) {
            $searchQuery = implode(" AND ", $search_arr);
        }

        // Total number of records without filtering
        $totalRecords = 0;

        // Total number of record with filtering
        $sql = "SELECT `section_id` FROM `online_exam` WHERE `publish_status` = '1'";
        if (!empty($searchQuery)) {
            $sql .= " AND " . $searchQuery;
        }
        $records = $this->db->query($sql)->result();
        $count = 0;
        foreach ($records as $key => $value) {
            $array = json_decode($value->section_id, true);
            if (in_array($enroll->section_id, $array)) {
                $count++;
            }
        }
        $totalRecordwithFilter = $count;

        // Fetch records
        $studentID = $this->db->escape(get_loggedin_user_id());
        $sql = "SELECT `online_exam`.*, `class`.`name` as `class_name`,(SELECT COUNT(`id`) FROM `questions_manage` WHERE `questions_manage`.`onlineexam_id`=`online_exam`.`id`) as `questions_qty`, (SELECT COUNT(`id`) FROM `online_exam_payment` WHERE `online_exam_payment`.`exam_id`=`online_exam`.`id` AND `online_exam_payment`.`student_id`= $studentID) as `payment_status`,`branch`.`name` as `branchname` FROM `online_exam` INNER JOIN `branch` ON `branch`.`id` = `online_exam`.`branch_id` LEFT JOIN `class` ON `class`.`id` = `online_exam`.`class_id` WHERE `publish_status` = '1'";
        if (!empty($searchQuery)) {
            $sql .= " AND " . $searchQuery;
        }
        $sql .= " ORDER BY " . $column_order[$columnIndex] . " $columnSortOrder LIMIT $start, $rowperpage";
        // log_message('error', 'Exam List SQL: ' . $sql);
        $records = $this->db->query($sql)->result();
        // exit;

        $data = array();
        $count = $start + 1;
        foreach ($records as $record) {
            $array = json_decode($record->section_id, true);
            if (in_array($enroll->section_id, $array)) {
                $startTime = strtotime($record->exam_start);
                $endTime = strtotime($record->exam_end);
                $now = strtotime("now");
                $examSubmitted = $this->onlineexam_model->getStudentSubmitted($record->id);
                $status = '';
                $labelmode = '';
                $takeExam = 0;

                log_message('error', "======== Exam Debug Info ========");
                log_message('error', 'Server Timezone: ' . date_default_timezone_get());
                log_message('error', 'Local Server Time: ' . date('Y-m-d H:i:s'));
                log_message('error', 'Exam Start (DB): ' . $record->exam_start . ' | Timestamp: ' . strtotime($record->exam_start));
                log_message('error', 'Exam End   (DB): ' . $record->exam_end . ' | Timestamp: ' . strtotime($record->exam_end));
                log_message('error', 'Now (Server PHP): ' . date('Y-m-d H:i:s', $now) . ' | Timestamp: ' . $now);



                // exam status
                if ($record->publish_result == 1 && !empty($examSubmitted)) {
                    $status = translate('result_published');
                    $labelmode = 'label-success-custom';
                } else {
                    if (!empty($examSubmitted)) {
                        $status = '<i class="fas fa-check fa-fw"></i> ' . translate('already_submitted');
                        $labelmode = 'label-success-custom';

                    } elseif ($startTime <= $now && $now <= $endTime) {
                        $status = translate('live');
                        $labelmode = 'label-warning-custom';
                        $takeExam = 1;
                    } elseif ($startTime >= $now && $now <= $endTime) {
                        $status = '<i class="far fa-clock"></i> ' . translate('waiting');
                        $labelmode = 'label-info-custom';
                    } elseif ($now >= $endTime) {
                        $status = translate('closed');
                        $labelmode = 'label-danger-custom';
                    }
                }
                $row = array();
                $action = "";
                $paymentStatus = 0;
                if ($record->exam_type == 1 && $record->payment_status == 0) {
                    $paymentStatus = 1;
                }
                if ($takeExam == 1) {
                    $url = base_url('userrole/onlineexam_take/' . $record->id);
                    if ($paymentStatus == 1) {
                        $action .= '<a href="javascript:void(0);" onclick="paymentModal(' . $this->db->escape($record->id) . ')" class="btn btn-circle btn-default"> <i class="fas fa-credit-card"></i> ' . translate('pay') . " & " . translate('take_exam') . '</a>';
                    } else {
                        $action .= '<a href="' . $url . '" class="btn btn-circle btn-default"> <i class="fas fa-users-between-lines"></i> ' . translate('take_exam') . '</a>';
                    }
                } else {
                    if ($record->publish_result == 1 && !empty($examSubmitted)) {
                        $action .= '<a href="javascript:void(0);" onclick="getStudentResult(' . $this->db->escape($record->id) . ')" class="btn btn-circle btn-default"> <i class="fas fa-users-viewfinder"></i> ' . translate('view') . " " . translate('result') . '</a>';
                    } else {
                        $action .= '<a href="javascript:void(0);" disabled class="btn btn-circle btn-default"> <i class="fas fa-users-between-lines"></i> ' . translate('take_exam') . '</a>';
                    }
                }
                $row[] = $count++;
                $row[] = $record->title;
                $row[] = $record->class_name . " (" . $this->onlineexam_model->getSectionDetails($record->section_id) . ")";
                $row[] = $this->onlineexam_model->getSubjectDetails($record->subject_id);
                $row[] = $record->questions_qty;
                $row[] = _d($record->exam_start) . "<p class='text-muted'>" . date("h:i A", strtotime($record->exam_start)) . "</p>";
                $row[] = _d($record->exam_end) . "<p class='text-muted'>" . date("h:i A", strtotime($record->exam_end)) . "</p>";
                $row[] = $record->duration;
                $row[] = $record->exam_type == 0 ? translate('free') : $currency_symbol . $record->fee;
                $row[] = "<span class='label " . $labelmode . " '>" . $status . "</span>";
                $row[] = $action;
                $data[] = $row;
            }
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

        $enrollID = $this->session->userdata('enrollID');
        $enroll = $this->db->select('*')->where('id', $enrollID)->get('enroll')->row();
        $branch_id = get_loggedin_branch_id();
        $class_id = $enroll->class_id;
        $section_id = $enroll->section_id;

        // ✅ Fetch exams assigned to this branch from exam_assignment table
        $assignedExamIDs = $this->db->select('exam_id')
            ->where('branch_id', $branch_id)
            ->get('exam_assignment')
            ->result_array();
        $assignedExamIDs = array_column($assignedExamIDs, 'exam_id');

        // log_message('error', 'Assigned Exam IDs: ' . print_r($assignedExamIDs, true));

        // ✅ Build WHERE condition: created_by_branch OR assigned to branch
        $search_arr[] = " `online_exam`.`class_id` = " . $this->db->escape($class_id) . " ";
        $search_arr[] = " (
        `online_exam`.`created_by_branch` IS NULL
        OR `online_exam`.`created_by_branch` = " . $this->db->escape($branch_id) . "
        OR `online_exam`.`id` IN (" . implode(',', $assignedExamIDs ?: [0]) . ")
    )";

        if (count($search_arr) > 0) {
            $searchQuery = implode(" AND ", $search_arr);
        }

        // ✅ Total number of records with filtering
        $sql = "SELECT `id`, `section_id` FROM `online_exam`
            WHERE `publish_status` = '1' AND " . $searchQuery;

        // log_message('error', 'Exam Count Query: ' . $sql);
        $records = $this->db->query($sql)->result();

        $totalRecords = 0;
        $totalRecordwithFilter = 0;

        foreach ($records as $record) {
            $array = json_decode($record->section_id, true);
            if ((is_array($array) && in_array($section_id, $array)) || $record->section_id == $section_id) {
                $totalRecords++;
                $totalRecordwithFilter++;
            }
        }

        // ✅ Fetch records with pagination
        $studentID = $this->db->escape(get_loggedin_user_id());
        $sql = "SELECT `online_exam`.*, `class`.`name` as `class_name`,
            (SELECT COUNT(`id`) FROM `questions_manage` WHERE `questions_manage`.`onlineexam_id`=`online_exam`.`id`) as `questions_qty`,
            (SELECT COUNT(`id`) FROM `online_exam_payment` WHERE `online_exam_payment`.`exam_id`=`online_exam`.`id` AND `online_exam_payment`.`student_id`= $studentID) as `payment_status`,
            `branch`.`name` as `branchname`
            FROM `online_exam`
            LEFT JOIN `branch` ON `branch`.`id` = `online_exam`.`created_by_branch`
            LEFT JOIN `class` ON `class`.`id` = `online_exam`.`class_id`
            WHERE `publish_status` = '1' AND " . $searchQuery . "
            ORDER BY " . $column_order[$columnIndex] . " $columnSortOrder
            LIMIT $start, $rowperpage";

        // log_message('error', 'Exam Fetch Query: ' . $sql);
        $records = $this->db->query($sql)->result();

        $data = array();
        $count = $start + 1;

        foreach ($records as $record) {
            $array = json_decode($record->section_id, true);
            if ((is_array($array) && in_array($section_id, $array)) || $record->section_id == $section_id) {
                $startTime = strtotime($record->exam_start);
                $endTime = strtotime($record->exam_end);
                $now = time();
                $examSubmitted = $this->onlineexam_model->getStudentSubmitted($record->id);
                $status = '';
                $labelmode = '';
                $takeExam = 0;

                // exam status
                if ($record->publish_result == 1 && !empty($examSubmitted)) {
                    $status = translate('result_published');
                    $labelmode = 'label-success-custom';
                } else {
                    if (!empty($examSubmitted)) {
                        $status = '<i class="fas fa-check fa-fw"></i> ' . translate('already_submitted');
                        $labelmode = 'label-success-custom';
                    } elseif ($startTime <= $now && $now <= $endTime) {
                        $status = translate('live');
                        $labelmode = 'label-warning-custom';
                        $takeExam = 1;
                    } elseif ($startTime >= $now && $now <= $endTime) {
                        $status = '<i class="far fa-clock"></i> ' . translate('waiting');
                        $labelmode = 'label-info-custom';
                    } elseif ($now >= $endTime) {
                        $status = translate('closed');
                        $labelmode = 'label-danger-custom';
                    }
                }

                $row = array();
                $action = "";
                $paymentStatus = ($record->exam_type == 1 && $record->payment_status == 0) ? 1 : 0;

                if ($takeExam == 1) {
                    $url = base_url('userrole/onlineexam_take/' . $record->id);
                    if ($paymentStatus == 1) {
                        $action .= '<a href="javascript:void(0);" onclick="paymentModal(' . $this->db->escape($record->id) . ')" class="btn btn-circle btn-default"> <i class="fas fa-credit-card"></i> ' . translate('pay') . " & " . translate('take_exam') . '</a>';
                    } else {
                        $action .= '<a href="' . $url . '" class="btn btn-circle btn-default"> <i class="fas fa-users-between-lines"></i> ' . translate('take_exam') . '</a>';
                    }
                } else {
                    if ($record->publish_result == 1 && !empty($examSubmitted)) {
                        $action .= '<a href="javascript:void(0);" onclick="getStudentResult(' . $this->db->escape($record->id) . ')" class="btn btn-circle btn-default"> <i class="fas fa-users-viewfinder"></i> ' . translate('view') . " " . translate('result') . '</a>';
                    } else {
                        $action .= '<a href="javascript:void(0);" disabled class="btn btn-circle btn-default"> <i class="fas fa-users-between-lines"></i> ' . translate('take_exam') . '</a>';
                    }
                }

                $row[] = $count++;
                $row[] = $record->title;
                $row[] = $record->class_name . " (" . $this->onlineexam_model->getSectionDetails($record->section_id) . ")";
                $row[] = $this->onlineexam_model->getSubjectDetails($record->subject_id);
                $row[] = $record->questions_qty;
                $row[] = _d($record->exam_start) . "<p class='text-muted'>" . date("h:i A", strtotime($record->exam_start)) . "</p>";
                $row[] = _d($record->exam_end) . "<p class='text-muted'>" . date("h:i A", strtotime($record->exam_end)) . "</p>";
                $row[] = $record->duration;
                $row[] = $record->exam_type == 0 ? translate('free') : $currency_symbol . $record->fee;
                $row[] = "<span class='label " . $labelmode . " '>" . $status . "</span>";
                $row[] = $action;
                $data[] = $row;
            }
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



    public function getExamDetails_Old($onlineexamID)
    {
        $student = $this->getStudentDetails();
        $classID = $student['class_id'];
        $sectionID = $student['section_id'];
        $onlineexamID = $this->db->escape($onlineexamID);
        $sessionID = $this->db->escape(get_session_id());
        $branchID = $this->db->escape(get_loggedin_branch_id());
        $studentID = $this->db->escape(get_loggedin_user_id());
        $sql = "SELECT `online_exam`.*, `class`.`name` as `class_name`,(SELECT COUNT(`id`) FROM `questions_manage` WHERE `questions_manage`.`onlineexam_id`=`online_exam`.`id`) as `questions_qty`,(SELECT COUNT(`id`) FROM `online_exam_payment` WHERE `online_exam_payment`.`exam_id`=`online_exam`.`id` AND `online_exam_payment`.`student_id`=$studentID) as `payment_status`, `branch`.`name` as `branchname` FROM `online_exam` INNER JOIN `branch` ON `branch`.`id` = `online_exam`.`branch_id` LEFT JOIN `class` ON `class`.`id` = `online_exam`.`class_id` WHERE `online_exam`.`session_id` = $sessionID AND `online_exam`.`publish_status` = '1' AND `online_exam`.`id` = $onlineexamID AND `online_exam`.`branch_id` = $branchID AND `online_exam`.`class_id` = $classID";
        $records = $this->db->query($sql)->row();
        $sectionList = json_decode($records->section_id, true);
        if (in_array($sectionID, $sectionList)) {
            return $records;
        } else {
            return [];
        }
    }

    public function getExamDetails($onlineexamID)
    {
        $student = $this->getStudentDetails();
        $classID = $student['class_id'];
        $sectionID = $student['section_id'];
        $onlineexamID = $this->db->escape($onlineexamID);
        $sessionID = $this->db->escape(get_session_id());
        $branchID = $this->db->escape(get_loggedin_branch_id());
        $studentID = $this->db->escape(get_loggedin_user_id());

        // Fetch assigned exams to this branch
        $assignedExams = $this->db->select('exam_id')
            ->where('branch_id', get_loggedin_branch_id())
            ->get('exam_assignment')
            ->result_array();

        $assignedExamIDs = array_column($assignedExams, 'exam_id');
        $assignedExamIDsStr = !empty($assignedExamIDs) ? implode(',', $assignedExamIDs) : '0';

        $sql = "SELECT `online_exam`.*, `class`.`name` as `class_name`,
        (SELECT COUNT(`id`) FROM `questions_manage` WHERE `questions_manage`.`onlineexam_id`=`online_exam`.`id`) as `questions_qty`,
        (SELECT COUNT(`id`) FROM `online_exam_payment` WHERE `online_exam_payment`.`exam_id`=`online_exam`.`id` AND `online_exam_payment`.`student_id`=$studentID) as `payment_status`,
        `branch`.`name` as `branchname`
        FROM `online_exam`
        LEFT JOIN `branch` ON `branch`.`id` = `online_exam`.`created_by_branch`
        LEFT JOIN `class` ON `class`.`id` = `online_exam`.`class_id`
        WHERE `online_exam`.`session_id` = $sessionID
        AND `online_exam`.`publish_status` = '1'
        AND `online_exam`.`id` = $onlineexamID
        AND `online_exam`.`class_id` = $classID
        AND (
            `online_exam`.`created_by_branch` IS NULL
            OR `online_exam`.`created_by_branch` = $branchID
            OR `online_exam`.`id` IN ($assignedExamIDsStr)
        )";

        $records = $this->db->query($sql)->row();

        // Check section mapping
        if ($records) {
            $sectionList = json_decode($records->section_id, true);
            if (in_array($sectionID, $sectionList)) {
                return $records;
            }
        }

        return [];
    }



}
