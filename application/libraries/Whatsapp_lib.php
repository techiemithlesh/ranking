<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Whatsapp_lib
{
    private $CI;
    private $provider;

    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->load->model('whatsapp_model');
        $this->CI->load->library('bulkwa_lib');
        $this->provider = 'bulkwa';
    }

    /**
     * Send direct message.
     */
    public function send_text($number, $message, $module = 'general', $branch_id = 0)
    {
        $config = $this->CI->whatsapp_model->get_active_config($branch_id);
        if (empty($config)) {
            return ['status' => false, 'error' => 'No active WhatsApp config found'];
        }

        $payload = [
            'number' => $this->normalize_number($number, $config['country_code'] ?? '91'),
            'type' => 'text',
            'message' => $message,
            'instance_id' => $config['instance_id'],
            'access_token' => $config['access_token']
        ];

        $response = $this->CI->bulkwa_lib->send($payload);

        $this->log_message($config, $number, $message, null, $response, $module, $branch_id);
        return $response;
    }

    /**
     * Send media message (like PDF or image).
     */
    public function send_media($number, $caption, $media_url, $module = 'general', $branch_id = 0)
    {
        $config = $this->CI->whatsapp_model->get_active_config($branch_id);
        if (empty($config)) {
            return ['status' => false, 'error' => 'No active WhatsApp config found'];
        }

        $payload = [
            'number' => $this->normalize_number($number, $config['country_code'] ?? '91'),
            'type' => 'media',
            'message' => $caption,
            'media_url' => $media_url,
            'instance_id' => $config['instance_id'],
            'access_token' => $config['access_token']
        ];

        $response = $this->CI->bulkwa_lib->send($payload);

        $this->log_message($config, $number, $caption, $media_url, $response, $module, $branch_id);
        return $response;
    }

    /**
     * Send message from a saved template.
     */
    public function send_template($template_name, $number, $data = [], $module = 'general', $branch_id = 0, $language = 'en')
    {
        $template = $this->CI->whatsapp_model->get_template($template_name, $branch_id, $language);
        if (empty($template)) {
            return ['status' => false, 'error' => 'Template not found'];
        }

        $message = $template['message_body'];
        if (!empty($data)) {
            foreach ($data as $key => $val) {
                $message = str_replace('{' . $key . '}', $val, $message);
            }
        }

        if ($template['type'] === 'media' && !empty($template['media_url'])) {
            return $this->send_media($number, $message, $template['media_url'], $module, $branch_id);
        } else {
            return $this->send_text($number, $message, $module, $branch_id);
        }
    }

    /**
     * Save message log.
     */
    private function log_message($config, $to_number, $message, $media_url, $response, $module, $branch_id)
    {
        $this->CI->whatsapp_model->log([
            'branch_id' => $branch_id,
            'config_id' => $config['id'] ?? null,
            'provider' => $config['provider'] ?? 'bulkwa',
            'to_number' => $to_number,
            'template_name' => null,
            'message' => $message,
            'media_url' => $media_url,
            'status' => !empty($response['success']) ? 1 : 0,
            'response' => json_encode($response),
            'module' => $module,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    private function normalize_number($number, $country_code = '91')
    {
        $clean = preg_replace('/\D+/', '', $number);

        if (strpos($clean, $country_code) !== 0) {
            $clean = $country_code . $clean;
        }

        return $clean;
    }

}
