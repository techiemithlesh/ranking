<?php defined('BASEPATH') or exit('No direct script access allowed');

if (!function_exists('live_exam_log')) {
    function live_exam_log($level, $message = '')
    {
        // Allow calling live_exam_log('message') directly
        if ($message == '') {
            $message = $level;
            $level = 'INFO';
        }

        $log_path = APPPATH . 'logs/live-exam-' . date('Y-m-d') . '.php';

        if (!file_exists($log_path)) {
            file_put_contents($log_path, "<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>\n\n");
        }

        $entry = "[" . date('Y-m-d H:i:s') . "] [$level] $message\n";
        file_put_contents($log_path, $entry, FILE_APPEND);
    }
}
