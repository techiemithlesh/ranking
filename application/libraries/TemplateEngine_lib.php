<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class TemplateEngine_lib {
    protected $CI;

    public function __construct()
    {
        $this->CI = &get_instance();
        $this->CI->load->model('template_model');
        $this->CI->load->model('templateOverlay_model');

    }
}