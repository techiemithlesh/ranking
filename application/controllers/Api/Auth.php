<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @package : Schoolexcel ERP
 * @version : 2.0
 * @developed by : Mithlesh Patel
 * @support : techie.mithlesh@gmail.com
 * @author url : Mithlesh patel
 * @filename : Auth.php
 * @copyright : schoolexcel
 */

class Auth extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();

        $this->load->model('authentication_model');
        $this->load->helper(['form', 'url']);
        $this->load->library(['form_validation', 'session']);
        $this->load->model('userrole_model');
        $this->load->model('report_model');
        $this->load->model('application_model');
        $this->load->model('studentbook_model');
    }

    // public function login()
    // {
    //     $this->output->set_content_type('application/json');

    //     $json_input = json_decode(file_get_contents('php://input'), true);

    //     if (json_last_error() !== JSON_ERROR_NONE) {
    //         echo json_encode([
    //             'status' => false,
    //             'message' => 'Invalid JSON input'
    //         ]);
    //         return;
    //     }

    //     $rules = [
    //         [
    //             'field' => 'email',
    //             'label' => 'Email',
    //             'rules' => 'trim|required|valid_email',
    //         ],
    //         [
    //             'field' => 'password',
    //             'label' => 'Password',
    //             'rules' => 'trim|required',
    //         ],
    //     ];
    //     $this->form_validation->set_rules($rules);

    //     $_POST = $json_input;

    //     if ($this->form_validation->run() === false) {
    //         $response = [
    //             'status' => false,
    //             'message' => strip_tags(validation_errors()),
    //         ];
    //     } else {
    //         $email = $json_input['email'];
    //         $password = $json_input['password'];

    //         $login_credential = $this->authentication_model->login_credential($email, $password);

    //         if (!$login_credential) {
    //             $response = [
    //                 'status' => false,
    //                 'message' => "Invalid email or password.",
    //             ];
    //         } else {
    //             if (!$login_credential->active) {
    //                 $response = [
    //                     'status' => false,
    //                     'message' => 'Your account is inactive. Please contact support.',
    //                 ];
    //             } else {
    //                 // Determine user type
    //                 if ($login_credential->role == 6) {
    //                     $userType = 'parent';
    //                 } else {
    //                     $userType = 'student';
    //                 }

    //                 // Get user details
    //                 $getUser = $this->application_model->getUserNameByRoleID($login_credential->role, $login_credential->user_id);

    //                 // Update last login time
    //                 $this->db->update(
    //                     'login_credential',
    //                     array('last_login' => date('Y-m-d H:i:s')),
    //                     array('id' => $login_credential->id)
    //                 );

    //                 $response = [
    //                     'status' => true,
    //                     'message' => 'Login successful',
    //                     'user' => [
    //                         'id' => $login_credential->user_id,
    //                         'name' => $getUser['name'],
    //                         'photo' => $getUser['photo'],
    //                         'role' => $login_credential->role,
    //                         'user_type' => $userType,
    //                         'email' => $login_credential->email,
    //                     ],
    //                 ];
    //             }
    //         }
    //     }

    //     echo json_encode($response);
    // }



    public function login()
    {
        header('Content-Type: application/json');

        // Get JSON input
        $json_data = json_decode(file_get_contents("php://input"), true);
        $email = $json_data['email'] ?? '';
        $password = $json_data['password'] ?? '';

        // Validate Input
        if (empty($email) || empty($password)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Email and password required']);
            return;
        }

        // Check user credentials
        $user = $this->authentication_model->login_credential($email, $password);

        if (!$user) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
            return;
        }

        // Get user details
        $getUser = $this->application_model->getUserNameByRoleID($user->role, $user->user_id);
        $branch = $this->db->get_where('branch', ['id' => $getUser['branch_id']])->row();

        // Update last login time
        $this->db->update(
            'login_credential',
            ['last_login' => date('Y-m-d H:i:s')],
            ['id' => $user->id]
        );

        $response = [
            'success' => true,
            'message' => '',
            'user' => $getUser,
            'role' => $user->role
        ];
        switch ($user->role) {
            case 2:
                $response['message'] = 'Branch Login successfully!';
                break;
            case 6:
                $query = $this->db->select('id')->where(['parent_id' => $user->user_id])->get('student');
                // echo $this->db->last_query();
                // printVar($user);
                // exit;
                if ($query->num_rows() == 0) {
                    http_response_code(403);
                    echo json_encode(['success' => false, 'message' => 'No linked students found']);
                    return;
                }
                $students = $query->result_array();
                $studentDetails = [];
                foreach ($students as $student) {
                    $studentDetails[] = $this->report_model->getStudentDetails($student['id']);
                }
                $response['message'] = 'Parent Login successfully!';
                $response['students'] = $studentDetails;
                break;
            case 7:
                $student = $this->report_model->getStudentDetails($getUser['id']);
                $response['message'] = 'Student Logged in Successfully!';
                $response['studentData'] = $student;
                break;
            default:
                $response['message'] = 'Login successful!';
                break;
        }

        // Send response
        http_response_code(200);
        echo json_encode($response);
    }


    /**
     * API endpoint to retrieve books based on user role and branch
     * Handles JSON input and outputs JSON response
     */
    public function getBooks()
    {
        try {
            
            header('Content-Type: application/json');

            $json_data = json_decode(file_get_contents("php://input"), true);

            $roleId = isset($json_data['role_id']) ? intval($json_data['role_id']) : null;
            $branchId = isset($json_data['branch_id']) ? intval($json_data['branch_id']) : null;
            $studentId = isset($json_data['student_id']) ? intval($json_data['student_id']) : null;

            // Pagination parameters
            $page = isset($json_data['page']) ? max(1, intval($json_data['page'])) : 1;
            $perPage = isset($json_data['per_page']) ? max(1, intval($json_data['per_page'])) : 10;

            // Input validation
            if ($roleId === null || $branchId === null) {
                throw new Exception('Invalid input parameters');
            }

            // Retrieve books based on role with pagination
            $result = ($roleId == 6 || $roleId == 7)
                ? $this->studentbook_model->getFlipBox($roleId, $branchId, $studentId, $page, $perPage)
                : $this->studentbook_model->getFlipBox($roleId, $branchId, null, $page, $perPage);

            // Prepare response
            $response = [
                'success' => true,
                'branch_id' => $branchId,
                'main_menu' => 'My Interactive Book',
                'title' => translate('my_interactive_books') ?? 'My Interactive Books',
                'books' => $result['books'],
                'pagination' => $result['pagination']
            ];

            // Output JSON response
            echo json_encode($response);
            exit;

        } catch (Exception $e) {
            // Error response
            $errorResponse = [
                'success' => false,
                'message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                "e"=>$e->getFile(),
                "line"=>$e->getLine(),
            ];

            // Log error
            log_message('error', 'Book retrieval API error: ' . $e->getMessage());

            // Output error JSON
            header('HTTP/1.1 400 Bad Request');
            echo json_encode($errorResponse);
            exit;
        }
    }

    public function getStudentDetails()
    {
        $this->output->set_content_type('application/json');

        try {

            $json_input = json_decode(file_get_contents('php://input'), true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Invalid JSON input: ' . json_last_error_msg());
            }

            if (!isset($json_input['student_id']) || empty($json_input['student_id'])) {
                throw new Exception('Student ID is required');
            }

            $_POST = $json_input;

            $this->form_validation->set_rules([
                [
                    'field' => 'student_id',
                    'label' => 'Student Id',
                    'rules' => 'trim|required|numeric'
                ]
            ]);

            if ($this->form_validation->run() === false) {
                throw new Exception(strip_tags(validation_errors()));
            }

            // Get student data
            $student_id = $this->input->post('student_id');

            $studentMapped = $this->report_model->getStudentDetails($student_id);

            // Check if student data exists
            if (empty($studentMapped)) {
                throw new Exception('Student not found');
            }

            $response = [
                'status' => true,
                'message' => 'Student details fetched successfully!',
                'data' => [
                    'student' => $studentMapped,
                ]
            ];

        } catch (Exception $e) {
            $response = [
                'status' => false,
                'message' => $e->getMessage()
            ];
        }

        // Send JSON response
        echo json_encode($response);
        return;
    }

    

}
