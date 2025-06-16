<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @package : SchoolExcel school management system
 * @version : 2.0
 * @developed by : Mithlesh Patel
 * @support : techie.mithlesh@gmail.com
 * @author url : http://codewithmithlesh
 * @filename : SkillReport.php
 * @copyright : SchoolExcel
 */

class SkillReport extends Admin_Controller
{


    public function __construct()
    {
        parent::__construct();
        $this->load->model('application_model');
        $this->load->library('session');
        $this->load->model('skillreport_model');
    }

    public function category()
    {
        if ($this->input->post()) {
            if (is_superadmin_loggedin() || is_admin_loggedin()) {
                $this->form_validation->set_rules('subject_id', 'Subject', 'trim|required|numeric');
                $this->form_validation->set_rules('category_name[]', 'Category Name', 'trim|required');

                if (is_superadmin_loggedin()) {
                    $this->form_validation->set_rules('branch_id', 'Branch', 'trim|required|numeric');
                    $branchID = $this->input->post('branch_id');
                } else {
                    $branchID = get_loggedin_branch_id();
                }

                if ($this->form_validation->run() !== false) {

                    $category_names = $this->input->post('category_name');

                    foreach ($category_names as $name) {
                        if (!empty(trim($name))) {
                            $arrayStatus = array(
                                'subject_id' => $this->input->post('subject_id'),
                                'category_name' => trim($name),
                                'branch_id' => $branchID,
                            );
                            
                            $this->db->insert('tbl_skill_categories', $arrayStatus);
                        }
                    }

                    set_alert('success', translate('information_has_been_saved_successfully'));
                    $response = array(
                        'status' => 'success',
                        'url' => base_url('SkillReport/category'),
                        'error' => '',
                    );
                } else {

                    $response = array(
                        'status' => 'fail',
                        'url' => '',
                        'error' => $this->form_validation->error_array(),
                    );
                }

                echo json_encode($response);
                exit();
            }
        }

        $this->data['skill_category'] = $this->skillreport_model->getSkillCategoryList();
        $this->data['title'] = translate('skill_category');
        $this->data['sub_page'] = 'report/skill/category';
        $this->data['main_menu'] = 'skill_report';

        $this->load->view('layout/index', $this->data);
    }



    public function update()
    {
        // if (!get_permission('skill_status', 'is_edit')) {
        //     access_denied();
        // }

        if ($this->input->post()) {
            // Validation Rules
            $this->form_validation->set_rules('category_name', 'Category Name', 'trim|required');
            $this->form_validation->set_rules('subject_id', 'Subject', 'trim|required');

            if (is_superadmin_loggedin()) {
                $this->form_validation->set_rules('branch_id', 'Branch', 'trim|required');
                $branchID = $this->input->post('branch_id');
            } else {
                $branchID = get_loggedin_branch_id();
            }

            if ($this->form_validation->run() !== false) {
                $skillcategory_id = $this->input->post('skillstatus_id');
                $updateData = array(
                    'category_name' => $this->input->post('category_name'),
                    'subject_id' => $this->input->post('subject_id'),
                    'branch_id' => $branchID,
                );

                $this->db->where('id', $skillcategory_id);
                $this->db->update('tbl_skill_categories', $updateData);
                $response = array(
                    'status' => 'success',
                    'message' => translate('information_has_been_updated_successfully'),
                    'url' => base_url('SkillReport/category'),
                );
            } else {
                $response = array(
                    'status' => 'fail',
                    'errors' => $this->form_validation->error_array(),
                );
            }

            echo json_encode($response);
            exit();
        }
    }



    public function delete($id = '')
    {
        if (!is_superadmin_loggedin()) {
            $this->db->where('branch_id', get_loggedin_branch_id());
        }
        $this->db->where('id', $id);
        $this->db->delete('tbl_skill_categories');
    }


