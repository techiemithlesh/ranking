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

    public function isRewarded($exam_id, $student_id)
    {
        $this->db->where('exam_id', $exam_id);
        $this->db->where('student_id', $student_id);
        return $this->db->get('student_rewards')->num_rows() > 0;
    }


    /**
     * 21-10-2025 (LIVE EXAM INTEGRATION) 
     */


    /**
     * RANK PRIORITY INDUSTRY STANDARD (RANK->PERCENTILE->PERCENTAGE)
     * @param mixed $student_id
     * @param mixed $exam_id
     * @param mixed $exam_type
     * @param mixed $performance_values
     * @param mixed $session_code
     */


    public function getApplicableReward_($student_id, $exam_id, $exam_type, $performance_values = [], $session_code = null)
    {
        $this->db->select('rc.*');
        $this->db->from('reward_config as rc');
        $this->db->join(
            'enroll as e',
            'e.class_id = rc.class_id 
            AND e.section_id = rc.section_id 
            AND e.student_id = ' . $this->db->escape($student_id) . '
            AND e.session_id = ' . $this->db->escape(get_session_id()) . ''
        );
        $this->db->where('rc.exam_id', $exam_id);
        $this->db->where('rc.exam_type', $exam_type);
        $this->db->where('rc.is_active', 1);

        $configs = $this->db->get()->result_array();

        live_exam_log('debug', "The Reward Configs Fetched: " . json_encode($configs));
        live_exam_log('debug', "The Reward Configs Fetched Query: " . $this->db->last_query());

        if (empty($configs)) {
            live_exam_log('debug', "[Reward] No reward configs found for student={$student_id}");
            return null;
        }

        $priority = [
            'rank' => 1,
            'percentile' => 2,
            'percentage' => 3
        ];

        $bestRule = null;
        $bestPriority = PHP_INT_MAX;

        foreach ($configs as $config) {
            $basis = strtolower($config['reward_basis']);
            $qual = (float) $config['qualifying_value'];

            // REFACTORED: Replaced first 'match' with a 'switch' statement for $value
            $value = 0.0;
            switch ($basis) {
                case 'rank':
                    $value = (float) ($performance_values['rank'] ?? 0);
                    break;
                case 'percentile':
                    $value = (float) ($performance_values['percentile'] ?? 0);
                    break;
                default: // Catches 'percentage' and any other unknown basis
                    $value = (float) ($performance_values['percentage'] ?? 0);
                    break;
            }

            // REFACTORED: Replaced second 'match' with a 'switch' statement for $eligible
            $eligible = false;
            switch ($basis) {
                case 'rank':
                    $eligible = ($value > 0 && $value <= $qual);
                    break;
                case 'percentile':
                    $eligible = ($value >= $qual);
                    break;
                case 'percentage':
                    $eligible = ($value >= $qual);
                    break;
                    // No default needed as all bases are handled
            }

            live_exam_log('debug', "[RewardCheck] {$basis} -> value={$value}, qual={$qual}, eligible=" . ($eligible ? "YES" : "NO"));

            if ($eligible && $priority[$basis] < $bestPriority) {
                $bestPriority = $priority[$basis];
                $config['performance_value'] = $value;
                $bestRule = $config;
            }
        }

        if ($bestRule) {
            live_exam_log('debug', "[Reward] 🎯 Best Reward Selected: " . json_encode($bestRule));
            return $bestRule;
        }

        live_exam_log('debug', "[Reward] ❌ No eligible reward after evaluation");
        return null;
    }

    /**
     * Finds the most appropriate reward based on Rank > Percentile > Percentage.
     */
    public function getApplicableReward($student_id,$exam_id,$exam_type,array $performance,string $reward_scope = 'exam') {
        // 1️⃣ Fetch configs ONLY for this scope
        $this->db->select('rc.*');
        $this->db->from('reward_config rc');
        $this->db->join(
            'enroll e',
            'e.class_id = rc.class_id
         AND e.section_id = rc.section_id
         AND e.student_id = ' . $this->db->escape($student_id) . '
         AND e.session_id = ' . $this->db->escape(get_session_id())
        );
        $this->db->where([
            'rc.exam_id'     => $exam_id,
            'rc.exam_type'   => $exam_type,
            'rc.reward_scope' => $reward_scope,
            'rc.is_active'   => 1
        ]);

        $configs = $this->db->get()->result_array();

        if (empty($configs)) {
            return null;
        }

        // 2️⃣ Priority: Rank > Percentile > Percentage
        $priority = [
            'rank'       => 1,
            'percentile' => 2,
            'percentage' => 3
        ];

        $bestRule = null;
        $bestPriority = PHP_INT_MAX;

        foreach ($configs as $config) {
            $basis = strtolower($config['reward_basis']);
            $qual  = (float)$config['qualifying_value'];

            $value = $performance[$basis] ?? 0;
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
                ($currentPriority === $bestPriority && $config['coin_reward'] > ($bestRule['coin_reward'] ?? 0))
            ) {
                $bestPriority = $currentPriority;
                $config['performance_value'] = $value;
                $bestRule = $config;
            }
        }

        return $bestRule;
    }



    public function isAlreadyRewarded($student_id, $exam_id, $exam_type, $reward_scope, $session_code = null)
    {
        $this->db->where([
            'student_id' => $student_id,
            'exam_id' => $exam_id,
            'exam_type' => $exam_type,
            'reward_scope' => $reward_scope
        ]);

        // If reward is session-based, check session_code too
        if ($reward_scope === 'session' && $session_code) {
            $this->db->where('session_code', $session_code);
        }

        return $this->db->count_all_results('student_rewards') > 0;
    }


    /**
     * Log and apply a reward transaction safely (atomic).
     */
    public function logRewardTransaction($student_id, $exam_id, $exam_type, $coins, $remarks, $reference_type = 'exam', $session_code = null, $reward_scope = 'exam')
    {
        // Duplicate prevention based on reward scope
        $check = [
            'student_id' => $student_id,
            'exam_id' => $exam_id,
            'exam_type' => $exam_type
        ];

        if ($reward_scope === 'session' && $session_code) {
            $check['session_code'] = $session_code;
        }

        $already = $this->db->get_where('student_rewards', $check)->row_array();
        if ($already) {
            live_exam_log('debug', "[Reward] Already rewarded for student {$student_id}, exam {$exam_id}, scope {$reward_scope}");
            return false;
        }

        try {
            $this->db->trans_start();

            // Insert reward entry
            $this->db->insert('student_rewards', [
                'student_id' => $student_id,
                'exam_id' => $exam_id,
                'exam_type' => $exam_type,
                'earned_coins' => $coins,
                'remarks' => $remarks,
                'reward_scope' => $reward_scope,
                'session_code' => !empty($session_code) ? $session_code : null,
                'rewarded_at' => date('Y-m-d H:i:s'),
            ]);

            // Update wallet
            $wallet = $this->db->get_where('student_wallet', ['student_id' => $student_id])->row_array();
            if ($wallet) {
                $this->db->set('total_coins', 'total_coins + ' . (int) $coins, false)
                    ->set('last_updated', date('Y-m-d H:i:s'))
                    ->where('student_id', $student_id)
                    ->update('student_wallet');
            } else {
                $this->db->insert('student_wallet', [
                    'student_id' => $student_id,
                    'total_coins' => $coins,
                    'last_updated' => date('Y-m-d H:i:s')
                ]);
            }

            // Log transaction
            $this->db->insert('reward_transactions_log', [
                'student_id' => $student_id,
                'type' => 'earn',
                'coins' => $coins,
                'reference_id' => $exam_id,
                'reference_type' => $reference_type,
                'exam_type' => $exam_type,
                'session_code' => !empty($session_code) ? $session_code : null,
                'remarks' => $remarks,
            ]);

            $this->db->trans_complete();
            return $this->db->trans_status();
        } catch (Exception $e) {
            live_exam_log('error', '[Reward] Transaction failed: ' . $e->getMessage());
            return false;
        }
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
        $this->db->order_by('rc.exam_type', 'ASC');
        $this->db->order_by('rc.exam_id', 'ASC');
        $this->db->order_by('rc.qualifying_value', 'ASC');

        return $this->db->get()->result_array();
    }
}
