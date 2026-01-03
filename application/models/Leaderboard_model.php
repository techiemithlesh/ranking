<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}


class Leaderboard_model extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('live_exam_model');
    }

    public function getOfflineExamLeaderboard($branch_id, $class_id, $section_id, $exam_id, $subject_id = null)
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
        $this->db->where('online_exam.created_by_branch', $branch_id);

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

    /**
     * Compute leaderboard for given session
     * @param mixed $session_code
     * @return bool
     */
    public function computeLeaderboard($session_code)
    {
        $session = $this->live_exam_model->getSessionByCodeAnyStatus($session_code);

        live_exam_log('info', 'The session from model method is: ' . json_encode($session));

        if (!$session)
            return false;

        $session_id = $session['id'];
        $exam_id = $session['exam_id'];

        $students = $this->live_exam_model->getStudentByExamSession($session_id);

        $leaderboard = [];

        foreach ($students as $student) {
            $student_id = $student['student_id'];
            $report = $this->live_exam_model->getLiveExamSessionReport($session_code, $student_id);

            // estimate finish_time (joined_at → last_ping_at)
            $joinData = $this->db->select('joined_at,last_ping_at')
                ->get_where('exam_session_students', [
                    'session_id' => $session_id,
                    'student_id' => $student_id
                ])->row_array();
            $finish_time = !empty($joinData['last_ping_at']) ? $joinData['last_ping_at'] : null;

            $leaderboard[] = [
                'session_id' => $session_id,
                'session_code' => $session_code,
                'exam_id' => $exam_id,
                'student_id' => $student_id,
                'branch_id' => $student['branch_id'],
                'class_id' => $student['class_id'],
                'section_id' => $student['section_id'],
                'total_marks' => $report['total_marks'],
                'obtain_marks' => $report['total_obtain_marks'],
                'percentage' => $report['percentage'],
                'correct_ans' => $report['correct_ans'],
                'wrong_ans' => $report['wrong_ans'],
                'total_answered' => $report['total_answered'],
                'total_skipped' => $report['total_question'] - $report['total_answered'],
                'total_question' => $report['total_question'],
                'negative_marks' => $report['total_neg_marks'],
                'accuracy' => $report['total_answered'] > 0
                    ? round(($report['correct_ans'] / $report['total_answered']) * 100, 2)
                    : 0.00,
                'percentile' => 0.00, // will compute after ranks

                'rank_position' => 0, // temporary
                'rank_method' => 'competition',
                'tiebreaker' => 'finish_time',
                'finish_time' => $finish_time,
                'rank_band' => null,

                'computed_at' => date('Y-m-d H:i:s'),
                'is_published' => 1,
            ];
        }

        // 3. Clear old leaderboard for session
        $this->db->where('session_id', $session_id)->delete('exam_session_leaderboard');

        // 4. Insert fresh records
        if (!empty($leaderboard)) {
            $this->db->insert_batch('exam_session_leaderboard', $leaderboard);
        }

        // 5. Apply ranks & percentiles
        $this->applyRanks($session_id);

        return count($leaderboard);
    }

    /**
     * Assign ranks and percentiles
     */
    private function applyRanks($session_id)
    {
        // Get all students for this session, sorted by tie-break rules
        $students = $this->db
            ->order_by('obtain_marks', 'DESC')     // Highest marks first
            ->order_by('wrong_ans', 'ASC')         // Then fewer wrong answers
            ->order_by('total_skipped', 'ASC')     // Then fewer skips
            ->order_by('finish_time', 'ASC')       // Then fastest finish
            ->get_where('exam_session_leaderboard', ['session_id' => $session_id])
            ->result_array();

        if (empty($students))
            return;

        $rank = 1;             // Current rank to assign
        $lastRank = 1;         // Last assigned rank
        $lastScore = null;     // Track score + tiebreak attributes
        $total = count($students);

        foreach ($students as $i => $stu) {
            // Build a composite score for tie detection
            $currentScore = [
                $stu['obtain_marks'],
                $stu['wrong_ans'],
                $stu['total_skipped'],
                $stu['finish_time'],
            ];

            // Compare with last student: if different, update rank
            if ($lastScore !== null && $currentScore !== $lastScore) {
                $rank = $i + 1;   // competition ranking → next index + 1
            }

            // Compute percentile
            $percentile = round((($total - $rank) / $total) * 100, 2);

            // Update row
            $this->db->where('id', $stu['id'])->update('exam_session_leaderboard', [
                'rank_position' => $rank,
                'percentile' => $percentile,
                'rank_band' => $this->getRankBand($percentile)
            ]);

            // Save last state
            $lastScore = $currentScore;
            $lastRank = $rank;
        }
    }

    private function getRankBand($percentile)
    {
        if ($percentile >= 95)
            return 'Top 5%';
        if ($percentile >= 90)
            return 'Top 10%';
        if ($percentile >= 75)
            return 'Top 25%';
        if ($percentile >= 50)
            return 'Top 50%';
        if ($percentile >= 25)
            return 'Bottom 50%';
        return 'Bottom 25%';
    }

    public function getTopN($session_code, $limit = 3)
    {
        if (empty($session_code)) {
            return [];
        }

        return $this->db
            ->select('l.*, s.first_name, s.last_name, e.roll, c.name as class_name, sec.name as section_name, s.photo')
            ->from('exam_session_leaderboard l')
            ->join('student s', 's.id = l.student_id')
            ->join('enroll e', 'e.student_id = l.student_id')
            ->join('class c', 'c.id = e.class_id')
            ->join('section sec', 'sec.id = e.section_id')
            ->where('l.session_code', $session_code)
            ->order_by('l.rank_position', 'ASC')
            ->limit((int) $limit)
            ->get()
            ->result_array();
    }

    public function getRankPage($session_code, $offset = 0, $limit = 20, $excludeTopN = 3)
    {
        if (empty($session_code)) {
            return [];
        }

        $offset = max(0, (int) $offset);
        $limit = max(1, (int) $limit);

        // get top N student_ids first
        $topStudentIds = $this->db
            ->select('student_id')
            ->from('exam_session_leaderboard')
            ->where('session_code', $session_code)
            ->order_by('rank_position', 'ASC')
            ->limit($excludeTopN)
            ->get()
            ->result_array();

        $excludeIds = array_column($topStudentIds, 'student_id');

        $this->db
            ->select('l.*, s.first_name, s.last_name, e.roll, c.name as class_name, sec.name as section_name, s.photo')
            ->from('exam_session_leaderboard l')
            ->join('student s', 's.id = l.student_id')
            ->join('enroll e', 'e.student_id = l.student_id')
            ->join('class c', 'c.id = e.class_id')
            ->join('section sec', 'sec.id = e.section_id')
            ->where('l.session_code', $session_code);

        if (!empty($excludeIds)) {
            $this->db->where_not_in('l.student_id', $excludeIds);
        }

        return $this->db
            ->order_by('l.rank_position', 'ASC')
            ->limit($limit, $offset)
            ->get()
            ->result_array();
    }

    /**
     * Get student’s own rank details
     */
    public function getStudentRank($session_code, $student_id)
    {
        return $this->db
            ->select('l.*, s.first_name, s.last_name, s.photo, e.roll, c.name as class_name, sec.name as section_name')
            ->from('exam_session_leaderboard l')
            ->join('student s', 's.id = l.student_id')
            ->join('enroll e', 'e.student_id = l.student_id')
            ->join('class c', 'c.id = e.class_id')
            ->join('section sec', 'sec.id = e.section_id')
            ->where('l.session_code', $session_code)
            ->where('l.student_id', $student_id)
            ->get()
            ->row_array();
    }

    /**
     * Get nearby students (±5 ranks around logged-in student)
     */
    public function getNearbyStudents($session_code, $rank_position, $range = 5)
    {
        $min = max(1, $rank_position - $range);
        $max = $rank_position + $range;

        return $this->db
            ->select('l.*, s.first_name, s.last_name, s.photo, e.roll, c.name as class_name, sec.name as section_name')
            ->from('exam_session_leaderboard l')
            ->join('student s', 's.id = l.student_id')
            ->join('enroll e', 'e.student_id = l.student_id')
            ->join('class c', 'c.id = e.class_id')
            ->join('section sec', 'sec.id = e.section_id')
            ->where('l.session_code', $session_code)
            ->where('l.rank_position >=', $min)
            ->where('l.rank_position <=', $max)
            ->order_by('l.rank_position', 'ASC')
            ->get()
            ->result_array();
    }

    public function countLeaderboard($session_code)
    {
        return $this->db
            ->where('session_code', $session_code)
            ->count_all_results('exam_session_leaderboard');
    }

    public function getAllRank($branchID, $classID, $sectionId, $examID, $sessionCode = null)
    {
        $this->db->select('l.*, s.first_name, s.last_name, s.photo, c.name as class_name, sec.name as section_name');
        $this->db->from('exam_session_leaderboard l');
        $this->db->join('student s', 's.id = l.student_id');
        $this->db->join('enroll e', 'e.student_id = s.id');
        $this->db->join('class c', 'c.id = e.class_id');
        $this->db->join('section sec', 'sec.id = e.section_id');
        $this->db->where('l.exam_id', $examID);
        $this->db->where('l.class_id', $classID);
        $this->db->where('l.section_id', $sectionId);
        $this->db->where('l.branch_id', $branchID);

        if (!empty($sessionCode)) {
            $this->db->where('l.session_code', $sessionCode);
        }

        $this->db->order_by('l.rank_position', 'ASC');
        return $this->db->get()->result_array();
    }

    /**
     * LIVE EXAM REWARD SYSTEM INTEGRATION
     * 
     */

    public function getAllRankBySession($session_code)
    {
        if (empty($session_code)) {
            // log_message('error', '[Leaderboard] Missing session_code in getAllRankBySession');
            return [];
        }

        // ✅ Fetch leaderboard records from unified table
        $query = $this->db->select("
            el.id AS leaderboard_id,
            el.session_id,
            el.session_code,
            el.exam_id,
            el.student_id,
            el.branch_id,
            el.class_id,
            el.section_id,
            st.first_name,
            st.last_name,
            cl.name AS class_name,
            se.name AS section_name,
            el.total_marks,
            el.obtain_marks,
            el.percentage,
            el.correct_ans,
            el.wrong_ans,
            el.total_answered,
            el.total_skipped,
            el.total_question,
            el.percentile,
            el.rank_position
        ")
            ->from('exam_session_leaderboard AS el')
            ->join('student AS st', 'st.id = el.student_id', 'inner')
            ->join('class AS cl', 'cl.id = el.class_id', 'left')
            ->join('section AS se', 'se.id = el.section_id', 'left')
            ->where('el.session_code', $session_code)
            ->order_by('el.rank_position', 'ASC')
            ->get();

        $results = $query->result_array();

        if (empty($results)) {
            // log_message('debug', "[Leaderboard] No records found in leaderboard for session {$session_code}");
            return [];
        }

        // ✅ Ensure percentile is filled (in case older data lacks it)
        $total_students = count($results);
        foreach ($results as &$row) {
            if (empty($row['percentile']) && is_numeric($row['rank_position'])) {
                $row['percentile'] = $total_students > 1
                    ? round((($total_students - $row['rank_position']) / ($total_students - 1)) * 100, 2)
                    : 100;
            }

            // Normalize numeric fields
            $row['percentage'] = (float) $row['percentage'];
            $row['percentile'] = (float) $row['percentile'];
        }

        // log_message('debug', "[Leaderboard] Prepared leaderboard for reward processing: " . json_encode($results));

        return $results;
    }

    public function getLiveExamAllSessionsRank($branchID, $classID, $sectionID, $examID)
    {
        $rows = $this->db->select("
        l.student_id,
        s.first_name,
        s.last_name,
        s.photo,

        COUNT(DISTINCT l.session_id) AS sessions_count,

        SUM(l.obtain_marks) AS obtained_marks,
        SUM(l.total_marks) AS total_marks,

        ROUND(
            (SUM(l.obtain_marks) / NULLIF(SUM(l.total_marks), 0)) * 100,
            2
        ) AS percentage,

        SUM(l.correct_ans) AS correct,
        SUM(l.wrong_ans) AS wrong,
        SUM(l.total_skipped) AS skipped")
            ->from('exam_session_leaderboard l')
            ->join('student s', 's.id = l.student_id')
            ->join('enroll e', 'e.student_id = s.id')
            ->where('l.exam_id', $examID)
            ->where('l.branch_id', $branchID)
            ->where('l.class_id', $classID)
            ->where('l.section_id', $sectionID)
            ->group_by('l.student_id')
            ->get()
            ->result_array();

        return $this->applyAggregatedRank($rows);
    }

    private function applyAggregatedRank(array $rows)
    {
        usort($rows, function ($a, $b) {
            return
                $b['percentage'] <=> $a['percentage']
                ?: $b['sessions_count'] <=> $a['sessions_count']
                ?: $b['obtained_marks'] <=> $a['obtained_marks']
                ?: $a['student_id'] <=> $b['student_id'];
        });

        $rank = 1;
        foreach ($rows as &$row) {
            $row['rank_position'] = $rank++;
        }

        return $rows;
    }




    public function getLiveExamSubjectRank($branchID, $classID, $sectionID, $subjectID)
    {
        // Step 1: Fetch ALL session IDs where questions of this subject were used
        $this->db->select('es.id');
        $this->db->from('exam_sessions es');
        $this->db->join('questions_manage qm', 'qm.onlineexam_id = es.exam_id');
        $this->db->join('questions q', 'q.id = qm.question_id');
        $this->db->where('q.subject_id', $subjectID);
        $sessionRows = $this->db->get()->result_array();

        if (empty($sessionRows)) {
            return [];
        }

        $sessionIDs = array_column($sessionRows, 'id');

        // Step 2: Build main subject-wise leaderboard query
        $this->db->select("
        s.id AS student_id,
        s.first_name,
        s.last_name,
        s.photo,

        -- Marks obtained only when correct
        SUM(CASE WHEN esa.answer = q.answer THEN qm.marks ELSE 0 END) AS obtained_marks,

        -- Total possible marks
        SUM(qm.marks) AS total_marks,

        -- Percentage
        (
            SUM(CASE WHEN esa.answer = q.answer THEN qm.marks ELSE 0 END)
            / SUM(qm.marks)
        ) * 100 AS percentage,

        -- Wrong / Skipped / Time
        SUM(CASE WHEN esa.answer != q.answer AND esa.answer != '' THEN 1 ELSE 0 END) AS wrong,
        SUM(CASE WHEN esa.answer = '' OR esa.answer IS NULL THEN 1 ELSE 0 END) AS skipped,
        MIN(esa.submitted_at) AS submit_time");

        $this->db->from('exam_session_answers esa');
        $this->db->join('exam_sessions es', 'es.id = esa.session_id');
        $this->db->join('questions q', 'q.id = esa.question_id');
        $this->db->join('questions_manage qm', 'qm.question_id = q.id AND qm.onlineexam_id = es.exam_id');
        $this->db->join('student s', 's.id = esa.student_id');
        $this->db->join('enroll e', 'e.student_id = s.id');

        // Filters
        $this->db->where('q.subject_id', $subjectID);
        $this->db->where_in('esa.session_id', $sessionIDs);
        $this->db->where('e.class_id', $classID);
        $this->db->where('e.section_id', $sectionID);
        $this->db->where('e.branch_id', $branchID);

        $this->db->group_by('esa.student_id');

        $result = $this->db->get()->result_array();

        // Apply subject-wise ranking
        return $this->applySubjectWiseRank($result);
    }

    private function applySubjectWiseRank($list)
    {
        if (empty($list))
            return $list;

        // SORT by marks, wrong, skipped, time
        usort($list, function ($a, $b) {

            // 1. Higher marks first
            if ($b['obtained_marks'] != $a['obtained_marks']) {
                return $b['obtained_marks'] <=> $a['obtained_marks'];
            }

            // 2. Less wrong
            if ($a['wrong'] != $b['wrong']) {
                return $a['wrong'] <=> $b['wrong'];
            }

            // 3. Less skipped
            if ($a['skipped'] != $b['skipped']) {
                return $a['skipped'] <=> $b['skipped'];
            }

            // 4. Faster submit time wins
            return strtotime($a['submit_time']) <=> strtotime($b['submit_time']);
        });

        // Assign rank
        $rank = 1;
        $last = null;

        foreach ($list as $i => &$row) {

            $keys = [
                $row['obtained_marks'],
                $row['wrong'],
                $row['skipped'],
                $row['submit_time']
            ];

            if ($last !== null && $keys !== $last) {
                $rank = $i + 1;
            }

            $row['rank'] = $rank;
            $last = $keys;
        }

        return $list;
    }


    public function getOnlineExamSubjectReport($branchID, $classID, $sectionID, $examID, $subjectID = null)
    {
        $this->db->select("
        oes.student_id,
        s.first_name,
        s.last_name,
        s.photo,
        s.register_no,
        oe.subject_id,
        oe.section_id,
        oe.neg_mark,
        oe.passing_mark");
        $this->db->from('online_exam_submitted oes');
        $this->db->join('online_exam oe', 'oe.id = oes.online_exam_id', 'inner');
        $this->db->join('student s', 's.id = oes.student_id', 'left');
        $this->db->where('oes.online_exam_id', $examID);
        $this->db->where('oe.class_id', $classID);
        $this->db->where('oe.created_by_branch', $branchID);

        $rows = $this->db->get()->result_array();
        $report = [];

        foreach ($rows as $row) {

            // Get full exam result
            $examResult = $this->examResult($examID, $row['student_id']);

            if ($subjectID) {

                // Check if exam contains this subject
                $examSubjectArr = json_decode($row['subject_id'], true);
                if (!in_array($subjectID, $examSubjectArr))
                    continue;

                // Filter exam results to this subject
                $subjectResults = $this->filterExamResultBySubject($examID, $row['student_id'], $subjectID);

                if (!$subjectResults)
                    continue;

                $report[] = [
                    'student_id' => $row['student_id'],
                    'full_name' => trim($row['first_name'] . " " . $row['last_name']),
                    'photo' => $row['photo'],
                    'register_no' => $row['register_no'],

                    'total_questions' => $subjectResults['total_question'],
                    'correct' => $subjectResults['correct_ans'],
                    'wrong' => $subjectResults['wrong_ans'],
                    'skipped' => $subjectResults['total_question'] - $subjectResults['total_answered'],
                    'obtained_marks' => $subjectResults['total_obtain_marks'],
                    'total_marks' => $subjectResults['total_marks'],
                    'percentage' => $subjectResults['percentage'],
                ];
            } else {
                // FULL (ALL SUBJECTS)
                $report[] = [
                    'student_id' => $row['student_id'],
                    'full_name' => trim($row['first_name'] . " " . $row['last_name']),
                    'photo' => $row['photo'],
                    'register_no' => $row['register_no'],

                    'total_questions' => $examResult['total_question'],
                    'correct' => $examResult['correct_ans'],
                    'wrong' => $examResult['wrong_ans'],
                    'skipped' => $examResult['total_question'] - $examResult['total_answered'],
                    'obtained_marks' => $examResult['total_obtain_marks'],
                    'total_marks' => $examResult['total_marks'],
                    'percentage' => ($examResult['total_marks'] > 0)
                        ? round(($examResult['total_obtain_marks'] / $examResult['total_marks']) * 100, 2)
                        : 0,
                ];
            }
        }

        return $report;
    }

    private function filterExamResultBySubject($examID, $studentID, $subjectID)
    {
        $results = $this->getExamResults($examID, $studentID);

        $correct = 0;
        $wrong = 0;
        $total = 0;
        $answered = 0;
        $fullMarks = 0;
        $obtainMarks = 0;

        foreach ($results as $q) {
            if ($q->subject_id != $subjectID)
                continue;

            $total++;
            $fullMarks += $q->marks;

            if (!empty($q->ans_id)) {
                $answered++;

                if ($q->sb_ans == $q->answer) {
                    $correct++;
                    $obtainMarks += $q->marks;
                } else {
                    $wrong++;
                }
            }
        }

        return [
            'total_question' => $total,
            'correct_ans' => $correct,
            'wrong_ans' => $wrong,
            'total_answered' => $answered,
            'total_obtain_marks' => $obtainMarks,
            'total_marks' => $fullMarks,
            'percentage' => ($fullMarks > 0) ? round(($obtainMarks / $fullMarks) * 100, 2) : 0
        ];
    }

    public function getLiveExamSubjectReport_($branchID, $classID, $sectionID, $subjectID = null, $examID = null, $sessionCode = null)
    {
        $this->db->select("
                s.id AS student_id,
                s.first_name,
                s.last_name,

                COUNT(DISTINCT qm.question_id) AS total_questions,

                SUM(
                    CASE 
                        WHEN esa.answer IS NOT NULL AND esa.answer = q.answer THEN 1 
                        ELSE 0 
                    END
                ) AS correct_questions,

                SUM(
                    CASE 
                        WHEN esa.answer IS NOT NULL AND esa.answer != q.answer THEN 1 
                        ELSE 0 
                    END
                ) AS wrong,

                SUM(qm.marks) AS total_marks,

                SUM(
                    CASE 
                        WHEN esa.answer = q.answer THEN qm.marks 
                        ELSE 0 
                    END
                ) AS obtained_marks");


        // 🔴 START FROM SESSION STUDENTS (IMPORTANT)
        $this->db->from('exam_sessions es');
        $this->db->join('exam_session_students ess', 'ess.session_id = es.id');

        $this->db->join('student s', 's.id = ess.student_id');
        $this->db->join('enroll e', 'e.student_id = s.id');

        $this->db->join('questions_manage qm', 'qm.onlineexam_id = es.exam_id');
        $this->db->join('questions q', 'q.id = qm.question_id');

        // LEFT JOIN answers
        $this->db->join(
            'exam_session_answers esa',
            'esa.session_id = es.id 
         AND esa.student_id = s.id 
         AND esa.question_id = q.id',
            'LEFT'
        );

        // Filters
        if ($sessionCode) {
            $this->db->where('es.session_code', $sessionCode);
        }

        if ($examID) {
            $this->db->where('es.exam_id', $examID);
        }

        if ($subjectID) {
            $this->db->where('q.subject_id', $subjectID);
        }

        $this->db->where('e.branch_id', $branchID);
        $this->db->where('e.class_id', $classID);
        $this->db->where('e.section_id', $sectionID);

        $this->db->group_by('s.id');

        $rows = $this->db->get()->result_array();

        foreach ($rows as &$r) {
            $r['skipped'] = max(
                0,
                $r['total_questions'] - ($r['correct_questions'] + $r['wrong'])
            );


            $r['percentage'] = ($r['total_marks'] > 0)
                ? round(($r['obtained_marks'] / $r['total_marks']) * 100, 2)
                : 0;

            $r['full_name'] = trim($r['first_name'] . ' ' . $r['last_name']);
        }

        return $rows;
    }

    public function getLiveExamSubjectReport(
        $branchID,
        $classID,
        $sectionID,
        $subjectID = null,
        $examID = null,
        $sessionCode = null
    ) {
        $this->db->select("
        s.id AS student_id,
        s.first_name,
        s.last_name,
        COUNT(DISTINCT es.id) AS sessions_count,
        COUNT(q.id) AS total_questions,

        SUM(
            CASE 
                WHEN esa.answer IS NOT NULL 
                 AND esa.answer = q.answer 
                THEN 1 ELSE 0 
            END
        ) AS correct_questions,

        SUM(
            CASE 
                WHEN esa.answer IS NOT NULL 
                 AND esa.answer != q.answer 
                THEN 1 ELSE 0 
            END
        ) AS wrong,

        SUM(
            CASE 
                WHEN esa.answer IS NULL 
                THEN 1 ELSE 0 
            END
        ) AS skipped,

        SUM(qm.marks) AS total_marks,

        SUM(
            CASE 
                WHEN esa.answer = q.answer 
                THEN qm.marks 
                ELSE 0 
            END
        ) AS obtained_marks
    ");

        // 🔴 START FROM SESSION → ensures ONLY appeared students
        $this->db->from('exam_sessions es');
        $this->db->join('exam_session_students ess', 'ess.session_id = es.id');
        $this->db->join('student s', 's.id = ess.student_id');
        $this->db->join('enroll e', 'e.student_id = s.id');

        // Questions of this exam
        $this->db->join('questions_manage qm', 'qm.onlineexam_id = es.exam_id');
        $this->db->join('questions q', 'q.id = qm.question_id');

        // LEFT JOIN answers → skipped = NULL answers
        $this->db->join(
            'exam_session_answers esa',
            'esa.session_id = es.id 
         AND esa.student_id = s.id 
         AND esa.question_id = q.id',
            'LEFT'
        );

        // 🔎 Filters
        if (!empty($sessionCode)) {
            $this->db->where('es.session_code', $sessionCode);
        }

        if (!empty($examID)) {
            $this->db->where('es.exam_id', $examID);
        }

        if (!empty($subjectID)) {
            $this->db->where('q.subject_id', $subjectID);
        }

        $this->db->where('e.branch_id', $branchID);
        $this->db->where('e.class_id', $classID);
        $this->db->where('e.section_id', $sectionID);

        // ✅ ONE ROW PER STUDENT
        $this->db->group_by('s.id');

        $rows = $this->db->get()->result_array();

        // 📊 Post calculations
        foreach ($rows as &$r) {
            $r['percentage'] = ($r['total_marks'] > 0)
                ? round(($r['obtained_marks'] / $r['total_marks']) * 100, 2)
                : 0;

            $r['full_name'] = trim($r['first_name'] . ' ' . $r['last_name']);
        }

        return $rows;
    }


    public function array_equal($a, $b)
    {
        return (
            is_array($a) && is_array($b) && count($a) == count($b) && array_diff($a, $b) === array_diff($b, $a)
        );
    }
}
