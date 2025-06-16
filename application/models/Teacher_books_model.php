<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Teacher_books_model extends MY_Model
{
    protected $table = 'teacher_books';

    public function __construct()
    {
        parent::__construct();
    }



    public function save($data)
    {
        $classID = (!isset($data['all_class_set']) ? $data['class_id'] : 'unfiltered');
        $sectionId = (!isset($data['all_class_set']) ? $data['section_id'] : null);

        $arrayData = array(
            'branch_id' => $this->application_model->get_branch_id(),
            'staff_id' => $data['staff_id'],
            'title' => $data['title'],
            'book_url' => $data['book_url'],
            'uploader_id' => get_loggedin_user_id(),
            'class_id' => $classID,
            'section_id' => $sectionId,
        );

        if (!isset($data['all_class_set']) && !isset($data['subject_wise'])) {
            $arrayData['subject_id'] = $data['subject_id'];
        } else {
            $arrayData['subject_id'] = 'unfiltered';
        }

        // Handle image upload
        if (!isset($data['img_path'])) {

            // Set upload configuration for the image
            $config['upload_path'] = 'uploads/teachers_book/';
            $config['encrypt_name'] = true;
            $config['allowed_types'] = 'jpg|jpeg|png|gif|bmp';
            $this->upload->initialize($config);


            if ($this->upload->do_upload("img_path")) {

                $arrayData['book_img'] = $config['upload_path'] . $this->upload->data('file_name');

                $this->db->insert('teacher_books', $arrayData);
            } else {
                return ['error' => $this->upload->display_errors()];
            }
        }

        log_message('debug', 'Saving book upload with data: ' . json_encode($data));

        if ($this->db->affected_rows() > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function getBookList()
    {
        $sql = "
                SELECT 
                    teacher_books.id AS books_id, 
                    branch.name AS branch_name, 
                    branch.school_name, 
                    staff.name AS staff_name, 
                    class.name AS class_name, 
                    section.name AS section_name, 
                    teacher_books.title, 
                    teacher_books.book_url, 
                    teacher_books.book_img,
                    subject.name AS subject_name,
                    uploader.name AS uploader_name,
                    teacher_books.created_at AS book_upload_date
                FROM 
                    teacher_books
                INNER JOIN 
                    branch 
                    ON teacher_books.branch_id = branch.id
                LEFT JOIN 
                    staff 
                    ON teacher_books.staff_id = staff.id
                LEFT JOIN 
                    class 
                    ON teacher_books.class_id = class.id
                LEFT JOIN 
                    section 
                    ON teacher_books.section_id = section.id
                LEFT JOIN 
                    subject
                    ON teacher_books.subject_id =  subject.id
                LEFT JOIN 
                    staff AS uploader 
                    ON teacher_books.uploader_id = uploader.id;
                    ";

        // Execute the query
        $query = $this->db->query($sql);
        // log_message("query", $this->db->getLastQuery());
        // Return the result as an array
        return $query->result_array();
    }

}