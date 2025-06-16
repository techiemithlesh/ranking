<!doctype html>
<?php
$disable_sidebar = isset($is_new_design) && $is_new_design;
?>
<html class="fixed sidebar-left-sm <?php echo ($theme_config['dark_skin'] == 'true' ? 'dark' : 'sidebar-light'); ?>">
<!-- html header -->
<?php $this->load->view('layout/header.php'); ?>

<body class="loading-overlay-showing" data-loading-overlay>
	<!-- page preloader -->
	<div class="loading-overlay dark">
		<div class="ring-loader">
			Loading <span></span>
		</div>
	</div>
	<section class="body">
		<!-- top navbar-->
		<?php $this->load->view('layout/topbar.php'); ?>
		<div class="inner-wrapper">
			<!-- sidebar -->
			<?php

			if (is_student_loggedin() || is_parent_loggedin()) {

				// $this->load->view('userrole/sidebar');
			} else {
				$this->load->view('layout/sidebar');
			}
			?>
			<!-- page main content -->
			<section role="main" class="content-body">
				<header class="page-header">
					<a class="page-title-icon" href="<?php echo base_url('dashboard'); ?>"><i
							class="fas fa-home"></i></a>
					<h2><?php echo $title; ?></h2>
				</header>
				<?php $this->load->view($sub_page); ?>
			</section>
		</div>
	</section>

	<!-- Floating Support Button -->
	<?php
	if (!is_superadmin_loggedin()) {
		?>
		<div id="support-button" onclick="openSupportModal()">
			<i class="fas fa-life-ring"></i>
		</div>

		<!-- Support Modal -->
		<div id="support-modal" class="support-modal">
			<div class="support-modal-content">
				<span class="close" onclick="closeSupportModal()">&times;</span>
				<h3>Need Help? Contact Support</h3>

				<!-- Support Contact Details -->
				<div class="support-info">
					<p><strong>Email:</strong> <a href="mailto:support@example.com">support@example.com</a></p>
					<p><strong>Phone:</strong> <a href="tel:+1234567890">+1 (234) 567-890</a></p>
				</div>

				<hr>

				<!-- Support Form -->
				<input type="text" id="support-name" name="name" placeholder="Your Name" required>
				<input type="email" id="support-email" name="email" placeholder="Your Email" required>
				<input type="tel" id="support-phone" name="phone" placeholder="Your Phone Number" required>
				<textarea id="support-message" name="message" placeholder="Describe your issue..." required></textarea>

				<button onclick="submitSupportRequest()">Submit</button>
			</div>
		</div>
		<?php
	}
	?>

	<!-- JS Script -->
	<?php $this->load->view('layout/script.php'); ?>

	<?php
	$alertclass = "";
	if ($this->session->flashdata('alert-message-success')) {
		$alertclass = "success";
	} else if ($this->session->flashdata('alert-message-error')) {
		$alertclass = "error";
	} else if ($this->session->flashdata('alert-message-info')) {
		$alertclass = "info";
	}
	if ($alertclass != ''):
		$alert_message = $this->session->flashdata('alert-message-' . $alertclass);
		?>
		<script type="text/javascript">
			swal({
				toast: true,
				position: 'top-end',
				type: '<?php echo $alertclass ?>',
				title: '<?php echo $alert_message ?>',
				confirmButtonClass: 'btn btn-default',
				buttonsStyling: false,
				timer: 8000
			})
		</script>
	<?php endif; ?>

	<!-- sweetalert box -->
	<script type="text/javascript">

		function openSupportModal() {
			document.getElementById("support-modal").style.display = "block";
		}

		function closeSupportModal() {
			document.getElementById("support-modal").style.display = "none";
		}

		function submitSupportRequest() {
			let name = document.getElementById("support-name").value.trim();
			let email = document.getElementById("support-email").value.trim();
			let phone = document.getElementById("support-phone").value.trim();
			let message = document.getElementById("support-message").value.trim();

			if (!name || !email || !phone || !message) {
				swal({
					title: "Error!",
					text: "Please fill out all fields before submitting.",
					icon: "error",
					button: "OK",
				});
				return;
			}

			let formData = new FormData();
			formData.append("csrf_token_name", "<?= $this->security->get_csrf_hash(); ?>");
			formData.append("name", name);
			formData.append("email", email);
			formData.append("phone", phone);
			formData.append("message", message);

			fetch("<?= base_url('support/save') ?>", {
				method: "POST",
				body: formData
			})
				.then(response => response.json())
				.then(data => {
					swal({
						title: data.status ? "Success!" : "Error!",
						text: data.message,
						icon: data.status ? "success" : "error",
						button: "OK",
					}).then(() => {
						if (data.status) {
							closeSupportModal();
							formData('');
						}
					});
				})
				.catch(error => {
					swal({
						title: "Error!",
						text: "An error occurred while submitting the request.",
						icon: "error",
						button: "OK",
					});
					console.error("Error:", error);
				});
		}


		function confirm_modal(delete_url) {
			swal({
				title: "<?php echo translate('are_you_sure') ?>",
				text: "<?php echo translate('delete_this_information') ?>",
				type: "warning",
				showCancelButton: true,
				confirmButtonClass: "btn btn-default swal2-btn-default",
				cancelButtonClass: "btn btn-default swal2-btn-default",
				confirmButtonText: "<?php echo translate('yes_continue') ?>",
				cancelButtonText: "<?php echo translate('cancel') ?>",
				buttonsStyling: false,
				footer: "<?php echo translate('deleted_note') ?>"
			}).then((result) => {
				if (result.value) {
					$.ajax({
						url: delete_url,
						type: "POST",
						success: function (data) {
							swal({
								title: "<?php echo translate('deleted') ?>",
								text: "<?php echo translate('information_deleted') ?>",
								buttonsStyling: false,
								showCloseButton: true,
								focusConfirm: false,
								confirmButtonClass: "btn btn-default swal2-btn-default",
								type: "success"
							}).then((result) => {
								if (result.value) {
									location.reload();
								}
							});
						}
					});
				}
			});
		}
	</script>
</body>

</html>