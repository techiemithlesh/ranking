<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @package : Eduproject Global PVT LTD
 * @version : 2.0
 * @developed by : Schoolexcel
 * @support : Mithlesh Patel
 * @author url : Mithlesh Patel
 * @filename : DigitalLibrary.php
 * @copyright : Eduproject Global PVT LTD
 */

class DigitalLibrary extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('digitalLibrary_model');
        $this->load->model('branch_model');
    }

    public function index()
    {

        $this->data['branch_id'] = $this->application_model->get_branch_id();
        $this->data['booklists'] = $this->digitalLibrary_model->getDigitalBookList();


        $this->data['title'] = translate('Smart_library');
        $this->data['sub_page'] = 'digital_library/index';
        $this->data['main_menu'] = 'Smart_library';

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
            redirect(base_url('DigitalLibrary/index'));
        }

        // $this->form_validation->set_rules('branch_id', translate('branch'), 'required');
        $this->form_validation->set_rules('title', translate('title'), 'trim|required');
        $this->form_validation->set_rules('book_url', translate('Book Url'), 'trim|valid_url|required');
        $this->form_validation->set_rules('img_path', translate('Book Img'));

        if ($this->form_validation->run() === TRUE) {
            $post = $this->input->post();
            $response = $this->digitalLibrary_model->saveDigitalBook($post);


            if (is_array($response)) {
                $array = array('status' => 'fail', 'url' => '', 'error' => $response['error']);
            } else {
                if ($response) {
                    $array = array('status' => 'success', 'url' => base_url('DigitalLibrary/index'), 'error' => '');
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

        $this->data['booklists'] = $this->digitalLibrary_model->getBookForAssignBranch();

        $this->data['branches'] = $this->digitalLibrary_model->getBranches();
        $this->data['title'] = translate('Assign Branch');
        $this->data['sub_page'] = 'digital_library/assign_branch';
        $this->data['main_menu'] = 'Smart_library';

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

        $branches = $this->digitalLibrary_model->getBranches();

        $assignedBranchIds = $this->digitalLibrary_model->getAssignedBranches($bookIds);

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

        $result = $this->digitalLibrary_model->assignBranchesToBooks($book_ids, $branch_ids);

        echo $result;
    }


    public function assignClass()
    {
        $this->data['booklists'] = $this->digitalLibrary_model->getBooksForClassAssign();
        // printVar($this->data['booklists']);
        // die;
        $this->data['title'] = translate('Assign Class');
        $this->data['sub_page'] = 'digital_library/assign_class';
        $this->data['main_menu'] = 'Smart_library';

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

    /**
     * Get common classes for selected books
     * Returns class IDs that are common to all selected books
     * 
     * @return void
     */
    public function get_common_classes()
    {
        $book_ids = $this->input->post('book_ids');
        if (empty($book_ids)) {
            echo json_encode(['status' => 'error', 'message' => 'No books selected']);
            return;
        }

        // Initialize result array
        $result = ['status' => 'success', 'class_ids' => []];

        // Get classes for first book
        $this->db->select('class_id');
        $this->db->from('tbl_digital_library_class');
        $this->db->where('digital_id', $book_ids[0]);
        $query = $this->db->get();

        // Get class IDs for first book
        $firstBookClasses = [];
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                $firstBookClasses[] = $row['class_id'];
            }
        }

        // If there's only one book or no classes for first book, return its classes
        if (count($book_ids) == 1 || empty($firstBookClasses)) {
            $result['class_ids'] = $firstBookClasses;
            echo json_encode($result);
            return;
        }

        // For multiple books, find common classes
        $commonClasses = $firstBookClasses;

        // Loop through remaining books to find common classes
        for ($i = 1; $i < count($book_ids); $i++) {
            $bookId = $book_ids[$i];

            // Get classes for current book
            $this->db->select('class_id');
            $this->db->from('tbl_digital_library_class');
            $this->db->where('digital_id', $bookId);
            $query = $this->db->get();

            // Get class IDs for current book
            $currentBookClasses = [];
            if ($query->num_rows() > 0) {
                foreach ($query->result_array() as $row) {
                    $currentBookClasses[] = $row['class_id'];
                }
            }

            // Find intersection with previously common classes
            $commonClasses = array_intersect($commonClasses, $currentBookClasses);

            // If no common classes left, break early
            if (empty($commonClasses)) {
                break;
            }
        }

        // Convert to integer array
        $commonClasses = array_map('intval', $commonClasses);

        // Return common class IDs
        $result['class_ids'] = array_values($commonClasses);
        echo json_encode($result);
    }


    // public function assign_class_to_books()
    // {
    //     $class_id = $this->input->post('class_id');
    //     $book_ids = $this->input->post('book_ids');
    //     $branch_id = $this->input->post('branch_id');

    //     if (empty($class_id) || empty($book_ids)) {
    //         echo json_encode(['status' => 'error', 'message' => 'Class and book selection are required.']);
    //         return;
    //     }

    //     $this->db->trans_begin();

    //     // Remove existing class assignments for these books
    //     $this->db->where_in('digital_id', $book_ids)->delete('tbl_digital_library_class');

    //     // Prepare new assignment data
    //     $assignData = [];
    //     foreach ($book_ids as $bookId) {
    //         $assignData[] = [
    //             'digital_id' => $bookId,
    //             'class_id' => $class_id,
    //             'branch_id' => $branch_id,
    //             'assigned_by' => get_loggedin_branch_id(),
    //             'created_at' => date('Y-m-d H:i:s'),
    //         ];
    //     }

    //     // Insert new class assignments
    //     $this->db->insert_batch('tbl_digital_library_class', $assignData);

    //     if ($this->db->trans_status() === false) {
    //         $this->db->trans_rollback();
    //         echo json_encode(['status' => 'error', 'message' => 'Failed to assign class to books.']);
    //     } else {
    //         $this->db->trans_commit();
    //         echo json_encode(['status' => 'success', 'message' => 'Class successfully assigned to selected books.']);
    //     }
    // }


    public function assign_class_to_books()
    {
        $class_ids = $this->input->post('class_id');
        $book_ids = $this->input->post('book_ids');
        $branch_id = $this->input->post('branch_id');

        if (empty($class_ids) || empty($book_ids)) {
            echo json_encode(['status' => 'error', 'message' => 'Class and book selection are required.']);
            return;
        }

        $this->db->trans_begin();

        // Don’t delete existing assignments — just avoid duplicates

        $this->db->where_in('digital_id', $book_ids)->delete('tbl_digital_library_class');

        $assignData = [];

        foreach ($book_ids as $bookId) {
            foreach ($class_ids as $class_id) {

                $exists = $this->db->get_where('tbl_digital_library_class', [
                    'digital_id' => $bookId,
                    'class_id' => $class_id,
                    'branch_id' => $branch_id,
                ])->num_rows();

                if (!$exists) {
                    $assignData[] = [
                        'digital_id' => $bookId,
                        'class_id' => $class_id,
                        'branch_id' => $branch_id,
                        'assigned_by' => get_loggedin_user_id(),
                        'created_at' => date('Y-m-d H:i:s'),
                    ];
                }
            }
        }

        if (!empty($assignData)) {
            $this->db->insert_batch('tbl_digital_library_class', $assignData);
        }

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            echo json_encode(['status' => 'error', 'message' => 'Failed to assign class to books.']);
        } else {
            $this->db->trans_commit();
            echo json_encode(['status' => 'success', 'message' => 'Classes successfully assigned to selected books.']);
        }
    }


    public function deleteBooks($id)
    {
        if (!is_superadmin_loggedin()) {
            return redirect('index');
        }

        $this->db->trans_begin();

        $book_img = $this->db->select('book_img')
            ->where('id', $id)
            ->get('tbl_digital_library')
            ->row()
            ->book_img;

        if (!$book_img) {
            return ['error' => 'Book not found.'];
        }

        $this->db->where('digital_id', $id);
        $this->db->delete('tbl_digital_library_branch');


        $this->db->where('digital_id', $id);
        $this->db->delete('tbl_digital_library_class');


        $this->db->where('id', $id);
        $this->db->delete('tbl_digital_library');

        if ($this->db->affected_rows() > 0) {
            // Delete book image file
            if (file_exists($book_img)) {
                unlink($book_img);
            }

            $this->db->trans_commit();
            return ['success' => 'Digital Book deleted successfully.'];
        } else {
            $this->db->trans_rollback();
            return ['error' => 'Failed to delete the book.'];
        }
    }

    public function update()
    {
        if ($_POST) {

            $this->form_validation->set_rules('title', translate('title'), 'trim|required');
            $this->form_validation->set_rules('status', translate('status'), 'trim|required');
            $this->form_validation->set_rules('book_url', translate('book url', 'trim|valid_url|required'));

            if ($this->form_validation->run() === FALSE) {
                $this->session->set_flashdata('error', validation_errors());
                redirect('training');
            } else {

                $post = $this->input->post();
                // printVar($post);
                // die;
                $post['id'] = $this->input->post('book_id');

                $response = $this->digitalLibrary_model->updateDigitalBook($post);

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
}
