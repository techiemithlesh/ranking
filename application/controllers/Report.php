<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @package : SchoolExcel school management system
 * @version : 2.0
 * @developed by : Mithlesh Patel
 * @support : techie.mithlesh@gmail.com
 * @author url : http://codewithmithlesh
 * @filename : Report.php
 * @copyright : SchoolExcel
 */

class Report extends Admin_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('report_model');
        $this->load->model('fees_model');
        $this->load->model('application_model');
        $this->load->model('onlineexam_model');
        $this->load->library('session');

    }

    public function progress2()
    {
        $this->data = array();
        $branchID = $this->application_model->get_branch_id();

        if ($this->input->post('search')) {
            $sessionReportData = array(
                'reportbranch_id' => $branchID,
                'reportclass_id' => $classID,
                'reportstudent_id' => $studentId
            );
            $this->session->set_userdata($sessionReportData);
        }

        $this->data['branch_id'] = $branchID;
        $this->data['title'] = translate('Smart_Progress');

        $this->data['headerelements'] = array(
            'css' => array(
                'vendor/daterangepicker/daterangepicker.css',
            ),
            'js' => array(
                'vendor/moment/moment.js',
                'vendor/daterangepicker/daterangepicker.js',
            ),
        );
        $this->load->view('report/progress', $this->data);
    }

    public function progress()
    {
        $this->data = array();
        $branchID = $this->application_model->get_branch_id();

        if ($this->input->post('search')) {
            $sessionReportData = array(
                'reportbranch_id' => $branchID,
                'reportclass_id' => $classID,
                'reportstudent_id' => $studentId
            );
            $this->session->set_userdata($sessionReportData);
        }

        $this->data['branch_id'] = $branchID;
        $this->data['title'] = translate('Smart_Progress');
        $this->data['sub_page'] = 'report/progress';
        $this->data['main_menu'] = 'Reports';

        $this->data['headerelements'] = array(
            'css' => array(
                'vendor/daterangepicker/daterangepicker.css',
            ),
            'js' => array(
                'vendor/moment/moment.js',
                'vendor/daterangepicker/daterangepicker.js',
            ),
        );
        $this->load->view('layout/index', $this->data);
    }


    public function examReportSessionStore()
    {
        $reportCardData = $this->input->post();
        // printVar($reportCardData);

        if (is_admin_loggedin()) {
            $reportCardData['branch_id'] = get_loggedin_branch_id();
        }

        // Validation checks
        $requiredFields = ['branch_id', 'exam_id', 'class_id', 'section_id', 'student_id'];
        $fieldNames = [
            'branch_id' => 'Branch',
            'exam_id' => 'Exam',
            'class_id' => 'Class',
            'section_id' => 'Section',
            'student_id' => 'Student'
        ];

        foreach ($requiredFields as $field) {
            if (empty($reportCardData[$field])) {
                echo json_encode(["status" => false, "message" => "{$fieldNames[$field]} Missing"]);
                return;
            }
        }

        // Store session data
        $this->session->set_userdata(["reportCardData" => $reportCardData]);
        echo json_encode(["status" => true, "message" => "Session set successfully"]);
    }


    public function annual_examination_report()
    {
        $reportCardData = $this->session->userdata("reportCardData");
        $exam_id = $reportCardData['exam_id'];
        $student_id = $reportCardData['student_id'];
        $branch_id = $reportCardData['branch_id'];
        $firstDate = $reportCardData['first_date'];
        $lastDate = $reportCardData['last_date'];
        $sql = "SELECT *
            FROM mark
            JOIN (
                SELECT timetable_exam.*, subject.name AS subject_name
                FROM timetable_exam
                JOIN subject ON subject.id = timetable_exam.subject_id
                JOIN exam ON exam.id = timetable_exam.exam_id
                WHERE timetable_exam.exam_id = " . $exam_id . " 
                AND timetable_exam.class_id = " . $reportCardData['class_id'] . " 
                AND timetable_exam.section_id = " . $reportCardData['section_id'] . " 
                AND timetable_exam.branch_id = " . $branch_id . "
            ) AS subject 
            ON subject.subject_id = mark.subject_id 
            AND subject.exam_id = mark.exam_id 
            WHERE mark.student_id = " . $student_id . "
            ORDER BY mark.subject_id ASC";
        $this->data = array();
        $this->data["subjects"] = $this->db->query($sql)->result_array();
        $this->data["examName"] = $this->db->query("SELECT id, name AS exam_name FROM exam WHERE id = " . $exam_id)->row_array() ?? [];

        $this->data['branchData'] = $this->db->query("SELECT * FROM branch WHERE id='" . $branch_id . "'")->row_array() ?? [];
        $studentMpped = $this->report_model->getStudentDetails($student_id);
        $classId = $studentMpped->class_id;
        $sectionId = $studentMpped->section_id;

        $this->data['studentMpped'] = json_decode(json_encode($studentMpped), true);

        // ✅ Recalculate attendance using selected date range
        $queryPresent = $this->db->query("
        SELECT * FROM student_attendance 
        WHERE student_id = '$student_id' 
          AND branch_id = '$branch_id' 
          AND DATE(date) BETWEEN '$firstDate' AND '$lastDate' 
          AND status = 'P'
        ");

        $queryAbsent = $this->db->query("
        SELECT * FROM student_attendance 
        WHERE student_id = '$student_id' 
          AND branch_id = '$branch_id' 
          AND DATE(date) BETWEEN '$firstDate' AND '$lastDate' 
          AND status = 'A'
        ");

        $presentDays = sizeof($queryPresent->result());
        $absentDays = sizeof($queryAbsent->result());

        // ✅ Insert or update attendance report
        $existing = $this->db->get_where('student_attendance_report', [
            'student_id' => $student_id,
            'branch_id' => $branch_id,
            'exam_id' => $exam_id
        ])->row_array();

        if ($existing) {
            $this->db->where([
                'student_id' => $student_id,
                'branch_id' => $branch_id,
                'exam_id' => $exam_id
            ])->update('student_attendance_report', [
                        'present_days' => $presentDays,
                        'absent_days' => $absentDays
                    ]);
        } else {
            $this->db->insert('student_attendance_report', [
                'student_id' => $student_id,
                'branch_id' => $branch_id,
                'exam_id' => $exam_id,
                'present_days' => $presentDays,
                'absent_days' => $absentDays
            ]);
        }

        $totalDays = $presentDays + $absentDays;
        $this->data['presentDays'] = $presentDays;
        $this->data['absentDays'] = $absentDays;

        foreach ($this->data['subjects'] as $key => $item) {
            $mark["obtainMark"] = array_values(json_decode($item["mark"], true))[0] ?? 0;
            $this->data["subjects"][$key] = array_merge($this->data["subjects"][$key], $mark, array_values(json_decode($item["mark_distribution"], true))[0] ?? []);
        }

        $this->data['class_average'] = $this->getClassAverageByExamId($branch_id, $classId, $sectionId, $exam_id);

        $this->data['title'] = 'Exam Based - Matterhorn Report';
        $this->load->view('report/annual_examination_report/report', $this->data);
    }

    public function checkExistingReport()
    {
        $branch_id = $this->input->post('branch_id');
        $student_id = $this->input->post('student_id');
        $exam_id = $this->input->post('exam_id');

        if (empty($branch_id) || empty($student_id) || empty($exam_id)) {
            http_response_code(400);
            echo json_encode(['status' => false, 'message' => 'Missing required parameters']);
            return;
        }

        $attendanceReport = $this->db->query(
            "SELECT * FROM student_attendance_report WHERE student_id = ? AND branch_id = ? AND exam_id = ?",
            [$student_id, $branch_id, $exam_id]
        )->row_array();

        if ($attendanceReport) {
            echo json_encode(["status" => true, "message" => "Attendance found"]);
        } else {

            echo json_encode(["status" => false, "message" => "No attendance present"]);
        }
    }

    public function progressTracker()
    {
        $this->data = array();
        if ($_POST) {
            if (is_superadmin_loggedin()) {
                $this->form_validation->set_rules('branch_id', translate('branch'), 'required');
            } else {
                $this->form_validation->set_rules('class_id', translate('class'), 'trim|required');
                $this->form_validation->set_rules('section_id', translate('section'), 'trim|required');
                $this->form_validation->set_rules('student_id', translate('student'), 'trim|required');
            }

            if (is_superadmin_loggedin()) {
                $branchId = $this->input->post('branch_id');
            } else {
                $branchId = get_loggedin_branch_id();
            }

            $classId = $this->input->post('class_id');
            $sectionId = $this->input->post('section_id');
            $studentId = $this->input->post('student_id');
            $subjectId = $this->input->post('subject_id');

            $result = $this->get_subject_progress($branchId, $classId, $sectionId, $studentId, $subjectId);

            if (is_array($result)) {
                $result['studentId'] = $studentId;
                $result['subjectId'] = $subjectId;
            } elseif (is_object($result)) {
                $result->studentId = $studentId;
                $result->subjectId = $subjectId;
            }

            $this->data['branch_id'] = $branchId;
            $this->data['class_id'] = $classId;
            $this->data['section_id'] = $sectionId;
            $this->data['student_id'] = $studentId;
            $this->data['progressDetails'] = $result;
        }

        $this->data['title'] = translate('Progress_Tracker');
        $this->data['sub_page'] = 'report/tracker/index';
        $this->data['main_menu'] = 'progress_tracker';

        $this->data['headerelements'] = array(
            'css' => array(
                'vendor/dropify/css/dropify.min.css',
            ),
            'js' => array(
                'vendor/dropify/js/dropify.min.js',
            ),
        );

        $this->load->view('layout/index', $this->data);
    }
    private function get_progress_data($branchID, $classID, $sectionID, $studentID, $subjectID)
    {
        $sql = "SELECT s.name AS subject_name, 
                        te.name, 
                        m.mark AS marks_obtained, 
                        tte.mark_distribution AS total_marks 
                    FROM mark m 
                    JOIN subject s ON m.subject_id = s.id 
                    JOIN exam te ON m.exam_id = te.id 
                    INNER JOIN timetable_exam tte ON m.exam_id = tte.exam_id 
                        AND tte.class_id = m.class_id 
                        AND tte.section_id = m.section_id 
                        AND tte.subject_id = m.subject_id
                    WHERE m.student_id = ? 
                    AND m.class_id = ?
                    AND m.branch_id = ? 
                    AND m.section_id = ?
                    AND m.subject_id = ? 
                    ORDER BY te.created_at ASC";

        return $this->db->query($sql, [$studentID, $classID, $branchID, $sectionID, $subjectID])->result_array();
    }

    private function get_subject_progress($branchID, $classID, $sectionID, $studentID, $subjectID)
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

    public function ReportSessionStore()
    {
        $this->load->library('session');

        $student_id = $this->input->post('student_id');
        $subject_id = $this->input->post('subject_id');

        if (!empty($student_id) && !empty($subject_id)) {
            // Store in session
            $this->session->set_userdata('student_id', $student_id);
            $this->session->set_userdata('subject_id', $subject_id);

            echo json_encode(['status' => true]);
        } else {
            echo json_encode(['status' => false, 'message' => 'Invalid student or subject selection.']);
        }
    }

    public function student_progress($studentId, $subjectID)
    {
        if (!empty($studentId)) {
            $studentMpped = $this->report_model->getStudentDetails($studentId);
            $this->data['studentMpped'] = json_decode(json_encode($studentMpped), true);
            [0] ?? [];

            $branchId = $this->data['studentMpped']['branch_id'];
            $classId = $this->data['studentMpped']['class_id'];
            $sectionId = $this->data['studentMpped']['section_id'];
            $this->data['branchData'] = $this->db->query("SELECT * FROM branch WHERE id='" . $branchId . "'")->result_array()[0] ?? [];

            $this->data['progress'] = $this->$result = $this->get_progress_data($branchId, $classId, $sectionId, $studentId, $subjectID);

            foreach ($this->data['progress'] as $key => $item) {
                $markObtained = json_decode($item["marks_obtained"], true);
                $mark["marks_obtained"] = !empty($markObtained) ? array_values($markObtained)[0] : 0;

                $totalMarks = json_decode($item["total_marks"], true);
                if (!empty($totalMarks)) {
                    $firstKey = array_key_first($totalMarks);
                    $distributionData = $totalMarks[$firstKey] ?? [];
                    $mark["full_mark"] = $distributionData["full_mark"] ?? 0;
                    $mark["pass_mark"] = $distributionData["pass_mark"] ?? 0;
                } else {
                    $mark["full_mark"] = 0;
                    $mark["pass_mark"] = 0;
                }

                $this->data["progress"][$key] = array_merge(
                    $this->data["progress"][$key],
                    $mark
                );
            }

            $this->data['class_average'] = $this->get_class_average($branchId, $classId, $sectionId, $subjectID);

        }

        $this->data['title'] = translate('progress_Tracker');
        $this->load->view('report/tracker/track', $this->data);
    }

    public function get_class_average($branchId, $classId, $sectionId, $subjectID)
    {
        $query = "SELECT e.name AS exam_name, 
            AVG(
                CAST(
                    TRIM(BOTH '\"' FROM SUBSTRING_INDEX(SUBSTRING_INDEX(m.mark, ':', -1), '\"', 2))
                AS DECIMAL)
            ) AS avg_marks_obtained
        FROM 
            mark m
            JOIN exam e ON m.exam_id = e.id
        WHERE 
            m.class_id = ? 
            AND m.section_id = ? 
            AND m.branch_id = ?
            AND m.subject_id = ?
        GROUP BY e.id, e.name
        ORDER BY e.created_at ASC";

        $queryResult = $this->db->query($query, [$classId, $sectionId, $branchId, $subjectID])->result_array();

        $classAverages = [];
        foreach ($queryResult as $result) {
            $classAverages[$result['exam_name']] = $result['avg_marks_obtained'];
        }
        return $classAverages;
    }

    public function getClassAverageByExamId($branchId, $classId, $sectionId, $exam_id)
    {
        $params = [$classId, $sectionId, $branchId, $exam_id];
        $query = "
        SELECT 
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

    public function get_students_by_section()
    {

        $section_id = $this->input->post('section_id');
        $class_id = $this->input->post('class_id');
        if (!is_superadmin_loggedin()) {
            $branch_id = get_loggedin_branch_id();
        } else {
            $branch_id = $this->input->post('branch_id');
        }

        $this->db->select('s.id, s.first_name, s.last_name');
        $this->db->from('enroll as e');
        $this->db->join('student as s', 's.id = e.student_id', 'inner');
        $this->db->where('e.class_id', $class_id);
        $this->db->where('e.branch_id', $branch_id);
        if ($section_id != 'all') {
            $this->db->where('e.section_id', $section_id);
        }
        $this->db->order_by('s.first_name', 'asc');
        $students = $this->db->get()->result();

        $options = '';
        foreach ($students as $student) {
            $options .= "<option value='{$student->id}'>{$student->first_name} {$student->last_name}</option>";
        }
        echo $options;
    }

    public function skillReport2()
    {

        $this->load->view('report/skill_based_report/report_copy', $this->data);
    }

    public function skillReport()
    {
        $branchID = $this->application_model->get_branch_id();
        if ($this->input->post('search')) {

            $examId = $this->input->post('exam_id');
            $classID = $this->input->post('class_id');
            $sectionID = $this->input->post('section_id');
            $studentID = $this->input->post('student_id');

            if (!empty($examId)) {
                $report = [
                    'branch_id' => $branchID,
                    'exam_id' => $examId,
                    'class_id' => $classID,
                    'section_id' => $sectionID,
                    'student_id' => $studentID
                ];

                //   $this->data['reportData'] = $this->session->set_userdata($report);
                $this->session->set_userdata('reportData', $report);
            }

        }

        $this->data['reportData'] = $this->session->userdata('reportData');

        $this->data['title'] = translate('Skill_based_report');
        $this->data['sub_page'] = 'report/skill_based_report/index';
        $this->data['main_menu'] = 'skill_report';

        $this->data['headerelements'] = array(
            'css' => array(
                'vendor/dropify/css/dropify.min.css',
            ),
            'js' => array(
                'vendor/dropify/js/dropify.min.js',
            ),
        );

        $this->load->view('layout/index', $this->data);
    }

    public function skill_based_report()
    {
        $this->data = array();
        $reportData = $this->session->userdata("reportData");

        $branch_id = $reportData['branch_id'];
        $exam_id = $reportData['exam_id'];
        $class_id = $reportData['class_id'];
        $section_id = $reportData['section_id'];
        $student_id = $reportData['student_id'];

        $this->db->select("
        s.first_name AS student_name, 
        c.name AS class_name, 
        sc.category_name, 
        cr.criteria_text, 
        a.assessment_level, 
        sub.name AS subject_name, 
        en.roll AS roll_number");
        $this->db->from("tbl_skill_assessments AS a");
        $this->db->join("tbl_skill_criteria AS cr", "a.skill_criteria_id = cr.id");
        $this->db->join("tbl_skill_categories AS sc", "cr.skill_category_id = sc.id");
        $this->db->join("student AS s", "a.student_id = s.id");
        $this->db->join("enroll AS en", "s.id = en.student_id");
        $this->db->join("class AS c", "a.class_id = c.id");
        $this->db->join("subject AS sub", "a.subject_id = sub.id");
        $this->db->where("a.student_id", $student_id);
        $this->db->where("a.exam_id", $exam_id);

        $query = $this->db->get();
        $assessments = $query->result_array();

        $this->data["examName"] = $this->db->query("select id,name as exam_name, created_at as exam_date from exam where id = " . $exam_id)->result_array()[0] ?? [];

        $examDate = $examName['exam_date'] ?? date('Y-m-d');

        $attendanceQuery = $this->db->query("
                SELECT 
                    COUNT(sa.id) AS total_days,
                    SUM(CASE WHEN sa.status = 'P' THEN 1 ELSE 0 END) AS present_days,
                    SUM(CASE WHEN sa.status = 'A' THEN 1 ELSE 0 END) AS absent_days
                FROM student_attendance AS sa
                INNER JOIN enroll AS en ON sa.student_id = en.student_id
                WHERE sa.student_id = '$student_id'
                    AND en.class_id = '$class_id'
                    AND en.section_id = '$section_id'
                    AND sa.date <= '$examDate'
                ");
        $this->data['attendance'] = $attendanceQuery->row_array();

        if (empty($assessments)) {
            set_alert('error', translate('No data found to generate report card.'));

            if (is_superadmin_loggedin() || is_admin_loggedin() || is_teacher_loggedin()) {
                redirect(base_url('report/skillReport'));
            } else {
                redirect(base_url('userrole/skillBasedReport'));
            }

            redirect(base_url('report/skillReport'));
        }

        $this->data['studentMpped'] = $this->report_model->getStudentDetails($student_id);
        $query2 = $this->db->query("SELECT * FROM branch WHERE id='" . $branch_id . "'");
        $this->data['branchData'] = $query2->row();

        $this->data['assessments'] = $assessments;
        $this->data['title'] = 'Skill Based Report';

        $this->load->view('report/skill_based_report/report', $this->data);

    }


    public function subject_based_report()
    {
        $this->data = array();
        $this->data['title'] = 'Subject Based - Mount Etna Report';
        $this->load->view('report/subject_based_report/report', $this->data);
    }

    public function type_skill_based_report()
    {
        $this->data = array();
        $student_id = $this->session->userdata('reportstudent_id');
        $branch_id = $this->session->userdata('reportbranch_id');
        $this->data = array();

        $query = $this->db->query("SELECT * FROM student WHERE id='" . $student_id . "'");
        $this->data['studentData'] = $query->row();

        $studentMpped = $this->report_model->getStudentDetails($student_id);
        $this->data['studentMpped'] = $studentMpped;

        $query2 = $this->db->query("SELECT * FROM branch WHERE id='" . $branch_id . "'");
        $this->data['branchData'] = $query2->row();

        $currentYear = date('Y') - 1;
        $this->data['currentYear'] = $currentYear;
        $firstDate = $currentYear . '-01-01';
        $lastDate = $currentYear . '-12-31';
        $firstDateObj = strtotime($firstDate);
        $lastDateObj = strtotime($lastDate);

        $firstDate = date("Y-m-d", $firstDateObj);
        $lastDate = date("Y-m-d", $lastDateObj);

        $query3 = $this->db->query("SELECT * FROM student_attendance WHERE student_id='" . $student_id . "' AND branch_id = '" . $branch_id . "' AND DATE(date) >= '" . $firstDate . "' AND DATE(date) <= '" . $lastDate . "' AND status = 'P'");
        $this->data['present'] = sizeof($query3->result());

        $query4 = $this->db->query("SELECT * FROM student_attendance WHERE student_id='" . $student_id . "' AND branch_id = '" . $branch_id . "' AND DATE(date) >= '" . $firstDate . "' AND DATE(date) <= '" . $lastDate . "' AND status = 'A'");
        $this->data['absent'] = sizeof($query4->result());
        $this->data['totalSchoolDay'] = sizeof($query3->result()) + sizeof($query4->result());
        $this->data['absent'] = sizeof($query4->result());
        $this->data['totalSchoolDay'] = sizeof($query3->result()) + sizeof($query4->result());


        $this->data['title'] = 'Exam Type + Skill Based - Mount Nemrut Report';
        $this->load->view('report/type_skill_based_report/report', $this->data);
    }

    public function term_end_report()
    {
        $this->data = array();
        $this->data['title'] = 'Term End Report';
        $this->load->view('report/term_end_report/report', $this->data);
    }

    public function grade_book_report()
    {
        $this->data = array();
        $this->data['title'] = 'Grade Book Report';
        $this->load->view('report/grade_book_report/report', $this->data);
    }

    public function term_end_report_versin_2()
    {
        $this->data = array();
        $this->data['title'] = 'Term End Report Version 2';
        $this->load->view('report/term_end_report_versin_2/report', $this->data);
    }

    public function term_end_report_versin_3()
    {
        $this->data = array();
        $this->data['title'] = 'Term End Report Version 3';
        $this->load->view('report/term_end_report_versin_3/report', $this->data);
    }

    public function online_exam_progress()
    {
        if (!get_permission('online_exam_progress', 'is_view')) {
            access_denied();
        }

        if (isset($_POST['search'])) {
            $branchID = $this->application_model->get_branch_id();

            if (is_superadmin_loggedin() == true) {
                $this->form_validation->set_rules('branch_id', 'Branch', 'trim|required');
            }

            $this->form_validation->set_rules('class_id', 'Class', 'trim|required');
            $this->form_validation->set_rules('section_id', 'Section', 'trim|required');
            $this->form_validation->set_rules('exam_id', 'Exam', 'trim|required');
            $this->form_validation->set_rules('student_id', 'Student', 'trim|required');

            if ($this->form_validation->run() == true) {
                $classID = $this->input->post('class_id');
                $sectionId = $this->input->post('section_id');
                $examID = $this->input->post('exam_id');
                $studentId = $this->input->post('student_id');
                $this->data['studentID'] = $studentId;
                $studentMpped = $this->report_model->getStudentDetails($studentId);
                $this->data['studentMpped'] = json_decode(json_encode($studentMpped), true);
                $this->data['studentPhoto'] = base_url('uploads/images/student/' . $this->data['studentMpped']['student_photo']);
                $this->data['examID'] = $examID;
                $this->data['branchData'] = $this->db->get_where('branch', ['id' => $branchID])->row_array();
                $this->data['examName'] = $this->db->get_where('online_exam', ['id' => $examID])->row_array();
                $this->data['studentMpped'] = $this->application_model->getStudentDetails($studentId);
                $this->data['presentDays'] = 0;
                $this->data['absentDays'] = 0;

                $this->data['subjects'] = $this->report_model->getOnlineExamProgressReport($branchID, $classID, $sectionId, $examID, $studentId);

                // printVar($this->data['subjects']);
                // die;

                if (empty($this->data['subjects'])) {
                    set_alert('error', translate('Smart Progress not found.'));
                    redirect(base_url('Report/online_exam_progress'));
                }

                $this->data['class_average'] = $this->report_model->getClassAverageByOnlineExam($branchID, $classID, $sectionId, $examID);

                $this->load->view('report/online_exam_progress/overall_report', $this->data);
                return;
            }
        }

        $this->data['branch_id'] = $branchID;
        $this->data['title'] = translate('online_exam_progress');
        $this->data['sub_page'] = 'report/online_exam_progress/index';
        $this->data['main_menu'] = 'online_exam_progress';

        $this->data['headerelements'] = array(
            'css' => array(
                'vendor/dropify/css/dropify.min.css',
            ),
            'js' => array(
                'vendor/dropify/js/dropify.min.js',
            ),
        );

        $this->load->view('layout/index', $this->data);
    }
    public function online_exam_progress_subjectwise()
    {
        if (!get_permission('online_exam_progress', 'is_view')) {
            access_denied();
        }

        if (isset($_POST['search'])) {
            $branchID = $this->application_model->get_branch_id();

            if (is_superadmin_loggedin()) {
                $this->form_validation->set_rules('branch_id', 'Branch', 'trim|required');
            }
            $this->form_validation->set_rules('class_id', 'Class', 'trim|required');
            $this->form_validation->set_rules('section_id', 'Section', 'trim|required');
            $this->form_validation->set_rules('student_id', 'Student', 'trim|required');
            $this->form_validation->set_rules('subject_id', 'Subject', 'trim|required');

            if ($this->form_validation->run() == true) {
                $classID = $this->input->post('class_id');
                $sectionId = $this->input->post('section_id');
                $studentId = $this->input->post('student_id');
                $subjectId = $this->input->post('subject_id');

                $this->data['studentID'] = $studentId;
                $this->data['subjectID'] = $subjectId;
                $this->data['studentMpped'] = json_decode(json_encode($this->report_model->getStudentDetails($studentId)), true);

                $this->data['branchData'] = $this->db->query("SELECT * FROM branch WHERE id='" . $branchID . "'")->row_array();

                $progress = $this->report_model->getSubjectWiseOnlineExamProgress($branchID, $classID, $sectionId, $subjectId, $studentId);
                $subjectName = $this->db->get_where('subject', ['id' => $subjectId])->row('name');

                $student_exam_ids = array_map(function ($exam) {
                    return $exam['exam_id'];
                }, $progress);


                if (!empty($progress)) {
                    foreach ($progress as $key => $exam) {
                        $progress[$key]['subject_name'] = $subjectName;
                        $progress[$key]['marks_obtained'] = $exam['total_obtain_marks'];
                        $progress[$key]['full_mark'] = $exam['total_marks'];
                        $progress[$key]['pass_mark'] = 0;
                        $progress[$key]['name'] = $exam['title'];
                    }
                }

                $this->data['progress'] = $progress;

                $classAaverage = $this->report_model->getSubjectWiseClassAverage($branchID, $classID, $subjectId, $student_exam_ids);
                $this->data['class_average'] = !empty($classAaverage)
                    ? array_column($classAaverage, 'avg_percentage')
                    : [];


                $this->load->view('report/online_exam_progress/subjectwise_report', $this->data);
                return;
            }
        }

        $this->data['branch_id'] = $branchID;
        $this->data['title'] = translate('subject_wise_exam_progress');
        $this->data['sub_page'] = 'report/online_exam_progress/subjectwise_filter';
        $this->data['main_menu'] = 'online_exam_progress';

        $this->load->view('layout/index', $this->data);
    }

    public function subjectWiseResult()
    {

        $branchID = $this->application_model->get_branch_id();
        $reportRows = [];
        $sessionCode = null;
        $examType = null;
        $examId = null;
        $subjectId = null;

        if ($this->input->post('search')) {
            $classId = $this->input->post('class_id');
            $sectionId = $this->input->post('section_id');
            $examType = $this->input->post('exam_type');
            $examId = $this->input->post('exam_id');
            $subjectId = $this->input->post('subject_id');
            $sessionCode = $this->input->post('session_code');
            $sessionCode = !empty($sessionCode) ? $sessionCode : null;

            if (is_superadmin_loggedin()) {
                $this->form_validation->set_rules('branch_id', 'Branch', 'trim|required');
            }

            $this->form_validation->set_rules('class_id', 'Class', 'trim|required');
            $this->form_validation->set_rules('section_id', 'Section', 'trim|required');
            $this->form_validation->set_rules('exam_type', 'Exam Type', 'trim|required');

            if ($examType === 'online') {
                $this->form_validation->set_rules('exam_id', 'Exam', 'trim|required');
            }

            if ($this->form_validation->run() === true) {
                if ($examType === 'live_exam') {

                    $reportRows = $this->leaderboard_model->getLiveExamSubjectReport(
                        $branchID,
                        $classId,
                        $sectionId,
                        $subjectId,   // null allowed
                        $examId,      // null NOT allowed
                        $sessionCode  // null allowed
                    );
                    // printVar($this->db->last_query());
                    // die;
                } elseif ($examType === 'online') {
                    $reportRows = $this->leaderboard_model->getOnlineExamSubjectReport(
                        $branchID,
                        $classId,
                        $sectionId,
                        $examId,
                        $subjectId    // null = all subjects combined
                    );

                    printVar($this->db->last_query());
                    die;
                }
            } else {
                $this->data['form_error'] = $this->form_validation->error_array();
            }
        }


        $this->data['branch_id'] = $branchID;
        $this->data['class_id'] = $classId;
        $this->data['section_id'] = $sectionId;
        $this->data['exam_type'] = $examType;
        $this->data['exam_id'] = $examId;
        $this->data['subject_id'] = $subjectId;
        $this->data['sessionCode'] = $sessionCode;

        $this->data['report_rows'] = $reportRows;


        $this->data['title'] = translate('subject_wise_report');
        $this->data['sub_page'] = 'report/subject_wise_report';
        $this->data['main_menu'] = 'online_exam_progress';

        $this->load->view('layout/index', $this->data);

    }

}


