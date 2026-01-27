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



    public function saveTemplate($data)
    {
        try {
            $this->db->insert($this->table, $data);
            return $this->db->insert_id();
        } catch (\Exception $e) {
            return $e->getMessage();
        };
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

    public function countTemplatesWithOverlayCount($templateType = '', $editStatus = '', $status = '')
    {
        $dbType = '';
        if ($templateType === '1') $dbType = 'image';
        if ($templateType === '2') $dbType = 'video';

        $this->db->select('ta.id', false);
        $this->db->from($this->table . ' ta');
        $this->db->join('template_overlays to1', 'to1.template_id = ta.id', 'left');
        $this->db->group_by('ta.id');

        if ($dbType !== '') $this->db->where('ta.type', $dbType);
        if ($status !== '' && $status !== null) $this->db->where('ta.status', (int)$status);

        if ($editStatus !== '' && $editStatus !== null) {
            if ((string)$editStatus === '1') $this->db->having('COUNT(to1.id) >', 0);
            if ((string)$editStatus === '0') $this->db->having('COUNT(to1.id) =', 0);
        }

        // count groups (CI3 workaround)
        return $this->db->get()->num_rows();
    }

    public function getTemplatesWithOverlayCountPaged($templateType = '', $editStatus = '', $status = '', $limit = 12, $offset = 0)
    {
        $dbType = '';
        if ($templateType === '1') $dbType = 'image';
        if ($templateType === '2') $dbType = 'video';

        $this->db->select('ta.*, COUNT(to1.id) AS overlay_count', false);
        $this->db->from($this->table . ' ta');
        $this->db->join('template_overlays to1', 'to1.template_id = ta.id', 'left');
        $this->db->group_by('ta.id');
        $this->db->order_by('ta.id', 'DESC');

        if ($dbType !== '') $this->db->where('ta.type', $dbType);
        if ($status !== '' && $status !== null) $this->db->where('ta.status', (int)$status);

        if ($editStatus !== '' && $editStatus !== null) {
            if ((string)$editStatus === '1') $this->db->having('overlay_count >', 0);
            if ((string)$editStatus === '0') $this->db->having('overlay_count =', 0);
        }

        $this->db->limit((int)$limit, (int)$offset);
        return $this->db->get()->result_array();
    }

    public function getActiveTemplates()
    {
        return $this->db
            ->where('status', 1)
            ->order_by('id', 'DESC')
            ->get($this->table)
            ->result_array();
    }
}
