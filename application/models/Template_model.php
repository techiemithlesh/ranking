<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Template_model extends MY_Model
{
    protected $table = 'template_assets';
    protected $primary_key = 'id';

    public function __construct()
    {
        parent::__construct();
    }

    /* ---------------------------------------------
        INSERT or UPDATE template (image/video)
    --------------------------------------------- */
    public function saveEdit($data)
    {
        // Update
        if (!empty($data['id'])) {
            $this->db->where($this->primary_key, $data['id']);
            $this->db->update($this->table, $data);

            return $data['id'];
        }

        // Insert
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }

    /* ---------------------------------------------
        GET template by ID
    --------------------------------------------- */
    public function getById($id)
    {
        return $this->db->get_where($this->table, [
            $this->primary_key => $id
        ])->row_array();
    }

    /* ---------------------------------------------
        GET all templates
    --------------------------------------------- */
    public function getAll()
    {
        return $this->db
            ->order_by($this->primary_key, 'DESC')
            ->get($this->table)
            ->result_array();
    }

}
