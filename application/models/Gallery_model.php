<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Gallery_model extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
    }


    public function getGallery($branchId, $classId, $sectionId)
    {
        $this->db->select('g.*');
        $this->db->from('tbl_gallery g');


        if (!empty($branchId)) {
            $this->db->where('g.branch_id', $branchId);
            $this->db->where('g.status', 1);
        }

        if (!empty($classId)) {
            $this->db->where('g.class_id', $classId);
        }
        if (!empty($sectionId) && $sectionId !== 'all') {
            $this->db->where('g.section_id', $sectionId);
        }


        if (is_student_loggedin()) {
            $studentId = get_loggedin_user_id();

            $this->db->group_start();
            $this->db->where('g.section_id', 'all');
            $this->db->or_where("EXISTS (SELECT 1 FROM tbl_gallery_student gs WHERE gs.gallery_id = g.id AND gs.student_id = $studentId) AND g.status = 1", NULL, FALSE);
            $this->db->group_end();
        }

        $query = $this->db->get();
        return $query->result_array() ?? [];
    }

    public function getGalleryById($id)
    {
        $this->db->select('g.*');
        $this->db->from('tbl_gallery g');
        $this->db->where('g.status', 1);
        $this->db->where('g.id', $id);

        $query = $this->db->get();
        return $query->row_array();
    }


    public function saveGallery($data)
    {
        $data['uploader_id'] = get_loggedin_user_id();
        $data['branch_id'] = $data['branch_id'];
        $data['class_id'] = $data['class_id'];
        $data['section_id'] = ($data['section_id'] == 'all') ? null : $data['section_id'];
        $data['description'] = $data['description'] ?? null;

        if (!empty($_FILES['file_path']['name'])) {
            $fileExtension = strtolower(pathinfo($_FILES['file_path']['name'], PATHINFO_EXTENSION));
            $fileType = $this->getFileType($fileExtension);

            if ($fileType === 'unknown') {
                return ['status' => 'error', 'error' => 'Invalid file type.'];
            }

            $uploadConfig = [
                'encrypt_name' => true,
                'upload_path' => 'uploads/gallery/' . $fileType . '/',
                'max_size' => 1024 * 1024 * 2, // 2MB
                'allowed_types' => $this->getAllowedFileTypes($fileType),
            ];

            // Ensure upload directory exists
            if (!is_dir($uploadConfig['upload_path'])) {
                if (!mkdir($uploadConfig['upload_path'], 0755, true)) {
                    log_message('error', 'Failed to create upload directory: ' . $uploadConfig['upload_path']);
                    return ['status' => 'error', 'error' => 'Upload directory creation failed.'];
                }
            }

            // Initialize upload library with config
            $this->load->library('upload');
            $this->upload->initialize($uploadConfig);

            if (!$this->upload->do_upload('file_path')) {
                $uploadError = strip_tags($this->upload->display_errors());
                log_message('error', 'File upload failed: ' . $uploadError);
                return ['status' => 'error', 'error' => $uploadError];
            }

            $uploadData = $this->upload->data();

            // Prepare database entry
            $insertData = [
                'uploader_id' => get_loggedin_user_id(),
                'branch_id' => $data['branch_id'],
                'class_id' => $data['class_id'],
                'section_id' => $data['section_id'],
                'description' => $data['description'],
                'file_type' => $fileType,
                'file_path' => $uploadConfig['upload_path'] . $uploadData['file_name']
            ];

            log_message('debug', 'Insert data: ' . print_r($insertData, true));

            $this->db->insert('tbl_gallery', $insertData);

            if ($this->db->affected_rows() > 0) {
                return ['status' => 'success', 'file_id' => $this->db->insert_id()];
            }

            $dbError = $this->db->error();

            log_message('error', 'Database error: ' . print_r($dbError, true));


            return ['status' => 'error', 'error' => 'Failed to save data to the database.'];
        }

        return ['status' => 'error', 'error' => 'No file uploaded'];
    }

    public function saveMultipleGallery($data)
    {
        $uploadedFiles = $_FILES['file_path'];
        $successFiles = [];
        $errors = [];

        $fileCount = count($uploadedFiles['name']);
        for ($i = 0; $i < $fileCount; $i++) {
            $_FILES['single_file']['name'] = $uploadedFiles['name'][$i];
            $_FILES['single_file']['type'] = $uploadedFiles['type'][$i];
            $_FILES['single_file']['tmp_name'] = $uploadedFiles['tmp_name'][$i];
            $_FILES['single_file']['error'] = $uploadedFiles['error'][$i];
            $_FILES['single_file']['size'] = $uploadedFiles['size'][$i];

            $fileExtension = strtolower(pathinfo($_FILES['single_file']['name'], PATHINFO_EXTENSION));
            $fileType = $this->getFileType($fileExtension);

            if ($fileType === 'unknown') {
                $errors[] = "Invalid file type: " . $_FILES['single_file']['name'];
                continue;
            }

            $uploadConfig = [
                'encrypt_name' => true,
                'upload_path' => 'uploads/gallery/' . $fileType . '/',
                'max_size' => 102400, // 100MB
                'allowed_types' => $this->getAllowedFileTypes($fileType),
            ];

            if (!is_dir($uploadConfig['upload_path'])) {
                mkdir($uploadConfig['upload_path'], 0755, true);
            }

            $this->load->library('upload', $uploadConfig);
            $this->upload->initialize($uploadConfig);

            if (!$this->upload->do_upload('single_file')) {
                $errors[] = $_FILES['single_file']['name'] . ': ' . strip_tags($this->upload->display_errors());
                continue;
            }

            $uploadData = $this->upload->data();

            $insertData = [
                'uploader_id' => get_loggedin_user_id(),
                'branch_id' => $data['branch_id'],
                'class_id' => $data['class_id'],
                'section_id' => ($data['section_id'] == 'all') ? null : $data['section_id'],
                'description' => $data['description'] ?? null,
                'file_type' => $fileType,
                'file_path' => $uploadConfig['upload_path'] . $uploadData['file_name']
            ];

            $this->db->insert('tbl_gallery', $insertData);

            if ($this->db->affected_rows() > 0) {
                $successFiles[] = $this->db->insert_id();
            } else {
                $errors[] = $_FILES['single_file']['name'] . ': Failed to save in database.';
            }
        }

        if (!empty($successFiles)) {
            return ['status' => 'success', 'success' => $successFiles];
        } else {
            return ['status' => 'error', 'error' => implode(', ', $errors)];
        }
    }



    // public function updateGallery($data)
    // {
    //     $id = $data['id'];

    //     $existing = $this->db->get_where('tbl_gallery', ['id' => $id])->row_array();
    //     if (!$existing) {
    //         return ['status' => 'error', 'error' => 'Gallery record not found.'];
    //     }

    //     $upload_path = 'uploads/gallery/';
    //     $file_path = $data['old_file'] ?? $existing['file_path'];
    //     $fileType = $existing['fileType'] ?? null;

    //     if (!empty($_FILES['file_path']['name'])) {
    //         $fileExtension = strtolower(pathinfo($_FILES['file_path']['name'], PATHINFO_EXTENSION));
    //         $fileType = $this->getFileType($fileExtension);

    //         if ($fileType === 'unknown') {
    //             return ['status' => 'error', 'error' => 'Invalid file type.'];
    //         }

    //         $uploadConfig = [
    //             'encrypt_name' => true,
    //             'upload_path' => $upload_path . $fileType . '/',
    //             'max_size' => 1024 * 1024 * 10, // 10MB
    //             'allowed_types' => $this->getAllowedFileTypes($fileType),
    //         ];

    //         if (!is_dir($uploadConfig['upload_path'])) {
    //             if (!mkdir($uploadConfig['upload_path'], 0755, true)) {
    //                 return ['status' => 'error', 'error' => 'Failed to create upload directory.'];
    //             }
    //         }

    //         $this->load->library('upload');
    //         $this->upload->initialize($uploadConfig);

    //         if (!$this->upload->do_upload('file_path')) {
    //             return ['status' => 'error', 'error' => strip_tags($this->upload->display_errors())];
    //         }

    //         $uploadData = $this->upload->data();
    //         $file_path = $uploadConfig['upload_path'] . $uploadData['file_name'];
    //     }

    //     $updateData = [
    //         'branch_id' => $data['branch_id'],
    //         'class_id' => $data['class_id'],
    //         'section_id' => ($data['section_id'] == 'all') ? null : $data['section_id'],
    //         'description' => $data['description'] ?? null,
    //         'fileType' => $fileType,
    //         'file_path' => $file_path,
    //     ];

    //     // Start transaction
    //     $this->db->trans_start();

    //     // Update gallery
    //     $this->db->where('id', $id);
    //     $this->db->update('tbl_gallery', $updateData);
    //     $galleryAffected = $this->db->affected_rows();

    //     // Update student mappings only if section is specific and students are provided
    //     $studentAffected = 0;
    //     if (!empty($data['section_id']) && $data['section_id'] !== 'all' && !empty($data['student_ids'])) {
    //         $this->db->where('gallery_id', $id)->delete('tbl_gallery_student');

    //         $insertData = [];
    //         foreach ($data['student_ids'] as $studentId) {
    //             $insertData[] = [
    //                 'gallery_id' => $id,
    //                 'student_id' => $studentId,
    //             ];
    //         }

    //         if (!empty($insertData)) {
    //             $this->db->insert_batch('tbl_gallery_student', $insertData);
    //             $studentAffected = $this->db->affected_rows();
    //         }
    //     }

    //     // Complete transaction
    //     $this->db->trans_complete();

    //     // If transaction failed
    //     if ($this->db->trans_status() === false) {
    //         return ['status' => 'error', 'error' => 'Transaction failed. Please try again.'];
    //     }

    //     // Check if any rows were updated/inserted
    //     if ($galleryAffected > 0 || $studentAffected > 0) {
    //         return ['status' => 'success'];
    //     }

    //     return ['status' => 'error', 'error' => 'No changes detected or database update failed.'];
    // }



    public function updateGallery($data)
    {
        $id = $data['id'];

        $existing = $this->db->get_where('tbl_gallery', ['id' => $id])->row_array();
        if (!$existing) {
            log_message('error', "Gallery not found with ID: {$id}");
            return ['status' => 'error', 'error' => 'Gallery record not found.'];
        }

        $upload_path = 'uploads/gallery/';
        $file_path = $data['old_file'] ?? $existing['file_path'];
        $fileType = $existing['file_type'] ?? null;

        if (!empty($_FILES['file_path']['name'])) {
            $fileExtension = strtolower(pathinfo($_FILES['file_path']['name'], PATHINFO_EXTENSION));
            $fileType = $this->getFileType($fileExtension);

            if ($fileType === 'unknown') {
                log_message('error', "Unknown file type for extension: {$fileExtension}");
                return ['status' => 'error', 'error' => 'Invalid file type.'];
            }

            $uploadConfig = [
                'encrypt_name' => true,
                'upload_path' => $upload_path . $fileType . '/',
                'max_size' => 1024 * 1024 * 10,
                'allowed_types' => $this->getAllowedFileTypes($fileType),
            ];

            if (!is_dir($uploadConfig['upload_path'])) {
                if (!mkdir($uploadConfig['upload_path'], 0755, true)) {
                    log_message('error', 'Failed to create upload directory: ' . $uploadConfig['upload_path']);
                    return ['status' => 'error', 'error' => 'Upload directory creation failed.'];
                }
            }

            $this->load->library('upload');
            $this->upload->initialize($uploadConfig);

            if (!$this->upload->do_upload('file_path')) {
                $uploadError = strip_tags($this->upload->display_errors());
                log_message('error', 'File upload failed: ' . $uploadError);
                return ['status' => 'error', 'error' => $uploadError];
            }

            $uploadData = $this->upload->data();
            $file_path = $uploadConfig['upload_path'] . $uploadData['file_name'];
        }

        $updateData = [
            'branch_id' => $data['branch_id'],
            'class_id' => $data['class_id'],
            'section_id' => ($data['section_id'] == 'all') ? null : $data['section_id'],
            'description' => $data['description'] ?? null,
            'file_type' => $fileType,
            'file_path' => $file_path,
        ];

        // Start transaction
        $this->db->trans_start();

        // Update gallery
        $this->db->where('id', $id);
        $this->db->update('tbl_gallery', $updateData);
        if ($this->db->error()['code']) {
            log_message('error', 'Gallery update failed: ' . print_r($this->db->error(), true));
        }
        $galleryAffected = $this->db->affected_rows();

        $studentAffected = 0;
        if (!empty($data['section_id']) && $data['section_id'] !== 'all' && !empty($data['student_ids'])) {
            // Remove existing
            $this->db->where('gallery_id', $id)->delete('tbl_gallery_student');
            if ($this->db->error()['code']) {
                log_message('error', 'Gallery student delete failed: ' . print_r($this->db->error(), true));
            }

            $insertData = [];
            foreach ($data['student_ids'] as $studentId) {

                if (!is_numeric($studentId)) {
                    log_message('error', 'Invalid student ID format: ' . $studentId);
                    continue;
                }

                $insertData[] = [
                    'gallery_id' => $id,
                    'student_id' => $studentId,
                ];
            }

            log_message('debug', 'Inserting student records: ' . json_encode($insertData));

            if (!empty($insertData)) {
                $result = $this->db->insert_batch('tbl_gallery_student', $insertData);

                $errorCode = $this->db->error()['code'];
                $errorMessage = $this->db->error()['message'];

                log_message('debug', 'Insert batch result: ' . ($result ? 'true' : 'false'));
                log_message('debug', 'DB Error Code: ' . $errorCode);
                log_message('debug', 'DB Error Message: ' . $errorMessage);

                if ($this->db->error()['code']) {
                    log_message('error', 'Gallery student insert failed: ' . print_r($this->db->error(), true));
                }
                $studentAffected = $this->db->affected_rows();
            }
        }

        $this->db->trans_complete();

        // If transaction failed
        if ($this->db->trans_status() === false) {
            log_message('error', 'Transaction failed on updateGallery().');
            return ['status' => 'error', 'error' => 'Transaction failed. Please try again.'];
        }

        // Check if any data was updated
        if ($galleryAffected > 0 || $studentAffected > 0) {
            return ['status' => 'success'];
            log_message('debug', 'Students affected: ' . $studentAffected);
        }

        return ['status' => 'error', 'error' => 'No changes detected or database update failed.'];
    }

    private function getFileType($extension)
    {
        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];
        $videoExtensions = ['mp4', 'avi', 'mov', 'mkv'];

        if (in_array($extension, $imageExtensions, true)) {
            return 'image';
        } elseif (in_array($extension, $videoExtensions, true)) {
            return 'video';
        }

        return 'unknown';
    }

    private function getAllowedFileTypes($fileType)
    {
        $allowedTypes = [
            'image' => 'jpg|jpeg|png|gif|bmp|webp',
            'video' => 'mp4|avi|mov|mkv'
        ];

        return $allowedTypes[$fileType] ?? '';
    }



    /**
     * Assign students to the gallery.
     */
    public function assign_students_to_gallery($gallery_id, $student_ids)
    {
        $batch_data = [];
        foreach ($student_ids as $student_id) {
            if (is_numeric($student_id)) {
                $batch_data[] = [
                    'gallery_id' => (int) $gallery_id,
                    'student_id' => (int) $student_id
                ];
            }
        }

        if (!empty($batch_data)) {
            $this->db->insert_batch('tbl_gallery_student', $batch_data);
        }
    }

}