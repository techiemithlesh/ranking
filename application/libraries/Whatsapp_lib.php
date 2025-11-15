<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Whatsapp_lib
{
    private $CI;
    private $token;

    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->load->model('whatsapp_model');
        $this->CI->load->library('bulkwa_lib');

        $this->token = get_global_setting('wp_access_token');
    }

    /* ---------------------------------------------------------
     * SEND TEXT MESSAGE
     * --------------------------------------------------------- */
    public function send_text($number, $message, $module = 'general', $branch_id = 0)
    {
        $config = $this->CI->whatsapp_model->get_active_config($branch_id);
        if (!$config) {
            return ['status' => 0, 'error' => 'No active instance'];
        }

        $payload = [
            'number' => $this->normalize($number, $config['country_code']),
            'type' => 'text',
            'message' => $message,
            'instance_id' => $config['instance_id'],
            'access_token' => $this->token
        ];

        $response = $this->CI->bulkwa_lib->send($payload);
        $this->log($config, $number, $message, null, $response, $module, $branch_id);

        return $response;
    }

    /* ---------------------------------------------------------
     * SEND MEDIA
     * --------------------------------------------------------- */
    public function send_media($number, $caption, $file_url, $module = 'general', $branch_id = 0)
    {
        $config = $this->CI->whatsapp_model->get_active_config($branch_id);
        if (!$config) {
            return ['status' => 0, 'error' => 'No active instance'];
        }

        $payload = [
            'number' => $this->normalize($number, $config['country_code']),
            'type' => 'media',
            'message' => $caption,
            'media_url' => $file_url,
            'instance_id' => $config['instance_id'],
            'access_token' => $this->token
        ];

        $response = $this->CI->bulkwa_lib->send($payload);
        $this->log($config, $number, $caption, $file_url, $response, $module, $branch_id);

        return $response;
    }

    /* ---------------------------------------------------------
     * LOG MESSAGES
     * --------------------------------------------------------- */
    private function log($config, $to, $message, $media_url, $response, $module, $branch_id)
    {
        $this->CI->whatsapp_model->log([
            'branch_id' => $branch_id,
            'config_id' => $config['id'],
            'provider' => 'bulkwa',
            'to_number' => $to,
            'message' => $message,
            'media_url' => $media_url,
            'status' => !empty($response['success']) ? 1 : 0,
            'response' => json_encode($response),
            'module' => $module,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    private function normalize($num, $code = '91')
    {
        $n = preg_replace('/\D+/', '', $num);
        if (strpos($n, $code) !== 0) $n = $code . $n;
        return $n;
    }
}
