<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Bulkwa_lib {
    private $base = 'https://bulkwapanel.com';
    private $CI;

    public function __construct(){
        $this->CI =& get_instance();
    }

    public function send($payload)
    {
        $ch = curl_init($this->base . '/api/send');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_TIMEOUT => 25,
            CURLOPT_SSL_VERIFYPEER => false
        ]);
        $response = curl_exec($ch);
        $err = curl_error($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);

        $decoded = json_decode($response, true);
        $success = isset($decoded['status']) && in_array(strtolower($decoded['status']), ['ok','success','sent']);

        return [
            'success' => $success,
            'http' => $info['http_code'],
            'response' => $decoded,
            'error' => $err
        ];
    }
}