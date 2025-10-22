<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Reward_lib
{
    protected $CI;

    public function __construct()
    {
        $this->CI = &get_instance();
        $this->CI->load->model('reward_model');
        $this->CI->load->model('student_model');
    }

    /**
     * Process and reward coins to a student for an exam.
     */
    //  public function processExamReward($student_id, $exam_id, $exam_type, $percentage)
    // {
    //     $rewardRule = $this->CI->reward_model->getApplicableReward($student_id, $exam_id, $exam_type, $percentage);

    //     if (!$rewardRule || $percentage < $rewardRule['min_percentage']) {
    //         log_message('debug', "No applicable reward for student_id={$student_id}, exam_id={$exam_id}, percentage={$percentage}");
    //         return false;
    //     }

    //     $coins = (int)$rewardRule['coin_reward'];
    //     $remarks = "Rewarded for scoring $percentage% in $exam_type exam";

    //     return $this->CI->reward_model->logRewardTransaction($student_id, $exam_id, $exam_type, $coins, $remarks);
    // }




    /**
     * 21-10-2025 (LIVE EXAM INTEGRATION) 
     */

    /**
     * Unified reward processing method (supports percentage, rank, percentile).
     */
    public function processExamReward($student_id, $exam_id, $exam_type, $value, $basis = 'percentage', $session_code = null)
    {
        $rewardRule = $this->CI->reward_model->getApplicableReward($student_id, $exam_id, $exam_type, $value, $session_code);

        if (!$rewardRule) {
            log_message('debug', "[Reward] No rule found for student={$student_id}, exam={$exam_id}, value={$value}");
            return false;
        }

        // Validate rule based on basis type
        switch ($rewardRule['reward_basis']) {
            case 'percentage':
            case 'percentile':
                if ($value < $rewardRule['qualifying_value'])
                    return false;
                break;

            case 'rank':
                // For rank-based rewards, value is rank position (lower is better)
                if ($value > $rewardRule['qualifying_value'])
                    return false;
                break;
        }

        $coins = (int) $rewardRule['coin_reward'];
        $remarks = "Rewarded for {$rewardRule['reward_basis']} achievement ({$value}) in {$exam_type}";
        $scope = $rewardRule['reward_scope'] ?? 'exam';

        return $this->CI->reward_model->logRewardTransaction(
            $student_id,
            $exam_id,
            $exam_type,
            $coins,
            $remarks,
            'exam',
            $session_code,
            $scope
        );
    }


}
