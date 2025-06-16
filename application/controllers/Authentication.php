<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @package : schoolexcel school management system
 * @version : 2.0
 * @developed by : eduprojectsgloabaltech.in
 * @support : techie.mithlesh@gmail.com
 * @author url : http://codewithmithlesh.com
 * @filename : Authenticate.php
 * @copyright : Reserved RamomCoders Team
 */

class Authentication extends Authentication_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->helper('cookie');
    }

    public function PrivacyPolicy()
    {
        $this->load->view('authentication/PrivatePolicy');
    }

    public function RefoundPolicy()
    {
        $this->load->view('authentication/RefoundPolicy');
    }



    /* email is okey lets check the password now */
    // public function index()
    // {
    //     if (is_loggedin()) {
    //         redirect(base_url('dashboard'));
    //     }

    //     if ($_POST) {
    //         $rules = array(
    //             array(
    //                 'field' => 'email',
    //                 'label' => "Email",
    //                 'rules' => 'trim|required|valid_email',
    //                 'errors' => array(
    //                     'valid_email' => 'Please enter a valid email address.',
    //                 ),
    //             ),
    //             array(
    //                 'field' => 'password',
    //                 'label' => "Password",
    //                 'rules' => 'trim|required',
    //             ),
    //         );
    //         $this->form_validation->set_rules($rules);
    //         if ($this->form_validation->run() !== false) {
    //             $email = $this->input->post('email');
    //             $password = $this->input->post('password');
    //             // username is okey lets check the password now
    //             $login_credential = $this->authentication_model->login_credential($email, $password);
    //             if ($login_credential) {
    //                 if ($login_credential->active) {
    //                     if ($login_credential->role == 6) {
    //                         $userType = 'parent';
    //                     } elseif($login_credential->role == 7) {
    //                         $userType = 'student';
    //                     } else {
    //                         $userType = 'staff';
    //                     }
    //                     $getUser = $this->application_model->getUserNameByRoleID($login_credential->role, $login_credential->user_id);
    //                     $getConfig = $this->db->get_where('global_settings', array('id' => 1))->row_array();
    //                     $branch = $this->db->get_where('branch', ['id' => $getUser['branch_id']])->row();
    //                     // get logger name
    //                     $sessionData = array(
    //                         'name' => $getUser['name'],
    //                         'logger_photo' => $getUser['photo'],
    //                         'branch_logo' =>$branch->logo ?? null,
    //                         'loggedin_branch' => $getUser['branch_id'],
    //                         'loggedin_id' => $login_credential->id,
    //                         'loggedin_userid' => $login_credential->user_id,
    //                         'loggedin_role_id' => $login_credential->role,
    //                         'loggedin_type' => $userType,
    //                         'set_lang' => $getConfig['translation'],
    //                         'set_session_id' => $getConfig['session_id'],
    //                         'loggedin' => true,
    //                     );
    //                     $this->session->set_userdata($sessionData);
    //                     $this->db->update('login_credential', array('last_login' => date('Y-m-d H:i:s')), array('id' => $login_credential->id));
    //                     // is logged in
    //                     if ($this->session->has_userdata('redirect_url')) {
    //                         redirect($this->session->userdata('redirect_url'));
    //                     } else {
    //                         redirect(base_url('dashboard'));
    //                     }

    //                 } else {
    //                     set_alert('error', translate('inactive_account'));
    //                     redirect(base_url('authentication'));
    //                 }
    //             } else {
    //                 set_alert('error', translate('username_password_incorrect'));
    //                 redirect(base_url('authentication'));
    //             }

    //         }
    //     }
    //     $this->load->view('authentication/login', $this->data);
    // }


    public function index()
    {
        if (is_loggedin()) {
            redirect(base_url('dashboard'));
        }

        // Check for remember me cookie
        $remember_token = get_cookie('remember_token');
        $remember_user = get_cookie('remember_user');

        if ($remember_token && $remember_user) {
            $login_credential = $this->authentication_model->validate_remember_token($remember_user, $remember_token);

            if ($login_credential) {
                // Proceed with auto-login
                $this->_setup_login_session($login_credential);
                set_alert('success', 'Login Successful');
                redirect(base_url('dashboard'));

            } else {
                delete_cookie('remember_token');
                delete_cookie('remember_user');
            }
        }

        if ($_POST) {
            $rules = array(
                array(
                    'field' => 'email',
                    'label' => "Email",
                    'rules' => 'trim|required|valid_email',
                    'errors' => array(
                        'valid_email' => 'Please enter a valid email address.',
                    ),
                ),
                array(
                    'field' => 'password',
                    'label' => "Password",
                    'rules' => 'trim|required',
                ),
            );
            $this->form_validation->set_rules($rules);
            if ($this->form_validation->run() !== false) {
                $email = $this->input->post('email');
                $password = $this->input->post('password');
                $remember = (bool) $this->input->post('remember');

                
                $login_credential = $this->authentication_model->login_credential($email, $password);
                
                if ($login_credential) {
                    if ($login_credential->active) {
                        // Set up the login session
                        $this->_setup_login_session($login_credential);

                        // Handle remember me
                        if ($remember) {
                            $this->_set_remember_me($login_credential->id);
                        }

                        set_alert('success', 'Login Successful');

                        // is logged in
                        if ($this->session->has_userdata('redirect_url')) {
                            redirect($this->session->userdata('redirect_url'));
                        } else {
                            redirect(base_url('dashboard'));
                        }
                    } else {
                        set_alert('error', translate('inactive_account'));
                        redirect(base_url('authentication'));
                    }
                } else {
                    set_alert('error', translate('username_password_incorrect'));
                    redirect(base_url('authentication'));
                }
            }
        }
        // $this->load->view('authentication/login', $this->data);
        $this->load->view('authentication/login_new', $this->data);
        // $this->load->view('authentication/login_new_bg', $this->data);
    }

    // Helper function to set up login session
    private function _setup_login_session($login_credential)
    {
        $getUser = $this->application_model->getUserNameByRoleID($login_credential->role, $login_credential->user_id);
        $getConfig = $this->db->get_where('global_settings', array('id' => 1))->row_array();
        $branch = $this->db->get_where('branch', ['id' => $getUser['branch_id']])->row();
        $sessionID = $getConfig->session_id;

        if ($login_credential->role == 6) {
            $userType = 'parent';
        } elseif ($login_credential->role == 7) {
            $studentID = $getUser['id'];
             $this->session->set_userdata('student_id', $studentID);
             $studentSession = $this->application_model->getEnrollID($studentID, $sessionID);
             if (is_array($studentSession)) {
                 $this->session->set_userdata('enrollID', $studentSession['id']);
                 $sessionID = $studentSession['session_id'];
             } else {
                 $this->session->set_userdata('enrollID', $studentSession);
             }
            $userType = 'student';

        } else {
            $userType = 'staff';
        }
  
        // get logger name
        $sessionData = array(
            'name' => $getUser['name'],
            'logger_photo' => $getUser['photo'],
            'branch_logo' => $branch->logo ?? null,
            'loggedin_branch' => $getUser['branch_id'],
            'loggedin_id' => $login_credential->id,
            'loggedin_userid' => $login_credential->user_id,
            'loggedin_role_id' => $login_credential->role,
            'loggedin_type' => $userType,
            'set_lang' => $getConfig['translation'],
            'set_session_id' => $getConfig['session_id'],
            'loggedin' => true,
        );

        $this->session->set_userdata($sessionData);
        $this->db->update('login_credential', array('last_login' => date('Y-m-d H:i:s')), array('id' => $login_credential->id));
    }

    // Helper function to set remember me cookie
    private function _set_remember_me($user_id)
    {
        // Generate a secure token
        $token = bin2hex(random_bytes(32)); // Requires PHP 7+

        // Save the token in the database
        $this->db->where('id', $user_id);
        $this->db->update('login_credential', ['remember_token' => $token]);

        // Set cookies (30 days expiration)
        set_cookie('remember_token', $token, 60 * 60 * 24 * 30);
        set_cookie('remember_user', $user_id, 60 * 60 * 24 * 30);
    }

    // forgot password
    public function forgot()
    {
        if (is_loggedin()) {
            redirect(base_url('dashboard'), 'refresh');
        }

        if ($_POST) {
            $config = array(
                array(
                    'field' => 'username',
                    'label' => 'Email',
                    'rules' => 'trim|required',
                ),
            );
            $this->form_validation->set_rules($config);
            if ($this->form_validation->run() !== false) {
                $username = $this->input->post('username');
                $res = $this->authentication_model->lose_password($username);
                if ($res == true) {
                    $this->session->set_flashdata('reset_res', TRUE);
                    redirect(base_url('authentication/forgot'));
                } else {
                    $this->session->set_flashdata('reset_res', FALSE);
                    redirect(base_url('authentication/forgot'));
                }
            }
        }
        $this->load->view('authentication/forgot', $this->data);
    }

    /* password reset */
    public function pwreset()
    {
        if (is_loggedin()) {
            redirect(base_url('dashboard'), 'refresh');
        }

        $key = $this->input->get('key');
        if (!empty($key)) {
            $query = $this->db->get_where('reset_password', array('key' => $key));
            if ($query->num_rows() > 0) {
                if ($this->input->post()) {
                    $this->form_validation->set_rules('password', 'Password', 'trim|required|min_length[4]|matches[c_password]');
                    $this->form_validation->set_rules('c_password', 'Confirm Password', 'trim|required|min_length[4]');
                    if ($this->form_validation->run() !== false) {
                        // $password = $this->app_lib->pass_hashed($this->input->post('password')); //OLD METHOD
                        $password = $this->app_lib->has_password($this->input->post('password')); //NEW METHOD
                        $this->db->where('id', $query->row()->login_credential_id);
                        $this->db->update('login_credential', array('password' => $password));
                        $this->db->where('login_credential_id', $query->row()->login_credential_id);
                        $this->db->delete('reset_password');
                        set_alert('success', 'Password Reset Successfully');
                        redirect(base_url('authentication'));
                    }
                }
                $this->load->view('authentication/pwreset', $this->data);
            } else {
                set_alert('error', 'Token Has Expired');
                redirect(base_url('authentication'));
            }
        } else {
            set_alert('error', 'Token Has Expired');
            redirect(base_url('authentication'));
        }
    }

    /* session logout */
    public function logout()
    {
        $user_id = $this->session->userdata('loggedin_id');
        // Clear the remember token in the database
        if ($user_id) {
            $this->db->where('id', $user_id);
            $this->db->update('login_credential', ['remember_token' => NULL]);
        }

        // Delete the remember me cookies
        delete_cookie('remember_token');
        delete_cookie('remember_user');

        $this->session->unset_userdata('name');
        $this->session->unset_userdata('logger_photo');
        $this->session->unset_userdata('loggedin_id');
        $this->session->unset_userdata('loggedin_userid');
        $this->session->unset_userdata('loggedin_type');
        $this->session->unset_userdata('set_lang');
        $this->session->unset_userdata('set_session_id');
        $this->session->unset_userdata('loggedin_branch');
        $this->session->unset_userdata('loggedin');
        $this->session->sess_destroy();
        redirect(base_url(), 'refresh');
    }

    public function index2()
    {

        $this->load->view('authentication/login_new', $this->data);
    }
}
