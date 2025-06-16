<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}


class Skillreport_model extends MY_Model
{

    public function __construct()
    {
        parent::__construct();
    }

    public function getSkillCategoryList()
    {
        $this->db->select('tbl_skill_categories.id, tbl_skill_categories.category_name,
            subject.id AS subject_id, subject.name AS subject_name, branch.id AS branch_id, branch.name AS branch_name');
        $this->db->from('tbl_skill_categories');
        $this->db->join('subject', 'subject.id = tbl_skill_categories.subject_id', 'left');
        $this->db->join('branch', 'branch.id = tbl_skill_categories.branch_id', 'left');

        // If not superadmin, filter by logged-in branch ID
        if (!is_superadmin_loggedin()) {
            $this->db->where('tbl_skill_categories.branch_id', get_loggedin_branch_id());
        }

        return $this->db->get()->result_array();
    }



    public function getCriterialList()
    {
        $this->db->select('
        tbl_skill_criteria.id,
        tbl_skill_criteria.branch_id,
        tbl_skill_criteria.class_id,
        tbl_skill_criteria.section_id,
        tbl_skill_criteria.subject_id,
        tbl_skill_criteria.exam_id,
        tbl_skill_criteria.skill_category_id,
        tbl_skill_criteria.criteria_text,
        branch.name as branch_name,
        class.name as class_name,
        section.name as section_name,
        subject.name as subject_name,
        exam.name as exam_name,
        tbl_skill_categories.category_name as skill_category_name
    ');
        $this->db->from('tbl_skill_criteria');
        $this->db->join('branch', 'branch.id = tbl_skill_criteria.branch_id', 'left');
        $this->db->join('class', 'class.id = tbl_skill_criteria.class_id', 'left');
        $this->db->join('section', 'section.id = tbl_skill_criteria.section_id', 'left');
        $this->db->join('subject', 'subject.id = tbl_skill_criteria.subject_id', 'left');
        $this->db->join('exam', 'exam.id = tbl_skill_criteria.exam_id', 'left');
        $this->db->join('tbl_skill_categories', 'tbl_skill_categories.id = tbl_skill_criteria.skill_category_id', 'left');

        // Apply branch filter if not superadmin
        if (!is_superadmin_loggedin()) {
            $this->db->where('tbl_skill_criteria.branch_id', get_loggedin_branch_id());
        }

        return $this->db->get()->result_array();
    }


    public function getSkillDetails($classID, $sectionID, $examID, $subjectID)
    {
        $this->db->select('sc.id as category_id, sc.category_name, cri.id as criteria_id, cri.criteria_text');
        $this->db->from('tbl_skill_criteria as cri');
        $this->db->join('tbl_skill_categories as sc', 'sc.id = cri.skill_category_id');
        $this->db->where('cri.class_id', $classID);
        $this->db->where('cri.section_id', $sectionID);
        $this->db->where('cri.exam_id', $examID);
        $this->db->where('cri.subject_id', $subjectID);
        $query = $this->db->get();
        return $query->result_array();
    }


    public function getSkillCategoryBySubject($subjectID)
    {
        $this->db->select('id, category_name');
        $this->db->from('tbl_skill_categories');
        $this->db->where('subject_id', $subjectID);
        $query = $this->db->get();
        return $query->result_array();
    }

    public function getSkillCriteria($branchID, $classID, $sectionID, $examID, $subjectID)
    {
        $this->db->select('sc.id, sc.skill_category_id, sc.criteria_text, cat.category_name');
        $this->db->from('tbl_skill_criteria as sc');
        $this->db->join('tbl_skill_categories as cat', 'sc.skill_category_id = cat.id', 'left');
        $this->db->where('sc.branch_id', $branchID);
        $this->db->where('sc.class_id', $classID);
        $this->db->where('sc.section_id', $sectionID);
        $this->db->where('sc.exam_id', $examID);
        $this->db->where('sc.subject_id', $subjectID);
        $this->db->order_by('sc.skill_category_id', 'ASC');
        $query = $this->db->get();
        return $query->result_array();
    }





    public function getExistingAssessments($studentID, $examID, $subjectID)
    {
        $this->db->select('skill_criteria_id, assessment_level');
        $this->db->from('tbl_skill_assessments');
        $this->db->where('student_id', $studentID);
        $this->db->where('exam_id', $examID);
        $this->db->where('subject_id', $subjectID);
        $query = $this->db->get();

        $assessments = [];
        foreach ($query->result_array() as $row) {
            $assessments[$row['skill_criteria_id']] = $row['assessment_level'];
        }
        return $assessments;
    }


}