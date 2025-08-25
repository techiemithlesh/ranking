<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

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
     public function processExamReward($student_id, $exam_id, $exam_type, $percentage)
    {
        $rewardRule = $this->CI->reward_model->getApplicableReward($student_id, $exam_id, $exam_type, $percentage);

        if (!$rewardRule || $percentage < $rewardRule['min_percentage']) {
            log_message('debug', "No applicable reward for student_id={$student_id}, exam_id={$exam_id}, percentage={$percentage}");
            return false;
        }

        $coins = (int)$rewardRule['coin_reward'];
        $remarks = "Rewarded for scoring $percentage% in $exam_type exam";

        return $this->CI->reward_model->logRewardTransaction($student_id, $exam_id, $exam_type, $coins, $remarks);
    }
}
