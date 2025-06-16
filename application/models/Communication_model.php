<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Communication_model extends MY_Model
{

    public function __construct()
    {
        parent::__construct();
    }

    // mailbox compose
    public function mailbox_compose($data)
    {
        $id = '';
        $branchID = $this->application_model->get_branch_id();
        $sender = loggedin_role_id() . '-' . get_loggedin_user_id();
        $reciever = $data['role_id'] . '-' . $data['receiver_id'];
        $arrayMsg = array(
            'body' => $data['message_body'],
            'subject' => $data['subject'],
            'sender' => $sender,
            'reciever' => $reciever,
        );
        if ($_FILES["attachment_file"]['name'] != "") {
            // uploading file using codeigniter upload library
            $config['upload_path'] = 'uploads/attachments/';
            $config['encrypt_name'] = true;
            $config['allowed_types'] = '*';
            $this->upload->initialize($config);
            if ($this->upload->do_upload("attachment_file")) {
                $arrayMsg['file_name'] = $this->upload->data('orig_name');
                $arrayMsg['enc_name'] = $this->upload->data('file_name');
            }
        }
        $this->db->insert('message', $arrayMsg);
        $id = $this->db->insert_id();

        // send new message received email
        $this->db->where(array('branch_id' => $branchID, 'template_id' => 4));
        $getTemplate = $this->db->get('email_templates_details')->row_array();
        if ($getTemplate['notified'] == 1) {
            $message = $getTemplate['template_body'];
            $message = str_replace("{institute_name}", get_global_setting('institute_name'), $message);
            $message = str_replace("{recipient}", $this->application_model->get_name_mode_by_id($data['role'], $data['receiver_id']), $message);
            $message = str_replace("{message}", $data['message_body'], $message);
            $message = str_replace("{message_url}", base_url('communication/mailbox/read?type=inbox&id=' . $id), $message);
            $msg_data['recipient'] = get_type_name_by_id($data['role'], $data['receiver_id'], 'email');
            $msg_data['subject'] = $getTemplate['subject'];
            $msg_data['message'] = $message;
            $this->load->model("email_model");
            $this->email_model->send_mail($msg_data);
        }
        return $id;
    }

    public function mailbox_compose_all($data)
    {
        $branchID = $this->application_model->get_branch_id();
        $sender = loggedin_role_id() . '-' . get_loggedin_user_id();
        $attachments = [];

        // Handle attachment once
        if (!empty($_FILES["attachment_file"]['name'])) {
            $config['upload_path'] = 'uploads/attachments/';
            $config['encrypt_name'] = true;
            $config['allowed_types'] = '*';
            $this->upload->initialize($config);
            if ($this->upload->do_upload("attachment_file")) {
                $attachments['file_name'] = $this->upload->data('orig_name');
                $attachments['enc_name'] = $this->upload->data('file_name');
            }
        }

        // Load email template
        $this->db->where(['branch_id' => $branchID, 'template_id' => 4]);
        $getTemplate = $this->db->get('email_templates_details')->row_array();
        $emailEnabled = ($getTemplate['notified'] == 1);

        // Handle Send to All
        if ($data['receiver_id'] === 'all') {
            $role_id = $data['role_id'];
            if ($role_id == 7) {
                // Students
                $this->db->select('e.student_id AS id');
                $this->db->from('enroll AS e');
                $this->db->join('login_credential AS l', 'l.user_id = e.student_id AND l.role = 7', 'inner');
                $this->db->where('l.active', 1);
                $this->db->where('e.branch_id', $branchID);
                $this->db->where('e.session_id', get_session_id());

                if (!empty($data['class_id'])) {
                    $this->db->where('e.class_id', $data['class_id']);
                }

                if (!empty($data['section_id'])) {
                    $this->db->where('e.section_id', $data['section_id']);
                }

                $users = $this->db->get()->result();
            } elseif ($role_id == 6) {
                // Parents
                $this->db->select('id');
                $this->db->from('parent');
                $this->db->where('branch_id', $branchID);
                $users = $this->db->get()->result();
            } else {
                // Staff (excluding parents and students)
                $this->db->select('staff.id');
                $this->db->from('staff');
                $this->db->join('login_credential AS lc', 'lc.user_id = staff.id AND lc.role = ' . $role_id, 'inner');
                $this->db->where('staff.branch_id', $branchID);
                $users = $this->db->get()->result();
            }

            foreach ($users as $user) {
                $receiver = $role_id . '-' . $user->id;
                $arrayMsg = [
                    'body' => $data['message_body'],
                    'subject' => $data['subject'],
                    'sender' => $sender,
                    'reciever' => $receiver,
                ];

                if (!empty($attachments)) {
                    $arrayMsg['file_name'] = $attachments['file_name'];
                    $arrayMsg['enc_name'] = $attachments['enc_name'];
                }

                $this->db->insert('message', $arrayMsg);
                $msg_id = $this->db->insert_id();

                // Send email
                if ($emailEnabled) {
                    $recipientName = $this->application_model->get_name_mode_by_id($role_id, $user->id);
                    $recipientEmail = get_type_name_by_id($role_id, $user->id, 'email');

                    $message = str_replace(
                        ["{institute_name}", "{recipient}", "{message}", "{message_url}"],
                        [
                            get_global_setting('institute_name'),
                            $recipientName,
                            $data['message_body'],
                            base_url('communication/mailbox/read?type=inbox&id=' . $msg_id)
                        ],
                        $getTemplate['template_body']
                    );

                    $msg_data = [
                        'recipient' => $recipientEmail,
                        'subject' => $getTemplate['subject'],
                        'message' => $message,
                    ];

                    $this->load->model("email_model");
                    $this->email_model->send_mail($msg_data);
                }
            }

            return true;
        }

        // Individual message
        $receiver = $data['role_id'] . '-' . $data['receiver_id'];
        $arrayMsg = [
            'body' => $data['message_body'],
            'subject' => $data['subject'],
            'sender' => $sender,
            'reciever' => $receiver,
        ];

        if (!empty($attachments)) {
            $arrayMsg['file_name'] = $attachments['file_name'];
            $arrayMsg['enc_name'] = $attachments['enc_name'];
        }

        $this->db->insert('message', $arrayMsg);
        $id = $this->db->insert_id();

        // Send email
        if ($emailEnabled) {
            $recipientName = $this->application_model->get_name_mode_by_id($data['role_id'], $data['receiver_id']);
            $recipientEmail = get_type_name_by_id($data['role_id'], $data['receiver_id'], 'email');

            $message = str_replace(
                ["{institute_name}", "{recipient}", "{message}", "{message_url}"],
                [
                    get_global_setting('institute_name'),
                    $recipientName,
                    $data['message_body'],
                    base_url('communication/mailbox/read?type=inbox&id=' . $id)
                ],
                $getTemplate['template_body']
            );

            $msg_data = [
                'recipient' => $recipientEmail,
                'subject' => $getTemplate['subject'],
                'message' => $message,
            ];

            $this->load->model("email_model");
            $this->email_model->send_mail($msg_data);
        }

        return $id;
    }



    public function mark_messages_read($message_id)
    {
        $activeUser = loggedin_role_id() . '-' . get_loggedin_user_id();
        $this->db->where('reciever', $activeUser);
        $this->db->where('id', $message_id);
        $this->db->update('message', array('read_status' => 1));

        $this->db->where('sender', $activeUser);
        $this->db->where('id', $message_id);
        $this->db->update('message', array('reply_status' => 0));
    }
}
