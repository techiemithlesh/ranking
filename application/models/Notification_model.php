<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}


class Notification_model extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function sendNotification($to_user_id, $to_user_type, $title, $description = '', $link = '', $from_user_id = null, $from_user_type = null)
    {
        $data = array(
            'to_user_id' => $to_user_id,
            'to_user_type' => $to_user_type,
            'from_user_id' => $from_user_id,
            'from_user_type' => $from_user_type,
            'title' => $title,
            'description' => $description,
            'link' => $link,
            'created_at' => date('Y-m-d H:i:s'),
        );
        $this->db->insert('notifications', $data);
    }


    public function get_notifications($user_id, $user_type, $limit = 5)
    {
        $this->db->where('to_user_id', $user_id);
        $this->db->where('to_user_type', $user_type);
        $this->db->order_by('created_at', 'DESC');
        $this->db->limit($limit);
        return $this->db->get('notifications')->result_array();
    }




    public function count_unread($user_id, $user_type)
    {
        $this->db->where('to_user_id', $user_id);
        $this->db->where('to_user_type', $user_type);
        $this->db->where('is_read', 0);
        return $this->db->count_all_results('notifications');
    }


    public function mark_as_read($id)
    {
        $this->db->where('id', $id);
        $this->db->update('notifications', array('is_read' => 1));
    }

    public function getAllNotifications($user_id, $role_id, $limit = 10)
    {
        $this->db->select('*');
        $this->db->from('notifications');
        $this->db->where('to_user_id', $user_id);
        $this->db->where('to_user_type', $role_id);
        $this->db->order_by('id', 'DESC');
        $this->db->limit($limit);
        $query = $this->db->get();

        return $query->result_array();
    }

    public function markAsRead($notification_id, $user_id, $role_id)
    {
        $this->db->where('id', $notification_id);
        $this->db->where('to_user_id', $user_id);
        $this->db->where('to_user_type', $role_id);
        $this->db->update('notifications', array('is_read' => 1));
        
        return $this->db->affected_rows() > 0;
    }

    public function markAllAsRead($user_id, $role_id)
    {
        $this->db->where('to_user_id', $user_id);
        $this->db->where('to_user_type', $role_id);
        $this->db->where('is_read', 0);
        $this->db->update('notifications', array('is_read' => 1));
        
        return $this->db->affected_rows();
    }

    public function getUnreadCount($user_id, $role_id)
    {
        $this->db->where('to_user_id', $user_id);
        $this->db->where('to_user_type', $role_id);
        $this->db->where('is_read', 0);
        return $this->db->count_all_results('notifications');
    }


}