<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Whatsapp extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('whatsapp_model');
        $this->load->library('bulkwa_lib');
    }

    public function config()
    {
        if (!get_permission('whatsapp_config', 'is_view'))
            access_denied();

        $branchId = is_superadmin_loggedin() ? null : get_loggedin_branch_id();

        if ($_POST) {

            // VALIDATION
            $this->form_validation->set_rules('instance_id', 'Instance ID', 'trim|required');
            $this->form_validation->set_rules('access_token', 'Access Token', 'trim|required');

            if (is_superadmin_loggedin()) {
                $this->form_validation->set_rules('branch_id', 'Branch', 'required|integer');
            }

            if ($this->form_validation->run() !== false) {

                $branch_id = is_superadmin_loggedin() ? $this->input->post('branch_id') : get_loggedin_branch_id();
                $instance_id = $this->input->post('instance_id');

                $data = [
                    'branch_id' => $branch_id,
                    'provider' => 'bulkwa',
                    'instance_id' => $instance_id,
                    'updated_at' => date('Y-m-d H:i:s'),
                ];

                // Check if this branch already has a config
                $existing = $this->whatsapp_model->getConfigByBranch($branch_id);

                if ($existing) {
                    // UPDATE
                    $this->db->where('id', $existing['id'])->update('whatsapp_config', $data);
                } else {
                    // INSERT
                    $data['created_at'] = date('Y-m-d H:i:s');
                    $this->db->insert('whatsapp_config', $data);
                }

                set_alert('success', 'WhatsApp configuration saved successfully');
                redirect(base_url('whatsapp/config'));
            } else {
                set_alert('error', validation_errors());
            }
        }


        $this->data['configs'] = $this->whatsapp_model->getConfigList($branchId);
        $this->data['branch_id'] = $branchId;
        $this->data['title'] = translate('whatsapp_config');
        $this->data['sub_page'] = 'whatsapp/config';
        $this->data['main_menu'] = 'whatsapp';

        $this->load->view('layout/index', $this->data);
    }

    /* --------------------------------------------------------
        AJAX → Return instance details of a branch
    -------------------------------------------------------- */
    public function get_branch_instance()
    {
        $branch_id = $this->input->post('branch_id');

        if (!$branch_id) {
            echo json_encode(['status' => 0, 'msg' => 'Branch ID missing']);
            return;
        }

        $instance = $this->whatsapp_model->getConfigByBranch($branch_id);

        if ($instance) {
            echo json_encode([
                'status' => 1,
                'instance_id' => $instance['instance_id'],
                'connected' => (int) $instance['status'],
                'sender_number' => $instance['sender_number'],
            ]);
        } else {
            echo json_encode(['status' => 0]);
        }
    }

    /* --------------------------------------------------------
        AJAX → Create Instance ID (BulkWA)
    -------------------------------------------------------- */
    public function ajax_create_instance()
    {
        $access_token = get_global_setting('wp_access_token');

        if (!$access_token) {
            echo json_encode(['status' => 0, 'msg' => 'Access Token missing']);
            return;
        }

        $url = "https://bulkwapanel.com/api/create_instance?access_token={$access_token}";
        $response = file_get_contents($url);
        $json = json_decode($response, true);

        if (!empty($json['instance_id'])) {
            echo json_encode(['status' => 1, 'instance_id' => $json['instance_id']]);
        } else {
            echo json_encode(['status' => 0, 'msg' => 'Failed to create instance']);
        }
    }

    /* --------------------------------------------------------
        AJAX → Get QR Code (BulkWA)
    -------------------------------------------------------- */
    public function ajax_get_qr()
    {
        $instance_id = $this->input->post('instance_id');
        $access_token = get_global_setting('wp_access_token');

        if (!$instance_id) {
            echo json_encode(['status' => 0, 'msg' => 'Instance ID missing']);
            return;
        }

        $url = "https://bulkwapanel.com/api/get_qrcode?instance_id={$instance_id}&access_token={$access_token}";
        $resp = file_get_contents($url);
        $json = json_decode($resp, true);

        if (!empty($json['base64'])) {
            echo json_encode([
                'status' => 1,
                'qr' => $json['base64']
            ]);
        } else {
            echo json_encode([
                'status' => 0,
                'msg' => 'Failed to fetch QR'
            ]);
        }
    }

    /* --------------------------------------------------------
        Incoming Webhook from BulkWA
    -------------------------------------------------------- */
    public function webhook()
    {
        $json = file_get_contents("php://input");
        $data = json_decode($json, true);

        log_message('debug', '[BulkWA Webhook] ' . $json);

        if (empty($data)) {
            echo json_encode(['status' => 0, 'msg' => 'Invalid JSON']);
            return;
        }

        $instance_id = $data['instance_id'] ?? null;
        $event = $data['event'] ?? null;

        if (!$instance_id || !$event) {
            echo json_encode(['status' => 0, 'msg' => 'Missing fields']);
            return;
        }

        $config = $this->db->where('instance_id', $instance_id)
            ->get('whatsapp_config')
            ->row_array();

        if (!$config) {
            echo json_encode(['status' => 0, 'msg' => 'Instance not found']);
            return;
        }

        switch ($event) {
            case 'logged_in':
                $this->db->where('instance_id', $instance_id)
                    ->update('whatsapp_config', [
                        'status' => 1,
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
                break;

            case 'disconnected':
            case 'logout':
                $this->db->where('instance_id', $instance_id)
                    ->update('whatsapp_config', [
                        'status' => 0,
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
                break;
        }

        echo json_encode(['status' => 1]);
    }
}
