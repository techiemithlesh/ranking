<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class App_lib
{
    protected $CI;

    public function __construct()
    {
        $this->CI = &get_instance();
        $this->CI->load->library('encryption');
    }

    public function get_credential_id($user_id, $staff = 'staff')
    {
        $this->CI->db->select('id');
        if ($staff == 'staff') {
            $this->CI->db->where_not_in('role', array(6, 7));
        } elseif ($staff == 'parent') {
            $this->CI->db->where('role', 6);
        } elseif ($staff == 'student') {
            $this->CI->db->where('role', 7);
        }
        $this->CI->db->where('user_id', $user_id);
        $result = $this->CI->db->get('login_credential')->row_array();
        return $result['id'];
    }

    function get_bill_no($table)
    {
        $result = $this->CI->db->select("max(bill_no) as id")->get($table)->row_array();
        $id = $result["id"];
        if (!empty($id)) {
            $bill = $id + 1;
        } else {
            $bill = 1;
        }
        return str_pad($bill, 4, '0', STR_PAD_LEFT);
    }

    function get_table($table, $id = NULL, $single = FALSE)
    {
        if ($single == TRUE) {
            $method = 'row_array';
        } else {
            $this->CI->db->order_by('id', 'ASC');
            $method = 'result_array';
        }
        if ($id != NULL) {
            $this->CI->db->where('id', $id);
        }
        $query = $this->CI->db->get($table);
        return $query->$method();
    }

    function getTable($table, $where = "", $single = FALSE)
    {
        if ($where != NULL) {
            $this->CI->db->where($where);
        }
        if (!is_superadmin_loggedin()) {
            $this->CI->db->where("branch_id", get_loggedin_branch_id());
        }
        if ($single == TRUE) {
            $method = "row_array";
        } else {
            $this->CI->db->order_by("id", "asc");
            $method = "result_array";
        }
        $this->CI->db->select("t.*,b.name as branch_name");
        $this->CI->db->from("$table as t");
        $this->CI->db->join("branch as b", "b.id = t.branch_id", "left");
        $query = $this->CI->db->get();
        return $query->$method();
    }

    public function getTableHybrid($table, $where = "", $single = FALSE)
    {
        if ($where != NULL) {
            $this->CI->db->where($where);
        }

        if (!is_superadmin_loggedin()) {
            $this->CI->db->where("(created_by_branch IS NULL OR created_by_branch = " . get_loggedin_branch_id() . ")");
        }

        if ($single == TRUE) {
            $method = "row_array";
        } else {
            $this->CI->db->order_by("id", "asc");
            $method = "result_array";
        }

        $this->CI->db->select("t.*, 
        (CASE 
            WHEN created_by_branch IS NULL THEN 'Global' 
            ELSE (SELECT name FROM branch WHERE id = created_by_branch) 
        END) AS branch_name");

        $this->CI->db->from("$table as t");

        $query = $this->CI->db->get();
        return $query->$method();
    }


    public function getSubjectsByBranch($branch_id)
    {
        $this->db->select("id, name");
        $this->db->from("subject");
        $this->db->where("branch_id", $branch_id);
        $query = $this->db->get();
        return $query->result_array();
    }

    public function check_branch_restrictions($table, $id = '')
    {
        if (empty($id)) {
            access_denied();
        }
        if (!is_superadmin_loggedin()) {
            $query = $this->CI->db->select('id,branch_id')->from($table)->where('id', $id)->limit(1)->get();
            if ($query->num_rows() != 0) {
                $branch_id = $query->row()->branch_id;
                if ($branch_id != $this->CI->session->userdata('loggedin_branch')) {
                    access_denied();
                }
            }
        }
    }

    public function pass_hashed($password)
    {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        return $hashed;
    }

    public function verify_password($password, $encrypt_password)
    {
        $hashed = password_verify($password, $encrypt_password);
        return $hashed;
    }

    public function verify_password2($input_password, $stored_encrypted_password)
    {
        $decrypted_password = $this->get_password($stored_encrypted_password);
        return $input_password === $decrypted_password;
    }

    // NEW HASSING ADDING FOR 2 WAY PROCESS ENCRYPTION AND DECRYPTION FOR PASSWORD VIEW

    // Initialize encryption #changing the encryption mehtod to show password in super admin of all login 24-01-25
    public function has_password($password)
    {

        $this->CI->encryption->initialize(array(
            'cipher' => 'aes-256',
            'mode' => 'cbc',
            'key' => $this->CI->config->item('encryption_key')
        ));

        // Encrypt the password
        $encrypted_password = $this->CI->encryption->encrypt($password);

        return $encrypted_password;
    }

    public function get_password($encrypted_password)
    {
        // echo "🔍 Decrypting: " . $encrypted_password . "<br>";

        $this->CI->encryption->initialize(array(
            'cipher' => 'aes-256',
            'mode' => 'cbc',
            'key' => $this->CI->config->item('encryption_key')
        ));

        $plaintext_password = $this->CI->encryption->decrypt($encrypted_password);

        if ($plaintext_password === false) {
            // echo "❌ Decryption failed!<br>";
        } else {
            // echo "✅ Decrypted Password: " . $plaintext_password . "<br>";
        }

        return $plaintext_password;
    }


    public function getStaffList($branch_id = '', $role = '')
    {
        if (empty($branch_id)) {
            $array = array('' => translate('select_branch_first'));
        } else {
            $this->CI->db->select('s.id,s.name,s.staff_id');
            $this->CI->db->from('staff as s');
            $this->CI->db->join('login_credential as l', 'l.user_id = s.id and l.role != 6 and l.role != 7', 'inner');
            if (!empty($branch_id)) {
                $this->CI->db->where('s.branch_id', $branch_id);
            }
            if (!empty($role)) {
                $this->CI->db->where_in('l.role', array($role));
            }
            $result = $this->CI->db->get()->result();
            $array = array('' => translate('select'));
            foreach ($result as $row) {
                $array[$row->id] = $row->name . ' (' . $row->staff_id . ')';
            }
        }
        return $array;
    }

    public function getClass($branch_id = '')
    {
        if (empty($branch_id)) {
            $array = array('' => translate('select_branch_first'));
        } else {
            if (loggedin_role_id() == 3) {
                $this->CI->db->select('class.id,class.name');
                $this->CI->db->from('teacher_allocation');
                $this->CI->db->join('class', 'class.id = teacher_allocation.class_id', 'left');
                $this->CI->db->where('teacher_allocation.teacher_id', get_loggedin_user_id());
                $this->CI->db->where('teacher_allocation.session_id', get_session_id());
                $result = $this->CI->db->get()->result();
            } else {
                $this->CI->db->where('branch_id', $branch_id);
                $result = $this->CI->db->get('class')->result();
            }
            $array = array('' => translate('select'));
            foreach ($result as $row) {
                $array[$row->id] = $row->name;
            }
        }
        return $array;
    }


    // NEW FOR 3.0 CHANGES
    public function getSelectClassByBranch($branch_id = '')
    {
        $CI = &get_instance();

        if (empty($branch_id)) {
            return array('' => translate('select_branch_first'));
        }

        $array = array('' => translate('select'));

        if (loggedin_role_id() == 3) {
            // Teacher role
            $CI->db->select('class.id, class.name');
            $CI->db->from('teacher_allocation');
            $CI->db->join('class', 'class.id = teacher_allocation.class_id', 'left');
            $CI->db->where('teacher_allocation.teacher_id', get_loggedin_user_id());
            $CI->db->where('teacher_allocation.session_id', get_session_id());
            $result = $CI->db->get()->result();
        } else {
            // Admin/staff — hybrid logic (assigned + created)
            $CI->db->select('c.id, c.name');
            $CI->db->from('class c');
            $CI->db->join('class_branch_map cbm', 'cbm.class_id = c.id', 'left');
            $CI->db->where('cbm.branch_id', $branch_id);
            $CI->db->or_where('c.created_by_branch', $branch_id);
            $CI->db->group_by('c.id');
            $CI->db->order_by('c.name', 'ASC');
            $result = $CI->db->get()->result();
        }

        foreach ($result as $row) {
            $array[$row->id] = $row->name;
        }

        return $array;
    }



    public function getStudentCategory($branch_id = '')
    {
        if (empty($branch_id)) {
            $array = array('' => translate('select_branch_first'));
        } else {
            $this->CI->db->where('branch_id', $branch_id);
            $result = $this->CI->db->get('student_category')->result();
            $array = array('' => translate('select'));
            foreach ($result as $row) {
                $array[$row->id] = $row->name;
            }
        }
        return $array;
    }

    public function classCategory()
    {
        $result = $this->CI->db->get('student_category')->result();
        $array = array('' => translate('select'));

        foreach ($result as $row) {
            $array[$row->id] = $row->name;
        }

        return $array;

    }

    public function getSections($class_id = '', $all = false, $multi = false)
    {
        if (empty($class_id)) {
            $array = array('' => translate('select_class_first'));
        } else {
            if (loggedin_role_id() == 3) {
                $result = $this->CI->db->select('teacher_allocation.section_id,section.name')
                    ->from('teacher_allocation')
                    ->join('section', 'section.id = teacher_allocation.section_id', 'left')
                    ->where(array(
                        'teacher_allocation.class_id' => $class_id,
                        'teacher_allocation.teacher_id' => get_loggedin_user_id(),
                        'teacher_allocation.session_id' => get_session_id()
                    ))
                    ->get()->result();
            } else {
                $this->CI->db->where('class_id', $class_id);
                $result = $this->CI->db->get('sections_allocation')->result();
            }
            if ($multi == false) {
                $array = array('' => translate('select'));
            }
            if ($all == true && loggedin_role_id() != 3) {
                $array['all'] = translate('all_sections');
            }
            foreach ($result as $row) {
                $array[$row->section_id] = get_type_name_by_id('section', $row->section_id);
            }
        }
        return $array;
    }

    // NEW FOR 3.0
    public function getSectionsByClass($class_id = '', $all = false, $multi = false)
    {
        $CI = &get_instance();

        if (empty($class_id)) {
            $array = array('' => translate('select_class_first'));
        } else {
            if (loggedin_role_id() == 3) {
                // Teacher role
                $result = $CI->db->select('teacher_allocation.section_id, section.name')
                    ->from('teacher_allocation')
                    ->join('section', 'section.id = teacher_allocation.section_id', 'left')
                    ->where(array(
                        'teacher_allocation.class_id' => $class_id,
                        'teacher_allocation.teacher_id' => get_loggedin_user_id(),
                        'teacher_allocation.session_id' => get_session_id()
                    ))
                    ->get()->result();
            } else {
                // Admin/staff — use Section table (new model)
                $CI->db->select('id, name');
                $CI->db->where('class_id', $class_id);
                $CI->db->order_by('name', 'ASC');
                $result = $CI->db->get('section')->result();
            }

            if ($multi == false) {
                $array = array('' => translate('select'));
            }

            if ($all == true && loggedin_role_id() != 3) {
                $array['all'] = translate('all_sections');
            }

            foreach ($result as $row) {
                if (loggedin_role_id() == 3) {
                    $array[$row->section_id] = $row->name;
                } else {
                    $array[$row->id] = $row->name;
                }
            }
        }

        return $array;
    }

    public function getDepartment($branch_id = '')
    {
        if (empty($branch_id)) {
            $array = array('' => translate('select_branch_first'));
        } else {
            $this->CI->db->where('branch_id', $branch_id);
            $result = $this->CI->db->get('staff_department')->result();
            $array = array('' => translate('select'));
            foreach ($result as $row) {
                $array[$row->id] = $row->name;
            }
        }
        return $array;
    }

    public function getDesignation($branch_id = '')
    {
        if ($branch_id == '') {
            $array = array('' => translate('select_branch_first'));
        } else {
            $this->CI->db->where('branch_id', $branch_id);
            $result = $this->CI->db->get('staff_designation')->result();
            $array = array('' => translate('select'));
            foreach ($result as $row) {
                $array[$row->id] = $row->name;
            }
        }
        return $array;
    }

    public function getVehicleByRoute($route_id = '')
    {
        if ($route_id == '') {
            $array = array('' => translate('first_select_the_route'));
        } else {
            $this->CI->db->where('route_id', $route_id);
            $result = $this->CI->db->get('transport_assign')->result();
            $array = array('' => translate('select'));
            foreach ($result as $row) {
                $array[$row->vehicle_id] = get_type_name_by_id('transport_vehicle', $row->vehicle_id, 'vehicle_no');
            }
        }
        return $array;
    }

    public function getRoomByHostel($hostel_id = '')
    {
        if ($hostel_id == '') {
            $array = array('' => translate('first_select_the_hostel'));
        } else {
            $this->CI->db->where('hostel_id', $hostel_id);
            $result = $this->CI->db->get('hostel_room')->result();
            $array = array('' => translate('select'));
            foreach ($result as $row) {
                $array[$row->id] = $row->name . ' (' . get_type_name_by_id('hostel_category', $row->category_id) . ')';
            }
        }
        return $array;
    }

    public function getSelectByBranch($table, $branch_id = '', $all = false, $where = '')
    {
        if (empty($branch_id)) {
            $array = array('' => translate('select_branch_first'));
        } else {
            if (is_array($where)) {
                $this->CI->db->where($where);
            }
            $this->CI->db->where('branch_id', $branch_id);
            $result = $this->CI->db->get($table)->result();
            $array = array('' => translate('select'));
            if ($all == true) {
                $array['all'] = translate('all_select');
            }
            foreach ($result as $row) {
                $array[$row->id] = $row->name;
            }
        }
        return $array;
    }

    public function getSelectList($table, $all = '')
    {
        $arrayData = array("" => translate('select'));
        if ($all == 'all') {
            $arrayData['all'] = translate('all_select');
        }
        $result = $this->CI->db->get($table)->result();
        foreach ($result as $row) {
            $arrayData[$row->id] = $row->name;
        }
        return $arrayData;
    }

    public function getRoles($arra_id = [1, 6, 7])
    {
        // If an admin is logged in, exclude School Admin (ID: 2)
        if (is_admin_loggedin()) {
            $arra_id[] = 2;
        }

        if ($arra_id != 'all') {
            $this->CI->db->where_not_in('id', $arra_id);
        }
        $rolelist = $this->CI->db->get('roles')->result();
        $role_array = array('' => translate('select'));
        foreach ($rolelist as $role) {
            $role_array[$role->id] = $role->name;
        }
        return $role_array;
    }

    public function getRolesForMsg($arra_id = [1, 6, 7])
    {

        if (is_student_loggedin()) {
            $this->CI->db->where_in('id', [2, 3]);
        } else {
            if (is_admin_loggedin()) {
                $arra_id[] = 2;
            }

            if ($arra_id != 'all') {
                $this->CI->db->where_not_in('id', $arra_id);
            }
        }

        $rolelist = $this->CI->db->get('roles')->result();
        $role_array = array('' => translate('select'));
        foreach ($rolelist as $role) {
            $role_array[$role->id] = $role->name;
        }

        return $role_array;
    }


    public function generateCSRF()
    {
        return '<input type="hidden" name="' . $this->CI->security->get_csrf_token_name() . '" value="' . $this->CI->security->get_csrf_hash() . '" />';
    }

    public function get_document_category()
    {
        $category = array(
            '' => translate('select'),
            '1' => "Resume File",
            '2' => "Offer Letter",
            '3' => "Joining Letter",
            '4' => "Experience Certificate",
            '5' => "Resignation Letter",
            '6' => "Other Documents",
        );
        return $category;
    }

    public function getDocumentCategory()
    {
        $category = array(
            '' => translate('select'),
            'Resume File' => "Resume File",
            'Offer Letter' => "Offer Letter",
            'Joining Letter' => "Joining Letter",
            'Experience Certificate' => "Experience Certificate",
            'Resignation Letter' => "Resignation Letter",
            'Other Documents' => "Other Documents",
        );
        return $category;
    }

    public function getAnimationslist()
    {
        $animations = array(
            'fadeIn' => "fadeIn",
            'fadeInUp' => "fadeInUp",
            'fadeInDown' => "fadeInDown",
            'fadeInLeft' => "fadeInLeft",
            'fadeInRight' => "fadeInRight",
            'bounceIn' => "bounceIn",
            'rotateInUpLeft' => "rotateInUpLeft",
            'rotateInDownLeft' => "rotateInDownLeft",
            'rotateInUpRight' => "rotateInUpRight",
            'rotateInDownRight' => "rotateInDownRight",
        );
        return $animations;
    }

    public function getMonthslist($m)
    {
        $months = array(
            '01' => 'January',
            '02' => 'February',
            '03' => 'March',
            '04' => 'April',
            '05' => 'May',
            '06' => 'June',
            '07' => 'July ',
            '08' => 'August',
            '09' => 'September',
            '10' => 'October',
            '11' => 'November',
            '12' => 'December',
        );
        return $months[$m];
    }

    public function getDateformat()
    {
        $date = array(
            "Y-m-d" => "yyyy-mm-dd",
            "Y/m/d" => "yyyy/mm/dd",
            "Y.m.d" => "yyyy.mm.dd",
            "d-M-Y" => "dd-mmm-yyyy",
            "d/M/Y" => "dd/mmm/yyyy",
            "d.M.Y" => "dd.mmm.yyyy",
            "d-m-Y" => "dd-mm-yyyy",
            "d/m/Y" => "dd/mm/yyyy",
            "d.m.Y" => "dd.mm.yyyy",
            "m-d-Y" => "mm-dd-yyyy",
            "m/d/Y" => "mm/dd/yyyy",
            "m.d.Y" => "mm.dd.yyyy",
        );
        return $date;
    }

    public function getBloodgroup()
    {
        $blood_group = array(
            '' => translate('select'),
            'A+' => 'A+',
            'A-' => 'A-',
            'B+' => 'B+',
            'B-' => 'B-',
            'O+' => 'O+',
            'O-' => 'O-',
            'AB+' => 'AB+',
            'AB-' => 'AB-',
        );
        return $blood_group;
    }

    function timezone_list()
    {
        static $timezones = null;
        if ($timezones === null) {
            $timezones = [];
            $offsets = [];
            $now = new DateTime('now', new DateTimeZone('UTC'));
            foreach (DateTimeZone::listIdentifiers() as $timezone) {
                $now->setTimezone(new DateTimeZone($timezone));
                $offsets[] = $offset = $now->getOffset();
                $timezones[$timezone] = '(' . $this->format_GMT_offset($offset) . ') ' . $this->format_timezone_name($timezone);
            }
            array_multisort($offsets, $timezones);
        }
        return $timezones;
    }

    function format_GMT_offset($offset)
    {
        $hours = intval($offset / 3600);
        $minutes = abs(intval($offset % 3600 / 60));
        return 'GMT' . ($offset ? sprintf('%+03d:%02d', $hours, $minutes) : '');
    }

    function format_timezone_name($name)
    {
        $name = str_replace('/', ', ', $name);
        $name = str_replace('_', ' ', $name);
        $name = str_replace('St ', 'St. ', $name);
        return $name;
    }


    public function getSessionList()
    {
        $this->CI->db->select('id, school_year');
        $this->CI->db->from('schoolyear');
        $this->CI->db->order_by('id', 'DESC');
        $query = $this->CI->db->get();

        $sessionList = array();
        $sessionList = array('' => translate('select session'));
        foreach ($query->result_array() as $row) {
            $sessionList[$row['id']] = $row['school_year'];
        }
        return $sessionList;
    }


    function getClassTeacher($classID = '')
    {
        if (loggedin_role_id() == 3) {
            $getMode = $this->CI->db
                ->select('teacher_restricted')
                ->where('id', get_loggedin_branch_id())
                ->get('branch')
                ->row()->teacher_restricted;
            if ($getMode == 0) {
                return false;
            } else {
                $this->CI->db->select('class.id,class.name,teacher_allocation.section_id,section.name as section_name');
                $this->CI->db->from('teacher_allocation');
                $this->CI->db->join('class', 'class.id = teacher_allocation.class_id', 'left');
                $this->CI->db->join('section', 'section.id = teacher_allocation.section_id', 'left');
                $this->CI->db->where('teacher_allocation.teacher_id', get_loggedin_user_id());
                $this->CI->db->where('teacher_allocation.session_id', get_session_id());
                if (!empty($classID)) {
                    $this->CI->db->where('teacher_allocation.class_id', $classID);
                }
                $result = $this->CI->db->get()->result_array();
                return $result;
            }
        } else {
            return false;
        }
    }

    function getSchoolConfig($branchID = '', $select = '*')
    {
        $ci = &get_instance();
        $branch_id = empty($branchID) ? $ci->session->userdata('loggedin_branch') : $branchID;
        $sql = "SELECT $select FROM branch WHERE id = " . $ci->db->escape($branch_id);
        $result = $ci->db->query($sql)->row();
        return $result;
    }


    public function get_global_classes()
    {
        $CI = &get_instance();
        $CI->db->select('id, name');
        $CI->db->from('class');
        $CI->db->where('created_by_branch IS NULL');
        $CI->db->order_by('name', 'ASC');
        return $CI->db->get()->result_array();
    }

    public function getSelectBranchGlobal($table)
    {
        $CI = &get_instance();
        $output = array();
        $CI->db->select('id, name');
        $CI->db->from($table);
        $CI->db->where('status', 1);
        $CI->db->order_by('id', 'ASC');
        $result = $CI->db->get()->result_array();

        foreach ($result as $row) {
            $output[$row['id']] = $row['name'];
        }

        return $output;
    }

    public function getSelectListGlobal($table)
    {
        $CI = &get_instance();
        $output = array();
        $CI->db->select('id, name');
        $CI->db->from($table);
        $CI->db->where('created_by_branch IS NULL');
        $CI->db->order_by('name', 'ASC');
        $result = $CI->db->get()->result_array();

        foreach ($result as $row) {
            $output[$row['id']] = $row['name'];
        }

        return $output;
    }

    public function getSelectClassList()
    {
        $CI = &get_instance();
        $output = array();

        if (is_superadmin_loggedin()) {
            $CI->db->select('c.id, c.name');
            $CI->db->from('class c');
            $CI->db->order_by('c.name', 'ASC');
            $result = $CI->db->get()->result_array();
        } else {
            $branch_id = get_loggedin_branch_id();
            $CI->db->select('c.id, c.name');
            $CI->db->from('class c');
            $CI->db->join('class_branch_map cbm', 'cbm.class_id = c.id', 'left');
            $CI->db->where('cbm.branch_id', $branch_id);
            $CI->db->or_where('c.created_by_branch', $branch_id);
            $CI->db->group_by('c.id');
            $CI->db->order_by('c.name', 'ASC');
            $result = $CI->db->get()->result_array();
        }

        foreach ($result as $row) {
            $output[$row['id']] = $row['name'];
        }

        return $output;
    }


}
