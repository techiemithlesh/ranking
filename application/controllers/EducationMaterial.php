<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @package : Eduproject Global PVT LTD
 * @version : 2.0
 * @developed by : Schoolexcel
 * @support : Mithlesh Patel
 * @author url : Mithlesh Patel
 * @filename : EducationMaterial.php
 * @copyright : Eduproject Global PVT LTD
 */


class EducationMaterial extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('educationMaterial_model');

    }

    public function test(){
        echo "Print";
    }

    public function index()
    {

        if (is_superadmin_loggedin()) {
            $branchId = $this->input->post('branch_id');
        } elseif (is_admin_loggedin()) {
            $branchId = get_loggedin_branch_id();
        }
        if ($_POST) {
            $fileType = $this->input->post('file_type');
            $this->data['marketing'] = $this->educationMaterial_model->get_all_materials($branchId, $fileType);
            // printVar($this->data['marketing']);
            // die;
        }

        $this->data['file_types'] = [
            'image' => 'Pamphlets',
            'video' => 'Videos',
            'pdf' => 'Brochures'
        ];

        $this->data['title'] = translate('Educational Material');
        $this->data['sub_page'] = 'educational_material/index';
        $this->data['main_menu'] = 'Resources';

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
            $response = ['status' => 'error', 'error' => 'You do not have permission to perform this action'];
            echo json_encode($response);
            return;
        }

        $this->form_validation->set_rules('branch_id', translate('branch'), 'required');
        $this->form_validation->set_rules('title', translate('title'), 'trim|required');
        $this->form_validation->set_rules('file_type', translate('File Type'), 'trim|required');

        if ($this->form_validation->run() === FALSE) {
            $response = ['status' => 'error', 'error' => validation_errors()];
        } else {
            $post = $this->input->post();
            $saveResponse = $this->educationMaterial_model->saveMaterial($post);

            if (isset($saveResponse['error'])) {
                $response = ['status' => 'error', 'error' => $saveResponse['error']];
            } else {
                $response = ['status' => 'success', 'url' => base_url('EducationMaterial/index')];
            }
        }

        echo json_encode($response);
    }


    public function update()
    {
        if ($_POST) {
            // Form validation
            $this->form_validation->set_rules('branch_id', translate('branch'), 'required');
            $this->form_validation->set_rules('title', translate('title'), 'trim|required');
            $this->form_validation->set_rules('file_type', translate('File Type'), 'trim|required');

            if ($this->form_validation->run() === FALSE) {
                $this->session->set_flashdata('error', validation_errors());
                redirect('EducationMaterial/index');
            } else {
                
                $post = $this->input->post();
                $post['id'] = $this->input->post('education_id');

                
                $response = $this->educationMaterial_model->update($post);

                if (is_array($response)) {
                    
                    set_alert('error', $response['error']);
                } else {
                    if ($response) {
                        // Display success message
                        set_alert('success', translate('education_material_has_been_update_successfully'));
                    }
                }


                $url = base_url('EducationMaterial/index');
                echo json_encode(['status' => 'success', 'url' => $url, 'error' => '']);
            }
        }
    }

    public function toggleStatus()
    {
        $id = $this->input->post('id');
        $status = $this->input->post('status');

        if (!isset($id) || !isset($status)) {
            echo json_encode(['success' => false, 'message' => 'Invalid request']);
            return;
        }

        $update = $this->educationMaterial_model->updateStatus($id, $status);

        if ($update) {
            echo json_encode(['success' => true, 'message' => 'Status updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update status']);
        }
    }


    public function delete($id)
    {

        if (empty($id)) {
            set_alert('error', translate('invalid_request'));
            redirect('EducationMaterial/index');
        }

        $this->db->select('file_path');
        $this->db->where('id', $id);
        $query = $this->db->get('tbl_educational_material');
        $row = $query->row();

        if (!$row) {
            set_alert('error', translate('record_not_found'));
            redirect('educationMaterial/index');
        }

        $this->db->where('id', $id);
        $this->db->delete('tbl_educational_material');

        if ($this->db->affected_rows() > 0) {

            if (!empty($row->file_path) && file_exists($row->file_path)) {
                if (unlink($row->file_path)) {
                    set_alert('success', translate('record_deleted_successfully'));
                } else {
                    set_alert('warning', translate('record_deleted_but_file_not_removed'));
                }
            } else {
                set_alert('success', translate('record_deleted_successfully'));
            }
        } else {
            set_alert('error', translate('delete_failed'));
        }

        // Redirect to the marketing page
        redirect('EducationMaterial/index');
    }

}