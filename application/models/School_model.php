<?php
defined('BASEPATH') or exit('No direct script access allowed');

class School_model extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getBranchID()
    {
        if (is_superadmin_loggedin()) {
            return $this->input->get('branch_id', true);
        } else {
            return get_loggedin_branch_id();
        }
    }

    // public function branchUpdate($data)
    // {
    //     $arrayBranch = array(
    //         'name' => $data['branch_name'],
    //         'school_name' => $data['school_name'],
    //         'email' => $data['email'],
    //         'mobileno' => $data['mobileno'],
    //         'currency' => $data['currency'],
    //         'symbol' => $data['currency_symbol'],
    //         'city' => $data['city'],
    //         'state' => $data['state'],
    //         'address' => $data['address'],
    //     );
    //     $this->db->where('id', $data['brance_id']);
    //     $this->db->update('branch', $arrayBranch);
    // }


    public function branchUpdate1($data)
    {
        $arrayBranch = array(
            'name' => $data['branch_name'],
            'school_name' => $data['school_name'],
            'email' => $data['email'],
            'mobileno' => $data['mobileno'],
            'currency' => $data['currency'],
            'symbol' => $data['currency_symbol'],
            'city' => $data['city'],
            'state' => $data['state'],
            'address' => $data['address'],
        );

        $oldLogo = null;
        $this->db->select('logo');
        $this->db->where('id', $data['branch_id']);
        $query = $this->db->get('branch');
        if ($query->num_rows() > 0) {
            $oldLogo = $query->row()->logo;
        }

        if (!empty($_FILES['logo']['name'])) {
            $uploadPath = 'uploads/branch/';

           
            if (!is_dir($uploadPath)) {
                if (!mkdir($uploadPath, 0755, true)) {
                    log_message('error', 'Failed to create upload directory: ' . $uploadPath);
                    return ['error' => 'Upload directory creation failed.'];
                }
            }

            $config = [
                'upload_path' => $uploadPath,
                'allowed_types' => 'jpg|jpeg|png|gif',
                'max_size' => 5120,
                'file_name' => uniqid() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $_FILES['logo']['name']),
            ];

            
            $this->upload->initialize($config);

            if (!$this->upload->do_upload('logo')) {
                $uploadError = $this->upload->display_errors();
                log_message('error', 'File upload failed: ' . $uploadError);
                return ['error' => $uploadError];
            }

            // Save new file path in the database
            $uploadData = $this->upload->data();
            $arrayBranch['logo'] = $uploadPath . $uploadData['file_name'];

            // Delete old logo if exists
            if (!empty($oldLogo) && file_exists($oldLogo)) {
                unlink($oldLogo);
            }
        }


        $this->db->where('id', $data['branch_id']);
        $this->db->update('branch', $arrayBranch);
    }

    public function branchUpdate($data)
{
    $arrayBranch = array(
        'name'        => $data['branch_name'],
        'school_name' => $data['school_name'],
        'email'       => $data['email'],
        'mobileno'    => $data['mobileno'],
        'currency'    => $data['currency'],
        'symbol'      => $data['currency_symbol'],
        'city'        => $data['city'],
        'state'       => $data['state'],
        'address'     => $data['address'],
    );

    // Fetch old logo before transaction
    $oldLogo = null;
    $this->db->select('logo');
    $this->db->where('id', $data['branch_id']);
    $query = $this->db->get('branch');
    if ($query->num_rows() > 0) {
        $oldLogo = $query->row()->logo;
    }

    // Handle file upload before transaction (can't roll back file system)
    $newLogoPath = null;
    if (!empty($_FILES['logo']['name'])) {
        $uploadPath = 'uploads/branch/';

        if (!is_dir($uploadPath)) {
            if (!mkdir($uploadPath, 0755, true)) {
                log_message('error', 'Failed to create upload directory: ' . $uploadPath);
                return ['error' => 'Upload directory creation failed.'];
            }
        }

        $config = [
            'upload_path'   => $uploadPath,
            'allowed_types' => 'jpg|jpeg|png|gif|webp',
            'max_size'      => 5120,
            'file_name'     => uniqid('logo_'), // ✅ no extension — CI appends it
        ];

        $this->load->library('upload');
        $this->upload->initialize($config);

        if (!$this->upload->do_upload('logo')) {
            $uploadError = $this->upload->display_errors('', '');
            log_message('error', 'File upload failed: ' . $uploadError);
            return ['error' => $uploadError];
        }

        $uploadData = $this->upload->data();
        $newLogoPath = $uploadPath . $uploadData['file_name'];
    }

    // Start transaction — DB update only
    $this->db->trans_start();

    if (!empty($newLogoPath)) {
        $arrayBranch['logo'] = $newLogoPath;
    }

    $this->db->where('id', $data['branch_id']);
    $this->db->update('branch', $arrayBranch);

    $this->db->trans_complete();

    // Check if transaction succeeded
    if ($this->db->trans_status() === false) {
        log_message('error', 'Transaction failed for branch_id: ' . $data['branch_id']);

        // Roll back: delete the newly uploaded file since DB didn't save
        if (!empty($newLogoPath) && file_exists($newLogoPath)) {
            unlink($newLogoPath);
            log_message('debug', 'Rolled back uploaded file: ' . $newLogoPath);
        }

        return ['error' => 'Database update failed. Please try again.'];
    }

    // Transaction succeeded — now safe to delete old logo
    if (!empty($newLogoPath) && !empty($oldLogo) && file_exists($oldLogo)) {
        unlink($oldLogo);
        log_message('debug', 'Deleted old logo: ' . $oldLogo);
    }

    return ['success' => true];
}

    function getSmsConfig()
    {
        if (is_superadmin_loggedin()) {
            $branch_id = $this->input->get('branch_id');
        } else {
            $branch_id = get_loggedin_branch_id();
        }

        $api = array();
        $result = $this->db->get('sms_api')->result();
        foreach ($result as $key => $value) {
            $api[$value->name] = $this->db->where(array('sms_api_id' => $value->id, 'branch_id' => $branch_id))->get('sms_credential')->row_array();
        }
        return $api;
    }

}
