<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @package : Eduproject Global PVT LTD
 * @version : 2.0
 * @developed by : Schoolexcel
 * @support : Mithlesh Patel
 * @author url : Mithlesh Patel
 * @filename : Accounting.php
 * @copyright : Eduproject Global PVT LTD
 */


class Training extends Admin_Controller
{

    public function __construct()
    {
        parent::__construct();

        $this->load->helpers('download');
        $this->load->library('upload');
        $this->load->model('training_model');
    }

    public function index()
    {

        $this->data['training'] = $this->training_model->getList();

        $this->data['title'] = translate('Training Material');
        $this->data['sub_page'] = 'training/index';
        $this->data['main_menu'] = translate('Resources');

        $this->data['headerelements'] = array(
            'css' => array(
                'vendor/dropify/css/dropify.min.css',
            ),
            'js' => array(
                'vendor/dropify/js/dropify.min.js',
            ),
        );

        $this->load->view('layout/index', $this->data);

    }

    public function save()
    {

        if (!is_superadmin_loggedin()) {
            return ['error' => 'You do not have permission to perform this action'];
        }

        $this->form_validation->set_rules('title', translate('title'), 'trim|required');
        $this->form_validation->set_rules('video_url', translate('Video Url'), 'trim|valid_url|required');
        $this->form_validation->set_rules('thumbnail_path', translate('Thumbnail'));

        if ($this->form_validation->run() === FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('training');

        } else {

            $post = $this->input->post();
            $response = $this->training_model->save($post);

            if (is_array($response)) {
                set_alert('error', $response['error']);
            } else {
                if ($response) {
                    set_alert('success', translate('information_has_been_saved_successfully'));
                }
            }

            $url = base_url('training');
            $response = array('status' => 'success', 'url' => $url, 'error' => '');

        }

        echo json_encode($response);

    }


    public function update()
    {
        if ($_POST) {

            $this->form_validation->set_rules('title', translate('title'), 'trim|required');
            $this->form_validation->set_rules('video_url', translate('Video Url'), 'trim|valid_url|required');

            if ($this->form_validation->run() === FALSE) {
                $this->session->set_flashdata('error', validation_errors());
                redirect('training');
            } else {

                $post = $this->input->post();
                $post['id'] = $this->input->post('training_id');

                $response = $this->training_model->updateMaterial($post);

                if (is_array($response)) {
                    set_alert('error', $response['error']);
                } else {
                    if ($response) {
                        set_alert('success', translate('training_material_has_been_update_successfully'));
                    }
                }

                $url = base_url('training');
                echo json_encode(['status' => 'success', 'url' => $url, 'error' => '']);
            }
        }
    }

    public function delete($id)
    {
        $this->db->where('id', $id);

        $this->db->select('thumbnail_path');
        $query = $this->db->get('training_materials');
        $row = $query->row();

        $this->db->where('id', $id);
        $this->db->delete('training_materials');

        
        if ($this->db->affected_rows() > 0 && isset($row->thumbnail_path) && !empty($row->thumbnail_path)) {
            $file_path = './uploads/training/' . $row->thumbnail_path;

            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }
    }
}


