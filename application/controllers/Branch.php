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

class Branch extends Admin_Controller
{

    public function __construct()
    {
        parent::__construct();
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);
        $this->load->model('branch_model');
        $this->load->library('csvimport');

    }

    /* branch all data are prepared and stored in the database here */
    public function index()
    {
        if (is_superadmin_loggedin()) {
            if ($this->input->post('submit') == 'save') {
                $this->form_validation->set_rules('branch_name', translate('branch_name'), 'required|callback_unique_name');
                $this->form_validation->set_rules('school_name', translate('school_name'), 'required');
                $this->form_validation->set_rules('email', translate('email'), 'required|valid_email');
                $this->form_validation->set_rules('mobileno', translate('mobile_no'), 'required');
                $this->form_validation->set_rules('currency', translate('currency'), 'required');
                $this->form_validation->set_rules('currency_symbol', translate('currency_symbol'), 'required');
                if ($this->form_validation->run() == true) {
                    $post = $this->input->post();
                    // $response = $this->branch_model->save($post);
                    $response = $this->branch_model->saveWithAllDetails($post);
                    if ($response) {
                        set_alert('success', translate('information_has_been_saved_successfully'));
                    }
                    redirect(base_url('branch'));
                } else {
                    $this->data['validation_error'] = true;
                }
            }
            $this->data['title'] = translate('branch');
            $this->data['sub_page'] = 'branch/add';
            $this->data['main_menu'] = 'branch';
            $this->load->view('layout/index', $this->data);
        } else {
            $this->session->set_userdata('last_page', current_url());
            redirect(base_url(), 'refresh');
        }
    }

    /* branch information update here */
    public function edit($id = '')
    {
        if (is_superadmin_loggedin()) {
            if ($this->input->post('submit') == 'save') {
                $this->form_validation->set_rules('branch_name', translate('branch_name'), 'required|callback_unique_name');
                $this->form_validation->set_rules('school_name', translate('school_name'), 'required');
                $this->form_validation->set_rules('email', translate('email'), 'required|valid_email');
                $this->form_validation->set_rules('mobileno', translate('mobile_no'), 'required');
                $this->form_validation->set_rules('currency', translate('currency'), 'required');
                $this->form_validation->set_rules('currency_symbol', translate('currency_symbol'), 'required');
                if ($this->form_validation->run() == true) {
                    $post = $this->input->post();
                    $response = $this->branch_model->save($post, $id);
                    if ($response) {
                        set_alert('success', translate('information_has_been_updated_successfully'));
                    }
                    redirect(base_url('branch'));
                }
            }

            $this->data['data'] = $this->branch_model->getSingle('branch', $id, true);
            $this->data['title'] = translate('branch');
            $this->data['sub_page'] = 'branch/edit';
            $this->data['main_menu'] = 'branch';
            $this->load->view('layout/index', $this->data);
        } else {
            $this->session->set_userdata('last_page', current_url());
            redirect(base_url(), 'refresh');
        }
    }

    /* delete information */
    public function delete_data($id = '')
    {
        if (is_superadmin_loggedin()) {
            $this->db->where('id', $id);
            $this->db->delete('branch');
        } else {
            redirect(base_url(), 'refresh');
        }
    }

    /* unique valid branch name verification is done here */
    public function unique_name($name)
    {
        $branch_id = $this->input->post('branch_id');
        if (!empty($branch_id)) {
            $this->db->where_not_in('id', $branch_id);
        }
        $this->db->where('name', $name);
        $name = $this->db->get('branch')->num_rows();
        if ($name == 0) {
            return true;
        } else {
            $this->form_validation->set_message("unique_name", translate('already_taken'));
            return false;
        }
    }

    public function csv_import()
    {

        if (!is_superadmin_loggedin()) {
            echo json_encode(['message' => 'You are not authorised to perform this action!', 'status' => 'false']);
            redirect(base_url(), 'refresh');
        }

        // Set page data
        $this->data['title'] = translate('Multi Branch Import');
        $this->data['sub_page'] = 'branch/multi_branch_import';
        $this->data['main_menu'] = 'branch';
        $this->load->view('layout/index', $this->data);
    }

    public function csv_Sampledownloader()
    {
        $this->load->helper('download');
        $data = file_get_contents('uploads/multi_branch_sample.csv');
        force_download("multi_branch_sample.csv", $data);
    }


    public function csvCheckExistsData($email)
    {
        $array = ['status' => true];
        $query = $this->db->get_where('branch', array('email' => $email));

        if ($query->num_rows() > 0) {
            $array['status'] = false;
            $array['message'] = "Email Already Exists.";
        }
        return $array;
    }



    public function csv_upload()
    {
        // Early return if not authorized
        if (!is_superadmin_loggedin()) {
            return $this->sendJsonResponse(false, 'You are not authorised to perform this action!');
        }

        // Log request details with sanitization
        $this->logRequestDetails();

        try {
            if (!$this->input->post('save')) {
                return $this->sendJsonResponse(false, 'Invalid request.');
            }

            // Validate file
            $fileValidationResult = $this->validateUploadedFile();
            if (!$fileValidationResult['status']) {
                return $this->sendJsonResponse(false, $fileValidationResult['message']);
            }

            // Process CSV
            $importResult = $this->processCSVFile($_FILES["branchfile"]["tmp_name"]);
            return $this->sendJsonResponse(
                $importResult['status'],
                $importResult['message'],
                ['errors' => $importResult['errors'] ?? null]
            );

        } catch (Exception $e) {
            log_message('error', 'Exception in csv_upload: ' . $e->getMessage());
            log_message('error', 'Stack Trace: ' . $e->getTraceAsString());
            return $this->sendJsonResponse(false, 'An unexpected error occurred: ' . $e->getMessage());
        }
    }

    private function validateUploadedFile(): array
    {
        if (!isset($_FILES["branchfile"]) || empty($_FILES['branchfile']['name'])) {
            return ['status' => false, 'message' => 'CSV file is required.'];
        }

        $file_ext = strtolower(pathinfo($_FILES["branchfile"]["name"], PATHINFO_EXTENSION));
        if ($file_ext !== 'csv') {
            return ['status' => false, 'message' => 'Only CSV files are allowed.'];
        }

        return ['status' => true];
    }

    private function processCSVFile(string $filepath): array
    {
        $csv_array = $this->csvimport->get_array($filepath);
        if (!$csv_array) {
            return ['status' => false, 'message' => 'Invalid CSV file format.'];
        }

        $required_columns = ['branch_name', 'school_name', 'email', 'mobileno'];
        $optional_columns = ['currency', 'currency_symbol', 'city', 'state', 'address'];
        $all_columns = array_merge($required_columns, $optional_columns);

        $firstRow = reset($csv_array);
        $csvHeaders = array_keys($firstRow);

        // Check if all required columns exist
        $missing_columns = array_diff($required_columns, $csvHeaders);
        if (!empty($missing_columns)) {
            return ['status' => false, 'message' => 'Missing required columns: ' . implode(', ', $missing_columns)];
        }

        return $this->importCSVData($csv_array, $required_columns);
    }

    private function importCSVData(array $csv_array, array $required_columns): array
    {
        $successful_imports = 0;
        $error_messages = '';

        foreach ($csv_array as $row) {
            // Validate required fields
            $missing_fields = [];
            foreach ($required_columns as $column) {
                if (empty($row[$column])) {
                    $missing_fields[] = $column;
                }
            }

            if (!empty($missing_fields)) {
                $error_messages .= "Row for {$row['branch_name']} - Import Failed: Missing required fields: " .
                    implode(', ', $missing_fields) . "\n";
                continue;
            }

            if (!filter_var($row['email'], FILTER_VALIDATE_EMAIL)) {
                $error_messages .= "{$row['branch_name']} - Import Failed: Invalid Email Format.\n";
                continue;
            }

            $existence_check = $this->csvCheckExistsData($row['email']);
            if (!$existence_check['status']) {
                $error_messages .= "{$row['branch_name']} - Import Failed: {$existence_check['message']}\n";
                continue;
            }

            try {
                $this->branch_model->csvImport($row);
                $successful_imports++;
            } catch (Exception $e) {
                $error_messages .= "{$row['branch_name']} - Import Failed: {$e->getMessage()}\n";
            }
        }

        $message = $successful_imports > 0
            ? "$successful_imports branches have been successfully added!"
            : "No branches were imported.";

        return [
            'status' => $successful_imports > 0,
            'message' => $message,
            'errors' => $error_messages
        ];
    }

    private function logRequestDetails(): void
    {
        $sanitized_post = array_map('htmlspecialchars', $_POST);
        $sanitized_files = array_map(function ($file) {
            return array_map('htmlspecialchars', $file);
        }, $_FILES);

        log_message('info', 'POST Data: ' . json_encode($sanitized_post));
        log_message('info', 'FILES Data: ' . json_encode($sanitized_files));
        log_message('info', 'Request Method: ' . $this->input->method());
        log_message('info', 'Headers: ' . json_encode(getallheaders()));
    }

    private function sendJsonResponse(bool $status, string $message, array $additional_data = []): void
    {
        $response = array_merge(
            ['status' => $status, 'message' => $message],
            $additional_data
        );
        echo json_encode($response);
    }
}
