<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Support_model extends MY_Model
{
    protected $table = 'tbl_enquiry';

    public function __construct()
    {
        parent::__construct();
    }

    public function saveEnquiry($data)
    {

        return $this->db->insert($this->table, $data) ? $this->db->insert_id() : false;
    }

    public function getEnquiry()
    {
        $this->db->select('tbl_enquiry.*, branch.name as branch_name');
        $this->db->from($this->table);
        $this->db->join('branch', 'branch.id = tbl_enquiry.branch_id', 'left');

        if (is_admin_loggedin()) {
            $this->db->where('tbl_enquiry.branch_id', get_loggedin_branch_id());
        }

        $query = $this->db->get();
        return $query->num_rows() > 0 ? $query->result_array() : [];
    }


    public function getEnquiryById($id)
    {
        $this->db->where('id', $id);
        $query = $this->db->get($this->table);
        return $query->row_array();
    }

    public function deleteEnquiry($id)
    {
        $this->db->where('id', $id);
        return $this->db->delete($this->table);
    }

}