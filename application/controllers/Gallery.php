<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @package : Eduproject Global PVT LTD
 * @version : 2.0
 * @developed by : Schoolexcel
 * @support : Mithlesh Patel
 * @author url : Mithlesh Patel
 * @filename : Gallery.php
 * @copyright : Eduprojects Global PVT LTD
 */

class Gallery extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('gallery_model');
        $this->load->model('application_model');
        $this->load->model('student_model');
    }

    public function index()
    {

        if (!get_permission('digital_gallery', 'is_view')) {
            access_denied();
        }

        if ($_POST) {

            if (!get_permission('digital_gallery', 'is_add')) {
                ajax_access_denied();
            } else {
                if (is_admin_loggedin()) {
                    $branchId = $this->input->post('branch_id');
                } else {
                    $branchId = get_loggedin_branch_id();
                }

                $classId = $this->input->post('class_id');
                $sectionId = $this->input->post('section_id');

                $this->data['gallery'] = $this->gallery_model->getGallery($branchId, $classId, $sectionId);
                set_alert('success', translate('information_has_been_saved_successfully'));
                $url = base_url('gallery');
                $array = array('status' => 'success', 'url' => $url);
            }

        }

        $this->data['title'] = translate('my_Gallery');
        $this->data['sub_page'] = 'gallery/index';
        $this->data['main_menu'] = 'my_gallery';

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

    public function create()
    {
        if (!get_permission('digital_gallery', 'is_add')) {
            access_denied();
        }

        $this->data['title'] = translate('upload_my_Gallery');
        $this->data['sub_page'] = 'gallery/create';
        $this->data['main_menu'] = 'my_gallery';

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

    public function upload_media()
    {
        if (is_superadmin_loggedin()) {
            $this->form_validation->set_rules('branch_id', translate('branch'), 'required');
        }

        $this->form_validation->set_rules('class_id', translate('class'), 'trim|required');
        $this->form_validation->set_rules('section_id', translate('section'), 'trim|required');

        $branch_id = is_superadmin_loggedin() ? $this->input->post('branch_id') : get_loggedin_branch_id();


        if ($this->form_validation->run() === FALSE) {
            $response = ['status' => 'error', 'error' => validation_errors()];
        } else {
            $post = $this->input->post();
            $post['branch_id'] = $branch_id;

            $result = $this->gallery_model->saveGallery($post);

            if ($result['status'] === 'success') {
                // Handle student assignments only after successful file upload
                $studentIds = $this->input->post('student_ids');
                if (!empty($studentIds)) {
                    $this->gallery_model->assign_students_to_gallery($result['file_id'], $studentIds);
                }
                $response = ['status' => 'success', 'message' => 'Gallery Uploaded Successfully !', 'url' => base_url('gallery/index')];
            } else {
                $response = ['status' => 'error', 'error' => $result['error']];
            }
        }

        echo json_encode($response);
    }

    public function upload_media_bulk()
    {
        if (is_superadmin_loggedin()) {
            $this->form_validation->set_rules('branch_id', translate('branch'), 'required');
        }

        $this->form_validation->set_rules('class_id', translate('class'), 'trim|required');
        $this->form_validation->set_rules('section_id', translate('section'), 'trim|required');

        $branch_id = is_superadmin_loggedin() ? $this->input->post('branch_id') : get_loggedin_branch_id();

        if ($this->form_validation->run() === FALSE) {
            $response = ['status' => 'error', 'error' => validation_errors()];
        } else {
            $post = $this->input->post();
            $post['branch_id'] = $branch_id;

            $uploadResults = $this->gallery_model->saveMultipleGallery($post);

            if (!empty($uploadResults['success'])) {
                // Assign students if provided
                $studentIds = $this->input->post('student_ids');
                foreach ($uploadResults['success'] as $file_id) {
                    if (!empty($studentIds)) {
                        $this->gallery_model->assign_students_to_gallery($file_id, $studentIds);
                    }
                }

                $response = ['status' => 'success', 'message' => 'Files uploaded successfully!', 'url' => base_url('gallery/index')];
            } else {
                $response = ['status' => 'error', 'error' => $uploadResults['error'] ?? 'Upload failed'];
            }
        }

        echo json_encode($response);
    }

    public function edit($id = '')
    {
        if (!get_permission('digital_gallery', 'is_edit')) {
            access_denied();
        }

        $gallery = $this->gallery_model->getGalleryById($id);
        $this->data['gallery'] = $gallery;

        $query = $this->student_model->getStudentList(
            $gallery['class_id'],
            $gallery['section_id'],
            $gallery['branch_id']
        );
        $this->data['students'] = $query->result_array();

        $selected_students = $this->db->select('student_id')
            ->where('gallery_id', $id)
            ->get('tbl_gallery_student')
            ->result_array();

        $is_for_all_students = false;

        if (empty($selected_students)) {
            // No specific students, so it's for the whole class-section
            $this->db->select('enroll.student_id');
            $this->db->from('enroll');
            $this->db->where('enroll.class_id', $gallery['class_id']);
            $this->db->where('enroll.section_id', $gallery['section_id']);
            $this->db->where('enroll.session_id', get_session_id());
            $student_result = $this->db->get()->result_array();
            $selected_students = array_column($student_result, 'student_id');

            $is_for_all_students = true;
        } else {
            $selected_students = array_column($selected_students, 'student_id');
        }

        $this->data['selected_students'] = $selected_students;
        $this->data['is_for_all_students'] = $is_for_all_students;

        $this->data['title'] = translate('Edit_my_Gallery');
        $this->data['sub_page'] = 'gallery/edit';
        $this->data['main_menu'] = 'my_gallery';

        // printVar($this->data['selected_students']);
        // die;

        $this->data['headerelements'] = array(
            'css' => array('vendor/dropify/css/dropify.min.css'),
            'js' => array('vendor/dropify/js/dropify.min.js'),
        );

        $this->load->view('layout/index', $this->data);
    }

    public function update($id)
    {
        if (!get_permission('digital_gallery', 'is_edit')) {
            access_denied();
        }

        $this->form_validation->set_rules('class_id', 'Class', 'required');
        $this->form_validation->set_rules('section_id', 'Section', 'required');

        if ($this->form_validation->run() == false) {
            $this->edit($id);
            return;
        }

        $postData = $this->input->post();
        $postData['id'] = $id;

        $result = $this->gallery_model->updateGallery($postData);

        if ($result['status'] === 'success') {
            set_alert('success', translate('information_updated_successfully'));
        } else {
            set_alert('error', $result['error'] ?? 'An error occurred while updating.');
        }

        redirect('gallery');
    }


    public function get_students_by_section()
    {
        $section_id = $this->input->post('section_id');
        $class_id = $this->input->post('class_id');
        if (!is_superadmin_loggedin()) {
            $branch_id = get_loggedin_branch_id();
        } else {
            $branch_id = $this->input->post('branch_id');
        }

        $this->db->select('s.id, s.first_name, s.last_name');
        $this->db->from('enroll as e');
        $this->db->join('student as s', 's.id = e.student_id', 'inner');
        $this->db->where('e.class_id', $class_id);
        $this->db->where('e.branch_id', $branch_id);
        if ($section_id != 'all') {
            $this->db->where('e.section_id', $section_id);
        }
        $this->db->order_by('s.first_name', 'asc');
        $students = $this->db->get()->result();

        $options = '';
        foreach ($students as $student) {
            $options .= "<option value='{$student->id}'>{$student->first_name} {$student->last_name}</option>";
        }
        echo $options;
    }





    public function delete($id)
    {

        if (!get_permission('digital_gallery', 'is_delete')) {
            access_denied();
        }

        if (empty($id)) {
            set_alert('error', translate('invalid_request'));
            redirect('gallery/index');
        }

        $this->db->select('file_path, section_id');
        $this->db->where('id', $id);
        $query = $this->db->get('tbl_gallery');
        $row = $query->row();

        if (!$row) {
            set_alert('error', translate('record_not_found'));
            redirect('gallery/index');
        }

        // Start transaction
        $this->db->trans_start();

        // Check if gallery ID exists in tbl_gallery_student and delete if present
        $this->db->where('gallery_id', $id);
        if ($this->db->count_all_results('tbl_gallery_student') > 0) {
            $this->db->where('gallery_id', $id);
            $this->db->delete('tbl_gallery_student');
        }

        // Delete from tbl_gallery
        $this->db->where('id', $id);
        $this->db->delete('tbl_gallery');

        // Commit or rollback transaction based on execution status
        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            set_alert('error', translate('delete_failed'));
            redirect('gallery/index');
        } else {
            $this->db->trans_commit();

            $filePath = FCPATH . $row->file_path;
            if (!empty($row->file_path) && file_exists($filePath)) {
                if (!unlink($filePath)) {
                    set_alert('warning', translate('record_deleted_but_file_not_removed'));
                }
            }

            set_alert('success', translate('record_deleted_successfully'));
        }

        // Redirect to gallery page
        redirect('gallery/index');
    }



}


