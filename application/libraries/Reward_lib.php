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


    public function processExamReward($student_id,$exam_id,$exam_type,$value,$basis = 'percentage',$session_code = null) {
        // Convert to performance array
        $performance = is_array($value)
            ? $value
            : [$basis => (float)$value];

        // 🔑 Decide reward scope
        $reward_scope = $session_code ? 'session' : 'exam';

        live_exam_log(
            'debug',
            "[RewardLib] Checking reward | Student={$student_id} Exam={$exam_id} Type={$exam_type} Scope={$reward_scope} Perf=" .
                json_encode($performance));

        // ✅ CORRECT CALL
        $rewardRule = $this->CI->reward_model->getApplicableReward(
            $student_id,
            $exam_id,
            $exam_type,
            $performance,
            $reward_scope
        );

        if (!$rewardRule) {
            live_exam_log('debug', "[RewardLib] No applicable reward rule.");
            return false;
        }

        $basis_used        = $rewardRule['reward_basis'];
        $performance_value = (float)($rewardRule['performance_value'] ?? ($performance[$basis_used] ?? 0));
        $coins             = (int)$rewardRule['coin_reward'];

        $remarks = sprintf(
            "Rewarded for %s (%s) in %s%s",
            ucfirst($basis_used),
            $basis_used === 'rank'
                ? "Rank {$performance_value}"
                : ($basis_used === 'percentile'
                    ? "{$performance_value} Percentile"
                    : "{$performance_value}% Score"),
            ucfirst($exam_type),
            $session_code ? " [Session: {$session_code}]" : ''
        );

        // ✅ CORRECT DUPLICATE CHECK
        if ($this->CI->reward_model->isAlreadyRewarded(
            $student_id,
            $exam_id,
            $exam_type,
            $reward_scope,
            $session_code
        )) {
            live_exam_log('debug', "[RewardLib] Skipped duplicate | Scope={$reward_scope}");
            return false;
        }

        // Grant reward
        return $this->CI->reward_model->logRewardTransaction(
            $student_id,
            $exam_id,
            $exam_type,
            $coins,
            $remarks,
            'exam',
            $session_code,
            $reward_scope
        );
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
