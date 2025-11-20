<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @package : SchoolExcel Management System
 * @version : 3.0
 * @developed by : EduprojectsGlobalTech
 * @support : techie.mithlesh@gmail.com
 * @author url : http://codewithmithlesh.com
 * @filename : Rewards.php
 */

class Rewards extends Admin_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('reward_model');
        if (!is_superadmin_loggedin()) {
            access_denied();
        }
    }

    protected function config_validation()
    {
        if (is_superadmin_loggedin()) {
            $this->form_validation->set_rules('branch_id', translate('branch'), 'required');
        }

        $this->form_validation->set_rules('class_id', translate('class'), 'trim|required|numeric');
        $this->form_validation->set_rules('section_id', translate('section'), 'trim|required|numeric');
        $this->form_validation->set_rules('exam_type', translate('exam_type'), 'trim|required|in_list[online,offline,live_exam]');
        $this->form_validation->set_rules('exam_id', translate('exam'), 'required');
        $this->form_validation->set_rules('coin_reward', translate('coin_reward'), 'required|numeric|greater_than[0]');
        $this->form_validation->set_rules('is_active', translate('status'), 'in_list[0,1]');

        // 🟢 Non-live exams (default % rule)
        if ($this->input->post('exam_type') !== 'live_exam') {
            $this->form_validation->set_rules(
                'qualifying_value',
                translate('qualifying_value'),
                'required|numeric|greater_than_equal_to[0]|less_than_equal_to[100]'
            );
            return;
        }

        // 🟢 Live exam specific
        $this->form_validation->set_rules(
            'reward_basis',
            translate('reward_basis'),
            'trim|required|in_list[percentage,rank,percentile]'
        );

        $this->form_validation->set_rules(
            'reward_scope',
            translate('reward_scope'),
            'trim|required|in_list[exam,session]'
        );

        $basis = $this->input->post('reward_basis');
        if ($basis === 'rank') {
            // 🟠 Allow numeric (so 1.0 or 1 is valid), but still >0
            $this->form_validation->set_rules(
                'qualifying_value',
                translate('qualifying_value'),
                'required|numeric|greater_than_equal_to[1]'
            );
        } else {
            // ✅ Percentage or percentile (0–100)
            $this->form_validation->set_rules(
                'qualifying_value',
                translate('qualifying_value'),
                'required|numeric|greater_than_equal_to[0]|less_than_equal_to[100]'
            );
        }
    }

    public function index()
    {
        if (isset($_POST['search'])) {
            if (is_superadmin_loggedin()) {
                $this->form_validation->set_rules('branch_id', translate('branch'), 'required');
            }
            $this->form_validation->set_rules('class_id', translate('class'), 'trim|required|numeric');
            $this->form_validation->set_rules('section_id', translate('section'), 'trim|required');
            if ($this->form_validation->run() == true) {
                $post = $this->input->post();
                $this->data['rewards'] = $this->reward_model->rewardList($post);
            } else {
                $error = $this->form_validation->error_array();
                $array = array('status' => 'fail', 'error' => $error);
            }

        }

        $this->data['sub_page'] = 'reward/index';
        $this->data['main_menu'] = 'Reward';
        $this->data['title'] = translate('rewards');
        $this->data['headerelements'] = array(
            'css' => array(
                'vendor/dropify/css/dropify.min.css',
            ),
            'js' => array(
                'vendor/dropify/js/dropify.min.js',
                'js/student.js'
            ),
        );
        $this->load->view('layout/index', $this->data);
    }

    public function config()
    {
        if ($this->input->post('search')) {
            $this->form_validation->set_rules('branch_id', translate('branch'), 'required');
            $this->form_validation->set_rules('class_id', translate('class'), 'trim|required');
            $this->form_validation->set_rules('section_id', translate('section'), 'trim|required');
            $this->form_validation->set_rules('exam_type', translate('exam_type'), 'trim|required');
            $this->form_validation->set_rules('exam_id', translate('exam'), 'required');

            if ($this->form_validation->run() == true) {
                $branch_id = $this->application_model->get_branch_id();
                $this->data['branch_id'] = $branch_id;
                $class_id = $this->input->post('class_id');
                $section_id = $this->input->post('section_id');
                $exam_type = $this->input->post('exam_type');
                $exam_id = $this->input->post('exam_id');

                $filters = array(
                    'branch_id' => $branch_id,
                    'class_id' => $class_id,
                    'section_id' => $section_id,
                    'exam_type' => $exam_type,
                    'exam_id' => $exam_id,
                );

                $this->data['reward_configs'] = $this->reward_model->getRewardConfigs($filters);
            } else {
                $error = $this->form_validation->error_array();
                $array = array('status' => 'fail', 'error' => $error);
            }
        }

        $this->data['sub_page'] = 'reward/config';
        $this->data['main_menu'] = 'Reward';
        $this->data['title'] = translate('reward_config');
        $this->data['headerelements'] = array(
            'css' => array(
                'vendor/dropify/css/dropify.min.css',
            ),
            'js' => array(
                'vendor/dropify/js/dropify.min.js',
            ),
        );
        $this->load->view('layout/index', $this->data);
    }
    public function configSave()
    {
        if ($_POST) {
            $this->config_validation();
            if ($this->form_validation->run() !== false) {

                $post = $this->input->post();
                $this->reward_model->save($post);

                if ($this->db->affected_rows() > 0) {
                    set_alert('success', translate('information_has_been_saved_successfully'));
                    $url = base_url('rewards/config');
                    responseMsg('success', 'Reward Added Successfully !');
                    $array = array('status' => 'success', 'url' => $url, 'error' => '');
                } else {
                    $array = ['status' => 'fail', 'url' => '', 'error' => ['db' => 'Insert failed']];
                }

            } else {
                $error = $this->form_validation->error_array();
                $array = array('status' => 'fail', 'url' => '', 'error' => $error);
            }

            echo json_encode($array);

        }

    }
    public function configEdit($id = '')
    {
        $reward = $this->db->get_where('reward_config', ['id' => $id])->row_array();
        if (!$reward) {
            set_alert('info', "Reward config doesn't exist with this ID.");
            return redirect()->back();
        }

        if ($_POST) {
            $this->config_validation();
            if ($this->form_validation->run() !== false) {
                $this->reward_model->save($this->input->post());
                set_alert('success', translate('reward_has_been_updated_successfully'));
                $url = base_url('rewards/config');
                $array = array('status' => 'success', 'url' => $url);
            } else {
                $error = $this->form_validation->error_array();
                $array = array('status' => 'fail', 'error' => $error);
            }
            echo json_encode($array);
            exit();
        }

        $this->data['reward'] = $reward;
        $this->data['sub_page'] = 'reward/config_edit';
        $this->data['main_menu'] = 'Reward';
        $this->data['title'] = translate('reward_config');
        $this->data['headerelements'] = array(
            'css' => array(
                'vendor/dropify/css/dropify.min.css',
            ),
            'js' => array(
                'vendor/dropify/js/dropify.min.js',
            ),
        );

        $this->load->view('layout/index', $this->data);
    }

    public function studentRewards()
    {
        $id = $this->input->post('student_id');
        // log_message('debug', 'Student ID received: ' . $id);

        $this->db->select('*');
        $this->db->from('view_student_details');
        $this->db->where('status', 1);
        $this->db->where('student_id', $id);
        $studentDetails = $this->db->get()->row_array();

        if (empty($studentDetails)) {
            responseMsg('error', 'Student not found.');
        }

        $rewards = $this->reward_model->getStudentRewards($id);

        $data = [
            'full_name' => $studentDetails['first_name'] . ' ' . $studentDetails['last_name'],
            'rewards' => $rewards,
        ];

        responseMsg('success', 'Reward History fetched successfully!', $data);
    }

}
