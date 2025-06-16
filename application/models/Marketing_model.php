<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}


class Marketing_model extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getList($branchId = null, $fileType = null)
    {
        $this->db->select('*');
        $this->db->from('tbl_marketing_material');

        if (!empty($branchId)) {
            $this->db->where('branch_id', $branchId);
        }
        if (!empty($fileType)) {
            $this->db->where('file_type', $fileType);
        }


        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            return $query->result_array();
        } else {
            return [];
        }
    }

    public function saveMaterial($data)
    {
        // Validate input data
        if (!in_array($data['file_type'], ['image', 'video', 'pdf'])) {
            // log_message('error', 'Invalid file type provided: ' . $data['file_type']);
            return ['error' => 'Invalid file type.'];
        }

        $uploadConfig = [
            'encrypt_name' => true,
            'upload_path' => 'uploads/marketing/' . $data['file_type'] . '/',
            'max_size' => 1024 * 1024 * 1024,
        ];

        switch ($data['file_type']) {
            case 'image':
                $uploadConfig['allowed_types'] = 'jpg|jpeg|png|gif|bmp|webp';
                break;
            case 'video':
                $uploadConfig['allowed_types'] = 'mp4|avi|mov|mkv';
                break;
            case 'pdf':
                $uploadConfig['allowed_types'] = 'pdf';
                break;
        }

        // Ensure upload directory exists
        if (!is_dir($uploadConfig['upload_path'])) {
            if (!mkdir($uploadConfig['upload_path'], 0755, true)) {
                // log_message('error', 'Failed to create upload directory: ' . $uploadConfig['upload_path']);
                return ['error' => 'Upload directory creation failed.'];
            }
        }

        // Initialize upload library with config
        $this->load->library('upload');
        $this->upload->initialize($uploadConfig);

        // Attempt file upload
        if (!$this->upload->do_upload('file_path')) {
            $uploadError = $this->upload->display_errors();
            // log_message('error', 'File upload failed: ' . $uploadError);
            return ['error' => $uploadError];
        }

        // Prepare database entry
        $uploadData = $this->upload->data();
        $arrayData = [
            'branch_id' => $data['branch_id'],
            'title' => $data['title'],
            'file_type' => $data['file_type'],
            'uploaded_by' => get_loggedin_user_id(),
            'file_path' => $uploadConfig['upload_path'] . $uploadData['file_name']
        ];

        // Insert into database
        $this->db->insert('tbl_marketing_material', $arrayData);

        if ($this->db->affected_rows() > 0) {
            // log_message('info', 'Material saved successfully: ' . json_encode($arrayData));
            return true;
        }

        // Database insertion failed
        $dbError = $this->db->error();
        // log_message('error', 'Database save failed: ' . json_encode($dbError));
        return ['error' => 'Failed to save data to the database.'];
    }

    public function updateMarketing($data)
    {
        // Validate input data
        if (!in_array($data['file_type'], ['image', 'video', 'pdf'])) {
            // log_message('error', 'Invalid file type provided: ' . $data['file_type']);
            return ['error' => 'Invalid file type.'];
        }

        // Fetch the current record from the database
        $existingRecord = $this->db->get_where('tbl_marketing_material', ['id' => $data['id']])->row_array();
        if (!$existingRecord) {
            return ['error' => 'Marketing material not found.'];
        }

        $uploadConfig = [
            'encrypt_name' => true,
            'upload_path' => 'uploads/marketing/' . $data['file_type'] . '/',
            'max_size' => 1024 * 1024 * 1024,
        ];

        switch ($data['file_type']) {
            case 'image':
                $uploadConfig['allowed_types'] = 'jpg|jpeg|png|gif|bmp|webp';
                break;
            case 'video':
                $uploadConfig['allowed_types'] = 'mp4|avi|mov|mkv';
                break;
            case 'pdf':
                $uploadConfig['allowed_types'] = 'pdf';
                break;
        }

        // Ensure upload directory exists
        if (!is_dir($uploadConfig['upload_path'])) {
            if (!mkdir($uploadConfig['upload_path'], 0755, true)) {
                // log_message('error', 'Failed to create upload directory: ' . $uploadConfig['upload_path']);
                return ['error' => 'Upload directory creation failed.'];
            }
        }

        // Initialize upload library
        $this->load->library('upload');
        $this->upload->initialize($uploadConfig);

        $newFileUploaded = false;
        $filePath = $existingRecord['file_path']; // Default to existing file path

        // Check if a new file is uploaded
        if (!empty($_FILES['file_path']['name'])) {
            $newFileUploaded = true;

            // Attempt file upload
            if (!$this->upload->do_upload('file_path')) {
                $uploadError = $this->upload->display_errors();
                // log_message('error', 'File upload failed: ' . $uploadError);
                return ['error' => $uploadError];
            }

            // Delete the old file if it exists
            if (file_exists($existingRecord['file_path'])) {
                unlink($existingRecord['file_path']);
            }

            // Get new file data
            $uploadData = $this->upload->data();
            $filePath = $uploadConfig['upload_path'] . $uploadData['file_name'];
        }

        // Prepare updated data for the database
        $updateData = [
            'branch_id' => $data['branch_id'],
            'title' => $data['title'],
            'file_type' => $data['file_type'],
            'uploaded_by' => get_loggedin_user_id(),
            'file_path' => $filePath,
        ];

        // Update the database record
        $this->db->where('id', $data['id']);
        $this->db->update('tbl_marketing_material', $updateData);

        if ($this->db->affected_rows() > 0) {
            // log_message('info', 'Marketing material updated successfully: ' . json_encode($updateData));
            return true;
        }

        // Handle case where no changes were made
        // log_message('info', 'No changes were made to the marketing material: ' . json_encode($updateData));
        return ['error' => 'No changes were made to the record.'];
    }



}

