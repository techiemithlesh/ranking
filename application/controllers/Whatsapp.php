<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Whatsapp extends Admin_Controller
{
    private $apiBase;
    private $webhookUrl;
    private $token;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('whatsapp_model');
        $this->load->library('whatsapp_lib');

        $this->apiBase = "https://bulkwapanel.com/api/";
        $this->webhookUrl = base_url("whatsapp/webhook");
        $this->token = get_global_setting('wp_access_token');
    }

    /* ---------------------------------------------------------
     * MAIN CONFIG PAGE
     * --------------------------------------------------------- */
    public function config()
    {
        if (!get_permission('whatsapp_config', 'is_view'))
            access_denied();

        $branchId = is_superadmin_loggedin() ? null : get_loggedin_branch_id();

        /* ---------------------------------------------------------
         * SAVE CONFIG FORM SUBMISSION
         * --------------------------------------------------------- */
        if ($_POST) {

            $this->form_validation->set_rules('instance_id', 'Instance ID', 'required');
            $this->form_validation->set_rules('access_token', 'Access Token', 'required');

            if (is_superadmin_loggedin()) {
                $this->form_validation->set_rules('branch_id', 'Branch', 'required|integer');
            }

            if ($this->form_validation->run() !== false) {

                $branch_id = is_superadmin_loggedin()
                    ? $this->input->post('branch_id')
                    : get_loggedin_branch_id();

                $instance_id = $this->input->post('instance_id');

                $data = [
                    'branch_id' => $branch_id,
                    'provider' => 'bulkwa',
                    'instance_id' => $instance_id,
                    'status' => 0,
                    'updated_at' => date('Y-m-d H:i:s'),
                ];

                $this->whatsapp_model->saveConfig($data);

                /** SET WEBHOOK AUTOMATICALLY */
                $this->setWebhook($instance_id);

                set_alert('success', 'WhatsApp configuration saved.');
                redirect('whatsapp/config');
            }
        }

        /* ---------------------------------------------------------
         * LOAD CONFIG VIEW
         * --------------------------------------------------------- */
        $this->data['configs'] = $this->whatsapp_model->getConfigList($branchId);
        $this->data['branch_id'] = $branchId;

        $this->data['title'] = "WhatsApp Config";
        $this->data['sub_page'] = 'whatsapp/config';
        $this->data['main_menu'] = 'whatsapp';

        $this->load->view('layout/index', $this->data);
    }

    /* ---------------------------------------------------------
     * AJAX → FIND INSTANCE FOR BRANCH
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

            // auto ensure webhook
            $this->setWebhook($cfg['instance_id']);

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
     * AJAX → CREATE BULKWA INSTANCE
     * --------------------------------------------------------- */
    public function ajax_create_instance()
    {
        if (!$this->token) {
            echo json_encode(['status' => 0, 'msg' => 'Access token missing']);
            return;
        }

        $url = $this->apiBase . "create_instance?access_token=" . $this->token;
        $resp = $this->curl_get($url);
        $json = json_decode($resp, true);

        if (!empty($json['instance_id'])) {

            // register webhook
            $this->setWebhook($json['instance_id']);

            echo json_encode([
                'status' => 1,
                'instance_id' => $json['instance_id']
            ]);
        } else {
            echo json_encode(['status' => 0, 'msg' => 'Failed to create instance']);
        }
    }

    /* ---------------------------------------------------------
     * AJAX → GET QR
     * --------------------------------------------------------- */
    public function ajax_get_qr()
    {
        $instance_id = $this->input->post('instance_id');

        $url = $this->apiBase . "get_qrcode?instance_id={$instance_id}&access_token={$this->token}";
        $resp = $this->curl_get($url);
        $json = json_decode($resp, true);

        if (!empty($json['base64'])) {
            echo json_encode(['status' => 1, 'qr' => $json['base64']]);
        } else {
            echo json_encode(['status' => 0, 'msg' => 'QR failed to load']);
        }
    }

    /* ---------------------------------------------------------
     * SET WEBHOOK FOR INSTANCE
     * --------------------------------------------------------- */
    private function setWebhook($instance_id)
    {
        $url = $this->apiBase
            . "set_webhook?access_token={$this->token}"
            . "&instance_id={$instance_id}"
            . "&webhook_url=" . urlencode($this->webhookUrl)
            . "&enable=true";

        $resp = $this->curl_get($url);
        log_message('debug', '[Webhook Set] ' . $resp);
    }

    /* ---------------------------------------------------------
     * WEBHOOK RECEIVER
     * --------------------------------------------------------- */
    public function webhook()
    {
        $json = file_get_contents("php://input");
        $data = json_decode($json, true);

        log_message('debug', '[BulkWA Webhook RAW] ' . $json);

        if (empty($data)) {
            echo json_encode(['status' => 0]);
            return;
        }

        $instance_id = $data['instance_id'] ?? null;
        $event = $data['event'] ?? 'unknown';

        // store in webhook logs
        $this->db->insert('whatsapp_webhook_log', [
            'instance_id' => $instance_id,
            'event' => $event,
            'payload' => $json,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        if (!$instance_id) {
            echo json_encode(['status' => 1]);
            return;
        }

        // update instance status
        switch ($event) {
            case 'logged_in':
                $this->whatsapp_model->update_status($instance_id, 1);
                break;

            case 'logout':
            case 'disconnected':
                $this->whatsapp_model->update_status($instance_id, 0);
                break;
        }

        echo json_encode(['status' => 1]);
    }

    /* ---------------------------------------------------------
     * TEST SENDING MESSAGE
     * --------------------------------------------------------- */
    public function test_whatsapp()
    {
        $response = $this->whatsapp_lib->send_text('919546858183', 'Hi Mithlesh Your Coding is awesome', 'test', 1);

        printVar($response);
        die;
    }

    /* ---------------------------------------------------------
     * HELPERS → CURL GET
     * --------------------------------------------------------- */
    private function curl_get($url)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $out = curl_exec($ch);
        curl_close($ch);
        return $out;
    }


    public function instance_action()
    {
        $action = $this->input->post('action');
        $instance_id = $this->input->post('instance_id');

        if (!$instance_id)
            responseMsg(0, "Instance ID missing");

        switch ($action) {

            case 'reconnect':
                $url = $this->apiBase . "reconnect?instance_id={$instance_id}&access_token={$this->token}";
                break;

            case 'reboot':
                $url = $this->apiBase . "restart?instance_id={$instance_id}&access_token={$this->token}";
                break;

            case 'reset':
                $url = $this->apiBase . "reset_instance?instance_id={$instance_id}&access_token={$this->token}";

                // clear config completely
                $this->db->where('instance_id', $instance_id)->update('whatsapp_config', [
                    'status' => 0,
                    'sender_number' => null,
                    'alias_name' => null,
                    'country_code' => null,
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
                break;

            case 'delete':
                $this->db->where('instance_id', $instance_id)->delete('whatsapp_config');
                responseMsg(1, "Instance deleted");
                return;

            default:
                responseMsg(0, "Invalid action");
        }

        $resp = $this->curl_get($url);
        log_message('debug', "[Instance Action - $action] " . $resp);

        responseMsg(1, ucfirst($action) . " executed successfully");
    }


    public function config_delete($instance_id)
    {
        $this->db->where('instance_id', $instance_id)->delete('whatsapp_config');
        responseMsg(1, "Deleted");
    }


}
