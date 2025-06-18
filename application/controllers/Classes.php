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

class Classes extends Admin_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('classes_model');
    }

    /* class form validation rules */
    protected function class_validation($id = null)
    {
        $this->form_validation->set_rules('name', translate('name'), [
            'trim',
            'required',
            [
                'uniqueClassNameCheck',
                function ($name) use ($id) {
                    $this->db->where('name', $name);
                    if ($id)
                        $this->db->where('id !=', $id);
                    return $this->db->get('class')->num_rows() == 0;
                }
            ]
        ]);
    }



    public function uniqueClassNameCheck($name = '')
    {
        $this->db->where('name', $name);
        $exists = $this->db->get('class')->num_rows();

        if ($exists > 0) {
            $this->form_validation->set_message('uniqueClassNameCheck', translate('the_name_already_exists'));
            return false;
        }
        return true;
    }


    public function index()
    {
        if (!get_permission('classes', 'is_view')) {
            access_denied();
        }
        if ($_POST) {
            if (get_permission('classes', 'is_add')) {
                $this->class_validation();
                if ($this->form_validation->run() !== false) {
                    $arrayClass = array(
                        'name' => $this->input->post('name')
                    );

                    if (is_superadmin_loggedin()) {
                        $arrayClass['created_by_branch'] = NULL;
                    } else {
                        $arrayClass['created_by_branch'] = get_loggedin_branch_id();
                    }

                    $this->db->insert('class', $arrayClass);
                    $class_id = $this->db->insert_id();

                    if ($class_id) {
                        set_alert('success', translate('information_has_been_saved_successfully'));
                        $url = base_url('classes');
                        $array = array('status' => 'success', 'url' => $url, 'error' => '');
                    }
                } else {
                    $error = $this->form_validation->error_array();
                    $array = array('status' => 'fail', 'url' => '', 'error' => $error);
                }
                echo json_encode($array);
                exit();
            }
        }

        $this->data['classlist'] = get_classes_by_user();

        $this->data['query_classes'] = $this->db->get('class');
        $this->data['title'] = translate('control_classes');
        $this->data['sub_page'] = 'classes/index';
        $this->data['main_menu'] = 'classes';
        $this->load->view('layout/index', $this->data);

    }

    public function edit($id = '')
    {
        $this->data['class'] = $this->app_lib->getTable('class', array('t.id' => $id), true);
        $this->data['title'] = translate('control_classes');
        $this->data['sub_page'] = 'classes/edit';
        $this->data['main_menu'] = 'classes';
        $this->load->view('layout/index', $this->data);
    }


    public function updateClass()
    {
        if ($_POST) {
            $id = $this->input->post('class_id');
            $this->class_validation($id);

            if ($this->form_validation->run() !== false) {
                $arrayClass = array(
                    'name' => $this->input->post('name')
                );

                if (!is_superadmin_loggedin()) {
                    $arrayClass['created_by_branch'] = get_loggedin_branch_id();
                }
                $this->db->where('id', $id);
                $this->db->update('class', $arrayClass);
                $url = base_url('classes');
                $array = array('status' => 'success', 'url' => $url, 'error' => '');
            } else {
                $error = $this->form_validation->error_array();
                $array = array('status' => 'fail', 'url' => '', 'error' => $error);
            }

            echo json_encode($array);
            exit();
        }
    }


    public function delete($id = '')
    {
        try {

            if (get_permission('classes', 'is_delete')) {
                $this->db->trans_begin();
                $this->db->where("class_id", $id)->delete("class_books");

                if (!is_superadmin_loggedin()) {
                    $this->db->where('branch_id', get_loggedin_branch_id());
                }
                $this->db->where('id', $id);
                $this->db->delete('class');
                if ($this->db->affected_rows() > 0) {
                    $this->db->where('class_id', $id);
                    $this->db->delete('sections_allocation');
                }
                if ($this->db->trans_status()) {
                    $this->db->trans_commit();
                    responseMsg(true, "data Delete Successfully", "");
                } else {
                    $this->db->trans_rollback();
                }
                responseMsg(false, "data Not Delete Due To Server Error", $this->db->error());
            } else {
                responseMsg(false, "Access denied", null);
            }
        } catch (Exception $e) {
            responseMsg(false, "Server Error!!!", null);
        }
    }

    // class teacher allocation
    public function teacher_allocation()
    {
        if (!get_permission('assign_class_teacher', 'is_view')) {
            access_denied();
        }
        $branch_id = $this->application_model->get_branch_id();
        $this->data['branch_id'] = $branch_id;
        $this->data['query'] = $this->classes_model->getTeacherAllocation($branch_id);
        $this->data['title'] = translate('assign_class_teacher');
        $this->data['sub_page'] = 'classes/teacher_allocation';
        $this->data['main_menu'] = 'classes';
        $this->load->view('layout/index', $this->data);
    }

    public function getAllocationTeacher()
    {
        if (get_permission('assign_class_teacher', 'is_edit')) {
            $allocation_id = $this->input->post('id');
            $this->data['data'] = $this->app_lib->get_table('teacher_allocation', $allocation_id, true);
            $this->load->view('classes/tallocation_modalEdit', $this->data);
        }
    }

    public function teacher_allocation_save()
    {
        if ($_POST) {
            if (is_superadmin_loggedin()) {
                $this->form_validation->set_rules('branch_id', translate('branch'), 'required');
            }
            $this->form_validation->set_rules('class_id', translate('class'), 'required');
            $this->form_validation->set_rules('section_id', translate('section'), 'required|callback_unique_sectionID');
            $this->form_validation->set_rules('staff_id', translate('teacher'), 'required|callback_unique_teacherID');
            if ($this->form_validation->run() !== false) {
                $post = $this->input->post();
                $this->classes_model->teacherAllocationSave($post);
                $url = base_url('classes/teacher_allocation');
                $array = array('status' => 'success', 'url' => $url);
            } else {
                $error = $this->form_validation->error_array();
                $array = array('status' => 'fail', 'error' => $error);
            }
            echo json_encode($array);
        }
    }

    public function teacher_allocation_delete($id = '')
    {
        if (get_permission('assign_class_teacher', 'is_delete')) {
            if (!is_superadmin_loggedin()) {
                $this->db->where('branch_id', get_loggedin_branch_id());
            }
            $this->db->where('id', $id);
            $this->db->delete('teacher_allocation');
        }
    }

    // validate here, if the check teacher allocated for this class
    public function unique_teacherID($teacher_id)
    {
        if (!empty($teacher_id)) {
            $classID = $this->input->post('class_id');
            $sectionID = $this->input->post('section_id');
            $allocationID = $this->input->post('allocation_id');
            if (!empty($allocationID)) {
                $this->db->where_not_in('id', $allocationID);
            }
            $this->db->where('teacher_id', $teacher_id);
            $this->db->where('class_id', $classID);
            $this->db->where('section_id', $sectionID);
            $query = $this->db->get('teacher_allocation');
            if ($query->num_rows() > 0) {
                $this->form_validation->set_message("unique_teacherID", translate('class_teachers_are_already_allocated_for_this_class'));
                return false;
            } else {
                return true;
            }
        }
    }

    // validate here, if the check teacher allocated for this class
    public function unique_sectionID($sectionID)
    {
        if (!empty($sectionID)) {
            $classID = $this->input->post('class_id');
            $allocationID = $this->input->post('allocation_id');
            if (!empty($allocationID)) {
                $this->db->where_not_in('id', $allocationID);
            }
            $this->db->where('class_id', $classID);
            $this->db->where('section_id', $sectionID);
            $query = $this->db->get('teacher_allocation');
            if ($query->num_rows() > 0) {
                $this->form_validation->set_message("unique_sectionID", translate('this_class_teacher_already_assigned'));
                return false;
            } else {
                return true;
            }
        }
    }

    public function classAssign()
    {
        if ($_POST) {
            $branch_ids = $this->input->post('branch_id', true);
            $class_ids = $this->input->post('class_assign', true);

            if (!empty($branch_ids) && !empty($class_ids)) {
                $data = [
                    'branch_ids' => $branch_ids,
                    'class_ids' => $class_ids
                ];
                $this->classes_model->classAllocationSave($data);

                echo json_encode([
                    'status' => 'success',
                    'message' => 'Class assignment updated successfully.',
                    'url' => base_url('classes/classAssign')
                ]);

                exit();
            } else {
                echo json_encode([
                    'status' => 'fail',
                    'message' => 'Please select at least one branch and one class.'
                ]);
                exit();
            }
        }

        $this->data['global_classes'] = $this->app_lib->get_global_classes();

        $this->db->select('cbm.id, b.name AS branch_name, c.name AS class_name');
        $this->db->from('class_branch_map cbm');
        $this->db->join('branch b', 'b.id = cbm.branch_id', 'left');
        $this->db->join('class c', 'c.id = cbm.class_id', 'left');
        $this->db->order_by('b.name ASC, c.name ASC');
        $this->data['assigned_class_list'] = $this->db->get()->result_array();

        $this->data['title'] = translate('assign_branch_class');
        $this->data['sub_page'] = 'classes/class_allocation';
        $this->data['main_menu'] = 'classes';
        $this->load->view('layout/index', $this->data);
    }


    public function deleteClassAssign($id = 0)
    {
        if (is_superadmin_loggedin()) {

            $row = $this->db->where('id', $id)->get('class_branch_map')->row();

            if ($row) {
                $this->db->where('id', $id)->delete('class_branch_map');

                set_alert('success', 'Class assignment deleted successfully.');
            } else {
                set_alert('error', 'Invalid assignment selected.');
            }

            redirect(base_url('classes/classAssign'));
        }
    }

}
