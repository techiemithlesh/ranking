<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Whatsapp_model extends MY_Model
{

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
        $query = $this->db->select("
            s.id AS student_id,
            s.fullname AS student_name,
            s.mobileno AS student_phone,
            p.father_name AS parent_name,
            p.mobileno AS parent_phone,
            s.branch_id,
            c.name AS class_name,
            sec.name AS section_name
        ")
            ->from('student s')
            ->join('enroll e', 'e.student_id = s.id', 'left')
            ->join('class c', 'c.id = e.class_id', 'left')
            ->join('section sec', 'sec.id = e.section_id', 'left')
            ->join('parent p', 'p.id = s.parent_id', 'left')
            ->where('s.id', $student_id)
            ->limit(1)
            ->get();

        if ($query->num_rows() > 0) {
            $row = $query->row_array();

            // Normalize phone numbers (remove spaces / non-digits)
            $row['parent_phone'] = preg_replace('/\D+/', '', $row['parent_phone']);
            $row['student_phone'] = preg_replace('/\D+/', '', $row['student_phone']);

            return $row;
        }

        return null;
    }
}