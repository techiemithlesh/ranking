<?php if (empty($student_id)): ?>
    <div class="smart-education-hub">
        <div class="hub-header">
            <div class="student-profile-section">
                <div class="student-info">
                    <p class="class-badge">Welcome <span><?= $this->session->userdata('name') ?></span></p>
                </div>
            </div>
        </div>

        <div class="dashboard-grid">
            <div class="dashboard-card common-card">
                <div class="card-icon">
                    <i class="fas fa-user-friends"></i>
                </div>
                <h4><?= translate('my_children') ?></h4>
                <a class="card-link" href="<?= base_url('parents/my_children') ?>"></a>
            </div>
        </div>
    </div>
<?php else: ?>
    <?php
    $this->db->select('s.id,s.first_name,s.last_name,s.photo,e.class_id,c.name as class_name');
    $this->db->from('student as s');
    $this->db->join('enroll as e', 'e.student_id = s.id', 'left');
    $this->db->join('class as c', 'e.class_id = c.id', 'left');
    $this->db->where('s.id', $student_id);
    $student_query = $this->db->get();
    $student_data = $student_query->row();

    $menu = [
        [
            'label' => 'My Learning Kit',
            'icon' => 'icon-book-open',
            'children' => [
                ['label' => 'My Interactive Book', 'icon' => 'icons icon-cloud-upload', 'url' => 'userrole/books_upload_list'],
                ['label' => 'Attachment Book', 'icon' => 'icons icon-cloud-upload', 'url' => 'userrole/attachments'],
                ['label' => 'My Homework', 'icon' => 'icon-note', 'url' => 'userrole/homework'],
                ['label' => 'Smart Library', 'icon' => 'icon-book-open', 'url' => 'userrole/digitalBook'],
                [
                    'label' => 'Library',
                    'icon' => 'icon-notebook',
                    'children' => [
                        ['label' => 'Book List', 'icon' => 'fas fa-book-open', 'url' => 'userrole/book'],
                        ['label' => 'Issued Book', 'icon' => 'fas fa-book-reade', 'url' => 'userrole/book_request'],
                    ]
                ],
            ]
        ],
        [
            'label' => 'Academic Master',
            'icon' => 'fas fa-user-graduate',
            'children' => [
                ['label' => 'Exam Schedule', 'icon' => 'icon-trophy', 'url' => 'userrole/exam_schedule'],
                ['label' => 'Class Schedule', 'icon' => 'fas fa-dna', 'url' => 'userrole/class_schedule'],
                ['label' => 'Subject', 'icon' => 'fas fa-book-reader', 'url' => 'userrole/subject'],
                ['label' => 'Attendance', 'icon' => 'icons icon-chart', 'url' => 'userrole/attendance'],
                ['label' => 'Events', 'icon' => 'icons icon-speech', 'url' => 'userrole/event'],
                ['label' => 'Online Exam', 'icon' => 'icon-screen-desktop', 'url' => 'userrole/online_exam'],
            ]
        ],
        ['label' => 'Live Classroom', 'icon' => 'fas fa-chalkboard-teacher', 'url' => 'userrole/live_class'],
        [
            'label' => 'My Progress',
            'icon' => 'icon-graph',
            'children' => [
                ['label' => 'Progress Report', 'icon' => 'fas fa-marker', 'url' => 'userrole/report_card'],
                ['label' => 'Smart Progress', 'icon' => 'fas fa-tasks', 'url' => 'userrole/progress'],
                ['label' => 'Progress Tracker', 'icon' => 'fas fa-chart-line', 'url' => 'userrole/my_progress'],
                ['label' => 'Skill Report', 'icon' => 'fas fa-clipboard-list', 'url' => 'userrole/skillBasedReport'],

                [
                    'label' => 'Online Exam',
                    'icon' => 'fas fa-laptop-code',
                    'children' => [
                        ['label' => 'Smart Progress', 'icon' => 'fas fa-globe', 'url' => 'userrole/online_exam_progress'],
                        ['label' => 'Progress Tracker', 'icon' => 'fas fa-file-alt', 'url' => 'userrole/exam_progress_subjectwise'],
                    ]
                ]
            ]
        ],
        ['label' => 'My Gallery', 'icon' => 'fas fa-images', 'url' => 'userrole/my_gallery']
    ];
    ?>

    <div class="smart-education-hub">
        <div class="hub-header">
            <div class="student-profile-section">
                <div class="student-avatar">
                    <img src="<?php echo get_image_url('student', $student_data->photo); ?>" alt="Student Photo">
                </div>
                <div class="student-info">
                    <h3><?= html_escape($student_data->first_name . " " . $student_data->last_name) ?></h3>
                    <span class="class-badge"><?= html_escape($student_data->class_name) ?></span>
                </div>
            </div>
            <!-- <div class="main_menu_container">
                <a href="#" onclick="showMainMenu()" class="menu-back-btn" id="backButton" style="display:none">&larr;
                    <?= translate('back') ?></a>
            </div> -->
        </div>

        <div class="breadcrumb-trail" id="breadcrumbTrail"></div>
        <div class="dashboard-grid" id="menuGrid"></div>
    </div>

    <script>
        const menu = <?= json_encode($menu) ?>;
        let currentMenu = menu;
        let breadcrumb = [];

        function renderMenu(list) {
            const grid = document.getElementById('menuGrid');
            const backBtn = document.getElementById('backButton');
            grid.innerHTML = '';

            list.forEach((item, idx) => {
                const icon = item.icon || 'icon-folder';
                const hasChildren = item.children && item.children.length;
                const onclick = hasChildren ? `onclick=\"showSubMenu(${idx})\"` : '';
                const href = !hasChildren && item.url ? item.url : '';

                grid.innerHTML += `
                <div class="dashboard-card common-card" ${onclick}>
                    <div class="card-icon"><i class="icons ${icon}"></i></div>
                    <h4>${item.label}</h4>
                    ${href ? `<a class='card-link' href='${href}'></a>` : ''}
                </div>`;
            });

            updateBreadcrumb();
            backBtn.style.display = breadcrumb.length > 0 ? 'inline-block' : 'none';
        }

        function updateBreadcrumb() {
            const trail = document.getElementById("breadcrumbTrail");
            trail.innerHTML = '';
            breadcrumb.forEach((item, idx) => {
                trail.innerHTML += `
                <span class="breadcrumb-item" onclick="goToBreadcrumb(${idx})">
                    <i class='icons ${item.icon || 'icon-folder'}'></i> ${item.label}
                </span>
                ${idx < breadcrumb.length - 1 ? ' <span class="breadcrumb-separator">></span> ' : ''}
            `;
            });
        }

        function goToBreadcrumb(index) {
            breadcrumb = breadcrumb.slice(0, index + 1);
            currentMenu = breadcrumb.length === 0 ? menu : breadcrumb[breadcrumb.length - 1].children;
            renderMenu(currentMenu);
            history.pushState({ breadcrumb: [...breadcrumb] }, '', '');
        }

        function showSubMenu(index) {
            const current = currentMenu[index];
            if (current.children && current.children.length) {
                breadcrumb.push(current);
                currentMenu = current.children;
                renderMenu(current.children);
                history.pushState({ breadcrumb: [...breadcrumb] }, '', '');
            } else if (current.url) {
                window.location.href = current.url;
            }
        }

        function showMainMenu() {
            breadcrumb.pop();
            currentMenu = breadcrumb.length === 0 ? menu : breadcrumb[breadcrumb.length - 1].children;
            renderMenu(currentMenu);
            history.pushState({ breadcrumb: [...breadcrumb] }, '', '');
        }

        window.addEventListener('popstate', function (event) {
            if (event.state && event.state.breadcrumb) {
                breadcrumb = event.state.breadcrumb;
                currentMenu = breadcrumb.length === 0 ? menu : breadcrumb[breadcrumb.length - 1].children;
                renderMenu(currentMenu);
            } else {
                breadcrumb = [];
                currentMenu = menu;
                renderMenu(menu);
            }
        });

        // Initial render
        renderMenu(menu);
        history.replaceState({ breadcrumb: [] }, '', '');
    </script>

<?php endif; ?>