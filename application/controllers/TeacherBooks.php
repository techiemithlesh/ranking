<?php


class TeacherBooks extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helpers('download');
        $this->load->library('upload');
        $this->load->model('teacher_books_model');

    }


    // BOOK LINK UPLOAD
    public function index()
    {
        // check access permission

        if (!get_permission('upload_books_link', 'is_view')) {
            access_denied();
        }

        $this->data['branch_id'] = $this->application_model->get_branch_id();
        $this->data['bookList'] = $this->teacher_books_model->getBookList();
        // print_r($this->data['bookList']);
        // echo '<pre>';
        // print_r($this->data['bookList']);
        // echo '</pre>';
        // exit;
        $this->data['title'] = translate('Book Upload for Teachers');
        $this->data['sub_page'] = 'book_upload/teachers_book';
        $this->data['main_menu'] = 'TeachersBook';

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

        if ($_POST) {

            if (is_superadmin_loggedin()) {
                $this->form_validation->set_rules('branch_id', translate('branch'), 'required');
            }

            $this->form_validation->set_rules('title', translate('title'), 'trim|required');
            $this->form_validation->set_rules('book_url', translate('Book Url'), 'trim|valid_url|required');
            $this->form_validation->set_rules('img_path', translate('Book Img'));

            if (!isset($_POST['all_class_set'])) {
                $this->form_validation->set_rules('class_id', translate('class'), 'trim|required');
                $this->form_validation->set_rules('section_id', translate('section'), 'trim|required');
            }

            if (!isset($_POST['subject_wise']) && !isset($_POST['all_class_set'])) {
                $this->form_validation->set_rules('subject_id', translate('subject'), 'trim|required');
            }

            if ($this->form_validation->run() !== false) {
                // log_message('debug', 'Validation passed, proceeding to save data.');

                $post = $this->input->post();
                $response = $this->teacher_books_model->save($post);

                if (is_array($response)) {
                    set_alert('error', $response['error']);
                } else {
                    if ($response) {
                        set_alert('success', translate('information_has_been_saved_successfully'));
                    }
                }

                $url = base_url('TeacherBooks/index');
                $array = array('status' => 'success', 'url' => $url, 'error' => '');
            } else {
                log_message('error', 'Validation failed: ' . print_r($this->form_validation->error_array(), true));

                $error = $this->form_validation->error_array();
                $array = array('status' => 'fail', 'url' => '', 'error' => $error);
            }
            echo json_encode($array);
        }


    }

    public function delete($id)
    {
        // Log the ID received for deletion
        // log_message('debug', 'Attempting to delete book with ID: ' . $id);

        // Check if the user has delete permissions
        if (!get_permission('book_uploads', 'is_delete')) {
            // log_message('error', 'Permission denied for deleting books.');
            return ['error' => 'You do not have permission to delete books.'];
        }

        // Fetch the book details
        $this->db->select('book_img, branch_id, uploader_id');
        $this->db->from('teacher_books');
        $this->db->where('id', $id);

        // Apply additional filters for non-superadmins
        if (!is_superadmin_loggedin()) {
            // log_message('debug', 'Applying additional filters for non-superadmin.');
            $this->db->where('branch_id', get_loggedin_branch_id());
            $this->db->where('uploader_id', get_loggedin_user_id());
        }

        $book = $this->db->get()->row();

        // Log the fetched book details
        // log_message('debug', 'Fetched book details: ' . print_r($book, true));

        // Check if the book exists
        if (!$book) {
            // log_message('error', 'Book not found or access denied for ID: ' . $id);
            return ['error' => 'Book not found or access denied.'];
        }

        // Attempt to delete the record
        $this->db->where('id', $id);
        $this->db->delete('teacher_books');

        // Log the deletion query
        // log_message('debug', 'Delete query executed. Affected rows: ' . $this->db->affected_rows());

        // Check if the deletion was successful
        if ($this->db->affected_rows() > 0) {
            // log_message('debug', 'Record deleted successfully. Attempting to delete file: ' . FCPATH . $book->book_img);

            // Check if the file exists and delete it
            if (file_exists(FCPATH . $book->book_img)) {
                if (unlink(FCPATH . $book->book_img)) {
                    // log_message('debug', 'File deleted successfully: ' . $book->book_img);
                } else {
                    // log_message('error', 'Failed to delete file: ' . $book->book_img);
                }
            } else {
                // log_message('error', 'File does not exist: ' . $book->book_img);
            }

            return ['success' => 'Book deleted successfully.'];
        } else {
            // log_message('error', 'Database deletion failed for ID: ' . $id);
            return ['error' => 'Failed to delete the book.'];
        }
    }



}