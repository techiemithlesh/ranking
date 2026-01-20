<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class TemplateEngine_lib
{
    protected $CI;

    public function __construct()
    {
        $this->CI = &get_instance();
        $this->CI->load->model('template_model');
        $this->CI->load->model('templateOverlay_model');
    }

    // Get template + overlays at once
    public function get_template_data($template_id)
    {
        $template = $this->CI->Template_model->get($template_id);
        $overlays = $this->CI->TemplateOverlay_model->get_by_template($template_id);
        return ['template' => $template, 'overlays' => $overlays];
    }
}
