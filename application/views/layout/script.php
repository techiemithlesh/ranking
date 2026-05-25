<script src="<?php echo base_url('assets/vendor/jquery-browser-mobile/jquery.browser.mobile.js'); ?>"></script>
<script src="<?php echo base_url('assets/vendor/bootstrap/js/bootstrap.js'); ?>"></script>
<?php if (is_student_loggedin()) { ?>
	<script src="<?php echo base_url('assets/vendor/fuelux/js/fuelux.min.js') ?>"></script>
<?php } ?>
<script src="<?php echo base_url('assets/vendor/nanoscroller/nanoscroller.js'); ?>"></script>
<script src="<?php echo base_url('assets/vendor/bootstrap-datepicker/js/bootstrap-datepicker.js'); ?>"></script>
<script src="<?php echo base_url('assets/vendor/jquery-placeholder/jquery-placeholder.js'); ?>"></script>
<script src="<?php echo base_url('assets/vendor/select2/js/select2.js'); ?>"></script>
<script src="<?php echo base_url('assets/vendor/fuelux/js/spinner.js'); ?>"></script>

<!-- Jquery Datatables JS -->
<script src="<?php echo base_url('assets/vendor/datatables/media/js/jquery.dataTables.min.js'); ?>"></script>
<script src="<?php echo base_url('assets/vendor/datatables/media/js/dataTables.bootstrap.min.js'); ?>"></script>
<script
	src="<?php echo base_url('assets/vendor/datatables/extras/TableTools/Buttons-1.4.2/js/dataTables.buttons.min.js'); ?>"></script>
<script
	src="<?php echo base_url('assets/vendor/datatables/extras/TableTools/Buttons-1.4.2/js/buttons.bootstrap.min.js'); ?>"></script>
<script
	src="<?php echo base_url('assets/vendor/datatables/extras/TableTools/Buttons-1.4.2/js/buttons.html5.min.js'); ?>"></script>
<script
	src="<?php echo base_url('assets/vendor/datatables/extras/TableTools/Buttons-1.4.2/js/buttons.print.min.js'); ?>"></script>
<script
	src="<?php echo base_url('assets/vendor/datatables/extras/TableTools/Buttons-1.4.2/js/buttons.colVis.min.js'); ?>"></script>
<script src="<?php echo base_url('assets/vendor/datatables/extras/TableTools/JSZip-2.5.0/jszip.min.js'); ?>"></script>
<script
	src="<?php echo base_url('assets/vendor/datatables/extras/TableTools/pdfmake-0.1.32/pdfmake.min.js'); ?>"></script>
<script
	src="<?php echo base_url('assets/vendor/datatables/extras/TableTools/pdfmake-0.1.32/vfs_fonts.js'); ?>"></script>
<script
	src="<?php echo base_url('assets/vendor/datatables/extras/TableTools/RowGroup-1.0.2/js/dataTables.rowGroup.min.js'); ?>"></script>

<script src="<?php echo base_url('assets/vendor/jquery-appear/jquery-appear.js'); ?>"></script>
<script src="<?php echo base_url('assets/vendor/jquery-validation/jquery.validate.js'); ?>"></script>
<script src="<?php echo base_url('assets/vendor/magnific-popup/jquery.magnific-popup.js'); ?>"></script>
<script src="<?php echo base_url('assets/vendor/screenfull/screenfull.min.js'); ?>"></script>
<script src="<?php echo base_url('assets/vendor/sweetalert/sweetalert.min.js'); ?>"></script>
<script src="<?php echo base_url('assets/js/custom.js'); ?>"></script>
<script src="<?php echo base_url('assets/js/plug.init.js'); ?>"></script>
<script src="<?php echo base_url('assets/js/app.js') ?>"></script>
<script src="<?php echo base_url('assets/js/app.fn.js') ?>"></script>

<!-- BOOTBOX CDN FOR PRETIER URL COPY -->
<script src="https://cdn.jsdelivr.net/npm/bootbox@5/bootbox.min.js"></script>

