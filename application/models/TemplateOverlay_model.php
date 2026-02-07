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
        // Start the Transaction
        $this->db->trans_start();

        // Delete from the first table
        $this->db->where('template_id', $template_id)->delete('template_overlays');

        // Delete from the second table
        $this->db->where('template_id', $template_id)->delete('template_video_overlays');

        // Complete the transaction
        $this->db->trans_complete();

        // Check if transaction was successful
        if ($this->db->trans_status() === FALSE) {
            log_message('error', "Failed to delete overlays for Template ID: $template_id");
            return false;
        }

        return true;
    }

    /**
     * Insert multiple overlay rows at once.
     * @param array $rows Array of associative arrays matching table columns.
     * @return bool
     */
    public function insert_batch($rows)
    {
        if (empty($rows) || !is_array($rows)) {
            return false;
        }

        // Make sure required fields exist (basic safety)
        foreach ($rows as $r) {
            if (!isset($r['template_id'], $r['overlay_type'], $r['x'], $r['y'], $r['width'], $r['height'])) {
                return false;
            }
        }

        $ok = $this->db->insert_batch($this->table, $rows);

        // CI3 insert_batch returns number of rows inserted (int) or false
        return ($ok !== false);
    }
}
