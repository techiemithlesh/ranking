<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class DigitalLibrary_model extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function saveDigitalBook($data)
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
            $config['upload_path'] = 'uploads/digital_library/';
            $config['encrypt_name'] = true;
            $config['allowed_types'] = 'jpg|jpeg|png|gif';
            $this->upload->initialize($config);


            if ($this->upload->do_upload("img_path")) {
                $arrayData['book_img'] = $config['upload_path'] . $this->upload->data('file_name');
                $this->db->insert('tbl_digital_library', $arrayData);
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


    public function getDigitalBookList()
    {
        $this->db->select('
            tbl_digital_library.*,
            GROUP_CONCAT(DISTINCT branch.name SEPARATOR ", ") as assigned_branches,
            GROUP_CONCAT(DISTINCT class.name SEPARATOR ", ") as assigned_classes,
            CASE 
            WHEN tbl_digital_library.status = 1 THEN \'Active\'
            ELSE \'Inactive\'
        END as status_label
        ');
        $this->db->from('tbl_digital_library');
        $this->db->join('tbl_digital_library_branch', 'tbl_digital_library_branch.digital_id = tbl_digital_library.id', 'left');
        $this->db->join('branch', 'branch.id = tbl_digital_library_branch.branch_id', 'left');
        $this->db->join('tbl_digital_library_class', 'tbl_digital_library_class.digital_id = tbl_digital_library.id', 'left');
        $this->db->join('class', 'class.id = tbl_digital_library_class.class_id', 'left');

        // Filter based on user role
        if (!is_superadmin_loggedin()) {
            $loggedInBranchId = get_loggedin_branch_id();

            // Branch Admin: See books assigned to their branch or classes in their branch
            $this->db->group_start();
            $this->db->where('tbl_digital_library_branch.branch_id', $loggedInBranchId);
            $this->db->or_where('tbl_digital_library_class.branch_id', $loggedInBranchId);
            $this->db->group_end();
        }

        // Filter for students (role_id 6 or 7) based on class enrollment
        if (loggedin_role_id() == 6 || loggedin_role_id() == 7) {
            $studentId = get_loggedin_user_id();

            // Fetch the class ID of the logged-in student
            $classId = $this->db->select('class_id')
                ->where('student_id', $studentId)
                ->get('enroll')
                ->row()
                ->class_id;

            // Ensure only books assigned to the student's class are shown
            $this->db->where('tbl_digital_library_class.class_id', $classId);
        }

        // Group by digital library ID to avoid duplicate entries
        $this->db->group_by('tbl_digital_library.id');

        // Order by most recently uploaded resources
        $this->db->order_by('tbl_digital_library.id', 'desc');

        // Execute query
        $query = $this->db->get();
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
        $this->db->from('tbl_digital_library as b');
        $this->db->join('tbl_digital_library_branch as bb', 'bb.digital_id = b.id', 'left');
        $this->db->join('branch as br', 'br.id = bb.branch_id', 'left');
        $this->db->where('b.status = 1');
        $this->db->group_by('b.id');
        $query = $this->db->get();
        return $query->result_array();
    }

    public function getAssignedBranches($bookIds)
    {
        $this->db->select('branch.id, branch.name, 
        CASE WHEN tbl_digital_library_branch.digital_id IN (' . implode(',', $bookIds) . ') THEN 1 ELSE 0 END as assigned');
        $this->db->from('branch');
        $this->db->join('tbl_digital_library_branch', 'tbl_digital_library_branch.branch_id = branch.id', 'left');
        $this->db->group_by('branch.id');
        return $this->db->get()->result_array();
    }


    // public function getAssignedBranches($bookIds)
    // {
    //     $this->db->select('branch.id, branch.name, 
    //     MAX(CASE WHEN tbl_digital_library_branch.digital_id IS NOT NULL THEN 1 ELSE 0 END) as assigned');
    //     $this->db->from('branch');
    //     $this->db->join('tbl_digital_library_branch', 'tbl_digital_library_branch.digital_id = branch.id AND tbl_digital_library_branch.digital_id IN (' . implode(',', $bookIds) . ')', 'left');
    //     $this->db->group_by('branch.id, branch.name');

    //     // To check the actual SQL query
    //     $query = $this->db->get_compiled_select();


    //     $this->db->reset_query();

    //     return $this->db->query($query)->result_array();
    // }

    public function assignBranchesToBooks($book_ids, $branch_ids)
    {
        foreach ($book_ids as $book_id) {
            $existingBranches = $this->db->select('branch_id')
                ->from('tbl_digital_library_branch')
                ->where('digital_id', $book_id)
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
                    'digital_id' => $book_id,
                    'branch_id' => $branch_id,
                    'assigned_by' => 1
                ];
                $this->db->insert('tbl_digital_library_branch', $data);
            }

            // Remove unchecked branches
            if (!empty($branchesToRemove)) {
                $this->db->where('digital_id', $book_id)
                    ->where_in('branch_id', $branchesToRemove)
                    ->delete('tbl_digital_library_branch');
            }
        }

        return json_encode(['status' => 'success', 'message' => 'Branches updated successfully!', 'brnachRemove' => $existingBranchIds]);
    }


    public function getBooksForClassAssign()
    {
        $loggedInBranchId = get_loggedin_branch_id();

        $this->db->select('
        b.*, 
        GROUP_CONCAT(DISTINCT br.name SEPARATOR ", ") as assigned_branches, 
        GROUP_CONCAT(DISTINCT c.name SEPARATOR ", ") as assigned_classes, GROUP_CONCAT(DISTINCT cb.class_id SEPARATOR ", ") as assigned_classes_id');
        $this->db->from('tbl_digital_library as b');


        $this->db->join('tbl_digital_library_branch as bb', 'bb.digital_id = b.id', 'inner');
        $this->db->join('branch as br', 'br.id = bb.branch_id', 'left');

        // Join with tbl_digital_library_class and class to fetch assigned classes
        $this->db->join('tbl_digital_library_class as cb', 'cb.digital_id = b.id', 'left');
        $this->db->join('class as c', 'c.id = cb.class_id', 'left');


        $this->db->where('bb.branch_id', $loggedInBranchId);
        $this->db->where('bb.status', '1');

        $this->db->group_by('b.id');

        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            return $query->result_array();
        } else {
            return [];
        }
    }


    public function getBookByStudent($branchId, $classId)
    {
        try {
            // Validate input parameters
            if (empty($branchId) || empty($classId)) {
                throw new Exception("Branch ID and Class ID are required.");
            }


            $query = $this->db->get_where('tbl_digital_library_class', [
                'branch_id' => $branchId,
                'class_id' => $classId
            ]);

            // Check if records are found
            if ($query->num_rows() > 0) {
                return $query->result_array();
            } else {
                return [];
            }
        } catch (Exception $e) {

            log_message('error', 'Error in getBookByStudent: ' . $e->getMessage());
            return false; // Return false on failure
        }
    }

    public function updateDigitalBook($data)
    {
        $arrayData = array(
            'title' => $data['title'],
            'status' => $data['status'],
            'book_url' => $data['book_url']
        );

        $this->db->where('id', $data['id']);
        $existingBook = $this->db->get('tbl_digital_library')->row();

        if ($existingBook) {

            if (isset($_FILES['book_img']) && !empty($_FILES['book_img']['name'])) {
                // Upload new thumbnail
                $config['upload_path'] = 'uploads/digital_library/';
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
            $this->db->update('tbl_digital_library', $arrayData);

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