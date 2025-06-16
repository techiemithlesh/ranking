<?php if (empty($student_id)): ?>
    <div class="smart-education-hub">
        <div class="hub-header">
            <div class="student-profile-section">
                <div class="student-info">
                    <p class="class-badge">
                        Welcome <span><?= $this->session->userdata('name') ?></span>
                    </p>
                </div>
            </div>
        </div>

        <div class="dashboard-grid">
            <div class="dashboard-card common-card">
                <div class="card-icon"><i class="fas fa-user-friends"></i></div>
                <h4><?= translate('my_children') ?></h4>
                <a class="card-link" href="<?= base_url('parents/my_children') ?>"></a>
            </div>
        </div>
    </div>
<?php else: ?>
    <?php
    // fetch student info
    $this->db->select('s.id,s.first_name,s.last_name,s.photo,e.class_id,c.name as class_name');
    $this->db->from('student as s');
    $this->db->join('enroll as e', 'e.student_id = s.id', 'left');
    $this->db->join('class as c', 'e.class_id = c.id', 'left');
    $this->db->where('s.id', $student_id);
    $student_data = $this->db->get()->row();
    ?>

    <div class="smart-education-hub">
        <div class="hub-header">
            <div class="student-profile-section">
                <div class="student-avatar">
                    <img src="<?= get_image_url('student', $student_data->photo) ?>" alt="Student Photo">
                </div>
                <div class="student-info">
                    <h3><?= html_escape($student_data->first_name . " " . $student_data->last_name) ?></h3>
                    <span class="class-badge"><?= html_escape($student_data->class_name) ?></span>
                </div>
            </div>
            <div class="main_menu_container">
              
            </div>
        </div>

        <div class="dashboard-grid" id="menuGrid"></div>
    </div>

<?php endif; ?>
