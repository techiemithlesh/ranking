<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');


class TemplateOverlay_model extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function add_overlay($data)
    {
        $this->db->insert('template_overlays', $data);
        return $this->db->insert_id();
    }

    public function get_by_template($template_id)
    {
        return $this->db
            ->order_by('z_index', 'ASC')
            ->get_where('template_overlays', ['template_id' => $template_id])
            ->result_array();
    }

    

    public function delete_by_template($template_id)
    {
        $this->db->where('template_id', $template_id)->delete('template_overlays');
    }
}