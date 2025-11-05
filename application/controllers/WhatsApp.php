<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @package : SchoolExcel School Management System
 * @version : 4.0
 * @developed by : Mithlesh Patel
 * @support : techie.mithlesh@gmail.com
 * @author url : http://codewithmithlesh.com
 * @filename : Liveexam_student.php
 * @c
 */

class WhatsApp extends Public_Controller
{
    private $base = 'https://bulkwapanel.com';
    private $instance = '690427C3B8215';  // your instance ID
    private $token = '67344839386a2';

    public function send_message()
    {
        $number = $this->input->post('number') ?: '918452925291';
        $message = $this->input->post('message') ?: 'Test message from CI3';

        $url = $this->base . '/api/send';
        $payload = [
            'number' => $number,
            'type' => 'text',
            'message' => $message,
            'instance_id' => $this->instance,
            'access_token' => $this->token
        ];

        $response = $this->curl_post_json($url, $payload);

        echo '<pre>';
        print_r($response);
        echo '</pre>';
    }

    private function curl_post_json($url, $payload)
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_POST => 1,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => 1,
            CURLOPT_SSL_VERIFYHOST => 2
        ]);
        $body = curl_exec($ch);
        $err = curl_error($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);
        return [
            'http' => (int) $info['http_code'],
            'body' => $body,
            'json' => json_decode($body, true),
            'err' => $err
        ];
    }
}

