

<div class="topbar-student">
    <div class="left-group">
        <div class="logo">
            <a href="<?= base_url('dashboard'); ?>">
                <img src="<?= $this->session->has_userdata('branch_logo') && !empty($this->session->userdata('branch_logo')) ? base_url($this->session->userdata('branch_logo')) : base_url('uploads/app_image/logo-small.png'); ?>"
                    alt="Logo">
            </a>
        </div>
        <div class="branch-name">
            <?= get_type_name_by_id('branch', $this->session->userdata('loggedin_branch')) ?>
        </div>
    </div>

    <div class="right-icons">
        <a href="<?= base_url('authentication/logout'); ?>">
            <img src="<?= base_url('assets/icons/topbar/power.png'); ?>" title="Logout">
        </a>

        <img src="<?= base_url('assets/icons/topbar/settings.png'); ?>" title="Settings"
            onclick="toggleDropdown('profileDropdown')">

        <img src="<?= base_url('assets/icons/topbar/calendar.png'); ?>" title="Calendar"
            onclick="toggleDropdown('sessionDropdown')">

        <img src="<?= base_url('assets/icons/topbar/language.png'); ?>" title="Language"
            onclick="toggleDropdown('languageDropdown')">

        <img src="<?= base_url('assets/icons/topbar/bell.png'); ?>" title="Notifications"
            onclick="toggleDropdown('notificationDropdown')">

        <div id="notificationDropdown" class="header-menubox">
            <div class="notification-title">Notifications</div>
            <ul>
                <?php
                $unreadMessage = $this->application_model->unread_message_alert();
                $unreadNotifications = $this->application_model->unread_notifications_alert(10);
                $totalAlerts = count($unreadMessage) + count($unreadNotifications);
                if ($totalAlerts == 0) {
                    echo '<li class="text-center">No new notifications</li>';
                } else {
                    foreach ($unreadNotifications as $note) {
                        echo '<li><a href="' . $note['link'] . '">' . $note['title'] . '</a></li>';
                    }
                    foreach ($unreadMessage as $message) {
                        echo '<li><a href="' . base_url('communication/mailbox/read?type=' . $message['msg_type'] . '&id=' . $message['id']) . '">' . $message['message_details']['userName'] . '</a></li>';
                    }
                }
                ?>
            </ul>
        </div>

        <div id="languageDropdown" class="header-menubox">
            <div class="notification-title">Select Language</div>
            <ul>
                <?php
                $languages = $this->db->select('id,lang_field,name')->where('status', 1)->get('language_list')->result();
                foreach ($languages as $lang) {
                    echo '<li><a href="' . base_url('translations/set_language/' . $lang->lang_field) . '">' . ucfirst($lang->name) . '</a></li>';
                }
                ?>
            </ul>
        </div>
        <div id="sessionDropdown" class="header-menubox">
            <div class="notification-title">Select session</div>
            <ul>
                <?php
                $get_session = $this->db->get('schoolyear')->result();
                foreach ($get_session as $session):
                    ?>
                    <li>
                        <a href="<?php echo base_url('sessions/set_academic/' . $session->id); ?>">
                            <?php echo $session->school_year; ?>
                            <?php echo get_session_id() == $session->id ? '<i class="fas fa-check"></i>' : ''; ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div id="profileDropdown" class="header-menubox">
            <ul>
                <li><a href="<?php echo base_url('profile'); ?>"><i class="fas fa-user-shield"></i>
                        <?php echo translate('profile'); ?></a></li>
                <li><a href="<?php echo base_url('profile/password'); ?>"><i class="fas fa-mars-stroke-h"></i>
                        <?php echo translate('reset_password'); ?></a></li>
            </ul>
        </div>
    </div>
</div>

<script>
    function toggleDropdown(id) {
        const el = document.getElementById(id);
        document.querySelectorAll('.header-menubox').forEach(d => {
            if (d !== el) d.classList.remove('active');
        });
        el.classList.toggle('active');
    }

    // Mark notification read
    $(document).on('click', '.notification-item', function () {
        var notification_id = $(this).data('id');
        $.post('<?= base_url("notification/mark_as_read"); ?>', { notification_id });
    });
</script>