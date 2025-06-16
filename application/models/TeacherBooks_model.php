<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class TeacherBooks_model extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function saveTeacherBook($data)
    {
        $arrayData = array(
            'title' => $data['title'],
            'book_url' => $data['book_url'],
        );


        if (isset($data['uploaded_by'])) {
            $arrayData['uploaded_by'] = $data['uploaded_by'];
        } else {

            $arrayData['uploaded_by'] = get_loggedin_user_id();
        }

        // log_message('debug', 'coming data for upload: ' . json_encode($arrayData));

        if (!isset($data['img_path'])) {
            $config['upload_path'] = 'uploads/teachers_book/';
            $config['encrypt_name'] = true;
            $config['allowed_types'] = 'jpg|jpeg|png|gif';
            $this->upload->initialize($config);


            if ($this->upload->do_upload("img_path")) {
                $arrayData['book_img'] = $config['upload_path'] . $this->upload->data('file_name');
                $this->db->insert('tbl_teacher_books', $arrayData);
            } else {
                return ['error' => $this->upload->display_errors()];
            }
        }

        // log_message('debug', 'Saving book upload with data: ' . json_encode($data));

        if ($this->db->affected_rows() > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function getTeacherBookList()
    {
        $this->db->select('
            tbl_teacher_books.*,
            GROUP_CONCAT(DISTINCT branch.name SEPARATOR ", ") as assigned_branches,
            GROUP_CONCAT(DISTINCT class.name SEPARATOR ", ") as assigned_classes,
            CASE 
                WHEN tbl_teacher_books.status = 1 THEN \'Active\'
            ELSE \'Inactive\'
        END as status_label
        ');
        $this->db->from('tbl_teacher_books');
        $this->db->join('tbl_teacher_books_branch', 'tbl_teacher_books_branch.teacher_book_id = tbl_teacher_books.id', 'left');
        $this->db->join('branch', 'branch.id = tbl_teacher_books_branch.branch_id', 'left');
        $this->db->join('tbl_teacher_books_class', 'tbl_teacher_books_class.teacher_book_id = tbl_teacher_books.id', 'left');
        $this->db->join('class', 'class.id = tbl_teacher_books_class.class_id', 'left');

        // Filter based on user role
        if (!is_superadmin_loggedin()) {
            $loggedInBranchId = get_loggedin_branch_id();

            // Branch Admin: See books assigned to their branch or classes in their branch
            $this->db->group_start();
            $this->db->where('tbl_teacher_books_branch.branch_id', $loggedInBranchId);
            $this->db->or_where('tbl_teacher_books_class.branch_id', $loggedInBranchId);
            $this->db->group_end();
        }

        // Filter for students (role_id 6 or 7) based on class enrollment
        if (loggedin_role_id() == 6 || loggedin_role_id() == 7) {
            $studentId = get_loggedin_user_id();


            $classId = $this->db->select('class_id')
                ->where('student_id', $studentId)
                ->get('enroll')
                ->row()
                ->class_id;


            $this->db->where('tbl_teacher_books_class.class_id', $classId);
        }

        // filter for teacher login (role_id 3)
        if (loggedin_role_id() == 3) {
            
            $this->db->join('teacher_allocation', 'teacher_allocation.class_id = tbl_teacher_books_class.class_id AND teacher_allocation.branch_id = tbl_teacher_books_branch.branch_id', 'inner');
           
        }

        $this->db->group_by('tbl_teacher_books.id');


        $this->db->order_by('tbl_teacher_books.id', 'desc');

        // Execute query
        $query = $this->db->get();
        // echo $this->db->last_query();

        return $query->result_array();
    }


    public function getBranches()
    {
        $query = $this->db->get('branch');
        return $query->result_array();
    }

    public function getBookForAssignBranch()
    {
        $this->db->select('b.*, GROUP_CONCAT(br.name SEPARATOR ", ") as assigned_branches');
        $this->db->from('tbl_teacher_books as b');
        $this->db->join('tbl_teacher_books_branch as bb', 'bb.teacher_book_id  = b.id', 'left');
        $this->db->join('branch as br', 'br.id = bb.branch_id', 'left');
        $this->db->where('b.status = 1');
        $this->db->group_by('b.id');
        $query = $this->db->get();
        return $query->result_array();
    }


    public function getAssignedBranches($bookIds)
    {
        $this->db->select('branch.id, branch.name, 
        CASE WHEN tbl_teacher_books_branch.teacher_book_id IN (' . implode(',', $bookIds) . ') THEN 1 ELSE 0 END as assigned');
        $this->db->from('branch');
        $this->db->join('tbl_teacher_books_branch', 'tbl_teacher_books_branch.branch_id = branch.id', 'left');
        $this->db->group_by('branch.id');
        return $this->db->get()->result_array();
    }

    public function assignBranchesToBooks($book_ids, $branch_ids)
    {
        foreach ($book_ids as $book_id) {
            $existingBranches = $this->db->select('branch_id')
                ->from('tbl_teacher_books_branch')
                ->where('teacher_book_id', $book_id)
                ->get()
                ->result_array();

            // Convert the result to a simple array
            $existingBranchIds = array_column($existingBranches, 'branch_id');

            // Find branches to add (newly checked) and to remove (unchecked)
            $branchesToAdd = array_diff($branch_ids, $existingBranchIds); // New branches
            $branchesToRemove = array_diff($existingBranchIds, $branch_ids); // Unchecked branches


            foreach ($branchesToAdd as $branch_id) {
                $data = [
                    'teacher_book_id' => $book_id,
                    'branch_id' => $branch_id,
                    'assigned_by' => 1
                ];
                $this->db->insert('tbl_teacher_books_branch', $data);
            }


            if (!empty($branchesToRemove)) {
                $this->db->where('teacher_book_id', $book_id)
                    ->where_in('branch_id', $branchesToRemove)
                    ->delete('tbl_teacher_books_branch');
            }
        }

        return json_encode(['status' => 'success', 'message' => 'Branches updated successfully!']);
    }


    public function getBooksForClassAssign()
    {
        $loggedInBranchId = get_loggedin_branch_id();

        $this->db->select('
        b.*, 
        GROUP_CONCAT(DISTINCT br.name SEPARATOR ", ") as assigned_branches, 
        GROUP_CONCAT(DISTINCT c.name SEPARATOR ", ") as assigned_classes
        ');
        $this->db->from('tbl_teacher_books as b');


        $this->db->join('tbl_teacher_books_branch as bb', 'bb.teacher_book_id = b.id', 'inner');
        $this->db->join('branch as br', 'br.id = bb.branch_id', 'left');

        // Join with tbl_digital_library_class and class to fetch assigned classes
        $this->db->join('tbl_teacher_books_class as cb', 'cb.teacher_book_id = b.id', 'left');
        $this->db->join('class as c', 'c.id = cb.class_id', 'left');


        $this->db->where('bb.branch_id', $loggedInBranchId);
        $this->db->where('bb.status', '1');

        $this->db->group_by('b.id');

        $query = $this->db->get();

        // Return results in a consistent format
        if ($query->num_rows() > 0) {
            return $query->result_array(); // Return the fetched data
        } else {
            return []; // Return an empty array if no books are found
        }
    }


}