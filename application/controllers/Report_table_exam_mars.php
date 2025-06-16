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

class Report extends Admin_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('report_model');
        $this->load->model('fees_model');
        $this->load->model('application_model');
        $this->load->library('session');
    }

    public function progress()
    {
        $this->data = array();
        $branchID = $this->application_model->get_branch_id();

        if ($this->input->post('search')) {
            $classID = $this->input->post('class_id');
            $studentId  = $this->input->post('student_id');

            $sessionReportData = array(
                'reportbranch_id' => $branchID,
                'reportclass_id' => $classID,
                'reportstudent_id' => $studentId
            );
            $this->session->set_userdata($sessionReportData);
        }

        $this->data['branch_id'] = $branchID;
        $this->data['title'] = 'Progress Report';

        $this->data['headerelements']   = array(
            'css' => array(
                'vendor/daterangepicker/daterangepicker.css',
            ),
            'js' => array(
                'vendor/moment/moment.js',
                'vendor/daterangepicker/daterangepicker.js',
            ),
        );
        $this->load->view('report/progress', $this->data);
    }

    public function annual_examination_report()
    {
       $student_id = $this->session->userdata('reportstudent_id');
        $branch_id = $this->session->userdata('reportbranch_id');


        $this->data = array();

       $query = $this->db->query("SELECT * FROM student WHERE id='".$student_id."'");
       $this->data['studentData'] = $query->row();

       $studentMpped = $this->report_model->getStudentDetails($student_id);
       $this->data['studentMpped'] = $studentMpped;

       //echo '<pre>';
       //print_r($this->data['studentMpped']);
       //echo $studentMpped->class_id;
		
		$query2 = $this->db->query("SELECT * FROM branch WHERE id='".$branch_id."'");
        $this->data['branchData'] = $query2->row();
		
		
		$currentYear = date('Y')-1;
		$firstDate = $currentYear . '-01-01';
		$lastDate = $currentYear . '-12-31';
		$firstDateObj = strtotime($firstDate);
		$lastDateObj = strtotime($lastDate);
		
		$firstDate = date("Y-m-d", $firstDateObj);
		$lastDate = date("Y-m-d", $lastDateObj);
		
		$query3 = $this->db->query("SELECT * FROM student_attendance WHERE student_id='".$student_id."' AND branch_id = '".$branch_id."' AND DATE(date) >= '".$firstDate."' AND DATE(date) <= '".$lastDate."' AND status = 'P'");
        $this->data['present'] = sizeof($query3->result());
		
		$query4 = $this->db->query("SELECT * FROM student_attendance WHERE student_id='".$student_id."' AND branch_id = '".$branch_id."' AND DATE(date) >= '".$firstDate."' AND DATE(date) <= '".$lastDate."' AND status = 'A'");
        $this->data['absent'] = sizeof($query4->result());
		$this->data['totalSchoolDay'] = sizeof($query3->result())+sizeof($query4->result());
        $this->data['absent'] = sizeof($query4->result());
		$this->data['totalSchoolDay'] = sizeof($query3->result())+sizeof($query4->result());

       // $query5 = $this->db->query("SELECT * FROM mark as m LEFT JOIN subject as s ON s.id = m.subject_id WHERE m.student_id = '".$student_id."' AND m.class_id = '".$studentMpped->class_id."' AND m.section_id='".$studentMpped->section_id."' AND m.branch_id='".$branch_id."'");

        $query5 = $this->db->query("SELECT * FROM timetable_exam as m LEFT JOIN subject as s ON s.id = m.subject_id WHERE m.student_id = '".$student_id."' AND m.class_id = '".$studentMpped->class_id."' AND m.section_id='".$studentMpped->section_id."' AND m.branch_id='".$branch_id."'");
       
		$this->data['marks'] = $query5->result();
        //echo '<pre>';
        //print_r($this->data['marks']);
		
        $this->data['title'] = 'Exam Based - Matterhorn Report';
		//echo '<pre>';
		//print_r($this->data);
        $this->load->view('report/annual_examination_report/report', $this->data);
    }

    public function skill_based_report()
    {
        $this->data = array();
        $student_id = $this->session->userdata('reportstudent_id');
        $branch_id = $this->session->userdata('reportbranch_id');
        $this->data = array();

       $query = $this->db->query("SELECT * FROM student WHERE id='".$student_id."'");
       $this->data['studentData'] = $query->row();

       $studentMpped = $this->report_model->getStudentDetails($student_id);
       $this->data['studentMpped'] = $studentMpped;
       //echo '<pre>';
       //print_r($this->data['studentMpped']);
       //echo $studentMpped->class_id;
		
		$query2 = $this->db->query("SELECT * FROM branch WHERE id='".$branch_id."'");
        $this->data['branchData'] = $query2->row();
		
		
		$currentYear = date('Y')-1;
		$firstDate = $currentYear . '-01-01';
		$lastDate = $currentYear . '-12-31';
		$firstDateObj = strtotime($firstDate);
		$lastDateObj = strtotime($lastDate);
		
		$firstDate = date("Y-m-d", $firstDateObj);
		$lastDate = date("Y-m-d", $lastDateObj);
		
		$query3 = $this->db->query("SELECT * FROM student_attendance WHERE student_id='".$student_id."' AND branch_id = '".$branch_id."' AND DATE(date) >= '".$firstDate."' AND DATE(date) <= '".$lastDate."' AND status = 'P'");
        $this->data['present'] = sizeof($query3->result());
		
		$query4 = $this->db->query("SELECT * FROM student_attendance WHERE student_id='".$student_id."' AND branch_id = '".$branch_id."' AND DATE(date) >= '".$firstDate."' AND DATE(date) <= '".$lastDate."' AND status = 'A'");
        $this->data['absent'] = sizeof($query4->result());
		$this->data['totalSchoolDay'] = sizeof($query3->result())+sizeof($query4->result());
        $this->data['absent'] = sizeof($query4->result());
		$this->data['totalSchoolDay'] = sizeof($query3->result())+sizeof($query4->result());

        $this->data['title'] = 'Skill Based Report';
        $this->load->view('report/skill_based_report/report', $this->data);
    }

    public function subject_based_report()
    {
        $this->data = array();
        $this->data['title'] = 'Subject Based - Mount Etna Report';
        $this->load->view('report/subject_based_report/report', $this->data);
    }

    public function type_skill_based_report()
    {
        $this->data = array();
        $student_id = $this->session->userdata('reportstudent_id');
        $branch_id = $this->session->userdata('reportbranch_id');
        $this->data = array();

       $query = $this->db->query("SELECT * FROM student WHERE id='".$student_id."'");
       $this->data['studentData'] = $query->row();

       $studentMpped = $this->report_model->getStudentDetails($student_id);
       $this->data['studentMpped'] = $studentMpped;
       //echo '<pre>';
       //print_r($this->data['studentMpped']);
       //echo $studentMpped->class_id;
		
		$query2 = $this->db->query("SELECT * FROM branch WHERE id='".$branch_id."'");
        $this->data['branchData'] = $query2->row();
		
		
		$currentYear = date('Y')-1;
        $this->data['currentYear'] = $currentYear;
		$firstDate = $currentYear . '-01-01';
		$lastDate = $currentYear . '-12-31';
		$firstDateObj = strtotime($firstDate);
		$lastDateObj = strtotime($lastDate);
		
		$firstDate = date("Y-m-d", $firstDateObj);
		$lastDate = date("Y-m-d", $lastDateObj);
		
		$query3 = $this->db->query("SELECT * FROM student_attendance WHERE student_id='".$student_id."' AND branch_id = '".$branch_id."' AND DATE(date) >= '".$firstDate."' AND DATE(date) <= '".$lastDate."' AND status = 'P'");
        $this->data['present'] = sizeof($query3->result());
		
		$query4 = $this->db->query("SELECT * FROM student_attendance WHERE student_id='".$student_id."' AND branch_id = '".$branch_id."' AND DATE(date) >= '".$firstDate."' AND DATE(date) <= '".$lastDate."' AND status = 'A'");
        $this->data['absent'] = sizeof($query4->result());
		$this->data['totalSchoolDay'] = sizeof($query3->result())+sizeof($query4->result());
        $this->data['absent'] = sizeof($query4->result());
		$this->data['totalSchoolDay'] = sizeof($query3->result())+sizeof($query4->result());


        $this->data['title'] = 'Exam Type + Skill Based - Mount Nemrut Report';
        $this->load->view('report/type_skill_based_report/report', $this->data);
    }

    public function term_end_report()
    {
        $this->data = array();
        $this->data['title'] = 'Term End Report';
        $this->load->view('report/term_end_report/report', $this->data);
    }

    public function grade_book_report()
    {
        $this->data = array();
        $this->data['title'] = 'Grade Book Report';
        $this->load->view('report/grade_book_report/report', $this->data);
    }

    public function term_end_report_versin_2()
    {
        $this->data = array();
        $this->data['title'] = 'Term End Report Version 2';
        $this->load->view('report/term_end_report_versin_2/report', $this->data);
    }

    public function term_end_report_versin_3()
    {
        $this->data = array();
        $this->data['title'] = 'Term End Report Version 3';
        $this->load->view('report/term_end_report_versin_3/report', $this->data);
    }

}
