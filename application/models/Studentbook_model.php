<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Studentbook_model extends MY_Model
{
    protected $table = 'student_books';

    public function __construct()
    {
        parent::__construct();
    }

    public function save1($data)
    {
        $arrayData = array(
            'title' => $data['title'],
            'book_url' => $data['book_url'],
            'book_type' => $data['book_type'],
            'month_no' => (int)$data['month_no']
        );

        if (isset($data['uploader_id'])) {
            $arrayData['uploader_id'] = $data['uploader_id'];
        } else {
            $arrayData['uploader_id'] = get_loggedin_user_id();
        }

        // log_message('debug', 'coming data for upload: ' . json_encode($arrayData));

        if (!isset($data['img_path'])) {
            $config['upload_path'] = 'uploads/book_images/';
            $config['encrypt_name'] = true;
            $config['allowed_types'] = 'jpg|jpeg|png|gif';
            $this->upload->initialize($config);

            if ($this->upload->do_upload("img_path")) {
                $arrayData['book_img'] = $config['upload_path'] . $this->upload->data('file_name');
                $this->db->insert('student_books', $arrayData);
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

    public function save($data)
    {
        $arrayData = array(
            'title'     => $data['title'],
            'book_url'  => $data['book_url'],
            'book_type' => $data['book_type'],
            'month_no'  => (int)$data['month_no'],
            'uploader_id' => isset($data['uploader_id']) ? $data['uploader_id'] : get_loggedin_user_id(),
        );

        // Upload Image
        if (!empty($_FILES['img_path']['name'])) {

            $config['upload_path'] = 'uploads/book_images/';
            $config['encrypt_name'] = true;
            $config['allowed_types'] = 'jpg|jpeg|png|gif';

            $this->upload->initialize($config);

            if ($this->upload->do_upload("img_path")) {
                $arrayData['book_img'] = $config['upload_path'] . $this->upload->data('file_name');
            } else {
                return ['error' => $this->upload->display_errors()];
            }
        }

        // Insert row (always needed)
        $this->db->insert('student_books', $arrayData);

        return $this->db->affected_rows() > 0;
    }


    public function getBookUploadsList()
    {
        // Select columns
        $this->db->select('b.*, GROUP_CONCAT(br.name SEPARATOR ", ") as assigned_branches');
        $this->db->from('student_books as b');
        $this->db->join('book_branches as bb', 'bb.book_id = b.id', 'left');
        $this->db->join('branch as br', 'br.id = bb.branch_id', 'left');
        $this->db->group_by('b.id');

        // Filter based on user role
        if (!is_superadmin_loggedin()) {
            $loggedInBranchId = get_loggedin_branch_id();

            // Branch Admins: See books assigned to their branch by superadmin or assigned to classes within their branch
            $this->db->where('(bb.assigned_by_superadmin = 1 AND bb.branch_id = ' . $loggedInBranchId . ')');
            $this->db->or_group_start();
            $this->db->join('class_books as cb', 'cb.branch_id = b.id', 'left');
            $this->db->join('class as c', 'c.id = cb.class_id', 'left');
            $this->db->where('c.branch_id', $loggedInBranchId);
            $this->db->group_end();
        } else {
            // Super Admins: See all books
        }

        // Filter for students (role_id 6 or 7) based on class enrollment
        if (loggedin_role_id() == 6 || loggedin_role_id() == 7) {
            $studentId = get_loggedin_user_id();
            $classId = $this->db->select('class_id')
                ->where('student_id', $studentId)
                ->get('enroll')
                ->row()
                ->class_id;
            $this->db->where('b.id IN (SELECT book_id FROM class_books WHERE class_id = ' . $classId . ')');
        }

        // Order by upload ID descending
        $this->db->order_by('b.id', 'desc');

        // Execute query
        $query = $this->db->get();

        // Check for errors
        if ($this->db->error()['code'] != 0) {
            log_message('error', 'Database error in getBookUploadsList: ' . json_encode($this->db->error()));
            return false;
        }

        // Get results
        $result = $query->result_array();
        return $result;
    }

    public function getBookUploadsList3()
    {
        $this->db->select('b.*, GROUP_CONCAT(DISTINCT br.name SEPARATOR ", ") as assigned_branches, c.name as class_name');
        $this->db->from('student_books as b');
        $this->db->join('book_branches as bb', 'bb.book_id = b.id', 'left');
        $this->db->join('branch as br', 'br.id = bb.branch_id', 'left');
        $this->db->join('class_books as cb', 'cb.book_id = b.id', 'left');
        $this->db->join('class as c', 'c.id = cb.class_id', 'left');

        $role_id = loggedin_role_id();

        if (!is_superadmin_loggedin()) {
            $loggedInBranchId = get_loggedin_branch_id();

            // Branch Admin: books either directly assigned to branch OR assigned to classes of their branch
            $this->db->group_start();
            $this->db->where('bb.branch_id', $loggedInBranchId);
            $this->db->or_where('c.branch_id', $loggedInBranchId);
            $this->db->group_end();
        }

        if ($role_id == 6 || $role_id == 7) {
            // Students
            $studentId = get_loggedin_user_id();
            $student = $this->db->select('class_id, branch_id')->where('student_id', $studentId)->get('enroll')->row();
            if ($student) {
                $this->db->where('cb.class_id', $student->class_id);
                $this->db->where('c.branch_id', $student->branch_id); // ensure same branch
            }
        }

        if ($role_id == 3) {
            // Teachers
            $teacherId = get_loggedin_user_id();
            $sessionId = get_session_id();
            $branchId = get_loggedin_branch_id();

            // Join teacher_allocation based on class and branch
            $this->db->join('teacher_allocation as ta', 'ta.class_id = cb.class_id AND ta.branch_id = cb.branch_id', 'inner');
            $this->db->where('ta.teacher_id', $teacherId);
            $this->db->where('ta.session_id', $sessionId);
            $this->db->where('ta.branch_id', $branchId);
        }

        $this->db->group_by('b.id');
        $this->db->order_by('b.month_no', 'ASC');
        $this->db->order_by('b.id', 'desc');

        $query = $this->db->get();

        if ($this->db->error()['code'] != 0) {
            log_message('error', 'Database error in getBookUploadsList2: ' . json_encode($this->db->error()));
            return false;
        }

        return $query->result_array();
    }

    /**
     * Retrieve paginated books with advanced filtering based on user role and branch
     * 
     * @param int $roleID User's role ID
     * @param int $branchId Current branch ID
     * @param int|null $studentId Student ID (optional)
     * @param int $page Page number for pagination
     * @param int $perPage Number of items per page
     * @return array Paginated books data with total count
     */
    public function getFlipBox($roleID, $branchId, $studentId = null, $page = 1, $perPage = 10)
    {
        try {

            $page = max(1, intval($page));
            $perPage = max(1, intval($perPage));
            $offset = ($page - 1) * $perPage;

            $this->db->reset_query();

            $this->db->select('student_books.id AS book_id, 
                       student_books.title, 
                       student_books.book_img AS book_cover, student_books.book_url');
            $this->db->from('student_books');

            // Joins
            $this->db->join('book_branches', 'book_branches.book_id = student_books.id', 'left');
            $this->db->join('class_books cb', 'cb.book_id = student_books.id', 'left');
            $this->db->join('class', 'class.id = cb.class_id', 'left');

            // Join enroll table for student roles
            if (in_array($roleID, [6, 7]) && $studentId !== null) {
                $this->db->join('enroll', 'enroll.class_id = cb.class_id AND enroll.student_id = ' . $this->db->escape($studentId), 'inner');
            }

            // Role-based filtering
            if ($roleID != 1) {
                $this->db->group_start()
                    ->where('book_branches.assigned_by_superadmin', 1)
                    ->where('book_branches.branch_id', $branchId)
                    ->group_end();

                $this->db->or_group_start()
                    ->where('class.branch_id', $branchId)
                    ->group_end();
            }

            // Group by student_books.id to avoid duplicates
            $this->db->group_by('student_books.id');

            // Clone query for total count before adding limit
            $total_count = $this->db->count_all_results('', false);

            // printVar($this->db->last_query());
            // die;

            // Order and paginate
            $this->db->order_by('student_books.id', 'DESC');
            $this->db->limit($perPage, $offset);

            // Execute query
            $query = $this->db->get();
            $books = $query->result_array();

            // Add assigned branches
            foreach ($books as &$book) {
                $book['assigned_branches'] = $this->getBranchesForBook($book['book_id']);
            }

            // Prepare paginated response
            return [
                'books' => $books,
                'pagination' => [
                    'total_items' => $total_count,
                    'current_page' => $page,
                    'per_page' => $perPage,
                    'total_pages' => ceil($total_count / $perPage)
                ]
            ];
        } catch (Exception $e) {
            log_message('error', 'Book retrieval error: ' . $e->getMessage());
            return [
                'books' => [],
                'pagination' => [
                    'total_items' => 0,
                    'current_page' => $page,
                    'per_page' => $perPage,
                    'total_pages' => 0
                ],
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Retrieve assigned branches for a specific book
     * 
     * @param int $bookId Book ID
     * @return string Comma-separated branch names
     */
    private function getBranchesForBook($bookId)
    {
        $branches = $this->db->select('branch.name')
            ->from('book_branches')
            ->join('branch', 'branch.id = book_branches.branch_id')
            ->where('book_branches.book_id', $bookId)
            ->get()
            ->result_array();

        return $branches ? implode(', ', array_column($branches, 'name')) : '';
    }

    public function get_all_books()
    {
        $this->db->select('b.*, GROUP_CONCAT(br.name SEPARATOR ", ") as assigned_branches');
        $this->db->from('student_books as b');
        $this->db->join('book_branches as bb', 'bb.book_id = b.id', 'left');
        $this->db->join('branch as br', 'br.id = bb.branch_id', 'left');
        $this->db->group_by('b.id');
        $query = $this->db->get();
        return $query->result_array();
    }

    public function getBranches()
    {
        $query = $this->db->get('branch');
        return $query->result_array();
    }

    public function getAssignedBranches($bookIds)
    {
        $this->db->select('branch.id, branch.name, 
        MAX(CASE WHEN book_branches.book_id IS NOT NULL THEN 1 ELSE 0 END) as assigned');
        $this->db->from('branch');
        $this->db->join('book_branches', 'book_branches.branch_id = branch.id AND book_branches.book_id IN (' . implode(',', $bookIds) . ')', 'left');
        $this->db->group_by('branch.id, branch.name');

        $query = $this->db->get_compiled_select();

        $this->db->reset_query();

        return $this->db->query($query)->result_array();
    }
    public function assignBranchesToBooks($book_ids, $branch_ids)
    {
        foreach ($book_ids as $book_id) {
            // Get the currently assigned branches for this book
            $existingBranches = $this->db->select('branch_id')
                ->from('book_branches')
                ->where('book_id', $book_id)
                ->get()
                ->result_array();

            // Convert the result to a simple array
            $existingBranchIds = array_column($existingBranches, 'branch_id');

            // Find branches to add (newly checked) and to remove (unchecked)
            $branchesToAdd = array_diff($branch_ids, $existingBranchIds); // New branches
            $branchesToRemove = array_diff($existingBranchIds, $branch_ids); // Unchecked branches

            // Add new branches
            foreach ($branchesToAdd as $branch_id) {
                $data = [
                    'book_id' => $book_id,
                    'branch_id' => $branch_id,
                    'assigned_by_superadmin' => 1
                ];
                $this->db->insert('book_branches', $data);
            }

            // Remove unchecked branches
            if (!empty($branchesToRemove)) {
                $this->db->where('book_id', $book_id)
                    ->where_in('branch_id', $branchesToRemove)
                    ->delete('book_branches');
            }
        }

        return json_encode(['status' => 'success', 'message' => 'Branches updated successfully!']);
    }

    public function get_all_branches_with_assignment($bookIds)
    {
        $this->db->select('branch.id, branch.name, 
        CASE WHEN book_branch.book_id IN (' . implode(',', $bookIds) . ') THEN 1 ELSE 0 END as assigned');
        $this->db->from('branch');
        $this->db->join('book_branch', 'book_branch.branch_id = branches.id', 'left');
        $this->db->group_by('branches.id');
        return $this->db->get()->result_array();
    }

    public function get_unassigned_books_for_class()
    {
        if (is_superadmin_loggedin()) {
            // If Superadmin, get all unassigned books
            $this->db->where('class_id', NULL);
            $query = $this->db->get('student_books');
            return $query->result_array();
        } else {
            try {
                $session_data = $this->session->all_userdata();
                $loggedin_branch = $session_data['loggedin_branch'];

                // log_message('debug', "Current Branch ID: " . $loggedin_branch);

                $this->db->where('branch_id', $loggedin_branch);
                $this->db->where('class_id', NULL);
                $query = $this->db->get('student_books');
                return $query->result_array();
            } catch (Exception $e) {
                log_message('error', "Error getting unassigned books: " . $e->getMessage());
                return array();
            }
        }
    }

    public function getBooksForClassAssign()
    {
        $loggedInBranchId = get_loggedin_branch_id();
        $this->db->select('
                b.*, 
                GROUP_CONCAT(DISTINCT br.name SEPARATOR ", ") as assigned_branches, 
                GROUP_CONCAT(DISTINCT c.name SEPARATOR ", ") as assigned_classes
            ');
        $this->db->from('student_books as b');

        $this->db->join('book_branches as bb', 'bb.book_id = b.id', 'left');
        $this->db->join('branch as br', 'br.id = bb.branch_id', 'left');

        $this->db->join('class_books as cb', 'cb.book_id = b.id AND cb.branch_id = bb.branch_id', 'left');
        $this->db->join('class as c', 'c.id = cb.class_id', 'left');
        $this->db->where('bb.branch_id', $loggedInBranchId);

        $this->db->group_by('b.id');
        $query = $this->db->get();
        if ($query->num_rows() === 0) {
            return [];
        } else {
            return $query->result_array();
        }
    }


    public function updateBookBranches($book_id, $branch_ids)
    {
        // Get current branch assignments
        $currentBranches = $this->db->select('branch_id')
            ->where('book_id', $book_id)
            ->get('book_branches')
            ->result_array();

        $existingBranchIds = array_column($currentBranches, 'branch_id');

        // Find only the branches that were explicitly unchecked
        // This will only get branches that existed before but aren't in new selection
        $branchesToRemove = array_diff($existingBranchIds, $branch_ids);

        // Delete only explicitly unchecked branches
        if (!empty($branchesToRemove)) {
            $this->db->where('book_id', $book_id)
                ->where_in('branch_id', $branchesToRemove)
                ->delete('book_branches');
        }

        // Add only new branches that don't exist
        foreach ($branch_ids as $branch_id) {
            // Check if this branch assignment already exists
            $exists = $this->db->where('book_id', $book_id)
                ->where('branch_id', $branch_id)
                ->get('book_branches')
                ->num_rows();

            // Only insert if it doesn't exist
            if ($exists == 0) {
                $this->db->insert('book_branches', [
                    'book_id' => $book_id,
                    'branch_id' => $branch_id,
                    'assigned_by_superadmin' => get_loggedin_user_id()
                ]);
            }
        }
    }

    public function getBookByStudent($branchId, $classId)
    {
        try {
            // Validate input parameters
            if (empty($branchId) || empty($classId)) {
                throw new Exception("Branch ID and Class ID are required.");
            }

            // Query the database using branch ID and class ID
            $query = $this->db->get_where('student_books', [
                'branch_id' => $branchId,
                'class_id' => $classId
            ]);

            // Check if records are found
            if ($query->num_rows() > 0) {
                return $query->result_array(); // Return the results
            } else {
                return []; // No records found
            }
        } catch (Exception $e) {
            // Log error message
            log_message('error', 'Error in getBookByStudent: ' . $e->getMessage());
            return false; // Return false on failure
        }
    }


    public function updateBook($data)
    {
        $arrayData = array(
            'title' => $data['title'],
            'book_type' => $data['book_type'],
            'month_no' => (int) $data['month_no'],
            'status' => $data['status'],
            'book_url' => $data['book_url']
        );

        $this->db->where('id', $data['id']);
        $existingBook = $this->db->get('student_books')->row();

        if ($existingBook) {
            if (isset($_FILES['book_img']) && !empty($_FILES['book_img']['name'])) {
                // Upload new thumbnail
                $config['upload_path'] = 'uploads/book_images/';
                $config['encrypt_name'] = true;
                $config['allowed_types'] = 'jpg|jpeg|png|gif|bmp';
                $this->upload->initialize($config);

                if ($this->upload->do_upload('book_img')) {
                    // Get the uploaded file data
                    $uploadData = $this->upload->data();

                    // Delete the old thumbnail if it exists
                    if (!empty($existingBook->book_img) && file_exists($existingBook->book_img)) {
                        unlink($existingBook->book_img);
                    }

                    // Set the new thumbnail path
                    $arrayData['book_img'] = $config['upload_path'] . $uploadData['file_name'];
                } else {
                    // Return upload error
                    return ['error' => $this->upload->display_errors()];
                }
            }


            $this->db->where('id', $data['id']);
            $this->db->update('student_books', $arrayData);

            if ($this->db->affected_rows() > 0) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
}
