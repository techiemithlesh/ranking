<aside id="sidebar-left" class="sidebar-left">
    <div class="sidebar-header">
        <div class="sidebar-title">
            Main
        </div>
    </div>

    <div class="nano">
        <div class="nano-content">
            <nav id="menu" class="nav-main" role="navigation">
                <ul class="nav nav-main">
                    <!-- dashboard -->
                    <?php if (is_superadmin_loggedin()) { ?>
                        <li class="nav-parent <?php if ($main_menu == 'dashboard' && !empty($school_id))
                                                    echo 'nav-active nav-expanded'; ?>">
                            <a>
                                <i class="icons icon-grid"></i><span><?= translate('dashboard') ?></span>
                            </a>
                            <ul class="nav nav-children">
                                <?php $school_id = $this->input->get('school_id'); ?>
                                <li class="<?php if ($main_menu == 'dashboard' && empty($school_id))
                                                echo 'nav-active'; ?>">
                                    <a href="<?= base_url('dashboard') ?>">
                                        <span><i class="fas fa-caret-right" aria-hidden="true"></i>
                                            <?= translate('all_branches') ?></span>
                                    </a>
                                </li>
                                <?php
                                $branches = $this->db->get('branch')->result();
                                foreach ($branches as $row) {
                                ?>
                                    <li class="<?php if ($school_id == $row->id)
                                                    echo 'nav-active'; ?>">
                                        <a href="<?= base_url('dashboard/index?school_id=' . $row->id) ?>">
                                            <span><i class="fas fa-caret-right" aria-hidden="true"></i>
                                                <?= html_escape($row->name) ?></span>
                                        </a>
                                    </li>
                                <?php } ?>
                            </ul>
                        </li>
                    <?php } else { ?>
                        <li class="<?php if ($main_menu == 'dashboard')
                                        echo 'nav-active'; ?>">
                            <a href="<?= base_url('dashboard') ?>">
                                <i class="icons icon-grid"></i><span><?= translate('dashboard') ?></span>
                            </a>
                        </li>
                    <?php } ?>
                    <!-- TRAINING MATERIAL FOR VIDEO START HERE -->

                    <?php if (is_superadmin_loggedin() || is_admin_loggedin()): ?>
                        <li class="nav-parent <?php if ($main_menu == 'Template_manager') echo 'nav-expanded nav-active'; ?>">
                            <a>
                                <i class="fas fa-layer-group"></i><span><?= translate('Template_manager') ?></span>
                            </a>
                            <ul class="nav nav-children">

                                <?php if (is_superadmin_loggedin()): ?>
                                    <li class="<?php if ($sub_page == 'template_manager/index') echo 'nav-active'; ?>">
                                        <a href="<?= base_url('Template_manager') ?>">
                                            <span><?= translate('Template_manager') ?></span>
                                        </a>
                                    </li>
                                <?php endif; ?>

                                <?php if (is_admin_loggedin()): ?>
                                    <li class="<?php if ($sub_page == 'template_manager/branch_templates') echo 'nav-active'; ?>">
                                        <a href="<?= base_url('Template_manager/branch_templates') ?>">
                                            <span><?= translate('download_template') ?></span>
                                        </a>
                                    </li>
                                <?php endif; ?>

                            </ul>
                        </li>
                    <?php endif; ?>

                    <?php if (is_superadmin_loggedin() || is_admin_loggedin() || is_teacher_loggedin()) { ?>

                        <li class="nav-parent <?php if ($main_menu == 'Resources')
                                                    echo 'nav-expanded'; ?>">
                            <a>
                                <i class="fas fa-book-reader"></i><?= translate('Resources') ?>
                            </a>
                            <ul class="nav nav-children">
                                <li class="<?php if ($sub_page == 'training/index')
                                                echo 'nav-active'; ?>">
                                    <a href="<?= base_url('training') ?>">
                                        <span><?= translate('Training Material') ?></span>
                                    </a>
                                </li>
                                <li class="<?php if ($sub_page == 'marketing/index')
                                                echo 'nav-active'; ?>">
                                    <a href="<?= base_url('marketing') ?>">
                                        <span><?= translate('Marketing Material') ?></span>
                                    </a>
                                </li>

                                <li class="<?php if ($sub_page == 'educational_material/index')
                                                echo 'nav-active'; ?>">
                                    <a href="<?= base_url('EducationMaterial/index') ?>">
                                        <span><?= translate('Educational Material') ?></span>
                                    </a>
                                </li>

                            </ul>
                        </li>

                    <?php } ?>

                    <!-- TRAINING MATERIAL FOR VIDEO END HERE -->

                    <!-- GALLERY -->

                    <?php
                    if (get_permission('digital_gallery', 'is_view') || get_permission('digital_gallery', 'is_add')) {
                    ?>
                        <li class="nav-parent <?php if ($main_menu == 'my_gallery')
                                                    echo 'nav-expanded'; ?>">
                            <a>
                                <i class="fas fa-images"></i><?= translate('MY_Gallery') ?>
                            </a>
                            <ul class="nav nav-children">

                                <li class="<?php if ($sub_page == 'gallery/index')
                                                echo 'nav-active'; ?>">
                                    <a href="<?= base_url('gallery/index') ?>">
                                        <span><?= translate('my_Gallery') ?></span>
                                    </a>
                                </li>

                                <li class="<?php if ($sub_page == 'gallery/create')
                                                echo 'nav-active'; ?>">
                                    <a href="<?= base_url('gallery/create') ?>">
                                        <span><?= translate('Create_Gallery') ?></span>
                                    </a>
                                </li>

                            </ul>
                        <?php
                    }
                        ?>


                        <?php if (is_superadmin_loggedin()): ?>
                            <!-- branch -->

                        <li class="nav-parent <?php if ($main_menu == 'branch')
                                                    echo 'nav-expanded nav-active'; ?>">
                            <a>
                                <i class="icons icon-directions"></i><?= translate('branch') ?></span>
                            </a>
                            <ul class="nav nav-children">
                                <li class="">
                                    <a href="<?= base_url('branch') ?>">
                                        <span><i class="fas fa-caret-right"
                                                aria-hidden="true"></i><?= translate('branch') ?></span>
                                    </a>
                                </li>

                                <li class="<?php if ($sub_page == 'branch/multi_branch_import')
                                                echo 'nav-active'; ?>">
                                    <a href="<?= base_url('branch/csv_import') ?>">
                                        <span><i class="fas fa-caret-right"
                                                aria-hidden="true"></i><?= translate('multiple_import') ?></span>
                                    </a>
                                </li>
                            </ul>
                        </li>

                    <?php endif; ?>

                    <?php
                    if (
                        get_permission('student', 'is_add') ||
                        get_permission('multiple_import', 'is_add') ||
                        get_permission('student_category', 'is_view')
                    ) {
                    ?>
                        <!-- admission -->
                        <li class="nav-parent <?php if ($main_menu == 'admission')
                                                    echo 'nav-expanded nav-active'; ?>">
                            <a>
                                <i class="far fa-edit"></i><span><?= translate('admission') ?></span>
                            </a>
                            <ul class="nav nav-children">
                                <?php if (get_permission('student', 'is_add')) { ?>
                                    <li class="<?php if ($sub_page == 'student/add')
                                                    echo 'nav-active'; ?>">
                                        <a href="<?= base_url('student/add') ?>">
                                            <span><i class="fas fa-caret-right"
                                                    aria-hidden="true"></i><?= translate('create_admission') ?></span>
                                        </a>
                                    </li>
                                <?php }
                                if (get_permission('multiple_import', 'is_add')) { ?>
                                    <li class="<?php if ($sub_page == 'student/multi_add')
                                                    echo 'nav-active'; ?>">
                                        <a href="<?= base_url('student/csv_import') ?>">
                                            <span><i class="fas fa-caret-right"
                                                    aria-hidden="true"></i><?= translate('multiple_import') ?></span>
                                        </a>
                                    </li>
                                <?php }

                                if (is_superadmin_loggedin() && get_permission('student_category', 'is_view')) { ?>
                                    <li class="<?php if ($sub_page == 'student/category')
                                                    echo 'nav-active'; ?>">
                                        <a href="<?= base_url('student/category') ?>">
                                            <span><i class="fas fa-caret-right"
                                                    aria-hidden="true"></i><?= translate('category') ?></span>
                                        </a>
                                    </li>
                                <?php } ?>
                            </ul>
                        </li>
                    <?php } ?>

                    <?php
                    if (
                        get_permission('student', 'is_view') ||
                        get_permission('student_disable_authentication', 'is_view') ||
                        get_permission('student_id_card', 'is_view')
                    ) {
                    ?>
                        <!-- student details -->
                        <li class="nav-parent <?php if ($main_menu == 'student')
                                                    echo 'nav-expanded nav-active'; ?>">
                            <a>
                                <i class="icon-graduation icons"></i><span><?= translate('student_details') ?></span>
                            </a>
                            <ul class="nav nav-children">
                                <?php if (get_permission('student', 'is_view')) { ?>
                                    <li class="<?php if ($sub_page == 'student/view' || $sub_page == 'student/profile')
                                                    echo 'nav-active'; ?>">
                                        <a href="<?= base_url('student/view') ?>">
                                            <span><i class="fas fa-caret-right"
                                                    aria-hidden="true"></i><?= translate('student_list') ?></span>
                                        </a>
                                    </li>
                                <?php }
                                if (get_permission('student_id_card', 'is_view')) { ?>
                                    <li class="<?php if ($sub_page == 'student/idcard')
                                                    echo 'nav-active'; ?>">
                                        <a href="<?= base_url('student/generate_idcard') ?>">
                                            <span><i class="fas fa-caret-right"
                                                    aria-hidden="true"></i><?= translate('id_card_generate') ?></span>
                                        </a>
                                    </li>
                                <?php }
                                if (get_permission('student_disable_authentication', 'is_view')) { ?>
                                    <li class="<?php if ($sub_page == 'student/disable_authentication')
                                                    echo 'nav-active'; ?>">
                                        <a href="<?= base_url('student/disable_authentication') ?>">
                                            <span><i class="fas fa-caret-right"
                                                    aria-hidden="true"></i><?= translate('login_deactivate') ?></span>
                                        </a>
                                    </li>
                                <?php } ?>
                            </ul>
                        </li>
                    <?php } ?>
                    <?php
                    if (
                        get_permission('parent', 'is_view') ||
                        get_permission('parent', 'is_add') ||
                        get_permission('parent_disable_authentication', 'is_view')
                    ) {
                    ?>
                        <!-- parents -->
                        <li class="nav-parent <?php if ($main_menu == 'parents')
                                                    echo 'nav-expanded nav-active'; ?>">
                            <a>
                                <i class="icons icon-user-follow"></i><span><?= translate('parents') ?></span>
                            </a>
                            <ul class="nav nav-children">
                                <?php if (get_permission('parent', 'is_view')) { ?>
                                    <li class="<?php if ($sub_page == 'parents/view' || $sub_page == 'parents/profile')
                                                    echo 'nav-active'; ?>">
                                        <a href="<?= base_url('parents/view') ?>">
                                            <span><i class="fas fa-caret-right"></i><?= translate('parents_list') ?></span>
                                        </a>
                                    </li>
                                <?php }
                                if (get_permission('parent', 'is_add')) { ?>
                                    <li class="<?php if ($sub_page == 'parents/add')
                                                    echo 'nav-active'; ?>">
                                        <a href="<?= base_url('parents/add') ?>">
                                            <span><i class="fas fa-caret-right"></i><?= translate('add_parent') ?></span>
                                        </a>
                                    </li>
                                <?php }
                                if (get_permission('parent_disable_authentication', 'is_view')) { ?>
                                    <li class="<?php if ($sub_page == 'parents/disable_authentication')
                                                    echo 'nav-active'; ?>">
                                        <a href="<?= base_url('parents/disable_authentication') ?>">
                                            <span><i class="fas fa-caret-right"
                                                    aria-hidden="true"></i><?= translate('login_deactivate') ?></span>
                                        </a>
                                    </li>
                                <?php } ?>
                            </ul>
                        </li>
                    <?php } ?>
                    <?php
                    if (
                        get_permission('employee', 'is_view') ||
                        get_permission('employee', 'is_add') ||
                        get_permission('designation', 'is_view') ||
                        get_permission('designation', 'is_add') ||
                        get_permission('department', 'is_view') ||
                        get_permission('employee_disable_authentication', 'is_view')
                    ) {
                    ?>
                        <!-- Employees -->
                        <li class="nav-parent <?php if ($main_menu == 'employee')
                                                    echo 'nav-expanded nav-active'; ?>">
                            <a><i class="fas fa-users"></i><span><?php echo translate('employee'); ?></span></a>
                            <ul class="nav nav-children">
                                <?php if (get_permission('employee', 'is_view')) { ?>
                                    <li class="<?php if ($sub_page == 'employee/view' || $sub_page == 'employee/profile')
                                                    echo 'nav-active'; ?>">
                                        <a href="<?php echo base_url('employee/view'); ?>">
                                            <span><i class="fas fa-caret-right"
                                                    aria-hidden="true"></i><?php echo translate('employee_list'); ?></span>
                                        </a>
                                    </li>
                                <?php }
                                if (get_permission('department', 'is_view') || get_permission('department', 'is_add')) { ?>
                                    <li class="<?php if ($sub_page == 'employee/department')
                                                    echo 'nav-active'; ?>">
                                        <a href="<?php echo base_url('employee/department'); ?>">
                                            <span><i class="fas fa-caret-right"
                                                    aria-hidden="true"></i><?php echo translate('add_department'); ?></span>
                                        </a>
                                    </li>
                                <?php }
                                if (get_permission('designation', 'is_view') || get_permission('designation', 'is_add')) { ?>
                                    <li class="<?php if ($sub_page == 'employee/designation')
                                                    echo 'nav-active'; ?>">
                                        <a href="<?php echo base_url('employee/designation'); ?>">
                                            <span><i class="fas fa-caret-right"
                                                    aria-hidden="true"></i><?php echo translate('add_designation'); ?></span>
                                        </a>
                                    </li>
                                <?php }
                                if (get_permission('employee', 'is_add')) { ?>
                                    <li class="<?php if ($sub_page == 'employee/add')
                                                    echo 'nav-active'; ?>">
                                        <a href="<?php echo base_url('employee/add'); ?>">
                                            <span><i class="fas fa-caret-right"
                                                    aria-hidden="true"></i><?php echo translate('add_employee'); ?></span>
                                        </a>
                                    </li>
                                <?php }
                                if (get_permission('employee_disable_authentication', 'is_view')) { ?>
                                    <li class="<?php if ($sub_page == 'employee/disable_authentication')
                                                    echo 'nav-active'; ?>">
                                        <a href="<?php echo base_url('employee/disable_authentication'); ?>">
                                            <span><i class="fas fa-caret-right"
                                                    aria-hidden="true"></i><?php echo translate('login_deactivate'); ?></span>
                                        </a>
                                    </li>
                                <?php } ?>
                            </ul>
                        </li>
                    <?php } ?>
                    <?php
                    if (
                        get_permission('salary_template', 'is_view') ||
                        get_permission('salary_assign', 'is_view') ||
                        get_permission('salary_payment', 'is_view') ||
                        get_permission('advance_salary_manage', 'is_view') ||
                        get_permission('advance_salary_request', 'is_view') ||
                        get_permission('leave_category', 'is_view') ||
                        get_permission('leave_category', 'is_add') ||
                        get_permission('leave_request', 'is_view') ||
                        get_permission('leave_manage', 'is_view') ||
                        get_permission('award', 'is_view')
                    ) {
                    ?>
                        <!-- human resource -->
                        <li class="nav-parent <?php if ($main_menu == 'payroll' || $main_menu == 'advance_salary' || $main_menu == 'leave' || $main_menu == 'award')
                                                    echo 'nav-expanded nav-active'; ?>">
                            <a>
                                <i class="icons icon-loop"></i><span><?= translate('hrm') ?></span>
                            </a>
                            <ul class="nav nav-children">
                                <?php
                                if (
                                    get_permission('salary_template', 'is_view') ||
                                    get_permission('salary_assign', 'is_view') ||
                                    get_permission('salary_payment', 'is_view')
                                ) {
                                ?>
                                    <!-- payroll -->
                                    <li class="nav-parent <?php if ($main_menu == 'payroll')
                                                                echo 'nav-expanded nav-active'; ?>">
                                        <a>
                                            <i class="far fa-address-card" aria-hidden="true"></i>
                                            <span><?= translate('payroll') ?></span>
                                        </a>
                                        <ul class="nav nav-children">
                                            <?php if (get_permission('salary_template', 'is_view')) { ?>
                                                <li class="<?php if ($sub_page == 'payroll/salary_templete' || $sub_page == 'payroll/salary_templete_edit')
                                                                echo 'nav-active'; ?>">
                                                    <a href="<?= base_url('payroll/salary_template') ?>">
                                                        <span><?= translate('salary_template') ?></span>
                                                    </a>
                                                </li>
                                            <?php }
                                            if (get_permission('salary_assign', 'is_view')) { ?>
                                                <li class="<?php if ($sub_page == 'payroll/salary_assign')
                                                                echo 'nav-active'; ?>">
                                                    <a href="<?= base_url('payroll/salary_assign') ?>">
                                                        <span><?= translate('salary_assign') ?></span>
                                                    </a>
                                                </li>
                                            <?php }
                                            if (get_permission('salary_payment', 'is_view')) { ?>
                                                <li class="<?php if ($sub_page == 'payroll/salary_payment' || $sub_page == 'payroll/create' || $sub_page == 'payroll/invoice')
                                                                echo 'nav-active'; ?>">
                                                    <a href="<?= base_url('payroll') ?>">
                                                        <span><?= translate('salary_payment') ?></span>
                                                    </a>
                                                </li>
                                            <?php } ?>
                                        </ul>
                                    </li>
                                <?php } ?>
                                <?php
                                if (
                                    get_permission('advance_salary_manage', 'is_view') ||
                                    get_permission('advance_salary_request', 'is_view')
                                ) {
                                ?>
                                    <!-- advance salary managements -->
                                    <li class="nav-parent <?php
                                                            if ($main_menu == 'advance_salary')
                                                                echo 'nav-expanded nav-active'; ?>">
                                        <a>
                                            <i class="fas fa-funnel-dollar" aria-hidden="true"></i>
                                            <span><?= translate('advance_salary') ?></span>
                                        </a>
                                        <ul class="nav nav-children">
                                            <?php if (get_permission('advance_salary_request', 'is_view')) { ?>
                                                <li class="<?php if ($sub_page == 'advance_salary/request')
                                                                echo 'nav-active'; ?>">
                                                    <a href="<?= base_url('advance_salary/request') ?>">
                                                        <span><?= translate('my_application') ?></span>
                                                    </a>
                                                </li>
                                            <?php }
                                            if (get_permission('advance_salary_manage', 'is_view')) { ?>
                                                <li class="<?php if ($sub_page == 'advance_salary/index')
                                                                echo 'nav-active'; ?>">
                                                    <a href="<?= base_url('advance_salary') ?>">
                                                        <span><?= translate('manage_application') ?></span>
                                                    </a>
                                                </li>
                                            <?php } ?>
                                        </ul>
                                    </li>
                                <?php } ?>
                                <?php
                                if (
                                    get_permission('leave_category', 'is_view') ||
                                    get_permission('leave_manage', 'is_view') ||
                                    get_permission('leave_request', 'is_view')
                                ) {
                                ?>
                                    <!-- leave managements -->
                                    <li class="nav-parent <?php
                                                            if ($main_menu == 'leave')
                                                                echo 'nav-expanded nav-active'; ?>">
                                        <a>
                                            <i class="fas fa-umbrella-beach" aria-hidden="true"></i>
                                            <span><?= translate('leave') ?></span>
                                        </a>
                                        <ul class="nav nav-children">
                                            <?php if (get_permission('leave_category', 'is_view')) { ?>
                                                <li class="<?php if ($sub_page == 'leave/category')
                                                                echo 'nav-active'; ?>">
                                                    <a href="<?= base_url('leave/category') ?>">
                                                        <span><?= translate('category') ?></span>
                                                    </a>
                                                </li>
                                            <?php }
                                            if (get_permission('leave_request', 'is_view')) { ?>
                                                <li class="<?php if ($sub_page == 'leave/request')
                                                                echo 'nav-active'; ?>">
                                                    <a href="<?= base_url('leave/request') ?>">
                                                        <span><?= translate('my_application') ?></span>
                                                    </a>
                                                </li>
                                            <?php }
                                            if (get_permission('leave_manage', 'is_view')) { ?>
                                                <li class="<?php if ($sub_page == 'leave/index')
                                                                echo 'nav-active'; ?>">
                                                    <a href="<?= base_url('leave') ?>">
                                                        <span><?= translate('manage_application') ?></span>
                                                    </a>
                                                </li>
                                            <?php } ?>
                                        </ul>
                                    </li>
                                <?php } ?>
                                <?php if (get_permission('award', 'is_view')) { ?>
                                    <li class="<?php if ($sub_page == 'award/index' || $sub_page == 'award/edit')
                                                    echo 'nav-active'; ?>">
                                        <a href="<?= base_url('award') ?>">
                                            <i class="fas fa-crown"></i>
                                            <span><?= translate('award') ?></span>
                                        </a>
                                    </li>
                                <?php } ?>
                            </ul>
                        </li>
                    <?php } ?>
                    <?php
                    if (
                        get_permission('classes', 'is_view') ||
                        get_permission('section', 'is_view') ||
                        get_permission('assign_class_teacher', 'is_view') ||
                        get_permission('subject', 'is_view') ||
                        get_permission('subject_class_assign', 'is_view') ||
                        get_permission('subject_teacher_assign', 'is_view') ||
                        get_permission('class_timetable', 'is_view')
                    ) {
                    ?>
                        <!-- academic -->
                        <li class="nav-parent <?php if (
                                                    $main_menu == 'classes' ||
                                                    $main_menu == 'sections' ||
                                                    $main_menu == 'timetable' ||
                                                    $main_menu == 'subject' ||
                                                    $main_menu == 'transfer'
                                                )
                                                    echo 'nav-expanded nav-active'; ?>">
                            <a>
                                <i class="icons icon-home" aria-hidden="true"></i><span><?= translate('academic') ?></span>
                            </a>

                            <ul class="nav nav-children">
                                <?php
                                if (
                                    get_permission('classes', 'is_view') ||
                                    get_permission('section', 'is_view') ||
                                    get_permission('assign_class_teacher', 'is_view')
                                ) {
                                ?>
                                    <!-- class -->
                                    <li class="nav-parent <?php
                                                            if ($main_menu == 'classes' || $main_menu == 'sections' || $main_menu == 'class_teacher_allocation')
                                                                echo 'nav-expanded nav-active'; ?>">
                                        <a>
                                            <i class="fas fa-tasks" aria-hidden="true"></i>
                                            <span><?= translate('class') . " & " . translate('section') ?></span>
                                        </a>
                                        <ul class="nav nav-children">
                                            <?php if (get_permission('classes', 'is_view') || get_permission('section', 'is_view')) { ?>
                                                <li class="<?php if (
                                                                $sub_page == 'classes/index' ||
                                                                $sub_page == 'classes/edit' ||
                                                                $sub_page == 'sections/index' ||
                                                                $sub_page == 'sections/edit'
                                                            )
                                                                echo 'nav-active'; ?>">
                                                    <a
                                                        href="<?= get_permission('classes', 'is_view') ? base_url('classes') : base_url('sections'); ?>">
                                                        <span><?= translate('control_classes') ?></span>
                                                    </a>
                                                </li>
                                                <?php if (is_superadmin_loggedin()) { ?>
                                                    <li class="<?php if ($sub_page == 'classes/class_allocation')
                                                                    echo 'nav-active'; ?>">
                                                        <a href="<?= base_url('classes/classAssign') ?>">
                                                            <span><?= translate('assign_class_to_branch') ?></span>
                                                        </a>
                                                    </li>
                                                <?php } ?>
                                            <?php } ?>
                                            <?php if (get_permission('assign_class_teacher', 'is_view')) { ?>
                                                <li class="<?php if ($sub_page == 'classes/teacher_allocation')
                                                                echo 'nav-active'; ?>">
                                                    <a href="<?= base_url('classes/teacher_allocation') ?>">
                                                        <span><?= translate('assign_class_teacher') ?></span>
                                                    </a>
                                                </li>
                                            <?php } ?>
                                        </ul>
                                    </li>
                                <?php } ?>
                                <?php
                                if (
                                    get_permission('subject', 'is_view') ||
                                    get_permission('subject_class_assign', 'is_view') ||
                                    get_permission('subject_teacher_assign', 'is_view')
                                ) {
                                ?>
                                    <!-- subject -->
                                    <li class="nav-parent <?php if ($main_menu == 'subject')
                                                                echo 'nav-expanded'; ?>">
                                        <a>
                                            <i class="fas fa-book-reader"></i><?= translate('subject') ?>
                                        </a>
                                        <ul class="nav nav-children">
                                            <?php if (get_permission('subject', 'is_view')) { ?>
                                                <li class="<?php if ($sub_page == 'subject/index' || $sub_page == 'subject/edit')
                                                                echo 'nav-active'; ?>">
                                                    <a href="<?= base_url('subject/index') ?>">
                                                        <span><?= translate('subject') ?></span>
                                                    </a>
                                                </li>
                                            <?php }

                                            if (is_superadmin_loggedin()) {
                                            ?>
                                                <li class="<?php if ($sub_page == 'subject/branch_allocation')
                                                                echo 'nav-active'; ?>">
                                                    <a href="<?= base_url('subject/subjectBranchAssign') ?>">
                                                        <span><?= translate('Assign_branch') ?></span>
                                                    </a>
                                                </li>
                                            <?php
                                            }

                                            if (get_permission('subject_class_assign', 'is_view')) { ?>
                                                <li class="<?php if ($sub_page == 'subject/class_assign')
                                                                echo 'nav-active'; ?>">
                                                    <a href="<?= base_url('subject/class_assign') ?>">
                                                        <span><?= translate('class_assign') ?></span>
                                                    </a>
                                                </li>
                                            <?php }
                                            if (get_permission('subject_teacher_assign', 'is_view')) { ?>
                                                <li class="<?php if ($sub_page == 'subject/teacher_assign')
                                                                echo 'nav-active'; ?>">
                                                    <a href="<?= base_url('subject/teacher_assign') ?>">
                                                        <span><?= translate('teacher') . ' ' . translate('assign') ?></span>
                                                    </a>
                                                </li>
                                            <?php } ?>
                                        </ul>
                                    </li>
                                <?php } ?>
                                <?php if (get_permission('class_timetable', 'is_view')) { ?>
                                    <li class="<?php if ($sub_page == 'timetable/viewclass' || $sub_page == 'timetable/update_classwise' || $sub_page == 'timetable/set_classwise')
                                                    echo 'nav-active'; ?>">
                                        <a href="<?= base_url('timetable/viewclass') ?>">
                                            <span><i class="fas fa-dna"
                                                    aria-hidden="true"></i><?= translate('class') . " " . translate('schedule') ?></span>
                                        </a>
                                    </li>
                                <?php } ?>
                                <?php if (get_permission('student_promotion', 'is_view')) { ?>
                                    <!-- student promotion -->
                                    <li class="<?php if ($sub_page == 'student/transfer')
                                                    echo 'nav-active'; ?>">
                                        <a href="<?= base_url('student/transfer') ?>">
                                            <span><i class="fab fa-deviantart"
                                                    aria-hidden="true"></i><?= translate('promotion') ?></span>
                                        </a>
                                    </li>
                                <?php } ?>
                            </ul>
                        </li>
                    <?php } ?>
                    <?php if (get_permission('live_class', 'is_view')) { ?>
                        <li class="<?php if ($main_menu == 'live_class')
                                        echo 'nav-active'; ?>">
                            <a href="<?= base_url('live_class') ?>">
                                <i class="icons icon-earphones-alt"></i><span><?= translate('live_class_rooms') ?></span>
                            </a>
                        </li>
                    <?php } ?>
                    <?php
                    if (
                        get_permission('attachments', 'is_view') ||
                        get_permission('attachment_type', 'is_view')
                    ) {
                    ?>
                        <!-- attachments upload -->
                        <li class="nav-parent <?php if ($main_menu == 'attachments')
                                                    echo 'nav-expanded nav-active'; ?>">
                            <a>
                                <i class="icons icon-cloud-upload"></i><span><?= translate('attachments_book') ?></span>
                            </a>
                            <ul class="nav nav-children">
                                <?php if (get_permission('attachments', 'is_view')) { ?>
                                    <li class="<?php if ($sub_page == 'attachments/index')
                                                    echo 'nav-active'; ?>">
                                        <a href="<?= base_url('attachments') ?>">
                                            <span><i class="fas fa-caret-right"
                                                    aria-hidden="true"></i><?= translate('upload_content') ?></span>
                                        </a>
                                    </li>
                                <?php }
                                if (get_permission('attachment_type', 'is_view')) { ?>
                                    <li class="<?php if ($sub_page == 'attachments/type')
                                                    echo 'nav-active'; ?>">
                                        <a href="<?= base_url('attachments/type') ?>">
                                            <span><i class="fas fa-caret-right"
                                                    aria-hidden="true"></i><?= translate('attachment_type') ?></span>
                                        </a>
                                    </li>
                                <?php } ?>
                            </ul>
                        </li>
                    <?php } ?>

                    <?php if (get_permission('upload_books_link', 'is_view')) { ?>
                        <li class="nav-parent <?php if ($main_menu == 'BookUpload')
                                                    echo 'nav-expanded nav-active'; ?>">
                            <a>
                                <i class="icons icon-cloud-upload"></i><span><?= translate('my_interactive_book') ?></span>
                            </a>
                            <ul class="nav nav-children">
                                <?php if (get_permission('attachments', 'is_view')) { ?>
                                    <li class="<?php if ($sub_page == 'book_upload/index')
                                                    echo 'nav-active'; ?>">
                                        <a href="<?= base_url('StudentBooks/index') ?>">
                                            <span><i class="fas fa-caret-right"
                                                    aria-hidden="true"></i><?= translate('students_book') ?></span>
                                        </a>
                                    </li>

                                    <?php if (is_superadmin_loggedin()) {
                                    ?>


                                        <?php if (get_permission('assign_branch', 'is_edit')) { ?>
                                            <li class="<?php if ($sub_page == 'book_upload/assign_branch')
                                                            echo 'nav-active'; ?>">
                                                <a href="<?= base_url('StudentBooks/assignBranches') ?>">
                                                    <span><i class="fas fa-caret-right"
                                                            aria-hidden="true"></i><?= translate('assign_branch') ?></span>
                                                </a>
                                            </li>
                                        <?php } ?>
                                    <?php
                                    }
                                    ?>


                                <?php } ?>

                                <?php if (is_admin_loggedin()) { ?>
                                    <?php if (get_permission('assign_class', 'is_edit')) { ?>
                                        <li class="<?php if ($sub_page == 'book_upload/assign_class')
                                                        echo 'nav-active'; ?>">
                                            <a href="<?= base_url('StudentBooks/assignClass') ?>">
                                                <span><i class="fas fa-caret-right"
                                                        aria-hidden="true"></i><?= translate('assign_class') ?></span>
                                            </a>
                                        </li>
                                    <?php } ?>

                                <?php
                                }
                                ?>

                            </ul>
                        </li>
                    <?php } ?>
                    <!-- TEACHERS BOOK -->
                    <?php
                    if (is_superadmin_loggedin() || is_admin_loggedin() || is_teacher_loggedin()) {
                    ?>
                        <li class="nav-parent <?php if ($main_menu == 'Teacher_materials')
                                                    echo 'nav-expanded nav-active'; ?>">
                            <a>
                                <i class="icons icon-notebook"></i><span><?= translate('Teacher_materials') ?></span>
                            </a>
                            <ul class="nav nav-children">
                                <?php if (is_superadmin_loggedin() || is_admin_loggedin() || is_teacher_loggedin()) { ?>
                                    <li class="<?php if ($sub_page == 'teacherbooks/index')
                                                    echo 'nav-active'; ?>">
                                        <a href="<?= base_url('Teacher_Books/index') ?>">
                                            <span><i class="fas fa-caret-right"></i><?= translate('digital_books') ?></span>
                                        </a>
                                    </li>


                                <?php }
                                if (is_superadmin_loggedin()) {
                                ?>
                                    <li class="<?php if ($sub_page == 'teacherbooks/assign_branch')
                                                    echo 'nav-active'; ?>">
                                        <a href="<?= base_url('Teacher_Books/assignBranch') ?>">
                                            <span><i class="fas fa-caret-right"></i><?= translate('assign_branch') ?></span>
                                        </a>
                                    </li>
                                <?php
                                }

                                if (is_admin_loggedin()) { ?>
                                    <li class="<?php if ($sub_page == 'digital_library/assign_class')
                                                    echo 'nav-active'; ?>">
                                        <a href="<?= base_url('Teacher_Books/assignClass') ?>">
                                            <span><i class="fas fa-caret-right"></i>Assign Class</span>
                                        </a>
                                    </li>


                                <?php } ?>

                                <?php if (is_teacher_loggedin()) { ?>
                                    <li class="<?php if ($sub_page == 'teacherbooks/my_books')
                                                    echo 'nav-active'; ?>">
                                        <a href="<?= base_url('Teacher_Books/myBooks') ?>">
                                            <span><i class="fas fa-caret-right"></i>My Books</span>
                                        </a>
                                    </li>
                                <?php } ?>


                            </ul>
                        </li>
                    <?php
                    }

                    ?>

                    <?php
                    if (
                        get_permission('homework', 'is_view') ||
                        get_permission('evaluation_report', 'is_view')
                    ) {
                    ?>
                        <!-- attachments upload -->
                        <li class="nav-parent <?php if ($main_menu == 'homework')
                                                    echo 'nav-expanded nav-active'; ?>">
                            <a>
                                <i class="icons icon-note"></i><span><?= translate('homework') ?></span>
                            </a>
                            <ul class="nav nav-children">
                                <?php if (get_permission('homework', 'is_view')) { ?>
                                    <li class="<?php if ($sub_page == 'homework/index' || $sub_page == 'homework/add' || $sub_page == 'homework/evaluate_list' || $sub_page == 'homework/edit')
                                                    echo 'nav-active'; ?>">
                                        <a href="<?= base_url('homework') ?>">
                                            <span><i class="fas fa-caret-right"
                                                    aria-hidden="true"></i><?= translate('homework') ?></span>
                                        </a>
                                    </li>
                                <?php }
                                if (get_permission('evaluation_report', 'is_view')) { ?>
                                    <li class="<?php if ($sub_page == 'homework/report')
                                                    echo 'nav-active'; ?>">
                                        <a href="<?= base_url('homework/report') ?>">
                                            <span><i class="fas fa-caret-right"
                                                    aria-hidden="true"></i><?= translate('evaluation_report') ?></span>
                                        </a>
                                    </li>
                                <?php } ?>
                            </ul>
                        </li>
                    <?php } ?>
                    <?php
                    if (
                        get_permission('exam', 'is_view') ||
                        get_permission('exam_term', 'is_view') ||
                        get_permission('mark_distribution', 'is_view') ||
                        get_permission('exam_hall', 'is_view') ||
                        get_permission('exam_timetable', 'is_view') ||
                        get_permission('exam_mark', 'is_view') ||
                        get_permission('exam_grade', 'is_view')
                    ) {
                    ?>
                        <!-- exam master -->
                        <li class="nav-parent <?php if ($main_menu == 'exam' || $main_menu == 'mark' || $main_menu == 'exam_timetable')
                                                    echo 'nav-expanded nav-active'; ?>">
                            <a>
                                <i class="icons icon-book-open"
                                    aria-hidden="true"></i><span><?= translate('exam_master') ?></span>
                            </a>
                            <ul class="nav nav-children">
                                <?php
                                if (
                                    get_permission('exam', 'is_view') ||
                                    get_permission('exam_term', 'is_view') ||
                                    get_permission('mark_distribution', 'is_view') ||
                                    get_permission('exam_hall', 'is_view')
                                ) {
                                ?>
                                    <!-- exam -->
                                    <li class="nav-parent <?php if ($main_menu == 'exam' || $main_menu == 'exam_term' || $main_menu == 'exam_hall')
                                                                echo 'nav-expanded nav-active'; ?>">
                                        <a>
                                            <i class="fas fa-flask"></i> <span><?= translate('exam') ?></span>
                                        </a>
                                        <ul class="nav nav-children">
                                            <?php if (get_permission('exam_term', 'is_view')) { ?>
                                                <li class="<?php if ($sub_page == 'exam/term')
                                                                echo 'nav-active'; ?>">
                                                    <a href="<?= base_url('exam/term') ?>">
                                                        <span><?= translate('exam_term') ?></span>
                                                    </a>
                                                </li>
                                            <?php }
                                            if (get_permission('exam_hall', 'is_view')) { ?>
                                                <li class="<?php if ($sub_page == 'exam/hall')
                                                                echo 'nav-active'; ?>">
                                                    <a href="<?= base_url('exam/hall') ?>">
                                                        <span><?= translate('exam_hall') ?></span>
                                                    </a>
                                                </li>
                                            <?php }
                                            if (get_permission('mark_distribution', 'is_view')) { ?>
                                                <li class="<?php if ($sub_page == 'exam/mark_distribution')
                                                                echo 'nav-active'; ?>">
                                                    <a href="<?= base_url('exam/mark_distribution') ?>">
                                                        <span><?= translate('distribution') ?></span>
                                                    </a>
                                                </li>
                                            <?php }
                                            if (get_permission('exam', 'is_view')) { ?>
                                                <li class="<?php if ($sub_page == 'exam/index')
                                                                echo 'nav-active'; ?>">
                                                    <a href="<?= base_url('exam') ?>">
                                                        <span><?= translate('exam_setup') ?></span>
                                                    </a>
                                                </li>
                                            <?php } ?>
                                        </ul>
                                    </li>
                                <?php } ?>
                                <?php
                                if (get_permission('exam_timetable', 'is_view')) {
                                ?>
                                    <!-- exam schedule -->
                                    <li class="nav-parent <?php if ($main_menu == 'exam_timetable')
                                                                echo 'nav-expanded nav-active'; ?>">
                                        <a>
                                            <i class="fas fa-dna"></i>
                                            <span><?= translate('exam') . " " . translate('schedule') ?></span>
                                        </a>
                                        <ul class="nav nav-children">
                                            <?php if (get_permission('exam_timetable', 'is_view')) { ?>
                                                <li class="<?php if ($sub_page == 'timetable/viewexam')
                                                                echo 'nav-active'; ?>">
                                                    <a href="<?= base_url('timetable/viewexam') ?>">
                                                        <span><?= translate('schedule') ?></span>
                                                    </a>
                                                </li>
                                            <?php }
                                            if (get_permission('exam_timetable', 'is_view')) { ?>
                                                <li class="<?php if ($sub_page == 'timetable/set_examwise')
                                                                echo 'nav-active'; ?>">
                                                    <a href="<?= base_url('timetable/set_examwise') ?>">
                                                        <span><?= translate('add') . " " . translate('schedule') ?></span>
                                                    </a>
                                                </li>
                                            <?php } ?>
                                        </ul>
                                    </li>
                                <?php } ?>
                                <?php
                                if (
                                    get_permission('exam_mark', 'is_view') ||
                                    get_permission('generate_position', 'is_view') ||
                                    get_permission('exam_grade', 'is_view')
                                ) {
                                ?>
                                    <!-- marks -->
                                    <li class="nav-parent <?php if ($main_menu == 'mark')
                                                                echo 'nav-expanded nav-active'; ?>">
                                        <a>
                                            <i class="fas fa-marker"></i><span><?= translate('marks') ?></span>
                                        </a>
                                        <ul class="nav nav-children">
                                            <?php if (get_permission('exam_mark', 'is_view')) { ?>
                                                <li class="<?php if ($sub_page == 'exam/marks_register')
                                                                echo 'nav-active'; ?>">
                                                    <a href="<?= base_url('exam/mark_entry') ?>">
                                                        <span><?= translate('mark_entries') ?></span>
                                                    </a>
                                                </li>
                                            <?php }
                                            if (get_permission('generate_position', 'is_view')) { ?>
                                                <li class="<?php if ($sub_page == 'exam/class_position')
                                                                echo 'nav-active'; ?>">
                                                    <a href="<?= base_url('exam/class_position') ?>">
                                                        <span><?= translate('generate_position') ?></span>
                                                    </a>
                                                </li>

                                            <?php }

                                            if (get_permission('exam_grade', 'is_view')) { ?>
                                                <li class="<?php if ($sub_page == 'exam/grade')
                                                                echo 'nav-active'; ?>">
                                                    <a href="<?= base_url('exam/grade') ?>">
                                                        <span><?= translate('grades_range') ?></span>
                                                    </a>
                                                </li>
                                            <?php } ?>
                                        </ul>
                                    </li>
                                <?php } ?>
                            </ul>
                        </li>
                    <?php } ?>

                    <!-- ONLINE EXAM -->
                    <?php
                    if (moduleIsEnabled('online_exam')) {
                        if (
                            get_permission('online_exam', 'is_view') ||
                            get_permission('question_bank', 'is_view') ||
                            get_permission('exam_result', 'is_view') ||
                            get_permission('subject_wise_exam_result', 'is_view') ||
                            get_permission('position_generate', 'is_view') ||
                            get_permission('question_group', 'is_view') ||
                            get_permission('live_exam', 'is_view')
                        ) {
                    ?>
                            <li class="nav-parent <?php if ($main_menu == 'onlineexam')
                                                        echo 'nav-expanded nav-active'; ?>">
                                <a>
                                    <i class="icon-screen-desktop"></i><span><?= translate('online_exam') ?></span>
                                </a>
                                <ul class="nav nav-children">
                                    <?php if (get_permission('online_exam', 'is_view')) { ?>
                                        <li class="<?php if ($sub_page == 'onlineexam/index' || $sub_page == 'onlineexam/edit' || $sub_page == 'onlineexam/manage_question' || $sub_page == 'onlineexam/question_list')
                                                        echo 'nav-active'; ?>">
                                            <a href="<?= base_url('onlineexam') ?>">
                                                <span><i class="fas fa-caret-right"
                                                        aria-hidden="true"></i><?= translate('online_exam') ?></span>
                                            </a>
                                        </li>
                                    <?php }
                                    if (get_permission('question_bank', 'is_view')) { ?>
                                        <li class="<?php if ($sub_page == 'onlineexam/question' || $sub_page == 'onlineexam/question_add' || $sub_page == 'onlineexam/question_edit')
                                                        echo 'nav-active'; ?>">
                                            <a href="<?= base_url('onlineexam/question') ?>">
                                                <span><i class="fas fa-caret-right"
                                                        aria-hidden="true"></i><?= translate('question_bank') ?></span>
                                            </a>
                                        </li>
                                    <?php }
                                    if (is_superadmin_loggedin()) {
                                    ?>
                                        <li class="<?php if ($sub_page == 'onlineexam/branch_assign')
                                                        echo 'nav-active'; ?>">
                                            <a href="<?= base_url('onlineexam/question_branch_assign') ?>">
                                                <span><i class="fas fa-caret-right"
                                                        aria-hidden="true"></i><?= translate('assign_questions_to_branch') ?></span>
                                            </a>
                                        </li>
                                    <?php
                                    }
                                    if (get_permission('question_group', 'is_view')) { ?>
                                        <li class="<?php if ($sub_page == 'onlineexam/question_group')
                                                        echo 'nav-active'; ?>">
                                            <a href="<?= base_url('onlineexam/question_group') ?>">
                                                <span><i class="fas fa-caret-right"
                                                        aria-hidden="true"></i><?= translate('question_group') ?></span>
                                            </a>
                                        </li>
                                    <?php }
                                    if (get_permission('position_generate', 'is_view')) { ?>
                                        <li class="<?php if ($sub_page == 'onlineexam/position_generate')
                                                        echo 'nav-active'; ?>">
                                            <a href="<?= base_url('onlineexam/position_generate') ?>">
                                                <span><i class="fas fa-caret-right"
                                                        aria-hidden="true"></i><?= translate('position') . " " . translate('generate') ?></span>
                                            </a>
                                        </li>
                                    <?php }
                                    if (get_permission('exam_result', 'is_view')) { ?>
                                        <li class="<?php if ($sub_page == 'onlineexam/result')
                                                        echo 'nav-active'; ?>">
                                            <a href="<?= base_url('onlineexam/result') ?>">
                                                <span><i class="fas fa-caret-right"
                                                        aria-hidden="true"></i><?= translate('exam_result') ?></span>
                                            </a>
                                        </li>
                                    <?php } ?>

                                    <?php if (get_permission('subject_wise_exam_result', 'is_view')) { ?>
                                        <li class="<?php if ($sub_page == 'report/subject_wise_report')
                                                        echo 'nav-active'; ?>">
                                            <a href="<?= base_url('report/subjectWiseResult') ?>">
                                                <span><i
                                                        class="fas fa-caret-right"></i><?= translate('subject_wise_report') ?></span>
                                            </a>
                                        </li>
                                    <?php } ?>

                                    <!-- 🔹 NEW MENU FOR LIVE EXAM -->
                                    <?php if (get_permission('live_exam', 'is_view')) { ?>
                                        <li class="<?php if ($sub_page == 'live_exam/index' || $sub_page == 'live_exam/host')
                                                        echo 'nav-active'; ?>">
                                            <a href="<?= base_url('LiveExam') ?>">
                                                <span><i class="fas fa-broadcast-tower"
                                                        aria-hidden="true"></i><?= translate('live_exam') ?></span>
                                            </a>
                                        </li>
                                    <?php } ?>

                                </ul>
                            </li>
                    <?php }
                    } ?>

                    <!-- LEADERBOARD -->
                    <?php
                    if (moduleIsEnabled('leaderboard')) {
                        if (get_permission('leaderboard', 'is_view')) {
                    ?>
                            <li class="nav-parent <?php if ($main_menu == 'leaderboard')
                                                        echo 'nav-expanded nav-active'; ?>">
                                <a>
                                    <i class="fas fa-trophy"></i> <span><?= translate('leaderboard') ?></span>
                                </a>
                                <ul class="nav nav-children">
                                    <?php if (get_permission('leaderboard', 'is_view')) { ?>
                                        <li class="<?php if ($sub_page == 'leaderboard/index')
                                                        echo 'nav-active'; ?>">
                                            <a href="<?= base_url('leaderboard') ?>">
                                                <span><i class="fas fa-caret-right"
                                                        aria-hidden="true"></i><?= translate('leaderboard') ?></span>
                                            </a>
                                        </li>
                                    <?php }

                                    ?>
                                </ul>
                            </li>
                    <?php
                        }
                    }
                    ?>
                    <?php
                    if (
                        get_permission('hostel', 'is_view') ||
                        get_permission('hostel_category', 'is_view') ||
                        get_permission('hostel_room', 'is_view') ||
                        get_permission('hostel_allocation', 'is_view') ||
                        get_permission('transport_route', 'is_view') ||
                        get_permission('transport_vehicle', 'is_view') ||
                        get_permission('transport_stoppage', 'is_view') ||
                        get_permission('transport_assign', 'is_view') ||
                        get_permission('transport_allocation', 'is_view')
                    ) {
                    ?>
                        <!-- supervision -->
                        <li class="nav-parent <?php if ($main_menu == 'hostels' || $main_menu == 'transport')
                                                    echo 'nav-expanded nav-active'; ?>">
                            <a>
                                <i class="icons icon-feed"
                                    aria-hidden="true"></i><span><?= translate('supervision') ?></span>
                            </a>
                            <ul class="nav nav-children">
                                <?php
                                if (
                                    get_permission('hostel', 'is_view') ||
                                    get_permission('hostel_category', 'is_view') ||
                                    get_permission('hostel_room', 'is_view') ||
                                    get_permission('hostel_allocation', 'is_view')
                                ) {
                                ?>
                                    <!-- hostels -->
                                    <li class="nav-parent <?php if ($main_menu == 'hostels')
                                                                echo 'nav-expanded nav-active'; ?>">
                                        <a>
                                            <i class="fas fa-store-alt"></i><span><?= translate('hostel') ?></span>
                                        </a>
                                        <ul class="nav nav-children">
                                            <?php if (get_permission('hostel', 'is_view')) { ?>
                                                <li class="<?php if ($sub_page == 'hostels/index' || $sub_page == 'hostels/edit')
                                                                echo 'nav-active'; ?>">
                                                    <a href="<?= base_url('hostels') ?>">
                                                        <span><?= translate('hostel_master') ?></span>
                                                    </a>
                                                </li>
                                            <?php }
                                            if (get_permission('hostel_room', 'is_view')) { ?>
                                                <li class="<?php if ($sub_page == 'hostels/room' || $sub_page == 'hostels/room_edit')
                                                                echo 'nav-active'; ?>">
                                                    <a href="<?= base_url('hostels/room') ?>">
                                                        <span><?= translate('hostel_room') ?></span>
                                                    </a>
                                                </li>
                                            <?php }
                                            if (get_permission('hostel_category', 'is_view')) { ?>
                                                <li class="<?php if ($sub_page == 'hostels/category')
                                                                echo 'nav-active'; ?>">
                                                    <a href="<?= base_url('hostels/category') ?>">
                                                        <span><?= translate('category') ?></span>
                                                    </a>
                                                </li>
                                            <?php }
                                            if (get_permission('hostel_allocation', 'is_view')) { ?>
                                                <li class="<?php if ($sub_page == 'hostels/allocation')
                                                                echo 'nav-active'; ?>">
                                                    <a href="<?= base_url('hostels/allocation_report') ?>">
                                                        <span><?= translate('allocation_report') ?></span>
                                                    </a>
                                                </li>
                                            <?php } ?>
                                        </ul>
                                    </li>
                                <?php } ?>
                                <?php
                                if (
                                    get_permission('transport_route', 'is_view') ||
                                    get_permission('transport_vehicle', 'is_view') ||
                                    get_permission('transport_stoppage', 'is_view') ||
                                    get_permission('transport_assign', 'is_view') ||
                                    get_permission('transport_allocation', 'is_view')
                                ) {
                                ?>
                                    <!-- transport -->
                                    <li class="nav-parent <?php if ($main_menu == 'transport')
                                                                echo 'nav-expanded nav-active'; ?>">
                                        <a>
                                            <i class="fas fa-bus"></i><span><?= translate('transport') ?></span>
                                        </a>
                                        <ul class="nav nav-children">
                                            <?php if (get_permission('transport_route', 'is_view')) { ?>
                                                <li class="<?php if ($sub_page == 'transport/route' || $sub_page == 'transport/route_edit')
                                                                echo 'nav-active'; ?>">
                                                    <a href="<?= base_url('transport/route') ?>">
                                                        <span><?= translate('route_master') ?></span>
                                                    </a>
                                                </li>
                                            <?php }
                                            if (get_permission('transport_vehicle', 'is_view')) { ?>
                                                <li class="<?php if ($sub_page == 'transport/vehicle' || $sub_page == 'transport/vehicle_edit')
                                                                echo 'nav-active'; ?>">
                                                    <a href="<?= base_url('transport/vehicle') ?>">
                                                        <span><?= translate('vehicle_master') ?></span>
                                                    </a>
                                                </li>
                                            <?php }
                                            if (get_permission('transport_stoppage', 'is_view')) { ?>
                                                <li class="<?php if ($sub_page == 'transport/stoppage' || $sub_page == 'transport/stoppage_edit')
                                                                echo 'nav-active'; ?>">
                                                    <a href="<?= base_url('transport/stoppage') ?>">
                                                        <span><?= translate('stoppage') ?></span>
                                                    </a>
                                                </li>
                                            <?php }
                                            if (get_permission('transport_assign', 'is_view')) { ?>
                                                <li class="<?php if ($sub_page == 'transport/assign' || $sub_page == 'transport/assign_edit')
                                                                echo 'nav-active'; ?>">
                                                    <a href="<?= base_url('transport/assign') ?>">
                                                        <span><?= translate('assign_vehicle') ?></span>
                                                    </a>
                                                </li>
                                            <?php }
                                            if (get_permission('transport_allocation', 'is_view')) { ?>
                                                <li class="<?php if ($sub_page == 'transport/allocation')
                                                                echo 'nav-active'; ?>">
                                                    <a href="<?= base_url('transport/report') ?>">
                                                        <span><?= translate('allocation_report') ?></span>
                                                    </a>
                                                </li>
                                            <?php } ?>
                                        </ul>
                                    </li>
                                <?php } ?>
                            </ul>
                        </li>
                    <?php } ?>
                    <?php
                    if (
                        get_permission('student_attendance', 'is_add') ||
                        get_permission('employee_attendance', 'is_add') ||
                        get_permission('exam_attendance', 'is_add')
                    ) {
                    ?>
                        <!-- attendance control -->
                        <li class="nav-parent <?php if ($main_menu == 'attendance')
                                                    echo 'nav-expanded nav-active'; ?>">
                            <a>
                                <i class="icons icon-chart"></i><span><?= translate('attendance') ?></span>
                            </a>
                            <ul class="nav nav-children">
                                <?php if (get_permission('student_attendance', 'is_add')) { ?>
                                    <li class="<?php if ($sub_page == 'attendance/student_entries')
                                                    echo 'nav-active'; ?>">
                                        <a href="<?= base_url('attendance/student_entry') ?>">
                                            <span><i class="fas fa-caret-right"></i><?= translate('student') ?></span>
                                        </a>
                                    </li>
                                <?php }
                                if (get_permission('employee_attendance', 'is_add')) { ?>
                                    <li class="<?php if ($sub_page == 'attendance/employees_entries')
                                                    echo 'nav-active'; ?>">
                                        <a href="<?= base_url('attendance/employees_entry') ?>">
                                            <span><i class="fas fa-caret-right"></i><?= translate('employee') ?></span>
                                        </a>
                                    </li>
                                <?php }
                                if (get_permission('exam_attendance', 'is_add')) { ?>
                                    <li class="<?php if ($sub_page == 'attendance/exam_entries')
                                                    echo 'nav-active'; ?>">
                                        <a href="<?= base_url('attendance/exam_entry') ?>">
                                            <span><i class="fas fa-caret-right"></i><?= translate('exam') ?></span>
                                        </a>
                                    </li>
                                <?php } ?>
                            </ul>
                        </li>
                    <?php } ?>
                    <?php
                    if (
                        get_permission('book', 'is_view') ||
                        get_permission('book_category', 'is_view') ||
                        get_permission('book_manage', 'is_view') ||
                        get_permission('book_request', 'is_view')
                    ) {
                    ?>
                        <!-- library -->
                        <li class="nav-parent <?php if ($main_menu == 'library')
                                                    echo 'nav-expanded nav-active'; ?>">
                            <a>
                                <i class="icons icon-notebook"></i><span><?= translate('library') ?></span>
                            </a>
                            <ul class="nav nav-children">
                                <?php if (get_permission('book', 'is_view')) { ?>
                                    <li class="<?php if ($sub_page == 'library/book')
                                                    echo 'nav-active'; ?>">
                                        <a href="<?= base_url('library/book') ?>">
                                            <span><i class="fas fa-caret-right"></i><?= translate('books') ?></span>
                                        </a>
                                    </li>
                                <?php }
                                if (get_permission('book_category', 'is_view')) { ?>
                                    <li class="<?php if ($sub_page == 'library/category')
                                                    echo 'nav-active'; ?>">
                                        <a href="<?= base_url('library/category') ?>">
                                            <span><i class="fas fa-caret-right"></i><?= translate('books_category') ?></span>
                                        </a>
                                    </li>
                                <?php }
                                if (get_permission('book_request', 'is_view')) { ?>
                                    <li class="<?php if ($sub_page == 'library/request')
                                                    echo 'nav-active'; ?>">
                                        <a href="<?= base_url('library/request') ?>">
                                            <span><i class="fas fa-caret-right"></i>My Issued Book</span>
                                        </a>
                                    </li>
                                <?php }
                                if (get_permission('book_manage', 'is_view')) { ?>
                                    <li class="<?php if ($sub_page == 'library/book_manage')
                                                    echo 'nav-active'; ?>">
                                        <a href="<?= base_url('library/book_manage') ?>">
                                            <span><i class="fas fa-caret-right"></i>Book Issue/Return</span>
                                        </a>
                                    </li>
                                <?php } ?>
                            </ul>
                        </li>
                    <?php } ?>

                    <!-- DIGITAL Library -->
                    <?php
                    if (is_superadmin_loggedin() || is_admin_loggedin() || is_teacher_loggedin()) {
                    ?>
                        <li class="nav-parent <?php if ($main_menu == 'Smart_library')
                                                    echo 'nav-expanded nav-active'; ?>">
                            <a>
                                <i class="icons icon-notebook"></i><span><?= translate('Smart_library') ?></span>
                            </a>
                            <ul class="nav nav-children">
                                <?php if (is_superadmin_loggedin() || is_admin_loggedin() || is_teacher_loggedin()) { ?>
                                    <li class="<?php if ($sub_page == 'digital_library/index')
                                                    echo 'nav-active'; ?>">
                                        <a href="<?= base_url('DigitalLibrary/index') ?>">
                                            <span><i class="fas fa-caret-right"></i><?= translate('digital_books') ?></span>
                                        </a>
                                    </li>


                                <?php }
                                if (is_superadmin_loggedin()) {
                                ?>
                                    <li class="<?php if ($sub_page == 'digital_library/assign_branch')
                                                    echo 'nav-active'; ?>">
                                        <a href="<?= base_url('DigitalLibrary/assignBranch') ?>">
                                            <span><i class="fas fa-caret-right"></i><?= translate('assign_branch') ?></span>
                                        </a>
                                    </li>
                                <?php
                                }

                                if (is_admin_loggedin() || is_teacher_loggedin()) { ?>
                                    <li class="<?php if ($sub_page == 'digital_library/assign_branch')
                                                    echo 'nav-active'; ?>">
                                        <a href="<?= base_url('DigitalLibrary/assignClass') ?>">
                                            <span><i class="fas fa-caret-right"></i>Assign Class</span>
                                        </a>
                                    </li>
                                <?php } ?>
                            </ul>
                        </li>
                    <?php
                    }
                    ?>
                    <?php
                    if (
                        get_permission('event', 'is_view') ||
                        get_permission('event_type', 'is_view')
                    ) {
                    ?>
                        <!-- envant -->
                        <li class="nav-parent <?php if ($main_menu == 'event')
                                                    echo 'nav-expanded nav-active'; ?>">
                            <a>
                                <i class="icons icon-speech"></i><span><?= translate('events') ?></span>
                            </a>
                            <ul class="nav nav-children">
                                <?php if (get_permission('event_type', 'is_view')) { ?>
                                    <li class="<?php if ($sub_page == 'event/types')
                                                    echo 'nav-active'; ?>">
                                        <a href="<?= base_url('event/types') ?>">
                                            <span><i class="fas fa-caret-right"></i><?= translate('event_type') ?></span>
                                        </a>
                                    </li>
                                <?php }
                                if (get_permission('event', 'is_view')) { ?>
                                    <li class="<?php if ($sub_page == 'event/index')
                                                    echo 'nav-active'; ?>">
                                        <a href="<?= base_url('event') ?>">
                                            <span><i class="fas fa-caret-right"></i><?= translate('events') ?></span>
                                        </a>
                                    </li>
                                <?php } ?>
                            </ul>
                        </li>
                    <?php } ?>

                    <?php
                    if (
                        get_permission('sendsmsmail', 'is_add') ||
                        get_permission('sendsmsmail_template', 'is_view') ||
                        get_permission('sendsmsmail_reports', 'is_view')
                    ) {
                    ?>
                        <!-- SMS -->
                        <li class="nav-parent <?php if ($main_menu == 'sendsmsmail')
                                                    echo 'nav-expanded nav-active'; ?>">
                            <a>
                                <i class="icons icon-bell"></i><span>Bulk Sms And Email</span>
                            </a>
                            <ul class="nav nav-children">
                                <?php if (get_permission('sendsmsmail', 'is_add')) { ?>
                                    <li class="<?php if ($sub_page == 'sendsmsmail/sms' || $sub_page == 'sendsmsmail/email')
                                                    echo 'nav-active'; ?>">
                                        <a href="<?= base_url('sendsmsmail/sms') ?>">
                                            <span><i class="fas fa-caret-right"></i>Send Sms / Email</span>
                                        </a>
                                    </li>
                                    <li class="<?php if ($sub_page == 'sendsmsmail/campaign_reports')
                                                    echo 'nav-active'; ?>">
                                        <a href="<?= base_url('sendsmsmail/campaign_reports') ?>">
                                            <span><i class="fas fa-caret-right"></i>Sms / Email Reports</span>
                                        </a>
                                    </li>
                                <?php }
                                if (get_permission('sendsmsmail_template', 'is_view')) { ?>
                                    <li class="<?php if ($sub_page == 'sendsmsmail/template_sms' || $sub_page == 'sendsmsmail/template_edit_sms')
                                                    echo 'nav-active'; ?>">
                                        <a href="<?= base_url('sendsmsmail/template/sms') ?>">
                                            <span><i class="fas fa-caret-right"></i>
                                                <?= translate('sms') . " " . translate('template') ?></span>
                                        </a>
                                    </li>
                                    <li class="<?php if ($sub_page == 'sendsmsmail/template_email' || $sub_page == 'sendsmsmail/template_edit_email')
                                                    echo 'nav-active'; ?>">
                                        <a href="<?= base_url('sendsmsmail/template/email') ?>">
                                            <span><i class="fas fa-caret-right"></i>
                                                <?= translate('email') . " " . translate('template') ?></span>
                                        </a>
                                    </li>
                                <?php } ?>
                            </ul>
                        </li>
                    <?php } ?>
                    <?php
                    if (
                        get_permission('fees_type', 'is_view') ||
                        get_permission('fees_group', 'is_view') ||
                        get_permission('fees_fine_setup', 'is_view') ||
                        get_permission('fees_allocation', 'is_view') ||
                        get_permission('invoice', 'is_view') ||
                        get_permission('due_invoice', 'is_view') ||
                        get_permission('fees_reminder', 'is_view')
                    ) {
                    ?>
                        <!-- student accounting -->
                        <li class="nav-parent <?php if ($main_menu == 'fees')
                                                    echo 'nav-expanded nav-active'; ?>">
                            <a>
                                <i class="icons icon-calculator"></i><span><?= translate('student_accounting') ?></span>
                            </a>
                            <ul class="nav nav-children">
                                <?php if (get_permission('fees_type', 'is_view')) { ?>
                                    <li class="<?php if ($sub_page == 'fees/type')
                                                    echo 'nav-active'; ?>">
                                        <a href="<?= base_url('fees/type') ?>"><span><i
                                                    class="fas fa-caret-right"></i><?= translate('fees_type') ?></span></a>
                                    </li>
                                <?php }
                                if (get_permission('fees_group', 'is_view')) { ?>
                                    <li class="<?php if ($sub_page == 'fees/group')
                                                    echo 'nav-active'; ?>">
                                        <a href="<?= base_url('fees/group') ?>"><span><i
                                                    class="fas fa-caret-right"></i><?= translate('fees_group') ?></span></a>
                                    </li>
                                <?php }
                                if (get_permission('fees_fine_setup', 'is_view')) { ?>
                                    <li class="<?php if ($sub_page == 'fees/fine_setup')
                                                    echo 'nav-active'; ?>">
                                        <a href="<?= base_url('fees/fine_setup') ?>"><span><i
                                                    class="fas fa-caret-right"></i><?= translate('fine_setup') ?></span></a>
                                    </li>
                                <?php }
                                if (get_permission('fees_allocation', 'is_view')) { ?>
                                    <li class="<?php if ($sub_page == 'fees/allocation')
                                                    echo 'nav-active'; ?>">
                                        <a href="<?= base_url('fees/allocation') ?>"><span><i
                                                    class="fas fa-caret-right"></i><?= translate('fees_allocation') ?></span></a>
                                    </li>
                                <?php }
                                if (get_permission('invoice', 'is_view')) { ?>
                                    <li class="<?php if ($sub_page == 'fees/invoice_list' || $sub_page == 'fees/collect')
                                                    echo 'nav-active'; ?>">
                                        <a href="<?= base_url('fees/invoice_list') ?>"><span><i
                                                    class="fas fa-caret-right"></i><?= translate('payments_history') ?></span></a>
                                    </li>
                                <?php }
                                if (get_permission('due_invoice', 'is_view')) { ?>
                                    <li class="<?php if ($sub_page == 'fees/due_invoice')
                                                    echo 'nav-active'; ?>">
                                        <a href="<?= base_url('fees/due_invoice') ?>"><span><i
                                                    class="fas fa-caret-right"></i><?= translate('due_fees_invoice') ?></span></a>
                                    </li>
                                <?php }
                                if (get_permission('fees_reminder', 'is_view')) { ?>
                                    <li class="<?php if ($sub_page == 'fees/reminder')
                                                    echo 'nav-active'; ?>">
                                        <a href="<?= base_url('fees/reminder') ?>"><span><i
                                                    class="fas fa-caret-right"></i><?= translate('fees_reminder') ?></span></a>
                                    </li>
                                <?php } ?>
                            </ul>
                        </li>
                    <?php } ?>
                    <?php
                    if (
                        get_permission('account', 'is_view') ||
                        get_permission('voucher_head', 'is_view') ||
                        get_permission('deposit', 'is_view') ||
                        get_permission('expense', 'is_view') ||
                        get_permission('all_transactions', 'is_view')
                    ) {
                    ?>
                        <!-- office accounting -->
                        <li class="nav-parent <?php if ($main_menu == 'accounting')
                                                    echo 'nav-expanded nav-active'; ?>">
                            <a>
                                <i class="icon-credit-card icons"></i><span><?= translate('office_accounting') ?></span>
                            </a>
                            <ul class="nav nav-children">
                                <?php if (get_permission('account', 'is_view')) { ?>
                                    <li class="<?php if ($sub_page == 'accounting/index' || $sub_page == 'accounting/edit')
                                                    echo 'nav-active'; ?>">
                                        <a href="<?php echo base_url('accounting'); ?>">
                                            <span><i class="fas fa-caret-right"
                                                    aria-hidden="true"></i><?php echo translate('account'); ?></span>
                                        </a>
                                    </li>
                                <?php }
                                if (get_permission('deposit', 'is_view')) { ?>
                                    <li class="<?php if ($sub_page == 'accounting/voucher_deposit' || $sub_page == 'accounting/voucher_deposit_edit')
                                                    echo 'nav-active'; ?>">
                                        <a href="<?php echo base_url('accounting/voucher_deposit'); ?>">
                                            <span><i class="fas fa-caret-right"
                                                    aria-hidden="true"></i><?php echo translate('new_deposit'); ?></span>
                                        </a>
                                    </li>
                                <?php }
                                if (get_permission('expense', 'is_view')) { ?>
                                    <li class="<?php if ($sub_page == 'accounting/voucher_expense' || $sub_page == 'accounting/voucher_expense_edit')
                                                    echo 'nav-active'; ?>">
                                        <a href="<?php echo base_url('accounting/voucher_expense'); ?>">
                                            <span><i class="fas fa-caret-right"
                                                    aria-hidden="true"></i><?php echo translate('new_expense'); ?></span>
                                        </a>
                                    </li>
                                <?php }
                                if (get_permission('all_transactions', 'is_view')) { ?>
                                    <li class="<?php if ($sub_page == 'accounting/all_transactions')
                                                    echo 'nav-active'; ?>">
                                        <a href="<?php echo base_url('accounting/all_transactions'); ?>">
                                            <span><i class="fas fa-caret-right"
                                                    aria-hidden="true"></i><?php echo translate('all_transactions'); ?></span>
                                        </a>
                                    </li>
                                <?php }
                                if (get_permission('voucher_head', 'is_view') || get_permission('voucher_head', 'is_add')) { ?>
                                    <li class="<?php if ($sub_page == 'accounting/voucher_head')
                                                    echo 'nav-active'; ?>">
                                        <a href="<?php echo base_url('accounting/voucher_head'); ?>">
                                            <span><i class="fas fa-caret-right"
                                                    aria-hidden="true"></i><?php echo translate('voucher') . " " . translate('head'); ?></span>
                                        </a>
                                    </li>
                                <?php } ?>
                            </ul>
                        </li>
                    <?php } ?>
                    <!-- message -->
                    <li class="<?php if ($main_menu == 'message')
                                    echo 'nav-active'; ?>">
                        <a href="<?= base_url('communication/mailbox/inbox') ?>">
                            <i class="icons icon-envelope-open"></i><span><?= translate('message') ?></span>
                        </a>
                    </li>

                    <?php
                    $attendance_report = false;
                    if (
                        get_permission('student_attendance_report', 'is_view') ||
                        get_permission('employee_attendance_report', 'is_view') ||
                        get_permission('exam_attendance_report', 'is_view')
                    ) {
                        $attendance_report = true;
                    }

                    if (
                        get_permission('fees_reports', 'is_view') ||
                        get_permission('accounting_reports', 'is_view') ||
                        get_permission('salary_summary_report', 'is_view') ||
                        get_permission('leave_reports', 'is_view') ||
                        ($attendance_report == true) ||
                        get_permission('report_card', 'is_view') ||
                        get_permission('tabulation_sheet', 'is_view') ||
                        get_permission('online_exam_report', 'is_view')
                    ) {
                    ?>
                        <!-- reports -->
                        <li class="nav-parent <?php if (
                                                    $main_menu == 'accounting_repots' ||
                                                    $main_menu == 'fees_repots' ||
                                                    $main_menu == 'attendance_report' ||
                                                    $main_menu == 'payroll_reports' ||
                                                    $main_menu == 'leave_reports' ||
                                                    $main_menu == 'exam_reports' ||
                                                    $main_menu == 'skill_report' ||
                                                    $main_menu == 'online_exam_progress' ||
                                                    $main_menu == 'live_exam' ||
                                                    $main_menu == 'progress_tracker'
                                                )
                                                    echo 'nav-expanded nav-active'; ?>">
                            <a>
                                <i class="icons icon-pie-chart icons"></i><span><?= translate('reports') ?></span>
                            </a>
                            <ul class="nav nav-children">
                                <?php if (get_permission('fees_reports', 'is_view')) { ?>
                                    <li class="nav-parent <?php if ($main_menu == 'fees_repots')
                                                                echo 'nav-expanded nav-active'; ?>">
                                        <a><i class="fas fa-print"></i><span><?php echo translate('fees_reports'); ?></span></a>
                                        <ul class="nav nav-children">
                                            <li class="<?php if ($sub_page == 'fees/student_fees_report')
                                                            echo 'nav-active'; ?>">
                                                <a
                                                    href="<?= base_url('fees/student_fees_report') ?>"><?= translate('fees_report') ?></a>
                                            </li>
                                            <li class="<?php if ($sub_page == 'fees/payment_history')
                                                            echo 'nav-active'; ?>">
                                                <a
                                                    href="<?= base_url('fees/payment_history') ?>"><?= translate('receipts_report') ?></a>
                                            </li>
                                            <li class="<?php if ($sub_page == 'fees/due_report')
                                                            echo 'nav-active'; ?>">
                                                <a
                                                    href="<?= base_url('fees/due_report') ?>"><?= translate('due_fees_report') ?></a>
                                            </li>
                                            <li class="<?php if ($sub_page == 'fees/fine_report')
                                                            echo 'nav-active'; ?>">
                                                <a
                                                    href="<?= base_url('fees/fine_report') ?>"><?= translate('fine_report') ?></a>
                                            </li>

                                        </ul>
                                    </li>
                                <?php } ?>
                                <?php if (get_permission('accounting_reports', 'is_view')) { ?>
                                    <li class="nav-parent <?php if ($main_menu == 'accounting_repots')
                                                                echo 'nav-expanded nav-active'; ?>">
                                        <a><i
                                                class="fas fa-print"></i><span><?php echo translate('financial_reports'); ?></span></a>
                                        <ul class="nav nav-children">
                                            <li class="<?php if ($sub_page == 'accounting/account_statement')
                                                            echo 'nav-active'; ?>">
                                                <a
                                                    href="<?php echo base_url('accounting/account_statement'); ?>"><?php echo translate('account') . " " . translate('statement'); ?></a>
                                            </li>
                                            <li class="<?php if ($sub_page == 'accounting/income_repots')
                                                            echo 'nav-active'; ?>">
                                                <a
                                                    href="<?php echo base_url('accounting/income_repots'); ?>"><?php echo translate('income') . " " . translate('repots'); ?></a>
                                            </li>
                                            <li class="<?php if ($sub_page == 'accounting/expense_repots')
                                                            echo 'nav-active'; ?>">
                                                <a href="<?php echo base_url('accounting/expense_repots'); ?>">
                                                    <?php echo translate('expense') . " " . translate('repots'); ?></a>
                                            </li>
                                            <li class="<?php if ($sub_page == 'accounting/transitions_repots')
                                                            echo 'nav-active'; ?>">
                                                <a href="<?php echo base_url('accounting/transitions_repots'); ?>">
                                                    <?php echo translate('transitions') . " " . translate('reports'); ?></a>
                                            </li>
                                            <li class="<?php if ($sub_page == 'accounting/balance_sheet')
                                                            echo 'nav-active'; ?>">
                                                <a
                                                    href="<?php echo base_url('accounting/balance_sheet'); ?>"><?php echo translate('balance') . " " . translate('sheet'); ?></a>
                                            </li>
                                            <li class="<?php if ($sub_page == 'accounting/income_vs_expense')
                                                            echo 'nav-active'; ?>">
                                                <a href="<?php echo base_url('accounting/incomevsexpense'); ?>">
                                                    <?php echo translate('income_vs_expense'); ?></a>
                                            </li>

                                        </ul>
                                    </li>
                                <?php } ?>
                                <?php if ($attendance_report == true) { ?>
                                    <li class="nav-parent <?php if ($main_menu == 'attendance_report')
                                                                echo 'nav-expanded nav-active'; ?>">
                                        <a><i
                                                class="fas fa-print"></i><span><?php echo translate('attendance_reports'); ?></span></a>
                                        <ul class="nav nav-children">
                                            <?php if (get_permission('student_attendance_report', 'is_view')) { ?>
                                                <li class="<?php if ($sub_page == 'attendance/student_report')
                                                                echo 'nav-active'; ?>">
                                                    <a href="<?= base_url('attendance/studentwise_report') ?>">
                                                        <?= translate('student') . ' ' . translate('reports') ?>
                                                    </a>
                                                </li>
                                            <?php }
                                            if (get_permission('employee_attendance_report', 'is_view')) { ?>
                                                <li class="<?php if ($sub_page == 'attendance/employees_report')
                                                                echo 'nav-active'; ?>">
                                                    <a href="<?= base_url('attendance/employeewise_report') ?>">
                                                        <?= translate('employee') . ' ' . translate('reports') ?>
                                                    </a>
                                                </li>
                                            <?php }
                                            if (get_permission('exam_attendance_report', 'is_view')) { ?>
                                                <li class="<?php if ($sub_page == 'attendance/exam_report')
                                                                echo 'nav-active'; ?>">
                                                    <a href="<?= base_url('attendance/examwise_report') ?>">
                                                        <?= translate('exam') . ' ' . translate('reports') ?>
                                                    </a>
                                                </li>
                                            <?php } ?>
                                        </ul>
                                    </li>
                                <?php } ?>

                                <?php if (get_permission('salary_summary_report', 'is_view') || get_permission('leave_reports', 'is_view')) { ?>
                                    <li class="nav-parent <?php if ($main_menu == 'payroll_reports' || $main_menu == 'leave_reports')
                                                                echo 'nav-expanded nav-active'; ?>">
                                        <a><i class="fas fa-print"></i><span><?php echo translate('hrm'); ?></span></a>
                                        <ul class="nav nav-children">
                                            <?php if (get_permission('salary_summary_report', 'is_view')) { ?>
                                                <li class="<?php if ($sub_page == 'payroll/salary_statement')
                                                                echo 'nav-active'; ?>">
                                                    <a href="<?= base_url('payroll/salary_statement') ?>">
                                                        <span><?= translate('payroll_summary') ?></span>
                                                    </a>
                                                </li>
                                            <?php }
                                            if (get_permission('leave_reports', 'is_view')) { ?>
                                                <li class="<?php if ($sub_page == 'leave/reports')
                                                                echo 'nav-active'; ?>">
                                                    <a href="<?= base_url('leave/reports') ?>">
                                                        <span><?= translate('leave') . " " . translate('reports') ?></span>
                                                    </a>
                                                </li>
                                            <?php } ?>
                                        </ul>
                                    </li>
                                <?php } ?>
                                <?php if (get_permission('report_card', 'is_view') || get_permission('tabulation_sheet', 'is_view')) { ?>
                                    <li class="nav-parent <?php if ($main_menu == 'exam_reports')
                                                                echo 'nav-expanded nav-active'; ?>">
                                        <a><i class="fas fa-print"></i><span><?php echo translate('examination'); ?></span></a>
                                        <ul class="nav nav-children">
                                            <?php if (get_permission('report_card', 'is_view')) { ?>
                                                <li class="<?php if ($sub_page == 'exam/marksheet')
                                                                echo 'nav-active'; ?>">
                                                    <a href="<?= base_url('exam/marksheet') ?>">
                                                        <span><?= translate('progress_report') ?></span>
                                                    </a>
                                                </li>
                                            <?php }
                                            if (get_permission('tabulation_sheet', 'is_view')) { ?>
                                                <li class="<?php if ($sub_page == 'exam/tabulation_sheet')
                                                                echo 'nav-active'; ?>">
                                                    <a href="<?= base_url('exam/tabulation_sheet') ?>">
                                                        <span><?= translate('tabulation_sheet') ?></span>
                                                    </a>
                                                </li>
                                            <?php } ?>
                                        </ul>
                                    </li>
                                <?php } ?>

                                <li class="nav-parent <?php if ($main_menu == 'skill_report')
                                                            echo 'nav-expanded nav-active'; ?>">
                                    <a>
                                        <i class="fas fa-print"></i><span>
                                            <?php echo translate('skill_based_report'); ?></span></a>
                                    <ul class="nav nav-children">

                                        <li class="<?php if ($sub_page == 'report/skill/category')
                                                        echo 'nav-active'; ?>">
                                            <a href="<?= base_url('SkillReport/category') ?>">
                                                <span><?= translate('skill_category') ?></span>
                                            </a>

                                        <li class="<?php if ($sub_page == 'report/skill/criteria')
                                                        echo 'nav-active'; ?>">
                                            <a href="<?= base_url('SkillReport/skillCriteria') ?>">
                                                <span><?= translate('Skill_criteria') ?></span>
                                            </a>
                                        </li>


                                        <li class="<?php if ($sub_page == 'report/skill/assessment')
                                                        echo 'nav-active'; ?>">
                                            <a href="<?= base_url('SkillReport/assessment') ?>">
                                                <span><?= translate('Performance_Review') ?></span>
                                            </a>
                                        </li>

                                        <li class="<?php if ($sub_page == 'report/type_skill_based_report/index')
                                                        echo 'nav-active'; ?>">
                                            <a href="<?= base_url('report/skillReport') ?>">
                                                <span><?= translate('generate_report') ?></span>
                                            </a>
                                        </li>

                                    </ul>
                                </li>

                                <li>
                                    <a href="<?= base_url('report/progress') ?>">
                                        <i class="fas fa-clipboard-list"></i><span>Smart Progress
                                        </span>
                                    </a>
                                </li>

                                <?php if (get_permission('online_exam_progress', 'is_view')) { ?>

                                    <li class="nav-parent <?php if ($main_menu == 'online_exam_progress')
                                                                echo 'nav-expanded nav-active'; ?>">
                                        <a>
                                            <i
                                                class="fas fa-laptop-code"></i><span><?php echo translate('online_exam'); ?></span></a>
                                        <ul class="nav nav-children">
                                            <li class="<?php if ($sub_page == 'report/online_exam_progress/index')
                                                            echo 'nav-active'; ?>">
                                                <a
                                                    href="<?= base_url('report/online_exam_progress') ?>"><?= translate('smart_progress') ?></a>
                                            </li>
                                            <li class="<?php if ($sub_page == 'report/online_exam_progress/subjectwise_filter')
                                                            echo 'nav-active'; ?>">
                                                <a
                                                    href="<?= base_url('report/online_exam_progress_subjectwise') ?>"><?= translate('progress_tracker') ?></a>
                                            </li>

                                        </ul>
                                    </li>
                                <?php } ?>
                                <!-- PROGRESS TRACKER -->
                                <li class="<?php if ($sub_page == 'report/tracker/index')
                                                echo 'nav-active'; ?>">
                                    <a href="<?= base_url('report/progressTracker') ?>">
                                        <i class="fas fa-clipboard-list"></i><span>
                                            <?= translate('progress_tracker') ?>
                                        </span>
                                    </a>
                                </li>

                                <!-- ✅ New Live Exam Menu -->
                                <?php if (get_permission('live_exam', 'is_view')) { ?>
                                    <li class="nav-parent <?php if ($main_menu == 'live_exam')
                                                                echo 'nav-expanded nav-active'; ?>">
                                        <a><i class="fas fa-bolt"></i><span><?= translate('live_exam') ?></span></a>
                                        <ul class="nav nav-children">
                                            <li class="<?php if ($sub_page == 'onlineexam/live_exam/report')
                                                            echo 'nav-active'; ?>">
                                                <a href="<?= base_url('LiveExam/getSessionReport') ?>">
                                                    <?= translate('session_report') ?>
                                                </a>
                                            </li>
                                            <li class="<?php if ($sub_page == 'onlineexam/live_exam/leaderboard_report')
                                                            echo 'nav-active'; ?>">
                                                <a href="<?= base_url('LiveExam/leaderboardReport') ?>">
                                                    <?= translate('Leaderboard_report') ?>
                                                </a>
                                            </li>
                                        </ul>
                                    </li>
                                <?php } ?>

                            </ul>
                        </li>
                    <?php } ?>
                    <?php

                    $schoolSettings = false;
                    if (
                        get_permission('school_settings', 'is_view') ||
                        get_permission('live_class_config', 'is_view') ||
                        get_permission('payment_settings', 'is_view') ||
                        get_permission('sms_settings', 'is_view') ||
                        get_permission('email_settings', 'is_view') ||
                        get_permission('accounting_links', 'is_view')
                    ) {
                        $schoolSettings = true;
                    }
                    if (
                        get_permission('global_settings', 'is_view') ||
                        ($schoolSettings == true) ||
                        get_permission('translations', 'is_view') ||
                        get_permission('cron_job', 'is_view') ||
                        get_permission('custom_field', 'is_view') ||
                        get_permission('backup', 'is_view') ||
                        get_permission('whatsapp_config', 'is_view')
                    ) {
                    ?>
                        <!-- setting -->
                        <li class="nav-parent <?php if ($main_menu == 'settings' || $main_menu == 'school_m')
                                                    echo 'nav-expanded nav-active'; ?>">
                            <a>
                                <i class="icons icon-briefcase"></i><span><?= translate('settings') ?></span>
                            </a>
                            <ul class="nav nav-children">
                                <?php if (get_permission('global_settings', 'is_view')) { ?>
                                    <li class="<?php if ($sub_page == 'settings/universal')
                                                    echo 'nav-active'; ?>">
                                        <a href="<?= base_url('settings/universal') ?>">
                                            <span><i class="fas fa-caret-right"
                                                    aria-hidden="true"></i><?= translate('global_settings') ?></span>
                                        </a>
                                    </li>
                                <?php }
                                if ($schoolSettings == true) { ?>
                                    <li class="<?php if ($main_menu == 'school_m')
                                                    echo 'nav-active'; ?>">
                                        <a href="<?= base_url('school_settings') ?>">
                                            <span><i class="fas fa-caret-right"
                                                    aria-hidden="true"></i><?= translate('school_settings') ?></span>
                                        </a>
                                    </li>
                                <?php }
                                if (is_superadmin_loggedin()) { ?>
                                    <li class="<?php if ($sub_page == 'role/index' || $sub_page == 'role/permission')
                                                    echo 'nav-active'; ?>">
                                        <a href="<?= base_url('role') ?>">
                                            <span><i class="fas fa-caret-right"
                                                    aria-hidden="true"></i><?= translate('role_permission') ?></span>
                                        </a>
                                    </li>
                                <?php }
                                if (is_superadmin_loggedin()) { ?>
                                    <li class="<?php if ($sub_page == 'sessions/index')
                                                    echo 'nav-active'; ?>">
                                        <a href="<?= base_url('sessions') ?>">
                                            <span><i class="fas fa-caret-right"
                                                    aria-hidden="true"></i><?= translate('session_settings') ?></span>
                                        </a>
                                    </li>
                                <?php }
                                if (get_permission('translations', 'is_view')) { ?>
                                    <li class="<?php if ($sub_page == 'language/index')
                                                    echo 'nav-active'; ?>">
                                        <a href="<?= base_url('translations') ?>">
                                            <span><i class="fas fa-caret-right"
                                                    aria-hidden="true"></i><?= translate('translations') ?></span>
                                        </a>
                                    </li>
                                <?php }
                                if (get_permission('cron_job', 'is_view')) { ?>
                                    <li class="<?php if ($sub_page == 'cron_api/index')
                                                    echo 'nav-active'; ?>">
                                        <a href="<?= base_url('cron_api') ?>">
                                            <span><i class="fas fa-caret-right"
                                                    aria-hidden="true"></i><?= translate('cron_job') ?></span>
                                        </a>
                                    </li>
                                <?php }
                                if (get_permission('custom_field', 'is_view')) { ?>
                                    <li class="<?php if ($sub_page == 'custom_field/index')
                                                    echo 'nav-active'; ?>">
                                        <a href="<?= base_url('custom_field') ?>">
                                            <span><i class="fas fa-caret-right"
                                                    aria-hidden="true"></i><?= translate('custom_field') ?></span>
                                        </a>
                                    </li>
                                <?php }
                                if (get_permission('backup', 'is_view')) { ?>
                                    <li class="<?php if ($sub_page == 'database_backup/index')
                                                    echo 'nav-active'; ?>">
                                        <a href="<?= base_url('backup') ?>">
                                            <span><i class="fas fa-caret-right"
                                                    aria-hidden="true"></i><?= translate('database_backup') ?></span>
                                        </a>
                                    </li>
                                <?php } ?>

                                <!--WHATSAPP SETTINGS -->
                                <?php if (get_permission('whatsapp_config', 'is_view')) { ?>
                                    <li class="<?php if ($sub_page == 'whatsapp/config')
                                                    echo 'nav-active'; ?>">
                                        <a href="<?= base_url('whatsapp/config') ?>">
                                            <span>
                                                <i class="fas fa-caret-right"></i>
                                                <?= translate('whatsapp_settings') ?>
                                            </span>
                                        </a>
                                    </li>
                                <?php } ?>
                            </ul>
                        </li>
                    <?php } ?>

                    <?php if (is_superadmin_loggedin()) {
                    ?>
                        <li class="nav-parent <?php if ($main_menu == 'Reward')
                                                    echo 'nav-expanded nav-active'; ?>">
                            <a>
                                <i class="fas fa-coins"></i><span><?= translate('rewards') ?></span>
                            </a>
                            <ul class="nav nav-children">

                                <li class="<?php if ($sub_page == 'rewards/index')
                                                echo 'nav-active'; ?>">
                                    <a href="<?php echo base_url('rewards'); ?>">
                                        <span><i class="fas fa-caret-right"
                                                aria-hidden="true"></i><?php echo translate('reward_list'); ?></span>
                                    </a>
                                </li>

                                <li class="<?= ($sub_page == 'reward/config') ? 'nav-active' : '' ?>">
                                    <a href="<?= base_url('rewards/config') ?>">
                                        <span><i class="fas fa-caret-right"></i>
                                            <?= translate('reward_configuration') ?></span>
                                    </a>
                                </li>

                            </ul>
                        </li>
                    <?php
                    }
                    ?>
                </ul>
            </nav>
        </div>
        <script>
            // maintain scroll position
            if (typeof localStorage !== 'undefined') {
                if (localStorage.getItem('sidebar-left-position') !== null) {
                    var initialPosition = localStorage.getItem('sidebar-left-position'),
                        sidebarLeft = document.querySelector('#sidebar-left .nano-content');
                    sidebarLeft.scrollTop = initialPosition;
                }
            }
        </script>
    </div>
</aside>
<!-- end sidebar -->