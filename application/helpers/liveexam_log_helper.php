<?php defined('BASEPATH') or exit('No direct script access allowed');

if (!function_exists('live_exam_log')) {
    function live_exam_log($message)
    {
        $CI =& get_instance();

        $log_path = APPPATH . 'logs/live-exam-' . date('Y-m-d') . '.php';

        // If file doesn't exist add PHP exit to secure it
        if (!file_exists($log_path)) {
            file_put_contents($log_path, "<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>\n\n");
        }

        $entry = "[" . date('Y-m-d H:i:s') . "] " . $message . "\n";
        file_put_contents($log_path, $entry, FILE_APPEND);
    }
}
