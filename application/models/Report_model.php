<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Report_model extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('leaderboard_model');

    }

    public function getStudentDetails_old($studentID)
    {
        $this->db->select('CONCAT(s.first_name, " ", s.last_name) as fullname,s.email as student_email,e.branch_id,e.student_id,s.hostel_id,s.room_id,s.route_id,s.vehicle_id,e.class_id,e.section_id,c.name as class_name,se.name as section_name,b.school_name,b.email as school_email,b.mobileno as school_mobileno,b.address as school_address');
        $this->db->from('enroll as e');
        $this->db->join('student as s', 's.id = e.student_id', 'inner');
        $this->db->join('branch as b', 'b.id = e.branch_id', 'left');
        $this->db->join('class as c', 'c.id = e.class_id', 'left');
        $this->db->join('section as se', 'se.id = e.section_id', 'left');
        $this->db->where('s.id', $studentID);
        return $this->db->get()->row();
    }

    public function getStudentDetails($studentID)
    {
        $this->db->select('CONCAT(s.first_name, " ", s.last_name) as fullname,s.email as student_email,e.branch_id,e.student_id,s.hostel_id,s.room_id,s.route_id,s.vehicle_id,s.birthday,e.class_id,e.section_id,
        c.name as class_name,se.name as section_name,
        b.school_name,b.email as school_email,
        b.mobileno as school_mobileno,
        b.address as school_address,
        s.register_no, s.photo as student_photo');
        $this->db->from('enroll as e');
        $this->db->join('student as s', 's.id = e.student_id', 'inner');
        $this->db->join('branch as b', 'b.id = e.branch_id', 'left');
        $this->db->join('class as c', 'c.id = e.class_id', 'left');
        $this->db->join('section as se', 'se.id = e.section_id', 'left');
        $this->db->where('s.id', $studentID);
        // printVar($this->db->getLatestQuery)
        return $this->db->get()->row();
    }


    // public function getOnlineExamProgressReport($branch_id, $class_id, $section_id, $exam_id, $student_id)
    // {

    //     $this->db->select('online_exam_submitted.student_id, student.register_no, student.photo, CONCAT(student.first_name, " ", student.last_name) as full_name, online_exam.*');
    //     $this->db->from('online_exam_submitted');
    //     $this->db->join('online_exam', 'online_exam.id = online_exam_submitted.online_exam_id', 'inner');
    //     $this->db->join('student', 'student.id = online_exam_submitted.student_id', 'left');
    //     $this->db->where('online_exam_submitted.online_exam_id', $exam_id);
    //     $this->db->where('online_exam.session_id', get_session_id());
    //     $this->db->where('online_exam.class_id', $class_id);
    //     $this->db->where('online_exam.branch_id', $branch_id);
    //     $this->db->where('online_exam_submitted.student_id', $student_id);

    //     $results = $this->db->get()->result_array();
    //     $subjectDetails = [];

    //     foreach ($results as $row) {
    //         $examSections = json_decode($row['section_id'], true);
    //         $examSubjects = json_decode($row['subject_id'], true);

    //         if (!in_array($section_id, $examSections)) {
    //             continue;
    //         }

    //         $examResult = $this->leaderboard_model->examResult($row['id'], $row['student_id']);
    //         $total_neg_marks = ($row['neg_mark'] == 0) ? 0 : $examResult['total_neg_marks'];
    //         $mark = $examResult['total_obtain_marks'] - $total_neg_marks;
    //         $fullMark = $examResult['total_marks'];

    //         $percentage = ($fullMark > 0) ? round(($mark / $fullMark) * 100, 2) : 0;

    //         foreach ($examSubjects as $subId) {
    //             $subjectDetails[] = [
    //                 'student_id' => $row['student_id'],
    //                 'register_no' => $row['register_no'],
    //                 'photo' => $row['photo'],
    //                 'full_name' => $row['full_name'],
    //                 'subject_id' => $subId,
    //                 'subject_name' => get_type_name_by_id('subject', $subId),
    //                 'obtainMark' => $examResult['per_subject'][$subId]['obtain'] ?? 0,
    //                 'full_mark' => $examResult['per_subject'][$subId]['full'] ?? 0,
    //                 'pass_mark' => $row['passing_mark'],
    //                 'percentage' => $percentage,
    //                 'remarks' => $examResult['per_subject'][$subId]['remark'] ?? null,
    //             ];
    //         }
    //     }

    //     return $subjectDetails;
    // }


    public function getOnlineExamProgressReport($branch_id, $class_id, $section_id, $exam_id, $student_id)
    {

        $this->db->select('
        oes.student_id, 
        s.register_no, 
        s.photo, 
        CONCAT(s.first_name, " ", s.last_name) AS full_name, 
        oes.remark AS teacher_remark,
        oe.*');
        $this->db->from('online_exam_submitted AS oes');
        $this->db->join('online_exam AS oe', 'oe.id = oes.online_exam_id', 'inner');
        $this->db->join('exam_assignment AS ea', 'ea.exam_id = oe.id', 'left');
        $this->db->join('student AS s', 's.id = oes.student_id', 'left');
        $this->db->where('(
            oe.created_by_branch = ' . $this->db->escape($branch_id) . ' 
            OR ea.branch_id = ' . $this->db->escape($branch_id) . ')');
        $this->db->where('oe.session_id', get_session_id());
        $this->db->where('oe.id', $exam_id);
        $this->db->where('oe.class_id', $class_id);
        $this->db->where('oes.student_id', $student_id);

        $results = $this->db->get()->result_array();

        $subjectDetails = [];

        foreach ($results as $row) {
            $examSections = json_decode($row['section_id'], true);
            $examSubjects = json_decode($row['subject_id'], true);

            // ✅ only include if section is assigned
            if (!in_array($section_id, $examSections ?? [])) {
                continue;
            }

            $examResult = $this->examProgressReport($row['id'], $row['student_id']);

            foreach ($examSubjects as $subId) {
                $subjectDetails[] = [
                    'student_id' => $row['student_id'],
                    'register_no' => $row['register_no'],
                    'photo' => $row['photo'],
                    'full_name' => $row['full_name'],
                    'subject_id' => $subId,
                    'subject_name' => get_type_name_by_id('subject', $subId),
                    'obtainMark' => $examResult['per_subject'][$subId]['obtain'] ?? 0,
                    'full_mark' => $examResult['per_subject'][$subId]['full'] ?? 0,
                    'teacher_remark' => $row['teacher_remark'] ?? ''
                ];
            }
        }

        return $subjectDetails;
    }


    public function examProgressReport($examID, $studentID)
    {
        $sql = "SELECT `questions_manage`.*, `questions`.`subject_id`, `questions`.`id` as `qus_id`, `questions`.*, `online_exam_answer`.`answer` as `sb_ans`, `online_exam_answer`.`id` as `ans_id` 
                FROM `questions_manage` 
                INNER JOIN `questions` ON `questions`.`id` = `questions_manage`.`question_id` 
                LEFT JOIN `online_exam_answer` 
                    ON `online_exam_answer`.`online_exam_id` = `questions_manage`.`onlineexam_id` 
                    AND `online_exam_answer`.`question_id` = `questions`.`id` 
                    AND `online_exam_answer`.`student_id` = " . $this->db->escape($studentID) . " 
                WHERE `questions_manage`.`onlineexam_id` = " . $this->db->escape($examID) . " 
                ORDER BY `questions_manage`.`id` ASC";

        $result = $this->db->query($sql)->result();

        $total_marks = 0;
        $total_obtain_marks = 0;
        $total_neg_marks = 0;
        $correct_ans = 0;
        $wrong_ans = 0;
        $total_answered = 0;
        $total_question = 0;
        $per_subject = [];

        if (!empty($result)) {
            $total_question = count($result);

            foreach ($result as $value) {
                $subject_id = $value->subject_id ?? null;
                if (!$subject_id)
                    continue;

                $marks = (float) $value->marks;
                $neg_marks = (float) $value->neg_marks;
                $total_marks += $marks;

                if (!isset($per_subject[$subject_id])) {
                    $per_subject[$subject_id] = [
                        'obtain' => 0,
                        'full' => 0,
                    ];
                }

                $per_subject[$subject_id]['full'] += $marks;

                if (!empty($value->ans_id)) {
                    $total_answered++;

                    $isCorrect = false;

                    if ($value->type == 1 || $value->type == 3) {
                        $isCorrect = ($value->sb_ans == $value->answer);
                    } elseif ($value->type == 2) {
                        $isCorrect = $this->array_equal(json_decode($value->answer), json_decode($value->sb_ans));
                    } elseif ($value->type == 4) {
                        $correctAns = strtolower(str_replace(" ", "_", $value->answer));
                        $studentAns = strtolower(str_replace(" ", "_", $value->sb_ans));
                        $isCorrect = ($correctAns == $studentAns);
                    }

                    if ($isCorrect) {
                        $correct_ans++;
                        $total_obtain_marks += $marks;
                        $per_subject[$subject_id]['obtain'] += $marks;
                    } else {
                        $wrong_ans++;
                        $total_neg_marks += $neg_marks;
                    }
                }
            }
        }

        return [
            'total_marks' => $total_marks,
            'total_obtain_marks' => $total_obtain_marks,
            'total_neg_marks' => $total_neg_marks,
            'correct_ans' => $correct_ans,
            'wrong_ans' => $wrong_ans,
            'total_answered' => $total_answered,
            'total_question' => $total_question,
            'per_subject' => $per_subject,
        ];
    }

    public function getClassAverageByOnlineExam($branchID, $classID, $sectionID, $examID)
    {
        $this->db->select('online_exam_submitted.student_id, online_exam.section_id');
        $this->db->from('online_exam_submitted');
        $this->db->join('online_exam', 'online_exam.id = online_exam_submitted.online_exam_id');
        $this->db->join('exam_assignment', 'exam_assignment.exam_id = online_exam.id', 'left');
        $this->db->where('online_exam_submitted.online_exam_id', $examID);
        $this->db->where('online_exam.class_id', $classID);
        $this->db->where('(
            online_exam.created_by_branch = ' . $this->db->escape($branchID) . ' 
            OR exam_assignment.branch_id = ' . $this->db->escape($branchID) . '
        )');

        $query = $this->db->get();
        if (!$query) {
            log_message('error', 'Failed to fetch students for class average');
            return [];
        }

        $studentsRaw = $query->result_array();
        $students = [];

        foreach ($studentsRaw as $row) {
            $examSections = json_decode($row['section_id'], true);
            if (is_array($examSections) && in_array($sectionID, $examSections)) {
                $students[] = $row['student_id'];
            }
        }

        $subjectScores = [];
        foreach ($students as $studentID) {
            $examResult = $this->examProgressReport($examID, $studentID);

            foreach ($examResult['per_subject'] as $subId => $score) {
                if (!isset($subjectScores[$subId])) {
                    $subjectScores[$subId] = ['total' => 0, 'count' => 0];
                }

                $subjectScores[$subId]['total'] += $score['obtain'];
                $subjectScores[$subId]['count'] += 1;
            }
        }

        $class_average = [];
        foreach ($subjectScores as $subId => $scoreData) {
            $class_average[$subId] = $scoreData['count'] > 0
                ? round($scoreData['total'] / $scoreData['count'], 2)
                : 0;
        }

        return $class_average;
    }

    public function getSubjectWiseOnlineExamProgress($branch_id, $class_id, $section_id, $subject_id, $student_id)
    {
        $this->db->select('oe.id as exam_id, oe.title, oes.student_id');
        $this->db->from('online_exam_submitted as oes');
        $this->db->join('online_exam as oe', 'oe.id = oes.online_exam_id', 'inner');
        $this->db->join('exam_assignment as ea', 'ea.exam_id = oe.id', 'left');
        $this->db->where('oe.class_id', $class_id);
        $this->db->where('(
        oe.created_by_branch = ' . $this->db->escape($branch_id) . ' 
        OR ea.branch_id = ' . $this->db->escape($branch_id) . '
        )');
        $this->db->where('oes.student_id', $student_id);
        $exams = $this->db->get()->result_array();

        $subjectWise = [];

        foreach ($exams as $exam) {
            $result = $this->examProgressReportSubjectwise($exam['exam_id'], $student_id, $subject_id);
            if ($result) {
                $subjectWise[] = array_merge($exam, $result);
            }
        }
        return $subjectWise;
        // return $exams;
    }


    public function examProgressReportSubjectwise($examID, $studentID, $subjectID)
    {
        $sql = "SELECT `questions_manage`.*, `questions`.`subject_id`, `questions`.`id` as `qus_id`, `questions`.*, `online_exam_answer`.`answer` as `sb_ans`, `online_exam_answer`.`id` as `ans_id`
            FROM `questions_manage`
            INNER JOIN `questions` ON `questions`.`id` = `questions_manage`.`question_id`
            LEFT JOIN `online_exam_answer`
                ON `online_exam_answer`.`online_exam_id` = `questions_manage`.`onlineexam_id`
                AND `online_exam_answer`.`question_id` = `questions`.`id`
                AND `online_exam_answer`.`student_id` = " . $this->db->escape($studentID) . "
            WHERE `questions_manage`.`onlineexam_id` = " . $this->db->escape($examID) . "
            AND `questions`.`subject_id` = " . $this->db->escape($subjectID) . "
            ORDER BY `questions_manage`.`id` ASC";

        $result = $this->db->query($sql)->result();

        $total_marks = 0;
        $total_obtain_marks = 0;
        $total_neg_marks = 0;
        $correct_ans = 0;
        $wrong_ans = 0;
        $total_answered = 0;
        $total_question = 0;

        if (!empty($result)) {
            $total_question = count($result);

            foreach ($result as $value) {
                $marks = (float) $value->marks;
                $neg_marks = (float) $value->neg_marks;
                $total_marks += $marks;

                if (!empty($value->ans_id)) {
                    $total_answered++;

                    $isCorrect = false;
                    if ($value->type == 1 || $value->type == 3) {
                        $isCorrect = ($value->sb_ans == $value->answer);
                    } elseif ($value->type == 2) {
                        $isCorrect = $this->array_equal(json_decode($value->answer), json_decode($value->sb_ans));
                    } elseif ($value->type == 4) {
                        $correctAns = strtolower(str_replace(" ", "_", $value->answer));
                        $studentAns = strtolower(str_replace(" ", "_", $value->sb_ans));
                        $isCorrect = ($correctAns == $studentAns);
                    }

                    if ($isCorrect) {
                        $correct_ans++;
                        $total_obtain_marks += $marks;
                    } else {
                        $wrong_ans++;
                        $total_neg_marks += $neg_marks;
                    }
                }
            }
        }

        return [
            'total_marks' => $total_marks,
            'total_obtain_marks' => $total_obtain_marks,
            'total_neg_marks' => $total_neg_marks,
            'correct_ans' => $correct_ans,
            'wrong_ans' => $wrong_ans,
            'total_answered' => $total_answered,
            'total_question' => $total_question,
        ];
    }

    public function getSubjectWiseClassAverage_old($branch_id, $class_id, $subject_id)
    {
        $query = "
        SELECT oe.id, oe.title 
        FROM online_exam AS oe
        LEFT JOIN exam_assignment AS ea ON ea.exam_id = oe.id
        WHERE ea.branch_id = ?
          AND oe.class_id = ?
          AND JSON_CONTAINS(oe.subject_id, '[\"$subject_id\"]')";

        $exams = $this->db->query($query, [$branch_id, $class_id])->result_array();

        if (empty($exams)) {
            return [];
        }

        $averages = [];

        foreach ($exams as $exam) {
            $examID = $exam['id'];
            // log_message('debug', "Processing Exam ID: $examID ({$exam['title']})");

            $students = $this->db->select('DISTINCT(student_id)')
                ->from('online_exam_submitted')
                ->where('online_exam_id', $examID)
                ->get()->result_array();

            $totalMarks = 0;
            $totalFullMarks = 0;
            $count = 0;

            foreach ($students as $s) {
                $studentID = $s['student_id'];
                $result = $this->examProgressReportSubjectwise($examID, $studentID, $subject_id);
                $totalMarks += $result['total_obtain_marks'];
                $totalFullMarks += $result['total_marks'];
                $count++;
            }

            $average = ($totalFullMarks > 0 && $count > 0)
                ? round(($totalMarks / $totalFullMarks) * 100, 2)
                : 0;

            $averages[$exam['title']] = $average;
            // log_message('debug', "Computed Average for Exam '{$exam['title']}': $average%");
        }

        // log_message('debug', "Final Averages: " . json_encode($averages));
        return $averages;
    }


    public function getSubjectWiseClassAverage($branch_id, $class_id, $subject_id, $student_exam_ids = [])
    {
        // 1️⃣ Only consider exams where student has actually appeared
        if (empty($student_exam_ids))
            return [];

        $this->db->select('oe.id as exam_id, oe.title');
        $this->db->from('online_exam as oe');
        $this->db->join('exam_assignment as ea', 'ea.exam_id = oe.id', 'left');
        $this->db->where_in('oe.id', $student_exam_ids); // Limit to exams our student took
        $this->db->where('(
        oe.created_by_branch = ' . $this->db->escape($branch_id) . ' 
        OR ea.branch_id = ' . $this->db->escape($branch_id) . '
        )');
        $this->db->where('oe.is_live', 0);
        $exams = $this->db->get()->result_array();

        $classAverage = [];

        foreach ($exams as $exam) {
            $examID = $exam['exam_id'];

            // 2️⃣ Get all students who submitted this exam
            $this->db->select('DISTINCT(student_id)');
            $this->db->from('online_exam_submitted');
            $this->db->where('online_exam_id', $examID);
            $students = $this->db->get()->result_array();

            if (empty($students)) {
                // Skip if no one took the exam
                continue;
            }

            $totalMarks = 0;
            $totalObtained = 0;
            $studentCount = 0;

            foreach ($students as $stu) {
                $result = $this->examProgressReportSubjectwise($examID, $stu['student_id'], $subject_id);

                if ($result && $result['total_marks'] > 0) {
                    $totalMarks += $result['total_marks'];
                    $totalObtained += $result['total_obtain_marks'];
                    $studentCount++;
                }
            }

            $avgPercentage = $studentCount > 0 ? ($totalObtained / $totalMarks) * 100 : 0;

            $classAverage[] = [
                'exam_id' => $examID,
                'title' => $exam['title'],
                'avg_percentage' => round($avgPercentage, 2),
                'student_count' => $studentCount,
            ];
        }

        return $classAverage;
    }


    public function getSubjectWiseClassAverage_f($branch_id, $class_id, $subject_id)
    {
        // 1️⃣ Fetch all online exams for this class & subject
        $this->db->select('oe.id as exam_id, oe.title');
        $this->db->from('online_exam as oe');
        $this->db->join('exam_assignment as ea', 'ea.exam_id = oe.id', 'left');
        $this->db->where('oe.class_id', $class_id);
        $this->db->where('(
        oe.created_by_branch = ' . $this->db->escape($branch_id) . ' 
        OR ea.branch_id = ' . $this->db->escape($branch_id) . '
        )');
        $this->db->where('oe.is_live', 0);
        $exams = $this->db->get()->result_array();

        $classAverage = [];

        // 2️⃣ Loop through each exam
        foreach ($exams as $exam) {
            $examID = $exam['exam_id'];

            // 3️⃣ Get all students who submitted this exam
            $this->db->select('DISTINCT(student_id)');
            $this->db->from('online_exam_submitted');
            $this->db->where('online_exam_id', $examID);
            $students = $this->db->get()->result_array();

            $totalMarks = 0;
            $totalObtained = 0;
            $studentCount = 0;

            // 4️⃣ Loop each student & compute their subject-wise result
            foreach ($students as $stu) {
                $studentID = $stu['student_id'];

                $result = $this->examProgressReportSubjectwise($examID, $studentID, $subject_id);

                if ($result && $result['total_marks'] > 0) {
                    $totalMarks += $result['total_marks'];
                    $totalObtained += $result['total_obtain_marks'];
                    $studentCount++;
                }
            }

            // 5️⃣ Compute average if students exist
            if ($studentCount > 0) {
                $avgPercentage = ($totalObtained / $totalMarks) * 100;
            } else {
                $avgPercentage = 0;
            }

            $classAverage[] = [
                'exam_id' => $examID,
                'title' => $exam['title'],
                'avg_percentage' => round($avgPercentage, 2),
                'student_count' => $studentCount,
            ];
        }

        return $classAverage;
    }



    private function array_equal($a, $b)
    {
        return (
            is_array($a) && is_array($b) && count($a) == count($b) && array_diff($a, $b) === array_diff($b, $a)
        );
    }
}
