<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}


class Training_model extends MY_Model
{


    public function __construct()
    {
        parent::__construct();


    }

    public function getList()
    {
        $this->db->select('*');
        $this->db->from('training_materials');
        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            return $query->result_array();
        } else {
            return [];
        }
    }


    public function save($data)
    {
        $arrayData = array(
            'title' => $data['title'],
            'video_url' => $data['video_url'],
            'uploader_id' => get_loggedin_user_id(),

        );

        // Handle image upload
        if (!isset($data['thumbnail_path'])) {

            $config['upload_path'] = 'uploads/training/';
            $config['encrypt_name'] = true;
            $config['allowed_types'] = 'jpg|jpeg|png|gif|bmp';
            $this->upload->initialize($config);


            if ($this->upload->do_upload("thumbnail_path")) {

                $arrayData['thumbnail_path'] = $config['upload_path'] . $this->upload->data('file_name');

                $this->db->insert('training_materials', $arrayData);
            } else {
                return ['error' => $this->upload->display_errors()];
            }
        }

        // log_message('debug', 'Saving Training upload with data: ' . json_encode($data));

        if ($this->db->affected_rows() > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function updateMaterial($data)
    {
        $arrayData = array(
            'title' => $data['title'],
            'video_url' => $data['video_url'],
            'uploader_id' => get_loggedin_user_id(),
        );

        $this->db->where('id', $data['id']);
        $existingMaterial = $this->db->get('training_materials')->row();

        if ($existingMaterial) {
           
            if (isset($_FILES['thumbnail_path']) && !empty($_FILES['thumbnail_path']['name'])) {
                // Upload new thumbnail
                $config['upload_path'] = 'uploads/training/';
                $config['encrypt_name'] = true;
                $config['allowed_types'] = 'jpg|jpeg|png|gif|bmp';
                $this->upload->initialize($config);

                if ($this->upload->do_upload('thumbnail_path')) {
                    // Get the uploaded file data
                    $uploadData = $this->upload->data();

                    // Delete the old thumbnail if it exists
                    if (!empty($existingMaterial->thumbnail_path) && file_exists($existingMaterial->thumbnail_path)) {
                        unlink($existingMaterial->thumbnail_path);
                    }

                    // Set the new thumbnail path
                    $arrayData['thumbnail_path'] = $config['upload_path'] . $uploadData['file_name'];
                } else {
                    // Return upload error
                    return ['error' => $this->upload->display_errors()];
                }
            }

            
            $this->db->where('id', $data['id']);
            $this->db->update('training_materials', $arrayData);

            if ($this->db->affected_rows() > 0) {
                return true;
            } else {
                return false; 
            }
        } else {
            return false; 
        }
    }

    


}