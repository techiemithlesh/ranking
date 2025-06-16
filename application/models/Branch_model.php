<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Branch_model extends MY_Model
{

    public function __construct()
    {
        parent::__construct();
    }

    // public function save($data)
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
    //         'wp_access_token' => $data['wp_access_token'] ?? '',
    //         'wp_instance_id' => $data['wp_instance_id'] ?? '',
    //     );
    //     if (!isset($data['branch_id'])) {
    //         $this->db->insert('branch', $arrayBranch);
    //     } else {
    //         $this->db->where('id', $data['branch_id']);
    //         $this->db->update('branch', $arrayBranch);
    //     }
        
    //     if ($this->db->affected_rows() > 0) {
    //         return true;
    //     } else {
    //         return false;
    //     }
    // }

    public function save($data)
    {
        $arrayBranch = [
            'name' => $data['branch_name'],
            'school_name' => $data['school_name'],
            'email' => $data['email'],
            'mobileno' => $data['mobileno'],
            'currency' => $data['currency'],
            'symbol' => $data['currency_symbol'],
            'city' => $data['city'],
            'state' => $data['state'],
            'address' => $data['address'],
        ];

        // Check if editing an existing branch
        $oldLogo = null;
        if (!empty($data['branch_id'])) {
            $this->db->select('logo');
            $this->db->where('id', $data['branch_id']);
            $query = $this->db->get('branch');
            $oldLogo = ($query->num_rows() > 0) ? $query->row()->logo : null;
        }

        // Handle file upload
        if (!empty($_FILES['logo']['name'])) {
            $uploadPath = 'uploads/branch/';

            // Ensure upload directory exists
            if (!is_dir($uploadPath)) {
                if (!mkdir($uploadPath, 0755, true)) {
                    log_message('error', 'Failed to create upload directory: ' . $uploadPath);
                    return ['error' => 'Upload directory creation failed.'];
                }
            }

            $config = [
                'upload_path' => $uploadPath,
                'allowed_types' => 'jpg|jpeg|png|gif',
                'max_size' => 2048,
                'file_name' => uniqid() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $_FILES['logo']['name']),
            ];

            $this->load->library('upload');
            $this->upload->initialize($config);

            if (!$this->upload->do_upload('logo')) {
                $uploadError = $this->upload->display_errors();
                log_message('error', 'File upload failed: ' . $uploadError);
                return ['error' => $uploadError];
            }

            // Save file path in database
            $uploadData = $this->upload->data();
            $arrayBranch['logo'] = $uploadPath . $uploadData['file_name'];

            // Delete old file if updating
            if ($oldLogo && file_exists($oldLogo)) {
                unlink($oldLogo);
            }
        }

        // Insert or update branch record
        if (empty($data['branch_id'])) {
            $this->db->insert('branch', $arrayBranch);
        } else {
            $this->db->where('id', $data['branch_id']);
            $this->db->update('branch', $arrayBranch);
        }

        return $this->db->affected_rows() > 0;
    }

    public function saveWithAllDetails($data)
    {
        $this->db->trans_begin();

        $arrayBranch = [
            'name' => $data['branch_name'],
            'school_name' => $data['school_name'],
            'email' => $data['email'],
            'mobileno' => $data['mobileno'],
            'currency' => $data['currency'],
            'symbol' => $data['currency_symbol'],
            'city' => $data['city'],
            'state' => $data['state'],
            'address' => $data['address'],
            'created_at' => date('Y-m-d H:i:s'),
        ];

        $logo = null;

        if (!empty($_FILES['logo']['name'])) {
            $uploadPath = 'uploads/branch/';

            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }

            $config = [
                'upload_path' => $uploadPath,
                'allowed_types' => 'jpg|jpeg|png|gif',
                'max_size' => 2048,
                'file_name' => uniqid() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $_FILES['logo']['name']),
            ];

            $this->load->library('upload');
            $this->upload->initialize($config);

            if ($this->upload->do_upload('logo')) {
                $uploadData = $this->upload->data();
                $logo = $uploadPath . $uploadData['file_name'];
                $arrayBranch['logo'] = $logo;
            } else {
                log_message('error', 'File upload failed: ' . $this->upload->display_errors());
                return ['error' => $this->upload->display_errors()];
            }
        }

        // Insert Branch
        $this->db->insert('branch', $arrayBranch);
        $branchId = $this->db->insert_id();

        if (!$branchId) {
            $this->db->trans_rollback();
            return ['error' => 'Branch creation failed'];
        }

        // Insert Department
        $arrayDepartment = [
            'name' => 'Administration',
            'branch_id' => $branchId
        ];

        $this->db->insert('staff_department', $arrayDepartment);
        $departmentId = $this->db->insert_id();

        if (!$departmentId) {
            $this->db->trans_rollback();
            return ['error' => 'Department creation failed'];
        }

        // Insert Designation
        $designation = [
            'name' => 'Admin Head',
            'branch_id' => $branchId
        ];

        $this->db->insert('staff_designation', $designation);
        $designationId = $this->db->insert_id();

        if (!$designationId) {
            $this->db->trans_rollback();
            return ['error' => 'Designation creation failed'];
        }

        // Insert Staff
        $staffData = [
            'branch_id' => $branchId,
            'staff_id' => substr(app_generate_hash(), 3, 7),
            'name' => 'admin',
            'sex' => 'Male',
            'religion' => 'Hindu',
            'blood_group' => '0',
            'birthday' => date('Y-m-d'),
            'mobileno' => $data['mobileno'],
            'present_address' => $data['address'],
            'permanent_address' => $data['address'],
            'photo' => $logo,
            'designation' => $designationId,
            'department' => $departmentId,
            'joining_date' => date('Y-m-d'),
            'qualification' => 'MA',
            'email' => $data['email'],
        ];

        $this->db->insert('staff', $staffData);
        $staffId = $this->db->insert_id();

        if (!$staffId) {
            $this->db->trans_rollback();
            return ['error' => 'Staff creation failed'];
        }

        $this->db->select('id');
        $this->db->order_by('id', 'DESC');
        $this->db->limit(1);
        $query = $this->db->get('login_credential');

        $newPassword = "admin@1";

        if ($query->num_rows() > 0) {
            $row = $query->row();
            $lastId = (int) $row->id;
            $newPassword = "admin@" . ($lastId + 1);
        }

        $encryptedPassword = $this->app_lib->has_password($newPassword);

        $loginData = [
            'username' => $data["email"],
            'role' => 2,
            'active' => 1,
            'user_id' => $staffId,
            'password' => $encryptedPassword,
        ];

        
        $this->db->insert('login_credential', $loginData);

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            return ['error' => 'Transaction failed'];
        } else {
            $this->db->trans_commit();
            return $staffId;
        }
    }

    public function getBranchesByBooks($book_ids)
    {
        $this->db->select('*');
        $this->db->from('branch');
        $this->db->where_in('id', $book_ids);
        $query = $this->db->get();
        return $query->result_array();
    }

    public function csvImport($data)
    {
        $insert_data = array(
            'name' => $data['branch_name'],
            'school_name' => $data['school_name'],
            'email' => $data['email'],
            'mobileno' => $data['mobileno'],
            'currency' => $data['currency'],
            'symbol' => $data['currency_symbol'],
            'city' => isset($data['city']) ? $data['city'] : null,
            'state' => isset($data['state']) ? $data['state'] : null,
            'address' => isset($data['address']) ? $data['address'] : null,
            'wp_access_token' => isset($data['wp_access_token']) ? $data['wp_access_token'] : null,
            'wp_instance_id' => isset($data['wp_instance_id']) ? $data['wp_instance_id'] : null,
        );

        $this->db->insert('branch', $insert_data);
        return $this->db->affected_rows() > 0;
    }
}
