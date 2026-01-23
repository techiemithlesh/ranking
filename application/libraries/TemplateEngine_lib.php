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

    public function storeTemplate()
    {
        $type = $this->CI->input->post('type');
        $config = [
            'upload_path'   => 'uploads/template-manager/' . $type . '/',
            'allowed_types' => ($type == 'image' ? 'jpg|jpeg|png' : 'mp4'),
            'encrypt_name'  => true
        ];

        if (!is_dir($config['upload_path'])) {
            mkdir($config['upload_path'], 0755, true);
        }

        $this->CI->load->library('upload');
        $this->CI->upload->initialize($config);

        if ($this->CI->upload->do_upload('template_file')) { // MUST match view name
            $uploadData = $this->CI->upload->data();
            $arrayData = [
                'title'     => $this->CI->input->post('title'),
                'type'      => $type,
                'file_path' => $config['upload_path'] . $uploadData['file_name'],
                'created_by' => get_loggedin_user_id()
            ];
            return $this->CI->Template_model->saveTemplate($arrayData);
        } else {
            // Return the actual upload error so it shows in Swal
            return ['error' => $this->CI->upload->display_errors('', '')];
        }
    }
}
