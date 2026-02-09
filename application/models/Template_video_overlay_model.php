<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Template_video_overlay_model extends MY_Model
{
    protected $table = 'template_video_overlays';
    protected $primary_key = 'id';

    public function __construct()
    {
        return parent::__construct();
    }

    public function add_overlay($data)
    {
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }

    public function get_by_template($template_id)
    {
        return $this->db
            ->where('template_id', $template_id)
            ->order_by('start_time', 'ASC')
            ->get($this->table)
            ->result_array();
    }

    public function update($id, $data)
    {
        $this->db->where($this->primary_key, $id);
        return $this->db->update($this->table, $data);
    }

    public function delete_by_template($template_id)
    {
        return $this->db->where('template_id', $template_id)->delete($this->table);
    }

    /**
     * Insert multiple overlay rows at once.
     * @param array $rows Array of associative arrays matching table columns.
     * @return bool
     */
}
