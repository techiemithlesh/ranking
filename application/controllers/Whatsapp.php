<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Whatsapp extends Admin_Controller
{
    private $apiBase;
    private $webhookUrl;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('whatsapp_model');
        $this->load->library('bulkwa_lib');

        $this->apiBase = "https://bulkwapanel.com/api/";
        $this->webhookUrl = base_url("whatsapp/webhook");
    }

    /* ---------------------------------------------------------
     * SAVE CONFIG PAGE
     * --------------------------------------------------------- */
    public function config()
    {
        if (!get_permission('whatsapp_config', 'is_view'))
            access_denied();

        $branchId = is_superadmin_loggedin() ? null : get_loggedin_branch_id();

        /* ------ SAVE CONFIG ------ */
        if ($_POST) {

            $this->form_validation->set_rules('instance_id', 'Instance ID', 'required');
            $this->form_validation->set_rules('access_token', 'Access Token', 'required');

            if (is_superadmin_loggedin())
                $this->form_validation->set_rules('branch_id', 'Branch', 'required|integer');

            if ($this->form_validation->run() !== FALSE) {

                $branch_id = is_superadmin_loggedin()
                    ? $this->input->post('branch_id')
                    : get_loggedin_branch_id();

                $instance_id = $this->input->post('instance_id');
                $token = $this->input->post('access_token');

                /** SAVE */
                $data = [
                    'branch_id' => $branch_id,
                    'provider' => 'bulkwa',
                    'instance_id' => $instance_id,
                    'access_token' => $token,
                    'status' => 0,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];

                $this->whatsapp_model->saveConfig($data);

                /** 🔥 SET WEBHOOK IMMEDIATELY */
                $this->setWebhook($instance_id, $token);

                set_alert('success', 'WhatsApp configuration saved.');
                redirect('whatsapp/config');
            }
        }

        $this->data['configs'] = $this->whatsapp_model->getConfigList($branchId);
        $this->data['branch_id'] = $branchId;
        $this->data['title'] = "WhatsApp Config";
        $this->data['sub_page'] = 'whatsapp/config';
        $this->data['main_menu'] = 'whatsapp';

        $this->load->view('layout/index', $this->data);
    }

    /* ---------------------------------------------------------
     * AJAX: CHECK BRANCH INSTANCE
     * --------------------------------------------------------- */
    public function get_branch_instance()
    {
        $branch_id = $this->input->post('branch_id');

        if (!$branch_id) {
            echo json_encode(['status' => 0]);
            return;
        }

        $cfg = $this->whatsapp_model->getConfigByBranch($branch_id);

        if ($cfg) {

            /** 🔥 Ensure webhook is always updated */
            $this->setWebhook($cfg['instance_id'], $cfg['access_token']);

            echo json_encode([
                'status' => 1,
                'instance_id' => $cfg['instance_id'],
                'connected' => $cfg['status'],
                'sender_number' => $cfg['sender_number']
            ]);
        } else {
            echo json_encode(['status' => 0]);
        }
    }

    /* ---------------------------------------------------------
     * AJAX: CREATE NEW INSTANCE
     * --------------------------------------------------------- */
    public function ajax_create_instance()
    {
        $token = get_global_setting('wp_access_token');
        if (!$token) {
            echo json_encode(['status' => 0, 'msg' => 'Token missing']);
            return;
        }

        $url = $this->apiBase . "create_instance?access_token=" . $token;

        $resp = file_get_contents($url);
        $json = json_decode($resp, true);

        if (!empty($json['instance_id'])) {

            /** 🔥 Register webhook immediately */
            $this->setWebhook($json['instance_id'], $token);

            echo json_encode([
                'status' => 1,
                'instance_id' => $json['instance_id']
            ]);
        } else {
            echo json_encode(['status' => 0, 'msg' => 'Failed to create instance']);
        }
    }

    /* ---------------------------------------------------------
     * AJAX: GET QR CODE
     * --------------------------------------------------------- */
    public function ajax_get_qr()
    {
        $instance_id = $this->input->post('instance_id');
        $token = get_global_setting('wp_access_token');

        $url = $this->apiBase . "get_qrcode?instance_id={$instance_id}&access_token={$token}";
        $resp = file_get_contents($url);
        $json = json_decode($resp, true);

        if (!empty($json['base64'])) {
            echo json_encode(['status' => 1, 'qr' => $json['base64']]);
        } else {
            echo json_encode(['status' => 0, 'msg' => 'QR could not load']);
        }
    }

    /* ---------------------------------------------------------
     * 🔥 SET WEBHOOK FOR INSTANCE
     * --------------------------------------------------------- */
    private function setWebhook($instance_id, $token)
    {
        $url = $this->apiBase
            . "set_webhook?access_token={$token}"
            . "&instance_id={$instance_id}"
            . "&webhook_url=" . urlencode($this->webhookUrl)
            . "&enable=true";

        $resp = file_get_contents($url);
        log_message('debug', '[Webhook Set] ' . $resp);
    }

    /* ---------------------------------------------------------
     * WEBHOOK RECEIVER
     * --------------------------------------------------------- */
    public function webhook()
    {
        // Get JSON from BulkWA
        $json = file_get_contents("php://input");
        $data = json_decode($json, true);

        // Log raw input for debugging
        log_message('debug', '[BulkWA Webhook RAW] ' . $json);

        if (empty($data)) {
            echo json_encode(['status' => 0, 'msg' => 'Invalid JSON']);
            return;
        }

        $instance_id = $data['instance_id'] ?? null;
        $event = $data['event'] ?? 'unknown';

        // Save webhook event to log table
        $this->db->insert('whatsapp_webhook_log', [
            'instance_id' => $instance_id,
            'event' => $event,
            'payload' => $json,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        if (!$instance_id) {
            echo json_encode(['status' => 1, 'msg' => 'Logged but instance missing']);
            return;
        }

        // Fetch config for this instance
        $config = $this->db->where('instance_id', $instance_id)
            ->get('whatsapp_config')
            ->row_array();

        if (!$config) {
            echo json_encode(['status' => 1, 'msg' => 'Instance not registered']);
            return;
        }

        // Process status change events
        switch ($event) {

            case 'logged_in':
                $this->db->where('instance_id', $instance_id)->update('whatsapp_config', [
                    'status' => 1,
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
                break;

            case 'logout':
            case 'disconnected':
                $this->db->where('instance_id', $instance_id)->update('whatsapp_config', [
                    'status' => 0,
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
                break;
        }

        echo json_encode(['status' => 1, 'msg' => 'OK']);
    }

}
