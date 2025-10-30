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
    public function processExamReward_1($student_id, $exam_id, $exam_type, $value, $basis = 'percentage', $session_code = null)
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

    public function processExamReward($student_id, $exam_id, $exam_type, $value, $basis = 'percentage', $session_code = null)
    {
        // Convert to performance array
        $performance = is_array($value)
            ? $value
            : [$basis => (float) $value];

        log_message('debug', "[RewardLib] Checking reward | Student={$student_id} Exam={$exam_id} Type={$exam_type} Perf=" . json_encode($performance) . " Session={$session_code}");

        $rewardRule = $this->CI->reward_model->getApplicableReward(
            $student_id,
            $exam_id,
            $exam_type,
            $performance,
            $session_code
        );

        log_message('debug', "APPLICABLE REWARD". json_encode($rewardRule));

        if (!$rewardRule) {
            log_message('debug', "[RewardLib] No applicable reward rule.");
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
            log_message('debug', "[RewardLib] Skipped (duplicate) | Scope={$scope}");
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

        log_message(
            $result ? 'debug' : 'error',
            $result
            ? "[RewardLib] ✅ Reward granted | Student={$student_id} Coins={$coins} Basis={$basis_used} Scope={$scope}"
            : "[RewardLib] ❌ Failed to grant reward"
        );

        return $result;
    }



    public function shouldReward($student_id, $exam_id, $exam_type, $session_code = null)
    {
        return !$this->CI->reward_model->isAlreadyRewarded($student_id, $exam_id, $exam_type, $session_code);
    }



}
