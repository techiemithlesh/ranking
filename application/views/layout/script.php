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


<?php if (is_new_design()): ?>
	<script>
		const menu = <?= json_encode(get_menu_by_role()) ?>,
		baseURL = '<?= base_url() ?>';
		let currentMenu = menu;
		const breadcrumbNav = document.getElementById('breadcrumbTrail');

		//–– storage helpers now store {label,url} objects
		function getTrail() {
			return JSON.parse(sessionStorage.getItem('breadcrumbTrail') || '[]');
		}
		function setTrail(trail) {
			sessionStorage.setItem('breadcrumbTrail', JSON.stringify(trail));
		}
		function pushCrumb(item) {
			const trail = getTrail();
			const existing = trail.find(t => t.url === item.url);
			if (!existing) {
				// brand-new URL → push full object
				trail.push(item);
			}
			else if (existing.label !== item.label) {
				// same URL but label changed → update just the label
				// existing.label = item.label;
				trail.push({ label: item.label });
			}
			setTrail(trail);

		}

		//–– build the grid of cards
		function renderMenu(list) {
			const grid = document.getElementById('menuGrid');
			grid.innerHTML = '';
			list.forEach((item, idx) => {
				const hasKids = Array.isArray(item.children) && item.children.length,
					icon = item.icon || 'icon-folder',
					label = item.label.replace(/'/g, "\\'"),
					url = item.url ? baseURL + item.url : '';

				if (hasKids) {
					grid.innerHTML += `
		  <div class="dashboard-card common-card MenuDive"
			   onclick="onParentClick(${idx})">
			<div class="card-icon"><i class="icons ${icon}"></i></div>
			<h4>${item.label}</h4>
		  </div>`;
				} else if (item.url) {
					grid.innerHTML += `
		  <div class="dashboard-card common-card MenuDive">
			<div class="card-icon"><i class="icons ${icon}"></i></div>
			<h4>${item.label}</h4>
			<a class="card-link" href="${url}"
			   onclick="onLeafClick(event,'${label}','${url}')"></a>
		  </div>`;
				}
			});
		}

		//–– find the path to the current URL
		function findPath(items, target = window.location.pathname) {
			const clean = s => s.replace(/^\/|\/$/g, '');
			const current = clean(target);
			for (let itm of items) {
				const p = clean(itm.url || '');
				if (p === current) return [itm];
				if (itm.children) {
					const sub = findPath(itm.children, target);
					if (sub.length) return [itm, ...sub];
				}
			}
			return [];
		}

		//–– render the breadcrumb trail
		function renderBreadcrumb() {
			if (!breadcrumbNav) {
				return;
			}
			const trail = getTrail();
			breadcrumbNav.innerHTML = `
			<a href="${baseURL}dashboard" class="breadcrumb-home">
				<i class="fas fa-home"></i>
			</a>`;
			trail.forEach(({ label, url }) => {
				console.log("label", label);
				breadcrumbNav.insertAdjacentHTML('beforeend', `
					<span class="breadcrumb-sep">›</span>			
				`);
				if (url) {
					breadcrumbNav.insertAdjacentHTML('beforeend', `
						<a href="${url}" class="breadcrumb-item">
						${label}
						</a>
					`);
				}
				// otherwise (the “current” page) → just plain text
				else {
					breadcrumbNav.insertAdjacentHTML('beforeend', `
				<span class="breadcrumb-item">
				${label}
				</span>
			`);
				}
			});
		}

		//–– navigate into a submenu
		function onParentClick(idx) {
			const sel = currentMenu[idx];
			const url = sel.url ? baseURL + sel.url : window.location.href;
			pushCrumb({ label: sel.label, url });
			currentMenu = sel.children || [];
			renderMenu(currentMenu);
			renderBreadcrumb();

		}

		//–– handle a leaf link click
		function onLeafClick(evt, label, url) {
			evt.preventDefault();
			pushCrumb({ label, url });
			renderBreadcrumb();
			window.location.href = url;
		}

		//–– clear trail and show top‐level menu
		function showMainMenu() {
			sessionStorage.removeItem('breadcrumbTrail');
			currentMenu = menu;
			renderMenu(menu);
			renderBreadcrumb();
		}

		//–– initialize on page load
		document.addEventListener('DOMContentLoaded', () => {
			renderMenu(menu);
			renderBreadcrumb();
		});

		//–– truncate & navigate when a breadcrumb is clicked
		breadcrumbNav.addEventListener('click', function (evt) {
			if (!evt.target.classList.contains('breadcrumb-item')) return;
			if(evt.target.getAttribute('href')){
				evt.preventDefault();
				const allCrumbs = Array.from(breadcrumbNav.querySelectorAll('.breadcrumb-item'));
				const clickedIndex = allCrumbs.indexOf(evt.target);
				const oldTrail = getTrail();
				const newTrail = oldTrail.slice(0, clickedIndex + 1);
				setTrail(newTrail);
				renderBreadcrumb();
				window.location.href = evt.target.getAttribute('href');
			}
		});

		//–– keep trail in sync on back/forward navigation
		document.addEventListener('DOMContentLoaded', () => {

			const nodes = findPath(menu, window.location.pathname);
			const newTrail = nodes.map(n => ({
				label: n.label,
				url: n.url ? baseURL + n.url : window.location.href
			}));
			if (window.location.href == baseURL || window.location.href==baseURL+"dashboard") {
				setTrail(newTrail);
			}
			renderBreadcrumb();
			// setTrail(newTrail);
		});
		
	</script>
<?php endif; ?>