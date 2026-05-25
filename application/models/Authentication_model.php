<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Authentication_model extends MY_Model
{

    // checking login credential
    // public function login_credential($username, $password)
    // {
    //     $this->db->select('*');
    //     $this->db->from('login_credential');
    //     $this->db->where('username', $username);
    //     $this->db->limit(1);
    //     $query = $this->db->get();
    //     if ($query->num_rows() == 1) {
    //         $verify_password = $this->app_lib->verify_password($password, $query->row()->password);
    //         if ($verify_password) {
    //             return $query->row();
    //         }
    //     }
    //     return false;
    // }

    // public function login_credential($username, $password)
    // {
    //     $this->db->select('*');
    //     $this->db->from('login_credential');
    //     $this->db->where('username', $username);
    //     $this->db->limit(1);
    //     $query = $this->db->get();

    //     if ($query->num_rows() == 1) {
    //         $stored_password = $query->row()->password;

    //         echo "Stored Password: " . $stored_password . "<br>";
    //         echo "Entered Password: " . $password . "<br>";

    //         // Try old password verification method
    //         if ($this->app_lib->verify_password2($password, $stored_password)) {
    //             echo "Old password method matched!<br>";
    //             return $query->row();
    //         }

    //         // Try new password verification method
    //         if ($this->app_lib->verify_password($password, $stored_password)) {
    //             echo "New password method matched!<br>";
    //             return $query->row();
    //         }

    //         echo "Password did not match any method.<br>";
    //     }else{
    //         echo "User not found in DB.<br>";
    //     }

    //     return false;
    // }


    public function login_credential($username, $password)
    {
        $this->db->select('*');
        $this->db->from('login_credential');
        $this->db->where('username', $username);
        $this->db->limit(1);
        $query = $this->db->get();

        if ($query->num_rows() == 1) {
            $stored_password = $query->row()->password;

            // echo "Stored Password: " . $stored_password . "<br>";
            // echo "Entered Password: " . $password . "<br>";

            // ✅ Try decrypting AES-256 first
            $decrypted_password = $this->app_lib->get_password($stored_password);
            if ($decrypted_password !== false && $password === $decrypted_password) {
                // echo "✅ Password matched after AES decryption!<br>";

                // 🔄 Re-encrypt the password with AES-256 (not bcrypt)
                $new_encrypted_password = $this->app_lib->has_password($password);
                $this->db->where('username', $username);
                $this->db->update('login_credential', ['password' => $new_encrypted_password]);

                // echo "🔄 Password re-encrypted with AES-256.<br>";
                return $query->row();
            }

            // ✅ If decryption failed, check if it's bcrypt (for safety)
            if (password_verify($password, $stored_password)) {
                // echo "✅ Password matched with bcrypt!<br>";

                // 🔄 Convert bcrypt back to AES-256
                $new_encrypted_password = $this->app_lib->has_password($password);
                $this->db->where('username', $username);
                $this->db->update('login_credential', ['password' => $new_encrypted_password]);

                // echo "🔄 Password converted from bcrypt to AES-256.<br>";
                return $query->row();
            }

            // echo "❌ Password did not match any method.<br>";
        } else {
            // echo "❌ User not found in DB.<br>";
        }

        return false;
    }




    public function validate_remember_token($user_id, $token)
    {
        $this->db->select('*');
        $this->db->from('login_credential');
        $this->db->where('id', $user_id);
        $this->db->where('remember_token', $token);
        $this->db->where('active', 1);
        $this->db->limit(1);
        $query = $this->db->get();

        if ($query->num_rows() == 1) {
            return $query->row();
        }

        return false;
    }


    // password forgotten
    public function lose_password($username)
    {
        if (!empty($username)) {
            $this->db->select('*');
            $this->db->from('login_credential');
            $this->db->where('username', $username);
            $this->db->limit(1);
            $query = $this->db->get();

            if ($query->num_rows() > 0) {
                $login_credential = $query->row();
                $getUser = $this->application_model->getUserNameByRoleID($login_credential->role, $login_credential->user_id);
                $key = hash('sha512', $login_credential->role . $login_credential->username . app_generate_hash());
                $query = $this->db->get_where('reset_password', array('login_credential_id' => $login_credential->id));
                if ($query->num_rows() > 0) {
                    $this->db->where('login_credential_id', $login_credential->id);
                    $this->db->delete('reset_password');
                }
                $arrayReset = array(
                    'key' => $key,
                    'login_credential_id' => $login_credential->id,
                    'username' => $login_credential->username,
                );
                $this->db->insert('reset_password', $arrayReset);
                // send email for forgot password
                $this->load->model('email_model');
                $arrayData = array(
                    'role' => $login_credential->role,
                    'branch_id' => $getUser['branch_id'],
                    'username' => $login_credential->username,
                    'name' => $getUser['name'],
                    'reset_url' => base_url('authentication/pwreset?key=' . $key),
                    'email' => $getUser['email'],
                );
                $this->email_model->sentForgotPassword($arrayData);
                return true;
            }
        }
        return false;
    }

    public function save_registration_lead($data)
    {
        // Check if email already submitted a lead
        $existing = $this->db->where('email', $data['email'])
                            ->where('status', 'pending')
                            ->get('register_leads')
                            ->row();
        if ($existing) {
            return false; // Already has a pending request
        }
    
        return $this->db->insert('register_leads', $data);
    }
}
