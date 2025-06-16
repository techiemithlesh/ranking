<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Bookuploads_model extends MY_Model
{
    protected $table = 'book_uploads';

    public function __construct()
    {

        parent::__construct();
    }

    public function save($data)
    {
        $classID = (!isset($data['all_class_set']) ? $data['class_id'] : 'unfiltered');
        $date = isset($data['date']) ? date("Y-m-d", strtotime($data['date'])) : date("Y-m-d");

        $arrayData = array(
            'branch_id' => $this->application_model->get_branch_id(),
            'title' => $data['title'],
            'book_url' => $data['book_url'],
            'date' => $date,
            'session_id' => get_session_id(),
            'uploader_id' => get_loggedin_user_id(),
            'class_id' => $classID,
            'subject_id' => get_loggedin_user_id(),
        );

        if (!isset($data['all_class_set']) && !isset($data['subject_wise'])) {
            $arrayData['subject_id'] = $data['subject_id'];
        } else {
            $arrayData['subject_id'] = 'unfiltered';
        }

        // Handle image upload
        if (!isset($data['img_path'])) {

            // Set upload configuration for the image
            $config['upload_path'] = 'uploads/book_images/';
            $config['encrypt_name'] = true;
            $config['allowed_types'] = 'pdf|doc|docx|xls|xlsx|txt|jpg|jpeg|png|gif|bmp|mp4|avi|mov|mkv';
            $this->upload->initialize($config);


            if ($this->upload->do_upload("img_path")) {
                // Save the uploaded image full path to 'book_img' column
                $arrayData['book_img'] = $config['upload_path'] . $this->upload->data('file_name');  // Full path
                // Insert the data into the 'book_uploads' table
                $this->db->insert('book_uploads', $arrayData);
            } else {
                return ['error' => $this->upload->display_errors()];  // Return error if upload fails
            }
        } else {
            // If an image path is provided, handle update logic
            if ($_FILES['img_path']['name'] != "") {
                // Set upload configuration for the image
                $config['upload_path'] = 'uploads/book_images/';
                $config['encrypt_name'] = true;
                $config['allowed_types'] = '*';
                $this->upload->initialize($config);

                // Attempt to upload the new file
                if ($this->upload->do_upload("img_path")) {
                    // Retrieve the old file path from the database
                    $oldFilePath = $this->db->select('book_img')->where('id', $data['attachment_id'])->get('book_uploads')->row()->book_img;

                    // Delete the old file if it exists
                    if (file_exists($oldFilePath)) {
                        unlink($oldFilePath);
                    }

                    // Save the full path of the new image
                    $arrayData['book_img'] = $config['upload_path'] . $this->upload->data('file_name');  // Full path

                    // Update the attachment record in the database
                    $this->db->where('id', $data['attachment_id']);
                    $this->db->update('book_uploads', $arrayData);
                } else {
                    return ['error' => $this->upload->display_errors()];  // Return error if upload fails
                }
            } else {
                // If no file is uploaded, update the attachment data without file changes
                if (!is_superadmin_loggedin()) {
                    $this->db->where('branch_id', get_loggedin_branch_id());
                }
                $this->db->where('id', $data['attachment_id']);
                $this->db->update('book_uploads', $arrayData);
            }
        }

        // Check if the insert or update operation was successful
        log_message('debug', 'Saving book upload with data: ' . json_encode($data));

        if ($this->db->affected_rows() > 0) {
            return true;
        } else {
            return false;
        }
    }


    public function getBookUploadsList()
    {
        // Select the columns you want to retrieve
        $this->db->select('b.*, br.name as branch_name, c.name as class_name, s.name as subject_name');
        $this->db->from('book_uploads as b');
        $this->db->join('class as c', 'c.id = b.class_id', 'left');
        $this->db->join('branch as br', 'br.id = b.branch_id', 'left');
        $this->db->join('subject as s', 's.id = b.subject_id', 'left');

        // Check if the user is not a superadmin, then filter by the logged-in branch
        if (!is_superadmin_loggedin()) {
            $this->db->where('b.branch_id', get_loggedin_branch_id());
        }

        // For students (role_id 6 or 7), filter by class ID
        if (loggedin_role_id() == 6) {
            // Get class ID based on student enrollment
            $classID = $this->db->select('class_id')->where('student_id', get_activeChildren_id())->get('enroll')->row()->class_id;
            $this->db->where('b.class_id', $classID)->or_where('b.class_id', 'unfiltered');
        }

        if (loggedin_role_id() == 7) {
            // Get class ID based on student enrollment
            $classID = $this->db->select('class_id')->where('student_id', get_loggedin_user_id())->get('enroll')->row()->class_id;
            $this->db->where('b.class_id', $classID)->or_where('b.class_id', 'unfiltered');
        }

        // Order by the book upload ID in descending order
        $this->db->order_by('b.id', 'desc');

        // Execute the query
        $query = $this->db->get();

        // Check if there was an error in the query
        if ($this->db->error()['code'] != 0) {
            log_message('error', 'Database error in getBookUploadsList: ' . json_encode($this->db->error()));
            return false;
        }

        // Get the results
        $result = $query->result_array();
        return $result;
    }





}