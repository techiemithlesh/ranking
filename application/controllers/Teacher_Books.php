<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @package : Eduproject Global PVT LTD
 * @version : 2.0
 * @developed by : Schoolexcel
 * @support : Mithlesh Patel
 * @author url : Mithlesh Patel
 * @filename : Teacher_Books.php
 * @copyright : Eduproject Global PVT LTD
 */


class Teacher_Books extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('teacherBooks_model');

    }

    public function index()
    {

        $user = $this->session->userdata();

        $this->data['booklists'] = $this->teacherBooks_model->getTeacherBookList();
        // get_loggedin_user_id();
        // printVar($user);
        // die;
        $this->data['title'] = translate('Teacher_materials');
        $this->data['sub_page'] = 'teacherbooks/index';
        $this->data['main_menu'] = 'Teachers_book';
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
        if (!(is_superadmin_loggedin())) {
            set_alert('error', 'You do not have permission to edit');
            redirect(base_url('Teacher_Books/index'));
        }

        $this->form_validation->set_rules('title', translate('title'), 'trim|required');
        $this->form_validation->set_rules('book_url', translate('Book Url'), 'trim|valid_url|required');
        $this->form_validation->set_rules('img_path', translate('Book Img'));

        if ($this->form_validation->run() === TRUE) {
            $post = $this->input->post();

            $response = $this->teacherBooks_model->saveTeacherBook($post);


            if (is_array($response)) {
                $array = array('status' => 'fail', 'url' => '', 'error' => $response['error']);
            } else {
                if ($response) {
                    $array = array('status' => 'success', 'url' => base_url('Teacher_Books/index'), 'error' => '');
                } else {
                    $array = array('status' => 'fail', 'url' => '', 'error' => 'Failed to save data.');
                }
            }
        } else {
            $error = $this->form_validation->error_array();
            $array = array('status' => 'fail', 'url' => '', 'error' => $error);
        }

        echo json_encode($array);
    }

    public function assignBranch()
    {

        $this->data['booklists'] = $this->teacherBooks_model->getBookForAssignBranch();

        $this->data['branches'] = $this->teacherBooks_model->getBranches();
        $this->data['title'] = translate('Assign Branch');
        $this->data['sub_page'] = 'teacherbooks/assign_branch';
        $this->data['main_menu'] = 'Teacher_materials';

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


    public function get_assigned_branches()
    {
        // Ensure the user has permission
        if (!is_superadmin_loggedin()) {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
            return;
        }

        $bookIds = $this->input->post('book_ids');
        if (empty($bookIds)) {
            echo json_encode(['status' => 'error', 'message' => 'No books selected']);
            return;
        }

        $branches = $this->teacherBooks_model->getBranches();

        $assignedBranchIds = $this->teacherBooks_model->getAssignedBranches($bookIds);

        echo json_encode([
            'status' => 'success',
            'branches' => $branches,
            'assignedBranchIds' => $assignedBranchIds
        ]);
    }


    public function assign_branches()
    {
        $book_ids = $this->input->post('book_ids');
        $branch_ids = $this->input->post('branch_ids');

        if (empty($book_ids)) {
            echo json_encode(['status' => 'error', 'message' => 'Book IDs are required']);
            return;
        }

        // Handle empty branch IDs (unassign all branches)
        if (empty($branch_ids)) {
            $branch_ids = [];
        }

        $result = $this->teacherBooks_model->assignBranchesToBooks($book_ids, $branch_ids);

        echo $result;
    }


    public function assignClass()
    {
        $this->data['booklists'] = $this->teacherBooks_model->getBooksForClassAssign();
        $this->data['title'] = translate('Assign Class');
        $this->data['sub_page'] = 'teacherbooks/assign_class';
        $this->data['main_menu'] = 'Teacher_materials';

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


    public function assign_class_to_books()
    {
        $class_id = $this->input->post('class_id');
        $book_ids = $this->input->post('book_ids');
        $branch_id = $this->input->post('branch_id');

        if (empty($class_id) || empty($book_ids)) {
            echo json_encode(['status' => 'error', 'message' => 'Class and book selection are required.']);
            return;
        }

        $this->db->trans_begin();

        // Remove existing class assignments for these books
        $this->db->where_in('teacher_book_id', $book_ids)->delete('tbl_teacher_books_class');

        // Prepare new assignment data
        $assignData = [];
        foreach ($book_ids as $bookId) {
            $assignData[] = [
                'teacher_book_id' => $bookId,
                'class_id' => $class_id,
                'branch_id' => $branch_id,
                'assigned_by' => get_loggedin_branch_id(), // Track who made the assignment
                'created_at' => date('Y-m-d H:i:s'),
            ];
        }

        // Insert new class assignments
        $this->db->insert_batch('tbl_teacher_books_class', $assignData);

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            echo json_encode(['status' => 'error', 'message' => 'Failed to assign class to books.']);
        } else {
            $this->db->trans_commit();
            echo json_encode(['status' => 'success', 'message' => 'Class successfully assigned to selected books.']);
        }
    }

}