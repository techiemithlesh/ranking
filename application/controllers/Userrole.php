<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @package : Schoolexcel school management system
 * @version : 2.0
 * @developed by : eduprojectsCoder
 * @support : techie.mithlesh@gmail.com
 * @author url : http://codewithmithlesh.com
 * @filename : Userrole.php
 * @copyright : Reserved EduprojectGlobalTech Team
 */

class Userrole extends User_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('userrole_model');
        $this->load->model('leave_model');
        $this->load->model('fees_model');
        $this->load->model('exam_model');
        $this->load->model('report_model');
        $this->load->model('onlineexam_model');
        $this->load->model('reward_model');
        $this->load->library('reward_lib');

    }

    public function index()
    {
        redirect(base_url(), 'refresh');
    }

    /* getting all teachers list */
    // public function teachers()
    // {
    //     $this->data['title'] = translate('teachers');
    //     $this->data['sub_page'] = 'userrole/teachers';
    //     $this->data['main_menu'] = 'teachers';
    //     $this->load->view('layout/index', $this->data);
    // }


    public function teachers()
    {

        $data['userData'] = $this->session->userdata();
        $user_id = $data['userData']['loggedin_userid'];

        $this->db->select('class_id, section_id, branch_id');
        $this->db->from('enroll');
        $this->db->where('student_id', $user_id);
        $enrollData = $this->db->get()->row_array();

        $class_id = $enrollData['class_id'];

        $this->db->select('ta.*, st.name as teacher_name, st.staff_id as teacher_id, c.name as class_name, c.branch_id, s.name as section_name');
        $this->db->from('teacher_allocation as ta');
        $this->db->join('staff as st', 'st.id = ta.teacher_id', 'left');
        $this->db->join('class as c', 'c.id = ta.class_id', 'left');
        $this->db->join('section as s', 's.id = ta.section_id', 'left');
        $this->db->where('ta.class_id', $class_id);
        $this->db->where('ta.session_id', get_session_id());
        $this->db->order_by('ta.id', 'ASC');
        $teachers = $this->db->get()->result_array();

        $this->data['title'] = translate('teachers');
        $this->data['sub_page'] = 'userrole/teachers';
        $this->data['main_menu'] = 'teachers';
        $this->data['employees'] = $teachers;

        $this->load->view('layout/index', $this->data);
    }

    public function subject()
    {
        $this->data['title'] = translate('subject');
        $this->data['sub_page'] = 'userrole/subject';
        $this->data['main_menu'] = 'academic';
        $this->load->view('layout/index', $this->data);
    }


    public function books_upload_list()
    {
        $student_data = $this->userrole_model->getStudentDetails();
        $branch_id = $student_data['branch_id'];
        $class_id = $student_data['class_id'];
        if (empty($student_data)) {
            return;
        }

        $this->data['booklist'] = $this->getBookUploadsList($student_data['class_id'], $student_data['branch_id']);
        $this->data['title'] = translate('Books Link');
        $this->data['sub_page'] = 'userrole/uploadbook_link';
        $this->data['main_menu'] = 'BookUpload';
        $this->load->view('layout/index', $this->data);
    }

    public function my_gallery()
    {
        if (!is_student_loggedin()) {
            access_denied();
        }

        $stu = $this->userrole_model->getStudentDetails();
        $branchId = $stu['branch_id'];
        $classId = $stu['class_id'];
        $sectionId = $stu['section_id'];

        $this->data['gallery'] = $this->userrole_model->getGallery($branchId, $classId, $sectionId);
        if (empty($this->data['gallery'])) {
            set_alert('info', translate('no_gallery_found'));
        }

        $this->data['branch_id'] = $this->application_model->get_branch_id();
        $this->data['title'] = translate('my_Gallery');
        $this->data['sub_page'] = 'userrole/gallery';
        $this->data['main_menu'] = 'my_gallery';
        $this->load->view('layout/index', $this->data);
    }

    // DIGITAL LIBRARY
    public function digitalBook()
    {
        $student_data = $this->userrole_model->getStudentDetails();
        $branch_id = $student_data['branch_id'];
        $class_id = $student_data['class_id'];

        if (empty($student_data)) {
            return;
        }
        $this->data['booklist'] = $this->getDigitalBook($student_data['class_id'], $student_data['branch_id']);
        $this->data['title'] = translate('Books Link');
        $this->data['sub_page'] = 'userrole/digital_book';
        $this->data['main_menu'] = 'Smart_library';
        $this->load->view('layout/index', $this->data);
    }


    public function getDigitalBook($class_id, $branch_id)
    {
        $this->db->select('sb.title, sb.book_url, sb.book_img');
        $this->db->from('tbl_digital_library as sb');
        $this->db->join('tbl_digital_library_class as cb', 'cb.digital_id = sb.id', 'left');
        $this->db->join('class as c', 'c.id = cb.class_id', 'left');
        $this->db->where('cb.class_id', $class_id);
        $this->db->where('cb.branch_id', $branch_id);

        $query = $this->db->get();
        return $query->result_array();
    }

    public function getBookUploadsList($class_id, $branch_id)
    {
        $this->db->select('sb.title, sb.book_url, sb.book_img');
        $this->db->from('student_books as sb');
        $this->db->join('class_books as cb', 'cb.book_id = sb.id', 'left');
        $this->db->join('class as c', 'c.id = cb.class_id', 'left');
        $this->db->where('cb.class_id', $class_id);
        $this->db->where('cb.branch_id', $branch_id);

        $query = $this->db->get();
        return $query->result_array();
    }

    public function getBookListForStudent($class_id, $branch_id)
    {
        $this->db->select('sb.title, sb.book_url, sb.book_img');
        $this->db->from('student_books as sb');
        $this->db->join('class_books as cb', 'cb.book_id = sb.id', 'left');
        $this->db->join('class as c', 'c.id = cb.class_id', 'left');
        $this->db->where('cb.class_id', $class_id);
        $this->db->where('cb.branch_id', $branch_id);

        $query = $this->db->get();
        return $query->result_array();
    }

    public function getStudentPhotoAndNameById()
    {
        $stu = $this->userrole_model->getStudentDetails();

        if (!isset($stu['student_id'])) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'status' => 'error',
                    'message' => 'Student ID not found in session data.'
                ]));
            return;
        }

        $result = $this->db->get_where('student', ['id' => $stu['student_id']]);

        $response = ($result->num_rows() > 0)
            ? ['status' => 'success', 'data' => $result->row_array()]
            : ['status' => 'error', 'message' => 'Student not found in the database.'];

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }


    // PROGESS REPORT CARD
    public function progress()
    {
        $this->data['stu'] = $this->userrole_model->getStudentDetails();

        // Get exams for the student's branch
        $branch_id = $this->data['stu']['branch_id'];
        $this->data['exams'] = $this->db->get_where('exam', array(
            'branch_id' => $branch_id,
            'session_id' => get_session_id()
        ))->result();

        $this->data['title'] = translate('progress_report');
        $this->data['main_menu'] = 'exam';
        $this->data['sub_page'] = 'userrole/report/progress';
        $this->load->view('layout/index', $this->data);
    }

    public function setExamId()
    {
        $examId = $this->input->post('exam_id');

        if (empty($examId)) {
            echo json_encode(["status" => false, "message" => "Exam Missing"]);
            return false;
        }

        $student = $this->userrole_model->getStudentDetails();
        $reportCardData = [
            'exam_id' => $examId,
            'class_id' => $student['class_id'],
            'section_id' => $student['section_id'],
            'branch_id' => $student['branch_id'],
            'student_id' => $student['student_id']
        ];

        $this->session->set_userdata('report_card_data', $reportCardData);
        echo json_encode(["status" => true, "message" => "Success"]);

    }

    public function annual_examination_report()
    {
        $reportCardData = $this->session->userdata('report_card_data');
        // printVar($reportCardData);

        if (empty($reportCardData)) {
            redirect(base_url('userrole/progress'));
        }

        $sql = "
            SELECT *
            FROM mark
            JOIN (
                SELECT timetable_exam.*, subject.name as subject_name
                FROM timetable_exam
                JOIN subject ON subject.id = timetable_exam.subject_id
                JOIN exam ON exam.id = timetable_exam.exam_id
                WHERE timetable_exam.exam_id = " . $this->db->escape($reportCardData['exam_id']) . "
                AND timetable_exam.class_id = " . $this->db->escape($reportCardData['class_id']) . "
                AND timetable_exam.section_id = " . $this->db->escape($reportCardData['section_id']) . "
                AND timetable_exam.branch_id = " . $this->db->escape($reportCardData['branch_id']) . "
            ) as subject ON subject.subject_id = mark.subject_id
             AND subject.exam_id = mark.exam_id
            WHERE mark.student_id = " . $this->db->escape($reportCardData['student_id']) . "
            ORDER BY mark.subject_id ASC
        ";

        $student_id = $reportCardData['student_id'];
        $branch_id = $reportCardData['branch_id'];
        $exam_id = $reportCardData["exam_id"];
        $this->data = array();
        $this->data['subjects'] = $this->db->query($sql)->result_array();
        $this->data["examName"] = $this->db->query("select id,name as exam_name from exam where id = " . $reportCardData["exam_id"])->result_array()[0] ?? [];

        $this->data['branchData'] = $this->db->query("SELECT * FROM branch WHERE id='" . $branch_id . "'")->result_array()[0] ?? [];

        $studentMpped = $this->report_model->getStudentDetails($student_id);
        $this->data['studentMpped'] = json_decode(json_encode($studentMpped), true);
        [0] ?? [];

        $classId = $studentMpped->class_id;
        $sectionId = $studentMpped->section_id;

        $attendenceReport = $this->db->query(
            "SELECT * FROM student_attendance_report WHERE student_id = ? AND branch_id = ? AND exam_id = ?",
            [$student_id, $branch_id, $exam_id]
        )->row_array();

        if ($attendenceReport) {
            $presentDays = $attendenceReport['present_days'];
            $absentDays = $attendenceReport['absent_days'];
        }

        $this->data['presentDays'] = $presentDays;
        $this->data['absentDays'] = $absentDays;
        $totalDays = $presentDays + $absentDays;

        foreach ($this->data['subjects'] as $key => $item) {
            $mark["obtainMark"] = array_values(json_decode($item["mark"], true))[0] ?? 0;
            $this->data["subjects"][$key] = array_merge($this->data["subjects"][$key], $mark, array_values(json_decode($item["mark_distribution"], true))[0] ?? []);
        }

        $this->data['class_average'] = $this->getClassAverageByExamId($branch_id, $classId, $sectionId, $exam_id);

        $this->data['title'] = 'Exam Based - Matterhorn Report';
        $this->load->view('userrole/report/annual_examination_report/report', $this->data);
    }

    public function getClassAverageByExamId($branchId, $classId, $sectionId, $exam_id)
    {
        $params = [$classId, $sectionId, $branchId, $exam_id];
        $query = "SELECT 
            m.subject_id,
            AVG(
                CAST(
                    TRIM(BOTH '\"' FROM SUBSTRING_INDEX(SUBSTRING_INDEX(m.mark, ':', -1), '\"', 2))
                AS DECIMAL)
            ) AS avg_marks_obtained
        FROM 
            mark m
        WHERE 
            m.class_id = ? 
            AND m.section_id = ? 
            AND m.branch_id = ? 
            AND m.exam_id = ?
        GROUP BY 
            m.subject_id";
        $queryResult = $this->db->query($query, $params)->result_array();

        if (empty($queryResult)) {
            return [];
        }
        $classAverages = [];
        foreach ($queryResult as $result) {
            $classAverages[$result['subject_id']] = $result['avg_marks_obtained'];
        }

        return $classAverages;
    }


    /* student or parent timetable preview page */
    public function class_schedule()
    {
        $stu = $this->userrole_model->getStudentDetails();
        $arrayTimetable = array(
            'class_id' => $stu['class_id'],
            'section_id' => $stu['section_id'],
            'session_id' => get_session_id(),
        );
        $this->db->order_by('time_start', 'asc');
        $this->data['timetables'] = $this->db->get_where('timetable_class', $arrayTimetable)->result();
        $this->data['student'] = $stu;
        $this->data['title'] = translate('class') . " " . translate('schedule');
        $this->data['sub_page'] = 'userrole/class_schedule';
        $this->data['main_menu'] = 'academic';
        $this->load->view('layout/index', $this->data);
    }


    public function leave_request()
    {
        $stu = $this->userrole_model->getStudentDetails();
        if (isset($_POST['save'])) {
            $this->form_validation->set_rules('leave_category', translate('leave_category'), 'required');
            $this->form_validation->set_rules('daterange', translate('leave_date'), 'trim|required|callback_date_check');
            $this->form_validation->set_rules('attachment_file', translate('attachment'), 'callback_handle_upload');
            if ($this->form_validation->run() !== false) {
                $leave_type_id = $this->input->post('leave_category');
                $branch_id = $this->application_model->get_branch_id();
                $daterange = explode(' - ', $this->input->post('daterange'));
                $start_date = date("Y-m-d", strtotime($daterange[0]));
                $end_date = date("Y-m-d", strtotime($daterange[1]));
                $reason = $this->input->post('reason');
                $apply_date = date("Y-m-d H:i:s");
                $datetime1 = new DateTime($start_date);
                $datetime2 = new DateTime($end_date);
                $leave_days = $datetime2->diff($datetime1)->format("%a") + 1;
                $orig_file_name = '';
                $enc_file_name = '';
                // upload attachment file
                if (isset($_FILES["attachment_file"]) && !empty($_FILES['attachment_file']['name'])) {
                    $config['upload_path'] = './uploads/attachments/leave/';
                    $config['allowed_types'] = "*";
                    $config['max_size'] = '2024';
                    $config['encrypt_name'] = true;
                    $this->upload->initialize($config);
                    $this->upload->do_upload("attachment_file");
                    $orig_file_name = $this->upload->data('orig_name');
                    $enc_file_name = $this->upload->data('file_name');
                }
                $arrayData = array(
                    'user_id' => $stu['student_id'],
                    'role_id' => 7,
                    'session_id' => get_session_id(),
                    'category_id' => $leave_type_id,
                    'reason' => $reason,
                    'branch_id' => $branch_id,
                    'start_date' => date("Y-m-d", strtotime($start_date)),
                    'end_date' => date("Y-m-d", strtotime($end_date)),
                    'leave_days' => $leave_days,
                    'status' => 1,
                    'orig_file_name' => $orig_file_name,
                    'enc_file_name' => $enc_file_name,
                    'apply_date' => $apply_date,
                );
                $this->db->insert('leave_application', $arrayData);
                set_alert('success', translate('information_has_been_saved_successfully'));
                redirect(base_url('userrole/leave_request'));
            }
        }
        $where = array('la.user_id' => $stu['student_id'], 'la.role_id' => 7);
        $this->data['leavelist'] = $this->leave_model->getLeaveList($where);
        $this->data['title'] = translate('leaves');
        $this->data['sub_page'] = 'userrole/leave_request';
        $this->data['main_menu'] = 'leave';
        $this->data['headerelements'] = array(
            'css' => array(
                'vendor/dropify/css/dropify.min.css',
                'vendor/daterangepicker/daterangepicker.css',
            ),
            'js' => array(
                'vendor/dropify/js/dropify.min.js',
                'vendor/moment/moment.js',
                'vendor/daterangepicker/daterangepicker.js',
            ),
        );
        $this->load->view('layout/index', $this->data);
    }

    //  date check for leave request
    public function date_check($daterange)
    {
        $daterange = explode(' - ', $daterange);
        $start_date = date("Y-m-d", strtotime($daterange[0]));
        $end_date = date("Y-m-d", strtotime($daterange[1]));
        $today = date('Y-m-d');
        if ($today == $start_date) {
            $this->form_validation->set_message('date_check', "You can not leave the current day.");
            return false;
        }
        if ($this->input->post('applicant_id')) {
            $applicant_id = $this->input->post('applicant_id');
            $role_id = $this->input->post('user_role');
        } else {
            $applicant_id = get_loggedin_user_id();
            $role_id = loggedin_role_id();
        }
        $getUserLeaves = $this->db->get_where('leave_application', array('user_id' => $applicant_id, 'role_id' => $role_id))->result();
        if (!empty($getUserLeaves)) {
            foreach ($getUserLeaves as $user_leave) {
                $get_dates = $this->user_leave_days($user_leave->start_date, $user_leave->end_date);
                $result_start = in_array($start_date, $get_dates);
                $result_end = in_array($end_date, $get_dates);
                if (!empty($result_start) || !empty($result_end)) {
                    $this->form_validation->set_message('date_check', 'Already have leave in the selected time.');
                    return false;
                }
            }
        }
        return true;
    }

    public function leave_check($type_id)
    {
        if (!empty($type_id)) {
            $daterange = explode(' - ', $this->input->post('daterange'));
            $start_date = date("Y-m-d", strtotime($daterange[0]));
            $end_date = date("Y-m-d", strtotime($daterange[1]));

            if ($this->input->post('applicant_id')) {
                $applicant_id = $this->input->post('applicant_id');
                $role_id = $this->input->post('user_role');
            } else {
                $applicant_id = get_loggedin_user_id();
                $role_id = loggedin_role_id();
            }
            if (!empty($start_date) && !empty($end_date)) {
                $leave_total = get_type_name_by_id('leave_category', $type_id, 'days');
                $total_spent = $this->db->select('IFNULL(SUM(leave_days), 0) as total_days')
                    ->where(array('user_id' => $applicant_id, 'role_id' => $role_id, 'category_id' => $type_id, 'status' => '2'))
                    ->get('leave_application')->row()->total_days;

                $datetime1 = new DateTime($start_date);
                $datetime2 = new DateTime($end_date);
                $leave_days = $datetime2->diff($datetime1)->format("%a") + 1;
                $left_leave = ($leave_total - $total_spent);
                if ($left_leave < $leave_days) {
                    $this->form_validation->set_message('leave_check', "Applyed for $leave_days days, get maximum $left_leave Days days.");
                    return false;
                } else {
                    return true;
                }
            } else {
                $this->form_validation->set_message('leave_check', "Select all required field.");
                return false;
            }
        }
    }

    public function user_leave_days($start_date, $end_date)
    {
        $dates = array();
        $current = strtotime($start_date);
        $end_date = strtotime($end_date);
        while ($current <= $end_date) {
            $dates[] = date('Y-m-d', $current);
            $current = strtotime('+1 day', $current);
        }
        return $dates;
    }

    public function handle_upload()
    {
        if (isset($_FILES["attachment_file"]) && !empty($_FILES['attachment_file']['name'])) {
            $file_type = $_FILES["attachment_file"]['type'];
            $file_size = $_FILES["attachment_file"]["size"];
            $file_name = $_FILES["attachment_file"]["name"];
            $allowedExts = array('pdf', 'doc', 'xls', 'docx', 'xlsx', 'jpg', 'jpeg', 'png', 'gif', 'bmp');
            $upload_size = 2097152;
            $extension = pathinfo($file_name, PATHINFO_EXTENSION);
            if ($files = filesize($_FILES['attachment_file']['tmp_name'])) {
                if (!in_array(strtolower($extension), $allowedExts)) {
                    $this->form_validation->set_message('handle_upload', translate('this_file_type_is_not_allowed'));
                    return false;
                }
                if ($file_size > $upload_size) {
                    $this->form_validation->set_message('handle_upload', translate('file_size_shoud_be_less_than') . " " . ($upload_size / 1024) . " KB");
                    return false;
                }
            } else {
                $this->form_validation->set_message('handle_upload', translate('error_reading_the_file'));
                return false;
            }
            return true;
        } else {
            return true;
        }
    }

    public function attachments()
    {
        $this->data['title'] = translate('attachments');
        $this->data['sub_page'] = 'userrole/attachments';
        $this->data['main_menu'] = 'attachments';
        $this->load->view('layout/index', $this->data);
    }

    /* exam timetable preview page */
    public function exam_schedule()
    {
        $stu = $this->userrole_model->getStudentDetails();

        $this->data['student'] = $stu;
        $this->db->select('*');
        $this->db->from('timetable_exam');
        $this->db->where('branch_id', $stu['branch_id']);
        $this->db->where('class_id', $stu['class_id']);
        $this->db->where('section_id', $stu['section_id']);
        $this->db->where('session_id', get_session_id());
        $this->db->group_by('exam_id');

        $this->data['exams'] = $this->db->get()->result_array();
        $this->data['title'] = translate('exam') . " " . translate('schedule');
        $this->data['sub_page'] = 'userrole/exam_schedule';
        $this->data['main_menu'] = 'exam';
        $this->load->view('layout/index', $this->data);
    }

    /* hostels user interface */
    public function hostels()
    {
        $this->data['student'] = $this->userrole_model->getStudentDetails();
        $this->data['title'] = translate('hostels');
        $this->data['sub_page'] = 'userrole/hostels';
        $this->data['main_menu'] = 'supervision';
        $this->load->view('layout/index', $this->data);
    }

    /* route user interface */
    public function route()
    {
        $stu = $this->userrole_model->getStudentDetails();
        $this->data['route'] = $this->userrole_model->getRouteDetails($stu['route_id'], $stu['vehicle_id']);
        $this->data['title'] = translate('route_master');
        $this->data['sub_page'] = 'userrole/transport_route';
        $this->data['main_menu'] = 'supervision';
        $this->load->view('layout/index', $this->data);
    }

    /* after login students or parents produced reports here */
    public function attendance()
    {
        if ($this->input->post('submit') == 'search') {
            $this->data['month'] = date('m', strtotime($this->input->post('timestamp')));
            $this->data['year'] = date('Y', strtotime($this->input->post('timestamp')));
            $this->data['days'] = cal_days_in_month(CAL_GREGORIAN, $this->data['month'], $this->data['year']);
            $this->data['student'] = $this->userrole_model->getStudentDetails();
        }
        $this->data['title'] = translate('student_attendance');
        $this->data['sub_page'] = 'userrole/attendance';
        $this->data['main_menu'] = 'attendance';
        $this->load->view('layout/index', $this->data);
    }

    // book page
    public function book()
    {
        $this->data['booklist'] = $this->app_lib->getTable('book');
        $this->data['title'] = translate('books');
        $this->data['sub_page'] = 'userrole/book';
        $this->data['main_menu'] = 'library';
        $this->load->view('layout/index', $this->data);
    }

    public function book_request()
    {
        $stu = $this->userrole_model->getStudentDetails();
        if ($_POST) {
            $this->form_validation->set_rules('book_id', translate('book_title'), 'required|callback_validation_stock');
            $this->form_validation->set_rules('date_of_issue', translate('date_of_issue'), 'trim|required');
            $this->form_validation->set_rules('date_of_expiry', translate('date_of_expiry'), 'trim|required|callback_validation_date');
            if ($this->form_validation->run() !== false) {
                $arrayIssue = array(
                    'branch_id' => $stu['branch_id'],
                    'book_id' => $this->input->post('book_id'),
                    'user_id' => $stu['student_id'],
                    'role_id' => 7,
                    'date_of_issue' => date("Y-m-d", strtotime($this->input->post('date_of_issue'))),
                    'date_of_expiry' => date("Y-m-d", strtotime($this->input->post('date_of_expiry'))),
                    'issued_by' => get_loggedin_user_id(),
                    'status' => 0,
                    'session_id' => get_session_id(),
                );
                $this->db->insert('book_issues', $arrayIssue);
                set_alert('success', translate('information_has_been_saved_successfully'));
                $url = base_url('userrole/book_request');
                $array = array('status' => 'success', 'url' => $url, 'error' => '');
            } else {
                $error = $this->form_validation->error_array();
                $array = array('status' => 'fail', 'url' => '', 'error' => $error);
            }
            echo json_encode($array);
            exit();
        }
        $this->data['stu'] = $stu;
        $this->data['title'] = translate('library');
        $this->data['sub_page'] = 'userrole/book_request';
        $this->data['main_menu'] = 'library';
        $this->load->view('layout/index', $this->data);
    }

    // book date validation
    public function validation_date($date)
    {
        if ($date) {
            $date = strtotime($date);
            $today = strtotime(date('Y-m-d'));
            if ($today >= $date) {
                $this->form_validation->set_message("validation_date", translate('today_or_the_previous_day_can_not_be_issued'));
                return false;
            } else {
                return true;
            }
        }
    }

    // validation book stock
    public function validation_stock($book_id)
    {
        $query = $this->db->select('total_stock,issued_copies')->where('id', $book_id)->get('book')->row_array();
        $stock = $query['total_stock'];
        $issued = $query['issued_copies'];
        if ($stock == 0 || $issued >= $stock) {
            $this->form_validation->set_message("validation_stock", translate('the_book_is_not_available_in_stock'));
            return false;
        } else {
            return true;
        }
    }

    public function event()
    {
        $branchID = $this->application_model->get_branch_id();
        $this->data['branch_id'] = $branchID;
        $this->data['title'] = translate('events');
        $this->data['sub_page'] = 'userrole/event';
        $this->data['main_menu'] = 'event';
        $this->load->view('layout/index', $this->data);
    }

    /* invoice user interface with information are controlled here */
    public function invoice()
    {
        $stu = $this->userrole_model->getStudentDetails();
        $this->data['branch_logo'] = $this->fees_model->getBranchLogo($stu['branch_id']);
        $this->data['config'] = $this->get_payment_config();
        $this->data['invoice'] = $this->fees_model->getInvoiceStatus($stu['student_id']);
        $this->data['basic'] = $this->fees_model->getInvoiceBasic($stu['student_id']);
        $this->data['title'] = translate('fees_history');
        $this->data['main_menu'] = 'fees';
        $this->data['sub_page'] = 'userrole/collect';
        $this->load->view('layout/index', $this->data);
    }

    /* invoice user interface with information are controlled here */
    public function report_card()
    {
        $this->data['stu'] = $this->userrole_model->getStudentDetails();
        $this->data['title'] = translate('exam_master');
        $this->data['main_menu'] = 'exam';
        $this->data['sub_page'] = 'userrole/report_card';
        $this->load->view('layout/index', $this->data);
    }

    public function homework()
    {
        $stu = $this->userrole_model->getStudentDetails();
        $this->data['homeworklist'] = $this->userrole_model->getHomeworkList($stu['student_id']);
        $this->data['title'] = translate('homework');
        $this->data['main_menu'] = 'homework';
        $this->data['sub_page'] = 'userrole/homework';
        $this->load->view('layout/index', $this->data);
    }

    public function live_class()
    {
        if (!is_student_loggedin()) {
            access_denied();
        }
        $this->data['branch_id'] = $this->application_model->get_branch_id();
        $this->data['title'] = translate('live_class_rooms');
        $this->data['sub_page'] = 'userrole/live_class';
        $this->data['main_menu'] = 'live_class';
        $this->load->view('layout/index', $this->data);
    }

    public function joinModal()
    {
        if (!is_student_loggedin()) {
            access_denied();
        }
        $this->data['meetingID'] = $this->input->post('meeting_id');
        echo $this->load->view('userrole/live_classModal', $this->data, true);
    }

    public function livejoin()
    {
        if (!is_student_loggedin()) {
            access_denied();
        }
        $meetingID = $this->input->get('meeting_id', true);
        $liveID = $this->input->get('live_id', true);
        if (empty($meetingID) || empty($liveID)) {
            access_denied();
        }
        $this->load->view('userrole/livejoin', $this->data);
    }

    public function my_progress()
    {
        $data['stu'] = $this->userrole_model->getStudentDetails();
        $this->data['stu'] = $data['stu'];
        $this->data['progressDetails'] = [];

        if ($this->input->post()) {
            $this->form_validation->set_rules('subject_id', translate('Subject'), 'trim|required');

            if ($this->form_validation->run() == true) {
                $subjectId = $this->input->post('subject_id');
                $progress = $this->getSubjectProgress($subjectId);

                if (!empty($progress)) {
                    $this->data['progressDetails'] = $progress;
                }
            }
        }

        $this->data['branch_id'] = $this->application_model->get_branch_id();
        $this->data['title'] = translate('my_progress_tracker');
        $this->data['sub_page'] = 'userrole/report/tracker/index';
        $this->data['main_menu'] = 'exam';

        $this->load->view('layout/index', $this->data);
    }

    public function getSubjectByStudent()
    {
        $response = array();

        if (is_student_loggedin()) {
            $studentID = get_loggedin_user_id();
        } elseif (is_parent_loggedin()) {
            $studentID = get_activeChildren_id();
        }

        $studentDetail = $this->application_model->getStudentDetails($studentID);

        if (!empty($studentDetail)) {
            $subjects = $this->userrole_model->getSubjectByClass($studentDetail);

            if (!empty($subjects)) {
                foreach ($subjects as $subject) {
                    $response[$subject['id']] = $subject['name'];
                }
            }
        }

        echo json_encode($response);
    }
    public function getSubjectProgress($subjectId)
    {

        $response = array();

        if (is_student_loggedin()) {
            $studentID = get_loggedin_user_id();
        } elseif (is_parent_loggedin()) {
            $studentID = get_activeChildren_id();
        }

        $studentDetail = $this->application_model->getStudentDetails($studentID);

        if (!empty($studentDetail)) {

            $progress = $this->userrole_model->get_subject_progress(
                $studentDetail['branch_id'],
                $studentDetail['class_id'],
                $studentDetail['section_id'],
                $studentDetail['id'],
                $subjectId
            );

            if ($progress) {
                $response = array_merge($progress, [
                    'student_id' => $studentDetail['id'],
                    'subject_id' => $subjectId
                ]);
            }
        }

        return $response;
    }
    public function skillBasedReport()
    {
        $this->data['stu'] = $this->userrole_model->getStudentDetails();
        $branch_id = $this->data['stu']['branch_id'];
        $this->data['exams'] = $this->db->get_where('exam', array(
            'branch_id' => $branch_id,
            'session_id' => get_session_id()
        ))->result();

        $this->data['title'] = translate('progress_report');
        $this->data['main_menu'] = 'exam';
        $this->data['sub_page'] = 'userrole/report/skillbased/index';
        $this->data['main_menu'] = 'exam';

        $this->load->view('layout/index', $this->data);
    }

    public function ReportCardSessionSet()
    {
        $exam_id = $this->input->post('exam_id');
        $student_id = $this->input->post('student_id');

        if (empty($exam_id) || empty($student_id)) {
            echo json_encode(['status' => false, 'message' => 'Invalid request.']);
            return;
        }

        // Store exam ID & student ID in session
        $reportData = [
            'exam_id' => $exam_id,
            'student_id' => $student_id
        ];
        $this->session->set_userdata('reportData', $reportData);
        echo json_encode(['status' => true, 'message' => 'Session updated successfully.']);
    }

    // ONLINE EXAM START HERE
    public function online_exam()
    {
        if (!is_student_loggedin()) {
            access_denied();
        }

        $this->data['headerelements'] = array(
            'js' => array(
                'js/online-exam.js',
            ),
        );
        $this->data['title'] = translate('online_exam');
        $this->data['sub_page'] = 'userrole/online_exam';
        $this->data['main_menu'] = 'onlineexam';

        $this->load->view('layout/index', $this->data);
    }

    public function getExamListDT()
    {
        if ($_POST) {
            $this->load->model('onlineexam_model');
            $postData = $this->input->post();
            $currencySymbol = $this->data['global_config']['currency_symbol'];
            echo $this->userrole_model->examListDT($postData, $currencySymbol);

        }
    }

    /* Online exam controller */
    public function onlineexam_take($id = '')
    {
        if (!is_student_loggedin()) {
            access_denied();
        }
        $this->load->model('onlineexam_model');
        $this->data['headerelements'] = array(
            'js' => array(
                'js/online-exam.js',
            ),
        );
        $exam = $this->userrole_model->getExamDetails($id);
        if (empty($exam)) {
            redirect(base_url('userrole/online_exam'));
        }

        if ($exam->exam_type == 1 && $exam->payment_status == 0) {
            set_alert('error', "You have to make payment to attend this exam !");
            redirect(base_url('userrole/online_exam'));
        }

        $this->data['studentSubmitted'] = $this->onlineexam_model->getStudentSubmitted($exam->id);
        $this->data['exam'] = $exam;
        $this->data['title'] = translate('online_exam');
        $this->data['sub_page'] = 'onlineexam/take';
        $this->data['main_menu'] = 'onlineexam';
        $this->load->view('layout/index', $this->data);
    }

    public function ajaxQuestions()
    {
        $status = 0;
        $totalQuestions = 0;
        $message = "";
        $this->load->model('onlineexam_model');
        $examID = $this->input->post('exam_id');
        $exam = $this->userrole_model->getExamDetails($examID);
        $totalQuestions = $exam->questions_qty;
        $studentAttempt = $this->onlineexam_model->getStudentAttempt($exam->id);
        $examSubmitted = $this->onlineexam_model->getStudentSubmitted($exam->id);
        if (!empty($exam)) {
            $startTime = strtotime($exam->exam_start);
            $endTime = strtotime($exam->exam_end);
            $now = strtotime("now");
            if (($startTime <= $now && $now <= $endTime) && (empty($examSubmitted)) && $exam->publish_status == 1) {
                if ($exam->limits_participation > $studentAttempt) {
                    $this->onlineexam_model->addStudentAttemts($exam->id);
                    $message = "";
                    $status = 1;
                } else {
                    $status = 0;
                    $message = "You already reach max exam attempt.";
                }
            } else {
                $message = "Maybe the test has expired or something wrong.";
            }
        }
        $data['exam'] = $exam;
        $data['questions'] = $this->onlineexam_model->getExamQuestions($exam->id, $exam->question_type);
        $pag_content = $this->load->view('onlineexam/ajax_take', $data, true);
        echo json_encode(array('status' => $status, 'total_questions' => $totalQuestions, 'message' => $message, 'page' => $pag_content));
    }

    public function getStudent_result()
    {
        if ($_POST) {
            $examID = $this->input->post('id');
            $this->load->model('onlineexam_model');
            $exam = $this->onlineexam_model->getExamDetails($examID);
            $data['exam'] = $exam;
            echo $this->load->view('userrole/onlineexam_result', $data, true);
        }
    }

    public function getExamPaymentForm()
    {
        if ($_POST) {
            $this->load->model('onlineexam_model');
            $status = 1;
            $page_data = "";
            $examID = $this->input->post('examID');
            $exam = $this->userrole_model->getExamDetails($examID);
            $message = "";
            if (empty($exam)) {
                $status = 0;
                $message = 'Exam not found.';
                echo json_encode(array('status' => $status, 'message' => $message));
                exit;
            }
            $data['config'] = $this->get_payment_config();
            $data['global_config'] = $this->data['global_config'];
            $data['getUser'] = $this->userrole_model->getUserDetails();
            $data['exam'] = $exam;
            if ($exam->payment_status == 0) {
                $status = 1;
                $page_data = $this->load->view('userrole/getExamPaymentForm', $data, true);
            } else {
                $status = 0;
                $message = 'The fee has already been paid.';
            }
            echo json_encode(array('status' => $status, 'message' => $message, 'data' => $page_data));
        }
    }

    public function onlineexam_submit_answer_org()
    {
        if ($_POST) {
            if (!is_student_loggedin()) {
                access_denied();
            }
            $studentID = get_loggedin_user_id();
            $online_examID = $this->input->post('online_exam_id');
            $variable = $this->input->post('answer');
            if (!empty($variable)) {
                $saveAnswer = array();
                foreach ($variable as $key => $value) {
                    if (isset($value[1])) {
                        $saveAnswer[] = array(
                            'student_id' => $studentID,
                            'online_exam_id' => $online_examID,
                            'question_id' => $key,
                            'answer' => $value[1],
                            'created_at' => date('Y-m-d H:i:s'),
                        );
                    }
                    if (isset($value[2])) {
                        $saveAnswer[] = array(
                            'student_id' => $studentID,
                            'online_exam_id' => $online_examID,
                            'question_id' => $key,
                            'answer' => json_encode($value[2]),
                            'created_at' => date('Y-m-d H:i:s'),
                        );
                    }
                    if (isset($value[3])) {
                        $saveAnswer[] = array(
                            'student_id' => $studentID,
                            'online_exam_id' => $online_examID,
                            'question_id' => $key,
                            'answer' => $value[3],
                            'created_at' => date('Y-m-d H:i:s'),
                        );
                    }
                    if (isset($value[4])) {
                        $saveAnswer[] = array(
                            'student_id' => $studentID,
                            'online_exam_id' => $online_examID,
                            'question_id' => $key,
                            'answer' => $value[4],
                            'created_at' => date('Y-m-d H:i:s'),
                        );
                    }
                }
                $this->db->insert_batch('online_exam_answer', $saveAnswer);
                $this->db->insert('online_exam_submitted', ['student_id' => get_loggedin_user_id(), 'online_exam_id' => $online_examID, 'created_at' => date('Y-m-d H:i:s')]);
            }
            set_alert('success', translate('your_exam_has_been_successfully_submitted'));
            redirect(base_url('userrole/online_exam'));
        }
    }


    public function onlineexam_submit_answer()
    {
        if ($_POST) {
            if (!is_student_loggedin()) {
                access_denied();
            }
            $studentID = get_loggedin_user_id();
            $online_examID = $this->input->post('online_exam_id');
            $variable = $this->input->post('answer');

            // log_message('info', 'Exam submission started. StudentID: ' . $studentID . ', ExamID: ' . $online_examID);

            if (!empty($variable)) {
                $saveAnswer = array();
                foreach ($variable as $key => $value) {
                    if (isset($value[1])) {
                        $saveAnswer[] = array(
                            'student_id' => $studentID,
                            'online_exam_id' => $online_examID,
                            'question_id' => $key,
                            'answer' => $value[1],
                            'created_at' => date('Y-m-d H:i:s'),
                        );
                    }
                    if (isset($value[2])) {
                        $saveAnswer[] = array(
                            'student_id' => $studentID,
                            'online_exam_id' => $online_examID,
                            'question_id' => $key,
                            'answer' => json_encode($value[2]),
                            'created_at' => date('Y-m-d H:i:s'),
                        );
                    }
                    if (isset($value[3])) {
                        $saveAnswer[] = array(
                            'student_id' => $studentID,
                            'online_exam_id' => $online_examID,
                            'question_id' => $key,
                            'answer' => $value[3],
                            'created_at' => date('Y-m-d H:i:s'),
                        );
                    }
                    if (isset($value[4])) {
                        $saveAnswer[] = array(
                            'student_id' => $studentID,
                            'online_exam_id' => $online_examID,
                            'question_id' => $key,
                            'answer' => $value[4],
                            'created_at' => date('Y-m-d H:i:s'),
                        );
                    }
                }

                // log_message('debug', 'Prepared Answers: ' . json_encode($saveAnswer));

                $this->db->insert_batch('online_exam_answer', $saveAnswer);

                // log_message('info', 'Answers inserted into online_exam_answer, Rows: ' . $this->db->affected_rows());

                $this->db->insert('online_exam_submitted', ['student_id' => get_loggedin_user_id(), 'online_exam_id' => $online_examID, 'created_at' => date('Y-m-d H:i:s')]);

                // log_message('info', 'Exam submission entry inserted into online_exam_submitted, Rows: ' . $this->db->affected_rows());

                // FETCH RESULT FOR REWARD SYSTEM
                if ($this->db->affected_rows() > 0) {

                    // log_message('info', 'Exam submission confirmed for StudentID: ' . $studentID);

                    $result = $this->onlineexam_model->examResult($online_examID, get_loggedin_user_id());
                    // log_message('debug', 'Exam Result: ' . json_encode($result));
                    $exam = $this->onlineexam_model->getExamDetails($online_examID);
                    // log_message('debug', 'Exam Details: ' . json_encode($exam));
                    $total_marks = $result['total_marks'];
                    $total_obtain_marks = $result['total_obtain_marks'];
                    $total_neg_marks = ($exam->neg_mark == 0 ? 0 : $result['total_neg_marks']);

                    $percentage = ($total_marks === 0) ? 0 : (($total_obtain_marks - $total_neg_marks) * 100) / $total_marks;

                    // log_message('info', 'EXAM PERCENTAGE: ' . $percentage);

                    $isRewarded = $this->reward_model->isRewarded($exam->id, $studentID);

                    // log_message('info', 'Reward already given? ' . ($isRewarded ? 'YES' : 'NO'));

                    if (!$isRewarded) {
                        // log_message('info', 'Calling Reward Library -> processExamReward() for StudentID: ' . $studentID . ', ExamID: ' . $exam->id);
                        $this->reward_lib->processExamReward($studentID, $exam->id, 'online', $percentage);
                        // log_message('info', 'Reward process executed for StudentID: ' . $studentID);
                    } else {
                        // log_message('info', 'Skipping reward, already rewarded.');
                    }
                }

            }
            set_alert('success', translate('your_exam_has_been_successfully_submitted'));
            redirect(base_url('userrole/online_exam'));
        }
    }

    // ONLINE EXAM CUSTOM REPORT CARD
    public function online_exam_progress()
    {
        if (isset($_POST['search'])) {
            $this->form_validation->set_rules('exam_id', translate('Exam'), 'trim|required');

            if ($this->form_validation->run() == true) {
                $examID = $this->input->post('exam_id');

                if (is_student_loggedin()) {
                    $studentID = get_loggedin_user_id();
                } elseif (is_parent_loggedin()) {
                    $studentID = get_activeChildren_id();
                }

                $studentDetail = $this->application_model->getStudentDetails($studentID);

                if (!$studentDetail) {
                    set_alert('error', translate('Student information not found.'));
                    redirect(base_url('userrole/online_exam_progress'));
                }

                $branchId = $studentDetail['branch_id'];
                $classId = $studentDetail['class_id'];
                $sectionId = $studentDetail['section_id'];

                $studentMpped = $this->report_model->getStudentDetails($studentID);
                $this->data['studentMpped'] = json_decode(json_encode($studentMpped), true);
                $this->data['studentPhoto'] = base_url('uploads/images/student/' . $this->data['studentMpped']['student_photo']);

                $this->data['examID'] = $examID;
                $this->data['branchData'] = $this->db->get_where('branch', ['id' => $branchId])->row_array();
                $this->data['examName'] = $this->db->get_where('online_exam', ['id' => $examID])->row_array();
                $this->data['presentDays'] = 0;
                $this->data['absentDays'] = 0;

                $this->data['subjects'] = $this->report_model->getOnlineExamProgressReport($branchId, $classId, $sectionId, $examID, $studentID);

                if (empty($this->data['subjects'])) {
                    set_alert('error', translate('Smart Progress not found.'));
                    redirect(base_url('userrole/online_exam_progress'));
                }

                $this->data['class_average'] = $this->report_model->getClassAverageByOnlineExam($branchId, $classId, $sectionId, $examID);
                $this->load->view('report/online_exam_progress/overall_report', $this->data);
                return;
            }
        }

        $this->data['title'] = translate('online_exam_progress');
        $this->data['main_menu'] = 'online_exam_progress';
        $this->data['sub_page'] = 'userrole/report/online_exam_progress_filter';

        $this->load->view('layout/index', $this->data);
    }
    public function exam_progress_subjectwise()
    {
        if (isset($_POST['search'])) {
            $this->form_validation->set_rules('subject_id', translate('Subject'), 'trim|required');

            if ($this->form_validation->run() == true) {
                $subjectId = $this->input->post('subject_id');

                // Identify the student
                if (is_student_loggedin()) {
                    $studentID = get_loggedin_user_id();
                } elseif (is_parent_loggedin()) {
                    $studentID = get_activeChildren_id();
                }

                $studentDetail = $this->application_model->getStudentDetails($studentID);
                if (!$studentDetail) {
                    set_alert('error', translate('Student information not found.'));
                    redirect(base_url('userrole/exam_progress_subjectwise'));
                }

                // Student info
                $branchId = $studentDetail['branch_id'];
                $classId = $studentDetail['class_id'];
                $sectionId = $studentDetail['section_id'];

                $studentMpped = $this->report_model->getStudentDetails($studentID);
                $this->data['studentMpped'] = json_decode(json_encode($studentMpped), true);
                $this->data['branchData'] = $this->db->get_where('branch', ['id' => $branchId])->row_array();

                // Fetch report data
                $progress = $this->report_model->getSubjectWiseOnlineExamProgress($branchId, $classId, $sectionId, $subjectId, $studentID);

                if (empty($progress)) {
                    set_alert('error', translate('No progress data found for the selected subject.'));
                    redirect(base_url('userrole/exam_progress_subjectwise'));
                }

                // Subject info & restructuring
                $subjectName = $this->db->get_where('subject', ['id' => $subjectId])->row('name');
                foreach ($progress as $key => $exam) {
                    $progress[$key]['subject_name'] = $subjectName;
                    $progress[$key]['marks_obtained'] = $exam['total_obtain_marks'];
                    $progress[$key]['full_mark'] = $exam['total_marks'];
                    $progress[$key]['pass_mark'] = 0;
                    $progress[$key]['name'] = $exam['title'];
                }

                $this->data['progress'] = $progress;
                $this->data['class_average'] = $this->report_model->getSubjectWiseClassAverage($branchId, $classId, $subjectId);

                $this->load->view('report/online_exam_progress/subjectwise_report', $this->data);
                return;
            }
        }

        // Load filter form initially
        $this->data['title'] = translate('subject_wise_exam_progress');
        $this->data['main_menu'] = 'online_exam_progress';
        $this->data['sub_page'] = 'userrole/report/subjectwise_filter';

        $this->load->view('layout/index', $this->data);
    }

    // REWARD SYSTEM
    public function my_rewards()
    {
        $studentDetails = $this->userrole_model->getStudentDetails();
        $student_id = $studentDetails['student_id'];
        $this->data['wallet'] = $this->reward_model->getWallet($student_id);
        $this->data['rewards'] = $this->reward_model->getAvailableRewards($studentDetails);
        $this->data['history'] = $this->reward_model->getStudentRewards($student_id);
        // printVar($this->data['rewards']);
        // die;
        $this->data['branch_id'] = $this->application_model->get_branch_id();
        $this->data['title'] = translate('my_rewards');
        $this->data['sub_page'] = 'userrole/reward';
        $this->data['main_menu'] = 'exam';

        $this->load->view('layout/index', $this->data);
    }


}
