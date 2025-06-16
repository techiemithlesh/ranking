<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @package : Eduprojects Global PVT LTD
 * @version : 2.0
 * @developed by : Schoolexcel
 * @support : support@schoolexcel.tch
 * @author url : Mithlesh Patel
 * @filename : Notification.php
 * @copyright : Eduproject Global PVT LTD
 */



class Notification extends Admin_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->helpers('custom_fields');
        $this->load->model('notification_model');
    }

    public function index()
    {
        redirect(base_url('notification/all'));
    }

    public function all()
    {
        $user_id = get_loggedin_user_id();
        $role_id = loggedin_role_id();

        $notifications = $this->notification_model->getAllNotifications($user_id, $role_id, 50);
        
        usort($notifications, function($a, $b) {
            if ($a['is_read'] == $b['is_read']) {
                return $b['id'] - $a['id'];
            }
            return $a['is_read'] - $b['is_read'];
        });
        
        $this->data['notifications'] = $notifications;
        $this->data['title'] = translate('notifications');
        $this->data['sub_page'] = 'notification/all';
        $this->data['main_menu'] = 'notification';
        $this->load->view('layout/index', $this->data);
    }

    public function mark_as_read()
    {
        if ($this->input->is_ajax_request()) {
            $notification_id = $this->input->post('notification_id');
            $user_id = get_loggedin_user_id();
            $role_id = loggedin_role_id();
            
            $success = $this->notification_model->markAsRead($notification_id, $user_id, $role_id);
            
            $response = array(
                'success' => $success
            );
            
            echo json_encode($response);
            exit;
        }
    }


    public function mark_all_read()
    {
        $user_id = get_loggedin_user_id();
        $role_id = loggedin_role_id();
        
        $this->notification_model->markAllAsRead($user_id, $role_id);
        
        if ($this->input->is_ajax_request()) {
            $response = array(
                'success' => true
            );
            echo json_encode($response);
            exit;
        } else {
            redirect(base_url('notification/all'));
        }
    }


}

