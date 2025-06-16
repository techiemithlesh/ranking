<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}


class Leaderboard_model extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getOfflineExamLeaderboard($branch_id, $class_id, $section_id, $exam_id, $subject_id = null)
    {
        $subjectWhere = $subject_id ? "AND mark.subject_id = {$subject_id}" : "";

        $sql = "SELECT 
                mark.*,
                subject.subject_name,
                subject.mark_distribution,
                student.register_no,
                student.photo,
                CONCAT(student.first_name, ' ', student.last_name) AS full_name
            FROM mark
            JOIN (
                SELECT 
                    timetable_exam.exam_id,
                    timetable_exam.subject_id,
                    timetable_exam.mark_distribution,
                    subject.name AS subject_name
                FROM timetable_exam
                JOIN subject ON subject.id = timetable_exam.subject_id
                WHERE timetable_exam.exam_id = {$exam_id}
                AND timetable_exam.class_id = {$class_id}
                AND timetable_exam.section_id = {$section_id}
                AND timetable_exam.branch_id = {$branch_id}
            ) AS subject 
            ON subject.subject_id = mark.subject_id 
            AND subject.exam_id = mark.exam_id
            JOIN student ON student.id = mark.student_id
            WHERE mark.exam_id = {$exam_id}
                AND mark.class_id = {$class_id}
                AND mark.section_id = {$section_id}
                AND mark.branch_id = {$branch_id}
                {$subjectWhere}";

        $result = $this->db->query($sql)->result_array();
        $leaderboard = [];
        foreach ($result as $row) {
            $markJson = json_decode($row['mark'], true);
            $markDistribution = json_decode($row['mark_distribution'], true);
            foreach ($markJson as $subId => $obtainMark) {
                $fullMark = isset($markDistribution[$subId]['full_mark']) ? $markDistribution[$subId]['full_mark'] : 100;
                $passMark = isset($markDistribution[$subId]['pass_mark']) ? $markDistribution[$subId]['pass_mark'] : 50;

                $percentage = ($fullMark > 0) ? round(($obtainMark / $fullMark) * 100, 2) : 0;

                $leaderboard[] = [
                    'student_id' => $row['student_id'],
                    'register_no' => $row['register_no'],
                    'photo' => $row['photo'],
                    'full_name' => $row['full_name'],
                    'subject_id' => $subId,
                    'subject_name' => $row['subject_name'],
                    'obtain_mark' => $obtainMark,
                    'full_mark' => $fullMark,
                    'pass_mark' => $passMark,
                    'percentage' => $percentage,
                    'remarks' => $row['remarks']
                ];
            }
        }

        usort($leaderboard, function ($a, $b) {
            return $b['obtain_mark'] <=> $a['obtain_mark'];
        });

        return $leaderboard;
    }

    public function getOfflineExamLeaderboard2($branch_id, $class_id, $section_id, $exam_id, $subject_id = null)
    {
        $subjectWhere = $subject_id ? "AND mark.subject_id = {$subject_id}" : "";
        $sql = "SELECT 
                mark.*,
                subject.subject_id AS exam_subject_id,
                subject.subject_name,
                subject.mark_distribution,
                student.register_no,
                student.photo,
                CONCAT(student.first_name, ' ', student.last_name) AS full_name
            FROM mark
            JOIN (
                SELECT 
                    timetable_exam.exam_id,
                    timetable_exam.subject_id,
                    timetable_exam.mark_distribution,
                    subject.name AS subject_name
                FROM timetable_exam
                JOIN subject ON subject.id = timetable_exam.subject_id
                WHERE timetable_exam.exam_id = {$exam_id}
                AND timetable_exam.class_id = {$class_id}
                AND timetable_exam.section_id = {$section_id}
                AND timetable_exam.branch_id = {$branch_id}
            ) AS subject 
            ON subject.subject_id = mark.subject_id 
            AND subject.exam_id = mark.exam_id
            JOIN student ON student.id = mark.student_id
            WHERE mark.exam_id = {$exam_id}
                AND mark.class_id = {$class_id}
                AND mark.section_id = {$section_id}
                AND mark.branch_id = {$branch_id}
                {$subjectWhere}";

        $result = $this->db->query($sql)->result_array();
        $leaderboard = [];

        if (!empty($subject_id)) {
            // ✅ SUBJECT FILTERED CASE
            foreach ($result as $row) {
                $markJson = json_decode($row['mark'], true);
                $markDistribution = json_decode($row['mark_distribution'], true);

                // Extract only markDistribution ID from the JSON
                foreach ($markJson as $distributionId => $obtainMark) {
                    $fullMark = isset($markDistribution[$distributionId]['full_mark']) ? $markDistribution[$distributionId]['full_mark'] : 100;
                    $passMark = isset($markDistribution[$distributionId]['pass_mark']) ? $markDistribution[$distributionId]['pass_mark'] : 50;

                    $percentage = ($fullMark > 0) ? round(($obtainMark / $fullMark) * 100, 2) : 0;

                    $leaderboard[] = [
                        'student_id' => $row['student_id'],
                        'register_no' => $row['register_no'],
                        'photo' => $row['photo'],
                        'full_name' => $row['full_name'],
                        'subject_id' => $row['exam_subject_id'],
                        'subject_name' => $row['subject_name'],
                        'obtain_mark' => $obtainMark,
                        'full_mark' => $fullMark,
                        'pass_mark' => $passMark,
                        'percentage' => $percentage,
                        'remarks' => $row['remarks']
                    ];
                }
            }
        } else {
            // ✅ NO SUBJECT FILTER = OVERALL SUMMATION
            $studentAggregate = [];

            foreach ($result as $row) {
                $markJson = json_decode($row['mark'], true);
                $markDistribution = json_decode($row['mark_distribution'], true);

                foreach ($markJson as $distributionId => $obtainMark) {
                    $fullMark = isset($markDistribution[$distributionId]['full_mark']) ? $markDistribution[$distributionId]['full_mark'] : 100;

                    if (!isset($studentAggregate[$row['student_id']])) {
                        $studentAggregate[$row['student_id']] = [
                            'student_id' => $row['student_id'],
                            'register_no' => $row['register_no'],
                            'photo' => $row['photo'],
                            'full_name' => $row['full_name'],
                            'total_obtain' => 0,
                            'total_full' => 0,
                        ];
                    }

                    $studentAggregate[$row['student_id']]['total_obtain'] += $obtainMark;
                    $studentAggregate[$row['student_id']]['total_full'] += $fullMark;
                }
            }

            foreach ($studentAggregate as $student) {
                $percentage = ($student['total_full'] > 0)
                    ? round(($student['total_obtain'] / $student['total_full']) * 100, 2)
                    : 0;

                $leaderboard[] = [
                    'student_id' => $student['student_id'],
                    'register_no' => $student['register_no'],
                    'photo' => $student['photo'],
                    'full_name' => $student['full_name'],
                    'subject_name' => 'Cumulative Score',
                    'obtain_mark' => $student['total_obtain'],
                    'full_mark' => $student['total_full'],
                    'percentage' => $percentage,
                    'remarks' => ''
                ];
            }
        }

        usort($leaderboard, fn($a, $b) => $b['percentage'] <=> $a['percentage']);
        return $leaderboard;
    }



    public function getOnlineExamLeaderboard($branch_id, $class_id, $section_id, $exam_id, $subject_id = null)
    {
        $this->db->select('online_exam_submitted.student_id, student.register_no, student.photo, CONCAT(student.first_name, " ", student.last_name) as full_name, online_exam.*');
        $this->db->from('online_exam_submitted');
        $this->db->join('online_exam', 'online_exam.id = online_exam_submitted.online_exam_id', 'inner');
        $this->db->join('student', 'student.id = online_exam_submitted.student_id', 'left');
        $this->db->where('online_exam_submitted.online_exam_id', $exam_id);
        $this->db->where('online_exam.session_id', get_session_id());
        $this->db->where('online_exam.class_id', $class_id);
        $this->db->where('online_exam.branch_id', $branch_id);

        $results = $this->db->get()->result_array();
        $leaderboard = [];

        foreach ($results as $row) {
            $examSections = json_decode($row['section_id'], true);
            $examSubjects = json_decode($row['subject_id'], true);

            if (!in_array($section_id, $examSections))
                continue;
            if ($subject_id && !in_array($subject_id, $examSubjects))
                continue;

            $examResult = $this->examResult($row['id'], $row['student_id']);

            $total_neg_marks = $row['neg_mark'] == 0 ? 0 : $examResult['total_neg_marks'];
            $mark = ($examResult['total_obtain_marks'] - $total_neg_marks);
            $fullMark = $examResult['total_marks'];
            $score = ($examResult['total_marks'] === 0) ? '0.00' : number_format(($mark * 100 / $examResult['total_marks']), 2, '.', '');

            $percentage = ($fullMark > 0) ? round(($mark / $fullMark) * 100, 2) : 0;

            foreach ($examSubjects as $subId) {
                if ($subject_id && $subId != $subject_id)
                    continue;
                $leaderboard[] = [
                    'student_id' => $row['student_id'],
                    'register_no' => $row['register_no'],
                    'photo' => $row['photo'],
                    'full_name' => $row['full_name'],
                    'subject_id' => $subId,
                    'subject_name' => get_type_name_by_id('subject', $subId),
                    'obtain_mark' => $mark,
                    'full_mark' => $examResult['total_marks'],
                    'pass_mark' => $row['passing_mark'],
                    'percentage' => $percentage,
                    'remarks' => $row['remark']
                ];
            }
        }

        usort($leaderboard, function ($a, $b) {
            return $b['obtain_mark'] <=> $a['obtain_mark'];
        });

        return $leaderboard;
    }


    public function getOnlineExamLeaderboard2($branch_id, $class_id, $section_id, $exam_id, $subject_id = null)
    {
        $this->db->select('online_exam_submitted.student_id, student.register_no, student.photo, CONCAT(student.first_name, " ", student.last_name) as full_name, online_exam.*');
        $this->db->from('online_exam_submitted');
        $this->db->join('online_exam', 'online_exam.id = online_exam_submitted.online_exam_id', 'inner');
        $this->db->join('student', 'student.id = online_exam_submitted.student_id', 'left');
        $this->db->where('online_exam_submitted.online_exam_id', $exam_id);
        $this->db->where('online_exam.session_id', get_session_id());
        $this->db->where('online_exam.class_id', $class_id);
        $this->db->where('online_exam.branch_id', $branch_id);

        $results = $this->db->get()->result_array();
        $leaderboard = [];
        $studentAggregate = [];

        foreach ($results as $row) {
            $examSections = json_decode($row['section_id'], true);
            $examSubjects = json_decode($row['subject_id'], true);

            // Check if this exam is for the current section
            if (!in_array($section_id, $examSections))
                continue;

            $examResult = $this->examResult($row['id'], $row['student_id']);
            $total_neg_marks = $row['neg_mark'] == 0 ? 0 : $examResult['total_neg_marks'];
            $mark = ($examResult['total_obtain_marks'] - $total_neg_marks);
            $fullMark = $examResult['total_marks'];
            $percentage = ($fullMark > 0) ? round(($mark / $fullMark) * 100, 2) : 0;

            if ($subject_id) {
                // ✅ SUBJECT-WISE MODE
                if (!in_array($subject_id, $examSubjects))
                    continue;

                $leaderboard[] = [
                    'student_id' => $row['student_id'],
                    'register_no' => $row['register_no'],
                    'photo' => $row['photo'],
                    'full_name' => $row['full_name'],
                    'subject_id' => $subject_id,
                    'subject_name' => get_type_name_by_id('subject', $subject_id),
                    'obtain_mark' => $mark,
                    'full_mark' => $examResult['total_marks'],
                    'pass_mark' => $row['passing_mark'],
                    'percentage' => $percentage,
                    'remarks' => $row['remark']
                ];

            } else {
                // ✅ COMBINED MODE (all subjects together)
                if (!isset($studentAggregate[$row['student_id']])) {
                    $studentAggregate[$row['student_id']] = [
                        'student_id' => $row['student_id'],
                        'register_no' => $row['register_no'],
                        'photo' => $row['photo'],
                        'full_name' => $row['full_name'],
                        'total_obtain' => 0,
                        'total_full' => 0,
                        'remarks' => $row['remark']
                    ];
                }

                $studentAggregate[$row['student_id']]['total_obtain'] += $mark;
                $studentAggregate[$row['student_id']]['total_full'] += $examResult['total_marks'];
            }
        }

        // Only build aggregate leaderboard if subject is not selected
        if (!$subject_id) {
            foreach ($studentAggregate as $student) {
                $percentage = ($student['total_full'] > 0)
                    ? round(($student['total_obtain'] / $student['total_full']) * 100, 2)
                    : 0;

                $leaderboard[] = [
                    'student_id' => $student['student_id'],
                    'register_no' => $student['register_no'],
                    'photo' => $student['photo'],
                    'full_name' => $student['full_name'],
                    'subject_name' => 'Cumulative Score', // alternative to 'Overall'
                    'obtain_mark' => $student['total_obtain'],
                    'full_mark' => $student['total_full'],
                    'pass_mark' => '', // optional
                    'percentage' => $percentage,
                    'remarks' => $student['remarks']
                ];
            }
        }

        // Sort leaderboard by marks or percentage
        usort($leaderboard, fn($a, $b) => $b['percentage'] <=> $a['percentage']);

        return $leaderboard;
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

    public function getExamResults($onlineexamID = null, $studentID = 0)
    {
        $sql = "SELECT `questions_manage`.*, `questions`.`id` as `qus_id`, `questions`.*, `online_exam_answer`.`answer` as `sb_ans`, `online_exam_answer`.`id` as `ans_id` FROM `questions_manage` INNER JOIN `questions` ON `questions`.`id` = `questions_manage`.`question_id` LEFT JOIN `online_exam_answer` ON `online_exam_answer`.`online_exam_id` = `questions_manage`.`onlineexam_id` and `online_exam_answer`.`question_id` = `questions`.`id` and `online_exam_answer`.`student_id` = " . $this->db->escape($studentID) . " WHERE `questions_manage`.`onlineexam_id` = " . $this->db->escape($onlineexamID) . " ORDER BY `questions_manage`.`id` ASC";
        $query = $this->db->query($sql);

        return $query->result();
    }


    public function array_equal($a, $b)
    {
        return (
            is_array($a) && is_array($b) && count($a) == count($b) && array_diff($a, $b) === array_diff($b, $a)
        );
    }
}
