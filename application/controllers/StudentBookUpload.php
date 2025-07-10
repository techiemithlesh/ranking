<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @package : MULTI SCHOOL MANAGMENT
 * @version : 2.0
 * @developed by : MITHLESH KUMAR
 * @support : mithlesh@knaptix.com
 * @author url : http://codewithmithlesh.com/
 * @filename : StudentBookUpload.php
 * @copyright : tech.schoolexcel
 */


class StudentBookUpload extends Admin_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('studentbook_model');
        $this->load->model('branch_model');
    }


    public function index()
    { 
        $this->data['branch_id'] = $this->application_model->get_branch_id();
        $this->data['digitalbooks'] = $this->studentbook_model->getBookUploadsList3(); 
        $this->data['title'] = translate('student_interactive_book ');
        $this->data['sub_page'] = 'book_upload/index';
        $this->data['main_menu'] = 'BookUpload';

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
            redirect(base_url('StudentBookUpload'));
        }

        // $this->form_validation->set_rules('branch_id', translate('branch'), 'required');
        $this->form_validation->set_rules('title', translate('title'), 'trim|required');
        $this->form_validation->set_rules('book_url', translate('Book Url'), 'trim|valid_url|required');
        $this->form_validation->set_rules('img_path', translate('Book Img'));

        if ($this->form_validation->run() === TRUE) {
            $post = $this->input->post();
            $response = $this->studentbook_model->save($post);
            if (is_array($response)) {
                $array = array('status' => 'fail', 'url' => '', 'error' => $response['error']);
            } else {
                if ($response) {
                    $array = array('status' => 'success', 'url' => base_url('StudentBookUpload'), 'error' => '');
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


    public function assignBranches()
    {
        if (!(is_superadmin_loggedin())) {
            set_alert('error', 'You do not have permission to edit');
            redirect(base_url('StudentBookUpload'));
        }

        // $this->data['attachmentss'] = $this->studentbook_model->get_unassigned_books();
        $this->data['attachmentss'] = $this->studentbook_model->get_all_books();
        $this->data['branches'] = $this->studentbook_model->getBranches();
        $this->data['title'] = translate('Assign Books to Branch');
        $this->data['sub_page'] = 'book_upload/assign_branch';
        $this->data['main_menu'] = 'BookUpload';

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
        if (!is_superadmin_loggedin()) {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
            return;
        }

        $bookIds = $this->input->post('book_ids');
        if (empty($bookIds)) {
            echo json_encode(['status' => 'error', 'message' => 'No books selected']);
            return;
        }

        $branches = $this->studentbook_model->getBranches();

        $assignedBranchIds = $this->studentbook_model->getAssignedBranches($bookIds);
        echo json_encode([
            'status' => 'success',
            'branches' => $branches,
            'assignedBranchIds' => $assignedBranchIds
        ]);
    }
   
    public function assign_branches() {
        $book_ids = $this->input->post('book_ids');
        $new_branch_ids = $this->input->post('branch_ids') ?? [];
    
        if (empty($book_ids)) {
            // log_message('error', 'Book assignment failed: No book IDs provided. POST data: ' . json_encode($_POST));
            echo json_encode(['status' => 'error', 'message' => 'Book IDs are required']);
            return;
        }
    
        try {
            $this->db->trans_start();
            foreach ($book_ids as $book_id) {
                $this->studentbook_model->updateBookBranches($book_id, $new_branch_ids);
            }
            $this->db->trans_complete();
            if ($this->db->trans_status() === FALSE) {
                $error = $this->db->error();
                // log_message('error', 'Book branch assignment transaction failed. Error: ' . json_encode($error) . 
                //            ' POST Data: ' . json_encode($_POST) . 
                //            ' Last Query: ' . $this->db->last_query());
                throw new Exception('Transaction failed');
            }
    
            log_message('info', 'Book branch assignment successful. Books: ' . json_encode($book_ids) . 
                       ' Branches: ' . json_encode($new_branch_ids));
            echo json_encode(['status' => 'success', 'message' => 'Branches updated successfully']);
    
        } catch (Exception $e) {
            log_message('error', 'Book branch assignment exception: ' . $e->getMessage() . 
                       ' Trace: ' . $e->getTraceAsString() . 
                       ' POST Data: ' . json_encode($_POST) . 
                       ' Last Query: ' . $this->db->last_query());
            echo json_encode(['status' => 'error', 'message' => 'Failed to update branches']);
        }
    }


    public function assignClass()
    {
        $this->data['books'] = $this->studentbook_model->getBooksForClassAssign();
        $this->data['title'] = translate('Assign Books to Class');
        $this->data['sub_page'] = 'book_upload/assign_class';
        $this->data['main_menu'] = 'BookUpload';

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

    public function assign_branch_to_books()
    {
        $branch_id = $this->input->post('branch_id');
        $book_ids = $this->input->post('book_ids');

        if (!empty($branch_id) && !empty($book_ids)) {
            foreach ($book_ids as $book_id) {
                $data = array('branch_id' => $branch_id);
                $this->db->where('id', $book_id);
                $this->db->update('student_books', $data);
            }

            $response = array(
                'status' => 'success',
                'message' => 'Books successfully assigned to the branch!'
            );
        } else {
            $response = array(
                'status' => 'error',
                'message' => 'Please select a branch and at least one book.'
            );
        }

        header('Content-Type: application/json');
        echo json_encode($response);
    }

   
    public function update()
     {
         if ($_POST) {
 
             $this->form_validation->set_rules('title', translate('title'), 'trim|required');
             $this->form_validation->set_rules('status', translate('status'), 'trim|required');
             $this->form_validation->set_rules('book_url', translate('book url', 'trim|valid_url|required'));
 
             if ($this->form_validation->run() === FALSE) {
                 $this->session->set_flashdata('error', validation_errors());
                 redirect('StudentBookUpload/index');
             } else {
                 $post = $this->input->post();
                 $post['id'] = $this->input->post('book_id');
                 $response = $this->studentbook_model->updateBook($post);
                 if (is_array($response)) {
                     set_alert('error', $response['error']);
                 } else {
                     if ($response) {
                         // set_alert('success', translate('interactive_book_has_been_update_successfully'));
                     }
                 }
 
                 $url = base_url('StudentBookUpload/index');
                 echo json_encode(['status' => 'success', 'url' => $url, 'error' => '']);
             }
         }
     }


    public function assign_class_to_books()
    {
        // Enable query debugging
        // $this->db->db_debug = TRUE;
        $class_id = $this->input->post('class_id');
        $book_ids = $this->input->post('book_ids');
        $branch_id = $this->input->post('branch_id');

        if (empty($class_id) || empty($book_ids) || empty($branch_id)) {
            $debug_msg = 'Validation failed - ';
            $debug_msg .= 'Class ID: ' . ($class_id ? 'Present' : 'Missing');
            $debug_msg .= ', Book IDs: ' . ($book_ids ? 'Present' : 'Missing');
            $debug_msg .= ', Branch ID: ' . ($branch_id ? 'Present' : 'Missing');

            $response = array(
                'status' => 'error',
                'message' => 'Invalid input data. Please provide all required fields.',
                'debug' => $debug_msg
            );
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($response));
            return;
        }

        try {
           
            $this->db->trans_start();
            foreach ($book_ids as $book_id) {
                $branch_data = array(
                    'book_id' => $book_id,
                    'branch_id' => $branch_id,
                    'status' => 1
                );
                $branch_exists = $this->db->get_where('book_branches', array(
                    'book_id' => $book_id,
                    'branch_id' => $branch_id
                ))->row();

                if (!$branch_exists) {
                    $this->db->insert('book_branches', $branch_data);
                    if ($this->db->affected_rows() == 0) {
                        throw new Exception('Failed to insert into book_branches');
                    }
                }

                $class_data = array(
                    'book_id' => $book_id,
                    'class_id' => $class_id,
                    'branch_id' => $branch_id
                );

                $class_exists = $this->db->get_where('class_books', array(
                    'book_id' => $book_id,
                    'branch_id' => $branch_id
                ))->row();

                if ($class_exists) {
                    
                    $this->db->where('book_id', $book_id);
                    $this->db->where('branch_id', $branch_id);
                    $this->db->update('class_books', $class_data);
                   
                } else {
                    $this->db->insert('class_books', $class_data);
                    if ($this->db->affected_rows() == 0) {
                        throw new Exception('Failed to insert into class_books');
                    }
                }
            }

            // Get any database errors
            $db_error = $this->db->error();
            if ($db_error['code'] !== 0) {
                log_message('error', 'Database Error: ' . print_r($db_error, true));
                throw new Exception('Database Error: ' . $db_error['message']);
            }

            // Complete transaction
            $this->db->trans_complete();

            if ($this->db->trans_status() === FALSE) {
                $error = $this->db->error();
                log_message('error', 'Transaction failed. Last error: ' . print_r($error, true));
                throw new Exception('Database transaction failed. Error: ' . $error['message']);
            }

            log_message('debug', 'Transaction completed successfully');

            $response = array(
                'status' => 'success',
                'message' => 'Books assigned to the class and branch successfully!'
            );
        } catch (Exception $e) {
            log_message('error', 'Exception caught: ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());

            // Get the last query that was run
            $last_query = $this->db->last_query();
            log_message('debug', 'Last query before error: ' . $last_query);

            $response = array(
                'status' => 'error',
                'message' => 'An error occurred while assigning books: ' . $e->getMessage(),
                'debug_info' => [
                    'last_query' => $last_query,
                    'db_error' => $this->db->error(),
                    'exception' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]
            );
        }

        log_message('debug', 'Final response: ' . print_r($response, true));

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }

    public function bookUploadEdit($id)
    {
        if (!get_permission('student_book_upload_edit', 'is_edit')) {
            access_denied();
        }

        $student_book_upload_db = $this->db->where('id', $id)->get('book_uploads')->row_array();

        $this->data['branch_id'] = $this->application_model->get_branch_id();
        $this->data['data'] = $this->db->where('id', $id)->get('book_uploads')->row_array();

        $this->data['title'] = translate('upload_content');
        $this->data['sub_page'] = 'book_upload/student_book_edit';
        $this->data['main_menu'] = 'BookUpload';

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


    public function deleteBooks($id)
    {
        if (get_permission('book_uploads', 'is_delete')) {

            // Get book image path
            $book_img = $this->db->select('book_img')
                ->where('id', $id)
                ->get('student_books')
                ->row()
                ->book_img;

            if (!$book_img) {
                return ['error' => 'Book not found.'];
            }

            // Check for superadmin or branch-specific deletion
            if (!is_superadmin_loggedin()) {
                $this->db->where('branch_id', get_loggedin_branch_id());
                $this->db->where('uploader_id', get_loggedin_user_id());
            }

            // Delete related records from book_branches
            $this->db->where('book_id', $id);
            $this->db->delete('book_branches');

            // Delete related records from class_books (if applicable)
            $this->db->where('book_id', $id);
            $this->db->delete('class_books');

            // Delete the record from student_books
            $this->db->where('id', $id);
            $this->db->delete('student_books');

            if ($this->db->affected_rows() > 0) {
                // Delete book image file
                if (file_exists($book_img)) {
                    unlink($book_img);
                }
                return ['success' => 'Book deleted successfully.'];
            } else {
                return ['error' => 'Failed to delete the book.'];
            }
        } else {
            return ['error' => 'You do not have permission to delete books.'];
        }
    }

}


