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
     * Unified reward processor
     * Supports: percentage, percentile, rank
     * Scope comes ONLY from reward_config
     */
    public function processExamReward_(
        $student_id,
        $exam_id,
        $exam_type,
        array $performance,
        $session_code = null
    ) {
        live_exam_log(
            'debug',
            "[RewardLib] Checking reward | Student={$student_id} Exam={$exam_id} Type={$exam_type} Perf=" .
                json_encode($performance)
        );

        // 🔑 Fetch applicable reward (scope decided by config)
        $rewardRule = $this->CI->reward_model->getApplicableReward(
            $student_id,
            $exam_id,
            $exam_type,
            $performance
        );

        if (!$rewardRule) {
            live_exam_log('debug', "[RewardLib] No applicable reward rule.");
            return false;
        }

        $scope             = $rewardRule['reward_scope']; // exam | session
        $basis_used        = $rewardRule['reward_basis'];
        $performance_value = (float) $rewardRule['performance_value'];
        $coins             = (int) $rewardRule['coin_reward'];

        // 🔁 Duplicate prevention (scope-aware)
        if ($this->CI->reward_model->isAlreadyRewarded(
            $student_id,
            $exam_id,
            $exam_type,
            $scope,
            $scope === 'session' ? $session_code : null
        )) {
            live_exam_log('debug', "[RewardLib] Skipped duplicate | Scope={$scope}");
            return false;
        }

        // 📝 Remarks
        $remarkValue =
            $basis_used === 'rank'
            ? "Rank {$performance_value}"
            : ($basis_used === 'percentile'
                ? "{$performance_value} Percentile"
                : "{$performance_value}% Score");

        $remarks = "Rewarded for {$basis_used} ({$remarkValue}) in {$exam_type}" .
            ($scope === 'session' && $session_code ? " [Session: {$session_code}]" : '');

        // 💰 Grant reward
        return $this->CI->reward_model->logRewardTransaction(
            $student_id,
            $exam_id,
            $exam_type,
            $coins,
            $remarks,
            'exam',
            $scope === 'session' ? $session_code : null,
            $scope
        );
    }

    public function processExamReward(
        $student_id,
        $exam_id,
        $exam_type,
        array $performance,
        $session_code = null) {
        live_exam_log(
            'debug',
            "[RewardLib] Checking reward | Student={$student_id} Exam={$exam_id} Type={$exam_type} Perf=" .
                json_encode($performance)
        );

        // 🔑 Fetch applicable reward (scope decided by config)
        $rewardRule = $this->CI->reward_model->getApplicableReward(
            $student_id,
            $exam_id,
            $exam_type,
            $performance
        );

        if (!$rewardRule) {
            live_exam_log('debug', "[RewardLib] No applicable reward rule.");
            return false;
        }

        $scope             = $rewardRule['reward_scope']; // exam | session
        $basis_used        = $rewardRule['reward_basis'];
        $performance_value = (float) $rewardRule['performance_value'];
        $coins             = (int) $rewardRule['coin_reward'];

        // 🔁 Duplicate prevention (scope-aware)
        $locked = $this->CI->reward_model->acquireRewardLock(
            $student_id,
            $exam_id,
            $exam_type,
            $scope,
            $scope === 'session' ? $session_code : null
        );

        if (!$locked) {
            live_exam_log('debug', "[RewardLib] Blocked by reward lock | Scope={$scope}");
            return false;
        }


        // 📝 Remarks
        $remarkValue =
            $basis_used === 'rank'
            ? "Rank {$performance_value}"
            : ($basis_used === 'percentile'
                ? "{$performance_value} Percentile"
                : "{$performance_value}% Score");

        $remarks = "Rewarded for {$basis_used} ({$remarkValue}) in {$exam_type}" .
            ($scope === 'session' && $session_code ? " [Session: {$session_code}]" : '');

        // 💰 Grant reward
        $this->CI->reward_model->logRewardTransaction(
            $student_id,
            $exam_id,
            $exam_type,
            $coins,
            $remarks,
            'exam',
            $scope === 'session' ? $session_code : null,
            $scope
        );
        
        live_exam_log(
            'debug',
            "[RewardLib] Reward granted & locked | Coins={$coins} Scope={$scope}"
        );

        return true;
    }


    public function shouldReward($student_id, $exam_id, $exam_type, $reward_scope, $session_code = null)
    {
        return !$this->CI->reward_model->isAlreadyRewarded(
            $student_id,
            $exam_id,
            $exam_type,
            $reward_scope,
            $session_code
        );
    }
}
