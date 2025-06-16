<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class MY_Model extends CI_Model
{

    function __construct()
    {
        parent::__construct();
    }

    public function hash($password)
    {
        return hash("sha512", $password . config_item("encryption_key"));
    }

    public function uploadImage($role)
    {
        $return_photo = 'defualt.png';
        $old_user_photo = $this->input->post('old_user_photo');
        if (isset($_FILES["user_photo"]) && !empty($_FILES['user_photo']['name'])) {
            $config['upload_path'] = './uploads/images/' . $role . '/';
            $config['allowed_types'] = 'jpg|png';
            $config['overwrite'] = FALSE;
            $config['encrypt_name'] = TRUE;
            $this->upload->initialize($config);
            if ($this->upload->do_upload("user_photo")) {
                // need to unlink previous photo
                if (!empty($old_user_photo)) {
                    $unlink_path = 'uploads/images/' . $role . '/';
                    if (file_exists($unlink_path . $old_user_photo)) {
                        @unlink($unlink_path . $old_user_photo);
                    }
                }
                $return_photo = $this->upload->data('file_name');
            }
        } else {
            if (!empty($old_user_photo)) {
                $return_photo = $old_user_photo;
            }
        }
        return $return_photo;
    }


    public function uploadImageWithResize($role)
    {
        $return_photo = 'default.png';
        $old_user_photo = $this->input->post('old_user_photo');

        if (isset($_FILES["user_photo"]) && !empty($_FILES['user_photo']['name'])) {
            // Create directory if it doesn't exist
            $upload_path = './uploads/images/' . $role . '/';
            if (!file_exists($upload_path)) {
                mkdir($upload_path, 0777, TRUE);
            }

            // Configure upload
            $config['upload_path'] = $upload_path;
            $config['allowed_types'] = 'jpg|jpeg|png';
            $config['overwrite'] = FALSE;
            $config['encrypt_name'] = TRUE;
            $config['max_size'] = '5120'; // 5MB max size

            $this->upload->initialize($config);

            if ($this->upload->do_upload("user_photo")) {
                $upload_data = $this->upload->data();
                $file_path = $upload_data['full_path'];
                $file_name = $upload_data['file_name'];

                // log_message('debug', "Uploaded file: {$file_name}");

                // Get original dimensions
                list($original_width, $original_height) = getimagesize($file_path);
                // log_message('debug', "Original dimensions: {$original_width}x{$original_height}");

                // Determine new dimensions while keeping the aspect ratio
                $target_size = 900;
                $resize_width = $target_size;
                $resize_height = $target_size;

                if ($original_width > $original_height) {
                    $resize_width = ($original_width / $original_height) * $target_size;
                } else {
                    $resize_height = ($original_height / $original_width) * $target_size;
                }

                // Resize
                $resize_config = array(
                    'image_library' => 'gd2',
                    'source_image' => $file_path,
                    'new_image' => $file_path,
                    'maintain_ratio' => TRUE,
                    'width' => $resize_width,
                    'height' => $resize_height,
                    'quality' => 100
                );

                $this->load->library('image_lib');
                $this->image_lib->initialize($resize_config);

                if (!$this->image_lib->resize()) {
                    log_message('error', 'Image resize failed: ' . $this->image_lib->display_errors());
                } else {
                    // log_message('debug', "Resize operation completed");
                }

                $this->image_lib->clear();

                // Get new dimensions
                list($resized_width, $resized_height) = getimagesize($file_path);
                // log_message('debug', "After resize dimensions: {$resized_width}x{$resized_height}");

                // Crop to exactly 900x900
                if ($resized_width != $target_size || $resized_height != $target_size) {
                    $crop_x = max(0, ($resized_width - $target_size) / 2);
                    $crop_y = max(0, ($resized_height - $target_size) / 2);

                    $crop_config = array(
                        'image_library' => 'gd2',
                        'source_image' => $file_path,
                        'maintain_ratio' => FALSE,
                        'width' => $target_size,
                        'height' => $target_size,
                        'x_axis' => $crop_x,
                        'y_axis' => $crop_y,
                        'quality' => 100
                    );

                    $this->image_lib->initialize($crop_config);

                    if (!$this->image_lib->crop()) {
                        log_message('error', 'Image crop failed: ' . $this->image_lib->display_errors());
                    } else {
                        // log_message('debug', "Crop operation completed");
                    }

                    $this->image_lib->clear();

                    // Get final dimensions
                    list($final_width, $final_height) = getimagesize($file_path);
                    // log_message('debug', "Final dimensions after crop: {$final_width}x{$final_height}");
                }

                // Unlink the old user photo
                if (!empty($old_user_photo) && $old_user_photo != 'default.png') {
                    $unlink_path = $upload_path . $old_user_photo;
                    if (file_exists($unlink_path)) {
                        @unlink($unlink_path);
                        // log_message('debug', "Removed old photo: {$old_user_photo}");
                    }
                }

                $return_photo = $file_name;
            } else {
                // log_message('error', 'Image upload failed: ' . $this->upload->display_errors());
            }
        } else {
            $return_photo = !empty($old_user_photo) ? $old_user_photo : 'default.png';
        }

        return $return_photo;
    }



    public function uploadSignature($role)
    {
        $return_photo = 'defualt.png';
        $old_user_signature = $this->input->post('old_signature');
        if (isset($_FILES["signature"]) && !empty($_FILES['signature']['name'])) {
            $config['upload_path'] = './uploads/images/' . $role . '/';
            $config['allowed_types'] = 'jpg|png';
            $config['overwrite'] = FALSE;
            $config['encrypt_name'] = TRUE;
            $this->upload->initialize($config);
            if ($this->upload->do_upload("signature")) {
                // need to unlink previous photo
                if (!empty($old_user_signature)) {
                    $unlink_path = 'uploads/images/' . $role . '/';
                    if (file_exists($unlink_path . $old_user_signature)) {
                        @unlink($unlink_path . $old_user_signature);
                    }
                }
                $return_photo = $this->upload->data('file_name');
            }
        } else {
            if (!empty($old_user_signature)) {
                $return_photo = $old_user_signature;
            }
        }
        return $return_photo;
    }

    public function get($table, $where_array = NULL, $single = false, $branch = false, $columns = '*')
    {
        $this->db->select($columns);
        if (is_array($where_array)) {
            $this->db->where($where_array);
        }
        if ($branch == true) {
            if (!is_superadmin_loggedin()) {
                $this->CI->db->where("branch_id", get_loggedin_branch_id());
            }
        }
        if ($single == true) {
            $method = 'row_array';
        } else {
            $method = 'result_array';
            $this->db->order_by('id', 'ASC');
        }
        $result = $this->db->get($table)->$method();
        return $result;
    }

    public function getSingle($table, $id = NULL, $single = false)
    {
        if ($single == true) {
            $method = 'row';
        } else {
            $method = 'result';
        }
        $q = $this->db->query("SELECT * FROM " . $table . " WHERE id = " . $this->db->escape($id));
        return $q->$method();
    }
}
