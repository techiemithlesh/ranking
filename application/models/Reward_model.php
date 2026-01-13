<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Reward_model extends MY_Model
{

    public function __construct()
    {
        parent::__construct();
    }

    public function getRewardConfigs($filters = [])
    {
        $this->db->select("
        rc.*,
        b.name AS branch_name,
        c.name AS class_name,
        s.name AS section_name,
        CASE 
            WHEN rc.exam_type = 'online' THEN oe.title
            WHEN rc.exam_type = 'live_exam' THEN le.title
            ELSE e.name
        END AS exam_name");

        $this->db->from('reward_config AS rc');
        $this->db->join('branch AS b', 'b.id = rc.branch_id', 'left');
        $this->db->join('class AS c', 'c.id = rc.class_id', 'left');
        $this->db->join('section AS s', 's.id = rc.section_id', 'left');
        $this->db->join('exam AS e', 'e.id = rc.exam_id AND rc.exam_type = "offline"', 'left');
        $this->db->join('online_exam AS oe', 'oe.id = rc.exam_id AND rc.exam_type = "online"', 'left');
        $this->db->join('online_exam AS le', 'le.id = rc.exam_id AND rc.exam_type = "live_exam"', 'left');

        if (!empty($filters['branch_id'])) {
            $this->db->where('rc.branch_id', $filters['branch_id']);
        }
        if (!empty($filters['class_id'])) {
            $this->db->where('rc.class_id', $filters['class_id']);
        }
        if (!empty($filters['section_id'])) {
            $this->db->where('rc.section_id', $filters['section_id']);
        }
        if (!empty($filters['exam_type'])) {
            $this->db->where('rc.exam_type', $filters['exam_type']);
        }
        if (!empty($filters['exam_id'])) {
            $this->db->where('rc.exam_id', $filters['exam_id']);
        }

        $this->db->order_by('rc.created_at', 'DESC');
        return $this->db->get()->result_array() ?? [];
    }


    public function save($data)
    {
        $arrayReward = array(
            'branch_id' => $this->application_model->get_branch_id(),
            'class_id' => $data['class_id'],
            'section_id' => $data['section_id'],
            'exam_type' => $data['exam_type'],
            'exam_id' => $data['exam_id'],
            'reward_basis' => isset($data['reward_basis']) ? $data['reward_basis'] : 'percentage',
            'reward_scope' => isset($data['reward_scope']) ? $data['reward_scope'] : 'exam',
            'qualifying_value' => $data['qualifying_value'],
            'coin_reward' => $data['coin_reward'],
            'is_active' => $data['is_active']
        );
        if (!isset($data['reward_config_id'])) {
            $this->db->insert('reward_config', $arrayReward);
        } else {
            $this->db->where('id', $data['reward_config_id']);
            $this->db->update('reward_config', $arrayReward);
        }
    }


    public function rewardRuleExists(array $data, ?int $excludeId = null): bool
    {
        $this->db->where([
            'branch_id'        => $this->application_model->get_branch_id(),
            'class_id'         => $data['class_id'],
            'section_id'       => $data['section_id'],
            'exam_type'        => $data['exam_type'],
            'exam_id'          => $data['exam_id'],
            'reward_scope'     => $data['reward_scope'],
            'reward_basis'     => $data['reward_basis'],
            'qualifying_value' => $data['qualifying_value']
        ]);

        // 👇 allow edit without blocking itself
        if (!empty($excludeId)) {
            $this->db->where('id !=', $excludeId);
        }

        return $this->db->count_all_results('reward_config') > 0;
    }


    public function isRewarded($exam_id, $student_id)
    {
        $this->db->where('exam_id', $exam_id);
        $this->db->where('student_id', $student_id);
        return $this->db->get('student_rewards')->num_rows() > 0;
    }

    /**
     * Finds the most appropriate reward based on Rank > Percentile > Percentage.
     */
    public function getApplicableReward(
        $student_id,
        $exam_id,
        $exam_type,
        array $performance
    ) {
        $this->db->select('rc.*');
        $this->db->from('reward_config rc');
        $this->db->join(
            'enroll e',
            'e.class_id = rc.class_id
         AND e.section_id = rc.section_id
         AND e.student_id = ' . $this->db->escape($student_id)
        );
        $this->db->where([
            'rc.exam_id'   => $exam_id,
            'rc.exam_type' => $exam_type,
            'rc.is_active' => 1
        ]);

        $configs = $this->db->get()->result_array();
        if (empty($configs)) {
            return null;
        }

        // Priority order
        $priority = [
            'rank'       => 1,
            'percentile' => 2,
            'percentage' => 3
        ];

        $bestRule = null;
        $bestPriority = PHP_INT_MAX;

        foreach ($configs as $config) {
            $basis = strtolower($config['reward_basis']);
            $qual  = (float) $config['qualifying_value'];
            $value = (float) ($performance[$basis] ?? 0);

            $eligible = false;
            switch ($basis) {
                case 'rank':
                    $eligible = ($value > 0 && $value <= $qual);
                    break;
                case 'percentile':
                case 'percentage':
                    $eligible = ($value >= $qual);
                    break;
            }

            if (!$eligible) {
                continue;
            }

            $currentPriority = $priority[$basis] ?? 99;

            if (
                $currentPriority < $bestPriority ||
                ($currentPriority === $bestPriority &&
                    $config['coin_reward'] > ($bestRule['coin_reward'] ?? 0))
            ) {
                $bestPriority = $currentPriority;
                $config['performance_value'] = $value;
                $bestRule = $config;
            }
        }

        return $bestRule;
    }


    public function isAlreadyRewarded(int $student_id, int $exam_id, string $exam_type, string $reward_scope, ?string $session_code = null)
    {
        $this->db->where([
            'student_id'   => $student_id,
            'exam_id'      => $exam_id,
            'exam_type'    => $exam_type,
            'reward_scope' => $reward_scope,
        ]);

        // 🔑 Session-based reward → must also match session_code
        if ($reward_scope === 'session') {
            live_exam_log('debug', "[RewardModel] Checking session-based reward for session_code={$session_code}");
            $this->db->where('session_code', $session_code);
        }

        return $this->db->count_all_results('student_rewards') > 0;
    }

    /**
     * Log and apply a reward transaction safely (atomic).
     */

    public function logRewardTransaction(
        $student_id,
        $exam_id,
        $exam_type,
        $coins,
        $remarks,
        $reference_type = 'exam',
        $session_code = null,
        $reward_scope = 'exam'
    ) {
        $check = [
            'student_id'  => $student_id,
            'exam_id'     => $exam_id,
            'exam_type'   => $exam_type,
            'reward_scope' => $reward_scope
        ];

        if ($reward_scope === 'session' && $session_code) {
            $check['session_code'] = $session_code;
        }

        if ($this->db->get_where('student_rewards', $check)->row()) {
            return false;
        }

        $this->db->trans_start();

        // Reward entry
        $this->db->insert('student_rewards', [
            'student_id'   => $student_id,
            'exam_id'      => $exam_id,
            'exam_type'    => $exam_type,
            'earned_coins' => $coins,
            'remarks'      => $remarks,
            'reward_scope' => $reward_scope,
            'session_code' => $session_code,
            'rewarded_at'  => date('Y-m-d H:i:s'),
        ]);

        // Wallet update
        $wallet = $this->db->get_where('student_wallet', ['student_id' => $student_id])->row_array();
        if ($wallet) {
            $this->db->set('total_coins', 'total_coins + ' . (int)$coins, false)
                ->where('student_id', $student_id)
                ->update('student_wallet');
        } else {
            $this->db->insert('student_wallet', [
                'student_id'  => $student_id,
                'total_coins' => $coins
            ]);
        }

        // Transaction log
        $this->db->insert('reward_transactions_log', [
            'student_id'    => $student_id,
            'type'          => 'earn',
            'coins'         => $coins,
            'reference_id'  => $exam_id,
            'reference_type' => $reference_type,
            'exam_type'     => $exam_type,
            'session_code'  => $session_code,
            'remarks'       => $remarks,
        ]);

        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    public function hasRewardLock(
        int $student_id,
        int $exam_id,
        string $exam_type,
        string $reward_scope,
        ?string $session_code = null
    ) {
        $this->db->where([
            'student_id'   => $student_id,
            'exam_id'      => $exam_id,
            'exam_type'    => $exam_type,
            'reward_scope' => $reward_scope,
        ]);

        if ($reward_scope === 'session') {
            $this->db->where('session_code', $session_code);
        }

        return $this->db->count_all_results('live_exam_reward_locks') > 0;
    }

    public function acquireRewardLock_(
        int $student_id,
        int $exam_id,
        string $exam_type,
        string $reward_scope,
        ?string $session_code = null
    ): bool {
        $this->db->insert('live_exam_reward_locks', [
            'student_id'   => $student_id,
            'exam_id'      => $exam_id,
            'exam_type'    => $exam_type,
            'reward_scope' => $reward_scope,
            'session_code' => $reward_scope === 'session' ? $session_code : null,
            'locked_at'    => date('Y-m-d H:i:s'),
        ]);

        // ✅ duplicate key or insert failure
        if ($this->db->affected_rows() === 0) {
            return false;
        }

        return true;
    }

    public function acquireRewardLock(
        int $student_id,
        int $exam_id,
        string $exam_type,
        string $reward_scope,
        ?string $session_code = null): bool {

        $this->db->insert('live_exam_reward_locks', [
            'student_id'   => $student_id,
            'exam_id'      => $exam_id,
            'exam_type'    => $exam_type,
            'reward_scope' => $reward_scope,
            'session_code' => ($reward_scope === 'session') ? $session_code : null,
            'locked_at'    => date('Y-m-d H:i:s'),
        ]);

        // ✅ Success
        if ($this->db->affected_rows() === 1) {
            return true;
        }

        // ❌ Insert failed → check reason
        $error = $this->db->error();

        // MySQL duplicate key error code
        if (!empty($error['code']) && $error['code'] == 1062) {
            return false; // already locked
        }

        // Other DB error → log it
        log_message(
            'error',
            '[RewardLock] DB error: ' . json_encode($error)
        );

        return false;
    }


    public function rewardList($data)
    {
        $this->db->select('*');
        $this->db->from('view_student_details');
        $this->db->where('status', 1);

        if (isset($data['branch_id']) && $data['branch_id'] != '') {
            $this->db->where('branch_id', $data['branch_id']);
        }

        if (isset($data['class_id']) && $data['class_id'] != '') {
            $this->db->where('class_id', $data['class_id']);
        }

        $section_id = strtolower(trim($data['section_id'] ?? ''));
        if (!empty($section_id) && $section_id != 'all') {
            $this->db->where('section_id', $section_id);
        }

        // log_message('debug', 'Section filter applied: ' . $section_id);


        $this->db->order_by('total_coins', 'DESC');
        return $this->db->get()->result_array();
    }

    public function getStudentRewards($student_id)
    {
        $result = $this->db
            ->select('
            sr.earned_coins, 
            sr.exam_type, 
            sr.reward_scope, 
            sr.session_code, 
            sr.remarks, 
            sr.rewarded_at as date,
            CASE 
                WHEN sr.exam_type = "online" THEN oe.title
                WHEN sr.exam_type = "offline" THEN e.name
                WHEN sr.exam_type = "live_exam" THEN le.title
                ELSE "N/A"
            END AS exam_name
        ', false)
            ->from('student_rewards sr')
            ->join('online_exam oe', 'oe.id = sr.exam_id AND sr.exam_type = "online"', 'left')
            ->join('exam e', 'e.id = sr.exam_id AND sr.exam_type = "offline"', 'left')
            ->join('online_exam le', 'le.id = sr.exam_id AND sr.exam_type = "live_exam"', 'left')
            ->where('sr.student_id', $student_id)
            ->order_by('sr.rewarded_at', 'desc')
            ->get()
            ->result_array();

        return $result;
    }

    public function getWallet($student_id)
    {
        $this->db->select('total_coins');
        $this->db->from('student_wallet');
        $this->db->where('student_id', $student_id);
        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            return $query->row_array(); // returns ['total_coins' => ...]
        } else {
            return ['total_coins' => 0]; // default if not found
        }
    }

    public function getAvailableRewards($student)
    {
        $this->db->select('
        rc.exam_id,
        rc.exam_type,
        rc.reward_basis,
        rc.reward_scope,
        rc.qualifying_value,
        rc.coin_reward,
        COALESCE(e.name, oe.title, le.title) AS exam_name');
        $this->db->from('reward_config rc');
        $this->db->join('exam e', 'e.id = rc.exam_id AND rc.exam_type = "offline"', 'left');
        $this->db->join('online_exam oe', 'oe.id = rc.exam_id AND rc.exam_type = "online"', 'left');
        $this->db->join('online_exam le', 'le.id = rc.exam_id AND rc.exam_type = "live_exam"', 'left');
        $this->db->where('rc.is_active', 1);
        $this->db->where('rc.class_id', $student['class_id']);
        $this->db->where('rc.section_id', $student['section_id']);
        $this->db->where('rc.branch_id', $student['branch_id']);
        // $this->db->order_by('rc.exam_type', 'ASC');
        // $this->db->order_by('rc.exam_id', 'DESC');
        $this->db->order_by('rc.created_at', 'DESC');
        $this->db->order_by('rc.qualifying_value', 'ASC');

        return $this->db->get()->result_array();
    }
}
