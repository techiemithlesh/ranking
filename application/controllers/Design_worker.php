<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Background worker for Template_manager/download_design. Command line only.
 *
 * Linux cron (every minute):
 *   * * * * * php /path/to/futurecampus/index.php design_worker run >/dev/null 2>&1
 * Windows Task Scheduler (every 1 minute):
 *   C:\MyWebStack\php\php-8.2\php.exe C:\MyWebStack\www\futurecampus\index.php design_worker run
 */
class Design_worker extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        if (!is_cli()) {
            show_404();
        }
    }

    public function run()
    {
        @set_time_limit(0);
        $this->load->library('design_queue_lib');
        $this->design_queue_lib->run();
    }
}