    public function skillCriteria()
    {
        // if (!get_permission('skill_criteria', 'is_add')) {
        //     access_denied();
        // }

        if ($_POST) {

            if (is_superadmin_loggedin()) {
                $this->form_validation->set_rules('branch_id', translate('branch'), 'trim|required');
                $branchID = $this->input->post('branch_id');
            } else {
                $branchID = get_loggedin_branch_id();
            }

            $this->form_validation->set_rules('class_id', translate('class'), 'trim|required');
            $this->form_validation->set_rules('section_id', translate('section'), 'trim|required');
            $this->form_validation->set_rules('subject_id', translate('subject'), 'trim|required');
            $this->form_validation->set_rules('exam_id', translate('exam'), 'trim|required');
            $this->form_validation->set_rules('skill_category_id', translate('skill_category'), 'trim|required');
            $this->form_validation->set_rules('criteria_text[]', translate('criteria / remarks'), 'trim|required');
            if ($this->form_validation->run() === true) {

                $criteria_texts = $this->input->post('criteria_text');

                foreach ($criteria_texts as $text) {
                    if (!empty(trim($text))) {
                        $data = array(
                            'branch_id' => $branchID,
                            'class_id' => $this->input->post('class_id'),
                            'section_id' => $this->input->post('section_id'),
                            'subject_id' => $this->input->post('subject_id'),
                            'exam_id' => $this->input->post('exam_id'),
                            'skill_category_id' => $this->input->post('skill_category_id'),
                            'criteria_text' => $text,
                            'created_at' => date('Y-m-d H:i:s'),
                        );
                        $this->db->insert('tbl_skill_criteria', $data);
                    }
                }
                
                set_alert('success', translate('skill_criteria_added_successfully'));
                redirect(base_url('SkillReport/skillCriteria'));
            } else {
                $this->session->set_flashdata('errors', $this->form_validation->error_array());
            }
        }

        $this->data['skill_criteria'] = $this->skillreport_model->getCriterialList();
        $this->data['title'] = translate('skill_criteria');
        $this->data['sub_page'] = 'report/skill/criteria';
        $this->data['main_menu'] = 'skill_report';

        $this->load->view('layout/index', $this->data);

    }


    public function updateCriteria()
    {
        // Check if super admin
        if (is_superadmin_loggedin()) {
            $this->form_validation->set_rules('branch_id', translate('branch'), 'trim|required');
            $branchID = $this->input->post('branch_id');
        } else {
            $branchID = $this->application_model->get_branch_id();
        }

        // Set validation rules
        $this->form_validation->set_rules('class_id', translate('class'), 'trim|required');
        $this->form_validation->set_rules('section_id', translate('section'), 'trim|required');
        $this->form_validation->set_rules('subject_id', translate('subject'), 'trim|required');
        $this->form_validation->set_rules('exam_id', translate('exam'), 'trim|required');
        $this->form_validation->set_rules('skill_category_id', translate('skill_category'), 'trim|required');
        $this->form_validation->set_rules('criteria_text', translate('criteria / remarks'), 'trim|required|min_length[3]');

        // Validate form
        if ($this->form_validation->run() == FALSE) {
            $errors = array(
                'class_id' => form_error('class_id'),
                'section_id' => form_error('section_id'),
                'subject_id' => form_error('subject_id'),
                'exam_id' => form_error('exam_id'),
                'skill_category_id' => form_error('skill_category_id'),
                'criteria_text' => form_error('criteria_text'),
            );

            // Include branch_id error only for super admins
            if (is_superadmin_loggedin()) {
                $errors['branch_id'] = form_error('branch_id');
            }

            echo json_encode(['status' => 'error', 'errors' => $errors]);
            return;
        }

        // Data for update
        $data = array(
            'branch_id' => $branchID, // Use processed branch ID
            'exam_id' => $this->input->post('exam_id'),
            'class_id' => $this->input->post('class_id'),
            'section_id' => $this->input->post('section_id'),
            'subject_id' => $this->input->post('subject_id'),
            'skill_category_id' => $this->input->post('skill_category_id'),
            'criteria_text' => $this->input->post('criteria_text'),
        );

        // Update criteria in the database
        $this->db->where('id', $this->input->post('criteria_id'));
        $update = $this->db->update('tbl_skill_criteria', $data);

        if ($update) {
            echo json_encode(['status' => 'success', 'message' => translate('criteria_updated_successfully')]);
        } else {
            echo json_encode(['status' => 'error', 'message' => translate('database_update_failed')]);
        }
    }


    public function deleteCriteria($id)
    {
        if (!is_superadmin_loggedin()) {
            $this->db->where('branch_id', get_loggedin_branch_id());
        }
        $this->db->where('id', $id);
        $this->db->delete('tbl_skill_criteria');

    }


