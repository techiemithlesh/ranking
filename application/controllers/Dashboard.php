<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @package : Ramom school management system
 * @version : 2.0
 * @developed by : RamomCoder
 * @support : ramomcoder@yahoo.com
 * @author url : http://codecanyon.net/user/RamomCoder
 * @filename : Accounting.php
 * @copyright : Reserved RamomCoders Team
 */

class Dashboard extends Admin_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('dashboard_model');
        // $this->load->helper('menu');
    }

    public function index()
    {
        $session = $this->session->userdata();
        $branch_id = $this->session->userdata('loggedin_branch');
        $userId = $this->session->userdata('loggedin_userid');

        if (is_student_loggedin() || is_parent_loggedin()) {
            $studentID = 0;
            if (is_student_loggedin()) {

                $this->db->select('branch.name, branch.school_name');
                $this->db->from('branch');
                $this->db->join('enroll', 'branch.id = enroll.branch_id');
                $this->db->where('enroll.branch_id', $branch_id);
                $this->db->where('enroll.student_id', $userId);
                $result = $this->db->get()->row_array();
                if ($result) {
                    $schoolName = $result['school_name'];
                    $this->data['title'] = "Welcome to " . $schoolName . " - " . translate('dashboard');
                } else {
                    $this->data['title'] = translate('welcome_to') . " " . $this->session->userdata('name');
                }
                $studentID = get_loggedin_user_id();
            } elseif (is_parent_loggedin()) {
                $studentID = $this->session->userdata('myChildren_id');

                $this->db->select('branch.name, branch.school_name');
                $this->db->from('branch');
                $this->db->join('parent', 'branch.id = parent.branch_id');
                $this->db->where('parent.id', $userId);
                $result = $this->db->get()->row_array();

                if ($result) {
                    $schoolName = $result['school_name'];
                    $this->data['title'] = "Welcome to " . $schoolName . " - " . translate('dashboard');
                } else {
                    $this->data['title'] = translate('welcome_to') . " " . $this->session->userdata('name');
                }
            }
            $this->data['student_id'] = $studentID;
            $schoolID = get_loggedin_branch_id();
            $this->data['school_id'] = $schoolID;
            if (!is_new_design()) {
                $this->data['sub_page'] = 'userrole/dashboard';
            }


            return $this->home();

        } else {
            if (is_superadmin_loggedin()) {
                if ($this->input->get('school_id')) {
                    $schoolID = $this->input->get('school_id');
                    $this->data['title'] = get_type_name_by_id('branch', $schoolID) . " " . translate('branch_dashboard');
                } else {
                    $this->data['title'] = translate('all_branch_dashboard');
                    $schoolID = "";
                }
            } else {
                $schoolID = get_loggedin_branch_id();
                $this->data['title'] = get_type_name_by_id('branch', $schoolID) . " " . translate('branch_dashboard');
            }
            $this->data['school_id'] = $schoolID;
            $this->data['student_by_class'] = $this->dashboard_model->getStudentByClass($schoolID);
            $this->data['fees_summary'] = $this->dashboard_model->annualFeessummaryCharts($schoolID);
            $this->data['income_vs_expense'] = $this->dashboard_model->getIncomeVsExpense($schoolID);
            $this->data['weekend_attendance'] = $this->dashboard_model->getWeekendAttendance($schoolID);
            $this->data['get_monthly_admission'] = $this->dashboard_model->getMonthlyAdmission($schoolID);
            $this->data['get_voucher'] = $this->dashboard_model->getVoucher($schoolID);
            $this->data['get_transport_route'] = $this->dashboard_model->get_transport_route($schoolID);
            $this->data['get_total_student'] = $this->dashboard_model->get_total_student($schoolID);
            $this->data['sub_page'] = 'dashboard/index';
        }
        $this->data['headerelements'] = array(
            'css' => array(
                'vendor/fullcalendar/fullcalendar.css',
            ),
            'js' => array(
                'vendor/chartjs/chart.min.js',
                'vendor/echarts/echarts.common.min.js',
                'vendor/moment/moment.js',
                'vendor/fullcalendar/fullcalendar.js',
            ),
        );
        $this->data['main_menu'] = 'dashboard';
        $this->load->view('layout/index', $this->data);
    }

    public function home()
    {

        $this->data['sub_page'] = 'userrole/student/home';
        $this->data['headerelements'] = array(
            'css' => array(
                'vendor/fullcalendar/fullcalendar.css',
            ),
            'js' => array(
                'vendor/chartjs/chart.min.js',
                'vendor/echarts/echarts.common.min.js',
                'vendor/moment/moment.js',
                'vendor/fullcalendar/fullcalendar.js',
            ),
        );
        $this->data['menu'] = get_menu_by_role();
        $this->data['main_menu'] = 'Home';
        $this->load->view('layout/index', $this->data);
    }
}
