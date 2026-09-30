<?php
defined('BASEPATH') or exit('No direct script access allowed');

// full paths are needed for the cron worker, which does not get the web server's PATH
$config['ffmpeg'] = [
    'windows' => 'C:\\ffmpeg\\bin\\ffmpeg.exe',
    'linux'   => '/usr/bin/ffmpeg'
];

$config['ffprobe'] = [
    'windows' => 'C:\\ffmpeg\\bin\\ffprobe.exe',
    'linux'   => '/usr/bin/ffprobe'
];