<!-- FOR DRAG AND DROP (MINI EDITOR) -->
<!-- <script src="<?= base_url('assets/vendor/interactjs/interact.min.js') ?>"></script> -->

<!-- FOR DROPIFY -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/Dropify/0.2.2/js/dropify.min.js"></script>

<!-- FOR NEW DESIGN -->
<script type="text/javascript">
	jQuery.extend(jQuery.validator.messages, {
		required: "<?= translate('this_value_is_required') ?>",
		email: "<?= translate('enter_valid_email') ?>",
		url: "Please enter a valid URL.",
		date: "Please enter a valid date.",
		dateISO: "Please enter a valid date (ISO).",
		number: "Please enter a valid number.",
		digits: "Please enter only digits.",
		remote: "Please fix this field.",
		creditcard: "Please enter a valid credit card number.",
		equalTo: "Please enter the same value again.",
		accept: "Please enter a value with a valid extension.",
		maxlength: jQuery.validator.format("Please enter no more than {0} characters."),
		minlength: jQuery.validator.format("Please enter at least {0} characters."),
		rangelength: jQuery.validator.format("Please enter a value between {0} and {1} characters long."),
		range: jQuery.validator.format("Please enter a value between {0} and {1}."),
		max: jQuery.validator.format("Please enter a value less than or equal to {0}."),
		min: jQuery.validator.format("Please enter a value greater than or equal to {0}.")
	});
</script>


