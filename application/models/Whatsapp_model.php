<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Whatsapp_model extends MY_Model
{

    public function __construct()
    {
        parent::__construct();
    }

    public function getConfigList($branchID = null)
    {
        $this->db->select('wc.*');

        // Add CASE WHEN without escaping
        $this->db->select('
        CASE 
            WHEN wc.branch_id = 0 THEN "Global"
            ELSE b.name
        END AS branch_name
    ', FALSE);

        $this->db->from('whatsapp_config AS wc');
        $this->db->join('branch AS b', 'b.id = wc.branch_id', 'left');

        if ($branchID !== null) {
            $this->db->where('wc.branch_id', $branchID);
        }

        return $this->db->get()->result_array();
    }

    public function getConfigByBranch($branch_id)
    {
        return $this->db->where('branch_id', $branch_id)
            ->get('whatsapp_config')
            ->row_array();
    }


    public function get_active_config($branch_id = 0)
    {
        $query = $this->db->query("
            SELECT * FROM whatsapp_config
            WHERE status = 1 AND (branch_id = ? OR branch_id = 0)
            ORDER BY branch_id DESC, last_used_at ASC
            LIMIT 1
        ", [$branch_id]);
        $config = $query->row_array();

        if (!empty($config)) {
            $this->db->where('id', $config['id'])
                ->update('whatsapp_config', ['last_used_at' => date('Y-m-d H:i:s')]);
        }

        return $config;
    }

    /**
     * Fetch template details by name.
     */
    public function get_template($template_name, $branch_id = 0, $language = 'en')
    {
        $query = $this->db->query("
            SELECT t.*, td.message_body, td.media_url, td.variables
            FROM whatsapp_template t
            JOIN whatsapp_template_details td ON t.id = td.template_id
            WHERE t.status = 1 AND td.status = 1
              AND t.name = ?
              AND (t.branch_id = ? OR t.branch_id = 0)
              AND td.language = ?
            ORDER BY t.branch_id DESC
            LIMIT 1
        ", [$template_name, $branch_id, $language]);

        return $query->row_array();
    }

    /**
     * Insert log entry.
     */
    public function log($data)
    {
        $this->db->insert('whatsapp_logs', $data);
    }


    public function getStudentWhatsappData($student_id)
    {
        $query = $this->db->query("
        SELECT 
            v.student_id,
            CONCAT(v.first_name, ' ', v.last_name) AS student_name,
            v.mobileno AS student_phone,
            v.branch_id,
            v.class_name,
            v.section_name
        FROM view_student_details v
        WHERE v.student_id = ?
        LIMIT 1
        ", [$student_id]);

        if ($query->num_rows() > 0) {
            $data = $query->row_array();
            $data['student_phone'] = preg_replace('/\D+/', '', $data['student_phone']);
            return $data;
        }

        return null;
    }

}