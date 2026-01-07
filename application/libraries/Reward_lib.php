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
     * 21-10-2025 (LIVE EXAM INTEGRATION) 
     */

    /**
     * Unified reward processing method (supports percentage, rank, percentile).
     */
    

    public function processExamReward($student_id, $exam_id, $exam_type, $value, $basis = 'percentage', $session_code = null)
    {
        // Convert to performance array
        $performance = is_array($value)
            ? $value
            : [$basis => (float) $value];

        live_exam_log('debug', "[RewardLib] Checking reward | Student={$student_id} Exam={$exam_id} Type={$exam_type} Perf=" . json_encode($performance) . " Session={$session_code}");

        $rewardRule = $this->CI->reward_model->getApplicableReward(
            $student_id,
            $exam_id,
            $exam_type,
            $performance,
            $session_code
        );

        live_exam_log('debug', "APPLICABLE REWARD". json_encode($rewardRule));

        if (!$rewardRule) {
            live_exam_log('debug', "[RewardLib] No applicable reward rule.");
            return false;
        }

        $basis_used = $rewardRule['reward_basis'];
        $qualifying_value = (float) $rewardRule['qualifying_value'];
        $performance_value = (float) ($rewardRule['performance_value'] ?? ($performance[$basis_used] ?? 0));
        $scope = $rewardRule['reward_scope'] ?? 'exam';
        $coins = (int) $rewardRule['coin_reward'];

        // Determine readable message
        $remarkDetail = ($basis_used === 'rank')
            ? "Rank {$performance_value}"
            : (($basis_used === 'percentile')
                ? "{$performance_value} Percentile"
                : "{$performance_value}% Score");

        $remarks = "Rewarded for " . ucfirst($basis_used) . " performance ({$remarkDetail}) in " . ucfirst($exam_type) .
            ($session_code ? " (Session: {$session_code})" : '');

        // ✅ Duplicate check (IMPORTANT FIX)
        $checkSession = ($scope === 'session') ? $session_code : null;

        if ($this->CI->reward_model->isAlreadyRewarded($student_id, $exam_id, $exam_type, $checkSession)) {
            live_exam_log('debug', "[RewardLib] Skipped (duplicate) | Scope={$scope}");
            return false;
        }

        // Grant reward
        $result = $this->CI->reward_model->logRewardTransaction(
            $student_id,
            $exam_id,
            $exam_type,
            $coins,
            $remarks,
            'exam',
            $session_code,
            $scope
        );


        return $result;
    }



    public function shouldReward($student_id, $exam_id, $exam_type, $session_code = null)
    {
        return !$this->CI->reward_model->isAlreadyRewarded($student_id, $exam_id, $exam_type, $session_code);
    }



}
