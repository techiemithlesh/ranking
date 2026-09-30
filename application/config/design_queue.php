<?php
defined('BASEPATH') or exit('No direct script access allowed');

/*
| Background queue for Template_manager/download_design.
| Worker: php index.php design_worker run   (cron, every minute)
*/

// where generated files and ZIPs are kept (application/cache is not web-accessible)
$config['design_queue_storage'] = APPPATH . 'cache/design_jobs/';

// finished ZIPs are deleted after this many days
$config['design_queue_retention_days'] = 3;

// a design is retried this many times before it is marked failed
$config['design_queue_max_attempts'] = 3;

// an item stuck in "processing" this long (worker crashed) is put back in the queue
$config['design_queue_stale_minutes'] = 30;

// how long one cron run keeps claiming new work (cron fires every 60s)
$config['design_queue_time_budget'] = 55;

// max workers running at once (each cron run takes a free slot or exits)
$config['design_queue_max_workers'] = 3;

// image designs claimed per round (videos are always one at a time)
$config['design_queue_image_batch'] = 10;

// active (unfinished) jobs one user may have at a time
$config['design_queue_max_active_jobs'] = 3;
