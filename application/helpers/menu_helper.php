<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('get_menu_by_role')) {
    function get_menu_by_role() {
        $CI = &get_instance();
        $CI->load->config('menu');
        if (is_student_loggedin()) {
            return $CI->config->item('menus')['student'];
        }
        if (is_parent_loggedin()) {
            return $CI->config->item('menus')['parent'];
        }
        // add teacher, admin, etc.
        return [];
    }
}
