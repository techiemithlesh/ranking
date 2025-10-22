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
        reward_config.*,
        branch.name as branch_name,
        class.name as class_name,
        section.name as section_name,
        CASE 
            WHEN reward_config.exam_type = 'online' THEN online_exam.title 
            ELSE exam.name 
        END as exam_name");

        $this->db->from('reward_config');
        $this->db->join('branch', 'branch.id = reward_config.branch_id', 'left');
        $this->db->join('class', 'class.id = reward_config.class_id', 'left');
        $this->db->join('section', 'section.id = reward_config.section_id', 'left');
        $this->db->join('exam', 'exam.id = reward_config.exam_id AND reward_config.exam_type = "offline"', 'left');
        $this->db->join('online_exam', 'online_exam.id = reward_config.exam_id AND reward_config.exam_type = "online"', 'left');

        if (!empty($filters['branch_id'])) {
            $this->db->where('reward_config.branch_id', $filters['branch_id']);
        }
        if (!empty($filters['class_id'])) {
            $this->db->where('reward_config.class_id', $filters['class_id']);
        }
        if (!empty($filters['section_id'])) {
            $this->db->where('reward_config.section_id', $filters['section_id']);
        }
        if (!empty($filters['exam_type'])) {
            $this->db->where('reward_config.exam_type', $filters['exam_type']);
        }
        if (!empty($filters['exam_id'])) {
            $this->db->where('reward_config.exam_id', $filters['exam_id']);
        }

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


    // public function getApplicableReward($student_id, $exam_id, $exam_type, $percentage)
    // {
    //     $this->db->select('rc.*');
    //     $this->db->from('reward_config as rc');
    //     $this->db->join(
    //         'enroll as e',
    //         'e.class_id = rc.class_id 
    //      AND e.section_id = rc.section_id 
    //      AND e.student_id = ' . $this->db->escape($student_id)
    //     );
    //     $this->db->where('rc.exam_id', $exam_id);
    //     $this->db->where('rc.exam_type', $exam_type);
    //     $this->db->where('rc.is_active', 1);
    //     $this->db->where('rc.min_percentage <=', $percentage);
    //     $this->db->order_by('rc.min_percentage', 'DESC');

    //     $row = $this->db->get()->row_array();

    //     log_message('debug', "Applicable reward config: " . json_encode($row));
    //     return $row;
    // }



    public function isRewarded($exam_id, $student_id)
    {
        $this->db->where('exam_id', $exam_id);
        $this->db->where('student_id', $student_id);
        return $this->db->get('student_rewards')->num_rows() > 0;
    }


    // public function logRewardTransaction($student_id, $exam_id, $examType, $coins, $remarks)
    // {
    //     // Check if already rewarded
    //     $already = $this->db->get_where('student_rewards', [
    //         'student_id' => $student_id,
    //         'exam_id' => $exam_id
    //     ])->row_array();

    //     if ($already) {
    //         responseMsg('fail', 'Reward already generated', $already);
    //         return false;
    //     }

    //     try {
    //         $this->db->trans_start(); // ✅ fixed method name

    //         // 1. Insert into student_rewards
    //         $this->db->insert('student_rewards', [
    //             'student_id' => $student_id,
    //             'exam_id' => $exam_id,
    //             'earned_coins' => $coins,
    //             'exam_type' => $examType,
    //             'remarks' => $remarks,
    //         ]);

    //         // 2. Update or Insert student_wallet
    //         $wallet = $this->db->get_where('student_wallet', ['student_id' => $student_id])->row_array();
    //         if ($wallet) {
    //             $this->db->set('total_coins', 'total_coins + ' . $coins, false)
    //                 ->set('last_updated', date('Y-m-d H:i:s'))
    //                 ->where('student_id', $student_id)
    //                 ->update('student_wallet');
    //         } else {
    //             $this->db->insert('student_wallet', [
    //                 'student_id' => $student_id,
    //                 'total_coins' => $coins,
    //             ]);
    //         }

    //         // 3. Log in reward_transactions_log
    //         $this->db->insert('reward_transactions_log', [
    //             'student_id' => $student_id,
    //             'type' => 'earn',
    //             'coins' => $coins,
    //             'reference_id' => $exam_id,
    //             'remarks' => $remarks,
    //         ]);

    //         $this->db->trans_complete(); // ✅ ends the transaction

    //         // Check for success
    //         if ($this->db->trans_status() === false) {
    //             return false;
    //         }

    //         return true;

    //     } catch (Exception $e) {
    //         log_message('error', 'Reward transaction failed: ' . $e->getMessage());
    //         return false;
    //     }
    // }

    /**
     * 21-10-2025 (LIVE EXAM INTEGRATION) 
     */


    /**
     * Get the applicable reward rule for a given student and exam context.
     */
    public function getApplicableReward($student_id, $exam_id, $exam_type, $performance_value, $session_code = null)
    {
        $this->db->select('rc.*');
        $this->db->from('reward_config as rc');
        $this->db->join(
            'enroll as e',
            'e.class_id = rc.class_id 
        AND e.section_id = rc.section_id 
        AND e.student_id = ' . $this->db->escape($student_id)
        );
        $this->db->where('rc.exam_id', $exam_id);
        $this->db->where('rc.exam_type', $exam_type);
        $this->db->where('rc.is_active', 1);
        $this->db->order_by('rc.qualifying_value', 'DESC');

        $configs = $this->db->get()->result_array();
        if (empty($configs))
            return null;

        foreach ($configs as $config) {
            $basis = $config['reward_basis'] ?? 'percentage';
            $qual = (float) $config['qualifying_value'];

            $isEligible = false;

            switch ($basis) {
                case 'rank':
                    // For rank, smaller number = better
                    $isEligible = $performance_value > 0 && $performance_value <= $qual;
                    break;

                case 'percentage':
                case 'percentile':
                default:
                    // For percentage and percentile, higher = better
                    $isEligible = $performance_value >= $qual;
                    break;
            }

            if ($isEligible) {
                log_message('debug', "[Reward] Eligible reward found for student {$student_id} on {$basis}={$performance_value}, rule=" . json_encode($config));
                return $config;
            }
        }

        log_message('debug', "[Reward] No matching reward found for student {$student_id}, exam {$exam_id}, basis={$basis}, value={$performance_value}");
        return null;
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
            log_message('debug', "[Reward] Already rewarded for student {$student_id}, exam {$exam_id}, scope {$reward_scope}");
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
            log_message('error', '[Reward] Transaction failed: ' . $e->getMessage());
            return false;
        }
    }

    public function isAlreadyRewarded($student_id, $exam_id, $exam_type, $session_code = null)
    {
        $this->db->where([
            'student_id' => $student_id,
            'exam_id' => $exam_id,
            'exam_type' => $exam_type,
        ]);

        // If reward is session-based, check session_code too
        if (!empty($session_code)) {
            $this->db->where('session_code', $session_code);
        }

        return $this->db->count_all_results('student_rewards') > 0;
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

        if (isset($data['section_id']) && $data['section_id'] !== 'all' && $data['section_id'] !== '') {
            $this->db->where('section_id', $data['section_id']);
        }

        $this->db->order_by('total_coins', 'DESC');
        return $this->db->get()->result_array();
    }

    // public function getStudentRewards($student_id)
    // {
    //     $result = $this->db
    //         ->select('sr.earned_coins, sr.exam_type, sr.remarks, sr.rewarded_at as date', false)
    //         ->select('COALESCE(oe.title, e.name) as exam_name', false)
    //         ->from('student_rewards sr')
    //         ->join('online_exam oe', 'oe.id = sr.exam_id AND sr.exam_type = "online"', 'left')
    //         ->join('exam e', 'e.id = sr.exam_id AND sr.exam_type = "offline"', 'left')
    //         ->where('sr.student_id', $student_id)
    //         ->order_by('sr.rewarded_at', 'desc')
    //         ->get()
    //         ->result_array();

    //     return $result;
    // }

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
            ->join('online_exam le', 'le.id = sr.exam_id AND sr.exam_type = "live_exam"', 'left') // live exams share same table
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

    // public function getAvailableRewards($student)
    // {
    //     $this->db->select('rc.exam_id, rc.exam_type, rc.qualifying_value, rc.coin_reward, 
    //                    COALESCE(e.name, oe.title) as exam_name');
    //     $this->db->from('reward_config rc');
    //     $this->db->join('exam e', 'e.id = rc.exam_id AND rc.exam_type = "offline"', 'left');
    //     $this->db->join('online_exam oe', 'oe.id = rc.exam_id AND rc.exam_type = "online"', 'left');
    //     $this->db->where('rc.is_active', 1);

    //     // Filter based on student details
    //     $this->db->where('rc.class_id', $student['class_id']);
    //     $this->db->where('rc.section_id', $student['section_id']);
    //     $this->db->where('rc.branch_id', $student['branch_id']);

    //     return $this->db->get()->result_array();
    // }

    public function getAvailableRewards($student)
    {
        $this->db->select('
        rc.exam_id,
        rc.exam_type,
        rc.reward_basis,
        rc.reward_scope,
        rc.qualifying_value,
        rc.coin_reward,
        COALESCE(e.name, oe.title, le.title) AS exam_name
    ');
        $this->db->from('reward_config rc');
        $this->db->join('exam e', 'e.id = rc.exam_id AND rc.exam_type = "offline"', 'left');
        $this->db->join('online_exam oe', 'oe.id = rc.exam_id AND rc.exam_type = "online"', 'left');
        $this->db->join('online_exam le', 'le.id = rc.exam_id AND rc.exam_type = "live_exam"', 'left');
        $this->db->where('rc.is_active', 1);
        $this->db->where('rc.class_id', $student['class_id']);
        $this->db->where('rc.section_id', $student['section_id']);
        $this->db->where('rc.branch_id', $student['branch_id']);
        $this->db->order_by('rc.exam_type', 'ASC');
        $this->db->order_by('rc.qualifying_value', 'ASC');

        return $this->db->get()->result_array();
    }



}

