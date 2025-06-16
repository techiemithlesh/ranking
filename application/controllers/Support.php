<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @package : Eduproject Global PVT LTD
 * @version : 2.0
 * @developed by : Schoolexcel
 * @support : Mithlesh Patel
 * @author url : Mithlesh Patel
 * @filename : Support.php
 * @copyright : Eduproject Global PVT LTD
 */


class Support extends Admin_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('support_model');
        $this->load->library('form_validation');
    }

    public function index()
    {
        $this->data['enquiry'] = $this->support_model->getEnquiry();
        $this->data['title'] = translate('customer_enquiry');
        $this->data['sub_page'] = 'support/index';
        $this->data['main_menu'] = 'support';
        $this->load->view('layout/index', $this->data);
    }

    public function save()
    {
        $this->form_validation->set_rules('name', 'Name', 'trim|required');
        $this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email');
        $this->form_validation->set_rules('phone', 'Phone', 'trim|required');
        $this->form_validation->set_rules('message', 'Message', 'trim|required');

        if ($this->form_validation->run() == FALSE) {
            $response = [
                'status' => false,
                'message' => validation_errors()
            ];
        } else {
            $data = [
                'name' => $this->input->post('name'),
                'email' => $this->input->post('email'),
                'phone' => $this->input->post('phone'),
                'message' => $this->input->post('message'),
                'user_id' => get_loggedin_user_id() ?? null,
                'branch_id' =>  get_loggedin_branch_id() ?? null
            ];

            $insert_id = $this->support_model->saveEnquiry($data);

            if ($insert_id) {
                $response = [
                    'status' => true,
                    'message' => 'Your enquiry has been submitted successfully.'
                ];
            } else {
                $response = [
                    'status' => false,
                    'message' => 'Something went wrong. Please try again.'
                ];
            }
        }

        echo json_encode($response);
    }


    public function delete($id)
    {
        if (!is_superadmin_loggedin()) {
            show_error('Unauthorized', 403);
        }

        if (!is_numeric($id) || empty($id)) {
            $this->session->set_flashdata('error', 'Invalid Enquiry ID.');
            redirect('support/index');
        }

        $enquiry = $this->support_model->getEnquiryById($id);
        if (!$enquiry) {
            $this->session->set_flashdata('error', 'Enquiry not found.');
            redirect('support/index');
        }

        // Delete enquiry
        $deleted = $this->support_model->deleteEnquiry($id);
        if ($deleted) {
            $this->session->set_flashdata('success', 'Enquiry deleted successfully.');
        } else {
            $this->session->set_flashdata('error', 'Failed to delete enquiry.');
        }

        redirect('support/index');
    }


}
