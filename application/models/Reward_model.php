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
            'min_percentage' => $data['min_percentage'],
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


    // public function getApplicableReward($student_id, $exam_id, $exam_type)
    // {
    //     $this->db->select('rc.*');
    //     $this->db->from('reward_config as rc');
    //     $this->db->join('enroll as e', 'e.class_id = rc.class_id AND e.section_id = rc.section_id AND e.student_id = ' . $this->db->escape($student_id));
    //     $this->db->where('rc.exam_id', $exam_id);
    //     $this->db->where('rc.exam_type', $exam_type);
    //     $this->db->where('rc.is_active', 1);

    //     return $this->db->get()->row_array();
    // }


    public function getApplicableReward($student_id, $exam_id, $exam_type, $percentage)
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
        $this->db->where('rc.min_percentage <=', $percentage);
        $this->db->order_by('rc.min_percentage', 'DESC');

        $row = $this->db->get()->row_array();

        log_message('debug', "Applicable reward config: " . json_encode($row));
        return $row;
    }

    public function isRewarded($exam_id, $student_id)
    {
        $this->db->where('exam_id', $exam_id);
        $this->db->where('student_id', $student_id);
        return $this->db->get('student_rewards')->num_rows() > 0;
    }


    public function logRewardTransaction($student_id, $exam_id, $examType, $coins, $remarks)
    {
        // Check if already rewarded
        $already = $this->db->get_where('student_rewards', [
            'student_id' => $student_id,
            'exam_id' => $exam_id
        ])->row_array();

        if ($already) {
            responseMsg('fail', 'Reward already generated', $already);
            return false;
        }

        try {
            $this->db->trans_start(); // ✅ fixed method name

            // 1. Insert into student_rewards
            $this->db->insert('student_rewards', [
                'student_id' => $student_id,
                'exam_id' => $exam_id,
                'earned_coins' => $coins,
                'exam_type' => $examType,
                'remarks' => $remarks,
            ]);

            // 2. Update or Insert student_wallet
            $wallet = $this->db->get_where('student_wallet', ['student_id' => $student_id])->row_array();
            if ($wallet) {
                $this->db->set('total_coins', 'total_coins + ' . $coins, false)
                    ->set('last_updated', date('Y-m-d H:i:s'))
                    ->where('student_id', $student_id)
                    ->update('student_wallet');
            } else {
                $this->db->insert('student_wallet', [
                    'student_id' => $student_id,
                    'total_coins' => $coins,
                ]);
            }

            // 3. Log in reward_transactions_log
            $this->db->insert('reward_transactions_log', [
                'student_id' => $student_id,
                'type' => 'earn',
                'coins' => $coins,
                'reference_id' => $exam_id,
                'remarks' => $remarks,
            ]);

            $this->db->trans_complete(); // ✅ ends the transaction

            // Check for success
            if ($this->db->trans_status() === false) {
                return false;
            }

            return true;

        } catch (Exception $e) {
            log_message('error', 'Reward transaction failed: ' . $e->getMessage());
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

        if (isset($data['section_id']) && $data['section_id'] !== 'all' && $data['section_id'] !== '') {
            $this->db->where('section_id', $data['section_id']);
        }

        $this->db->order_by('total_coins', 'DESC');
        return $this->db->get()->result_array();
    }


    // public function getStudentRewards($student_id)
    // {
    //     $result = $this->db
    //         ->select('sr.earned_coins, rc.exam_type, sr.remarks, sr.rewarded_at as date', false)
    //         ->select('COALESCE(oe.title, e.name) as exam_name', false)
    //         ->from('student_rewards sr')
    //         ->join('view_student_details vsd', 'vsd.id = sr.student_id', 'left')
    //         ->join('reward_config rc', 'rc.exam_id = sr.exam_id 
    //                                  AND rc.branch_id = vsd.branch_id 
    //                                  AND rc.class_id = vsd.class_id 
    //                                  AND rc.section_id = vsd.section_id', 'left')
    //         ->join('online_exam oe', 'oe.id = sr.exam_id AND rc.exam_type = "online"', 'left')
    //         ->join('exam e', 'e.id = sr.exam_id AND rc.exam_type = "offline"', 'left')
    //         ->where('sr.student_id', $student_id)
    //         ->order_by('sr.rewarded_at', 'desc')
    //         ->get()
    //         ->result_array();

    //     return $result;
    // }


    public function getStudentRewards($student_id)
    {
        $result = $this->db
            ->select('sr.earned_coins, sr.exam_type, sr.remarks, sr.rewarded_at as date', false)
            ->select('COALESCE(oe.title, e.name) as exam_name', false)
            ->from('student_rewards sr')
            ->join('online_exam oe', 'oe.id = sr.exam_id AND sr.exam_type = "online"', 'left')
            ->join('exam e', 'e.id = sr.exam_id AND sr.exam_type = "offline"', 'left')
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
        $this->db->select('rc.exam_id, rc.exam_type, rc.min_percentage, rc.coin_reward, 
                       COALESCE(e.name, oe.title) as exam_name');
        $this->db->from('reward_config rc');
        $this->db->join('exam e', 'e.id = rc.exam_id AND rc.exam_type = "offline"', 'left');
        $this->db->join('online_exam oe', 'oe.id = rc.exam_id AND rc.exam_type = "online"', 'left');
        $this->db->where('rc.is_active', 1);

        // Filter based on student details
        $this->db->where('rc.class_id', $student['class_id']);
        $this->db->where('rc.section_id', $student['section_id']);
        $this->db->where('rc.branch_id', $student['branch_id']);

        return $this->db->get()->result_array();
    }



}