    public function assessment()
    {
        if (is_superadmin_loggedin()) {
            $this->form_validation->set_rules('branch_id', translate('branch'), 'trim|required');
            $branchID = $this->input->post('branch_id');
        } else {
            $branchID = $this->application_model->get_branch_id();
        }

        if ($this->input->post('search')) {
            $this->form_validation->set_rules('class_id', translate('class'), 'trim|required');
            $this->form_validation->set_rules('section_id', translate('section'), 'trim|required');
            $this->form_validation->set_rules('subject_id', translate('subject'), 'trim|required');
            $this->form_validation->set_rules('exam_id', translate('exam'), 'trim|required');
            $this->form_validation->set_rules('student_id', translate('student'), 'trim|required');

            if ($this->form_validation->run() !== FALSE) {
                $classID = $this->input->post('class_id');
                $sectionID = $this->input->post('section_id');
                $subjectID = $this->input->post('subject_id');
                $examID = $this->input->post('exam_id');
                $studentId = $this->input->post('student_id');
                $this->data['skill_criteria'] = $this->skillreport_model->getSkillCriteria($branchID, $classID, $sectionID, $examID, $subjectID);
                $this->data['existing_assessments'] = $this->skillreport_model->getExistingAssessments($studentId, $examID, $subjectID);
            }
        }

        $this->data['branch_id'] = $branchID;
        $this->data['class_id'] = set_value('class_id');
        $this->data['section_id'] = set_value('section_id');
        $this->data['subject_id'] = set_value('subject_id');
        $this->data['exam_id'] = set_value('exam_id');
        $this->data['student_id'] = set_value('student_id');

        $this->data['title'] = translate('Performance_review');
        $this->data['sub_page'] = 'report/skill/assessment';
        $this->data['main_menu'] = 'skill_report';

        $this->load->view('layout/index', $this->data);
    }



    public function saveAssessment()
    {
        $response = array('success' => false, 'message' => 'No data submitted');
        if ($this->input->post()) {
            try {
                $branchId = $this->input->post('branch_id');
                $studentID = $this->input->post('student_id');
                $examID = $this->input->post('exam_id');
                $subjectID = $this->input->post('subject_id');
                $classID = $this->input->post('class_id');
                $sectionID = $this->input->post('section_id');
                $assessments = $this->input->post('assessment');
                if (empty($branchId) || empty($studentID) || empty($examID) || empty($subjectID) || empty($classID) || empty($sectionID)) {
                    $response = array('success' => false, 'message' => 'Required fields are missing');
                }
                // Check if assessments array exists
                elseif (!is_array($assessments) || empty($assessments)) {
                    $response = array('success' => false, 'message' => 'No assessment data submitted');
                } else {
                    $recordsUpdated = 0;
                    $recordsInserted = 0;

                    foreach ($assessments as $criteriaID => $level) {
                        // Skip if no assessment level is selected
                        if (empty($level))
                            continue;

                        $this->db->where('student_id', $studentID);
                        $this->db->where('exam_id', $examID);
                        $this->db->where('subject_id', $subjectID);
                        $this->db->where('skill_criteria_id', $criteriaID);
                        $query = $this->db->get('tbl_skill_assessments');

                        if ($query->num_rows() > 0) {
                            // Update existing record
                            $this->db->where('student_id', $studentID);
                            $this->db->where('exam_id', $examID);
                            $this->db->where('subject_id', $subjectID);
                            $this->db->where('skill_criteria_id', $criteriaID);
                            $this->db->update('tbl_skill_assessments', ['assessment_level' => $level]);
                            $recordsUpdated++;
                        } else {
                            // Insert new record
                            $data = [
                                'student_id' => $studentID,
                                'exam_id' => $examID,
                                'subject_id' => $subjectID,
                                'skill_criteria_id' => $criteriaID,
                                'assessment_level' => $level,
                                'branch_id' => $branchId,
                                'class_id' => $classID,
                                'section_id' => $sectionID,
                            ];

                            $this->db->insert('tbl_skill_assessments', $data);
                            $recordsInserted++;
                        }
                    }

                    if ($recordsInserted > 0 || $recordsUpdated > 0) {
                        $response = array(
                            'success' => true,
                            'message' => 'Assessment saved successfully! (Updated: ' . $recordsUpdated . ', Inserted: ' . $recordsInserted . ')'
                        );
                    } else {
                        $response = array('success' => false, 'message' => 'No records were updated or inserted');
                    }
                }
            } catch (Exception $e) {
                $response = array('success' => false, 'message' => 'Error: ' . $e->getMessage());
                // Log the error
                log_message('error', 'Error in saveAssessment: ' . $e->getMessage());
            }
        }

        // If AJAX request, return JSON response
        if ($this->input->is_ajax_request()) {
            echo json_encode($response);
            exit;
        }

        if ($response['success']) {
            $this->session->set_flashdata('success', $response['message']);
        } else {
            $this->session->set_flashdata('error', $response['message']);
        }

        redirect(base_url('SkillReport/assessment'));
    }

}