<?php if (is_new_design() && !is_superadmin_loggedin()): ?>

	<script>
    const menu = <?= json_encode(get_menu_by_role()) ?>;
    const baseURL = '<?= base_url() ?>';
    let currentMenu = menu;

    const breadcrumbNav = document.getElementById('breadcrumbTrail');
    const menuGrid = document.getElementById('menuGrid');

    // Storage helpers
    function getTrail() {
        return JSON.parse(sessionStorage.getItem('breadcrumbTrail') || '[]');
    }

    function setTrail(trail) {
        sessionStorage.setItem('breadcrumbTrail', JSON.stringify(trail));
    }

    function pushCrumb(item) {
        const trail = getTrail();
        // Match by label (more reliable for parents that share urls with children)
        const existing = trail.find(t => t.label === item.label && t.isParent === item.isParent);

        if (!existing) {
            trail.push(item);
        }
        else {
            // Update url/menuPath if changed
            existing.url = item.url;
            if (item.menuPath) existing.menuPath = item.menuPath;
        }

        setTrail(trail);
    }

    /**
     * Walk the menu tree using a path of indices and return the children at that level.
     * e.g. menuPath [0, 1] means menu[0].children[1].children
     */
    function getMenuAtPath(path) {
        let node = { children: menu };
        for (let i = 0; i < path.length; i++) {
            if (!node.children || !node.children[path[i]]) return menu;
            node = node.children[path[i]];
        }
        return node.children || [];
    }

    /**
     * Build the menuPath for the current crumb based on the trail so far.
     * Each parent crumb stores its index relative to its parent's children.
     */
    function buildMenuPath(trail) {
        return trail.filter(t => t.isParent).map(t => t.parentIndex);
    }

    // Build menu grid
    function renderMenu(list) {
        if (!menuGrid) return;

        menuGrid.innerHTML = '';

        list.forEach((item, idx) => {
            const hasKids = Array.isArray(item.children) && item.children.length;
            const icon = item.icon || 'icon-folder';
            const label = item.label.replace(/'/g, "\\'");
            const url = item.url ? baseURL + item.url : '';

            if (hasKids) {
                menuGrid.innerHTML += `
                    <div class="dashboard-card common-card MenuDive"
                         onclick="onParentClick(${idx})">
                        <div class="card-icon"><i class="icons ${icon}"></i></div>
                        <h4>${item.label}</h4>
                    </div>`;
            }
            else if (item.url) {
                menuGrid.innerHTML += `
                    <div class="dashboard-card common-card MenuDive">
                        <div class="card-icon"><i class="icons ${icon}"></i></div>
                        <h4>${item.label}</h4>
                        <a class="card-link" href="${url}"
                           onclick="onLeafClick(event,'${label}','${url}')"></a>
                    </div>`;
            }
        });
    }

    // Breadcrumb renderer
    function renderBreadcrumb() {
        if (!breadcrumbNav) return;

        const trail = getTrail();

        breadcrumbNav.innerHTML = `
            <a href="${baseURL}dashboard" class="breadcrumb-home"
               onclick="onHomeClick(event)">
                <i class="fas fa-home"></i>
            </a>`;

        trail.forEach(({ label, url, isParent }, idx) => {
            breadcrumbNav.insertAdjacentHTML('beforeend', `
                <span class="breadcrumb-sep">›</span>
            `);

            // For parent crumbs, the href goes to dashboard (we handle drill-down via JS)
            const href = isParent ? (baseURL + 'dashboard') : url;

            if (href) {
                breadcrumbNav.insertAdjacentHTML('beforeend', `
                    <a href="${href}" class="breadcrumb-item"
                       data-crumb-index="${idx}"
                       data-is-parent="${isParent ? '1' : '0'}">${label}</a>
                `);
            } else {
                breadcrumbNav.insertAdjacentHTML('beforeend', `
                    <span class="breadcrumb-item" data-crumb-index="${idx}">${label}</span>
                `);
            }
        });
    }

    // Home click — clear trail and go to dashboard
    function onHomeClick(evt) {
        evt.preventDefault();
        setTrail([]);
        window.location.href = baseURL + 'dashboard';
    }

    // Parent click (from menu grid card)
    function onParentClick(idx) {
        const sel = currentMenu[idx];

        pushCrumb({
            label: sel.label,
            url: baseURL + 'dashboard',
            isParent: true,
            parentIndex: idx
        });

        currentMenu = sel.children || [];
        renderMenu(currentMenu);
        renderBreadcrumb();
    }

    // Leaf click (from menu grid card)
    function onLeafClick(evt, label, url) {
        evt.preventDefault();
        pushCrumb({ label, url, isParent: false });
        renderBreadcrumb();
        window.location.href = url;
    }

    // Initialize ON PAGES WHERE menuGrid exists
    document.addEventListener('DOMContentLoaded', () => {

        if (menuGrid) {
            // If we have a trail with parent crumbs, restore the drill-down
            const trail = getTrail();
            const parentCrumbs = trail.filter(t => t.isParent);

            if (parentCrumbs.length > 0) {
                // Walk the menu tree following the parent indices
                let node = { children: menu };
                for (let i = 0; i < parentCrumbs.length; i++) {
                    const pIdx = parentCrumbs[i].parentIndex;
                    if (node.children && node.children[pIdx]) {
                        node = node.children[pIdx];
                    } else {
                        // Path is invalid, reset
                        node = { children: menu };
                        setTrail([]);
                        break;
                    }
                }
                currentMenu = node.children || [];
            }

            renderMenu(currentMenu);
        }

        if (breadcrumbNav) {
            renderBreadcrumb();
        }
    });

    // Breadcrumb click
    if (breadcrumbNav) {
        breadcrumbNav.addEventListener('click', function(evt) {
            const target = evt.target.closest('.breadcrumb-item');
            if (!target) return;

            evt.preventDefault();

            const crumbIndex = parseInt(target.getAttribute('data-crumb-index'));
            const isParent = target.getAttribute('data-is-parent') === '1';

            const oldTrail = getTrail();
            const newTrail = oldTrail.slice(0, crumbIndex + 1);
            setTrail(newTrail);

            if (isParent) {
                // Navigate to dashboard — the init code will restore the menu drill-down
                window.location.href = baseURL + 'dashboard';
            } else {
                // Leaf crumb — navigate to its actual URL
                const href = target.getAttribute('href');
                if (href) {
                    window.location.href = href;
                }
            }
        });
    }
</script>

<?php endif; ?>