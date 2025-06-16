<!doctype html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="keywords" content="school management system, schoolxcel, pune school">
	<meta name="description" content="Top Achievements and Performance Highlights">
	<meta name="author" content="schoolexcel.tech">
	<title><?php echo translate('password_restoration'); ?></title>
	<link rel="shortcut icon" href="<?php echo base_url('../../../assets/images/favicon.png'); ?>">

	<!-- Web Fonts -->
	<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Sans+Serif:300,400,600,700">
	<link rel="stylesheet" href="<?php echo base_url('assets/vendor/bootstrap/css/bootstrap.min.css'); ?>">
	<script src="<?php echo base_url('assets/vendor/jquery/jquery.min.js'); ?>"></script>
	
	<!-- SweetAlert js/css -->
	<link rel="stylesheet" href="<?php echo base_url('assets/vendor/sweetalert/sweetalert-custom.css'); ?>">
	<script src="<?php echo base_url('assets/vendor/sweetalert/sweetalert.min.js'); ?>"></script>

	<!-- Custom CSS -->
	<style>
		body {
			background-color: #f0f4f7;
			font-family: 'Sans Serif', sans-serif; /* Adding sans-serif font */
		}

		.auth-container {
			display: flex;
			flex-direction: column;
			justify-content: center;
			align-items: center;
			min-height: 100vh;
			padding: 20px;
		}

		.auth-card {
			background-color: white;
			border-radius: 15px;
			box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
			display: flex;
			flex-direction: column;
			width: 100%;
			max-width: 850px;
			overflow: hidden;
		}

		.auth-image {
			background-image: url('<?php echo base_url('../../../assets/login_page/image/Frame 322.png'); ?>');
			background-size: cover;
			background-position: center;
			height: 200px;
			width: 100%;
		}

		.auth-form {
			padding: 40px;
			width: 100%;
			background-color: #f8fbff;
		}

		.auth-form .form-control {
			border-radius: 50px;
			height: 45px;
			width: 432px;
		}

		.btn-circular {
			border-radius: 50px;
			padding: 10px 30px;
			font-size: 16px;
			width: 100%;
			height: 45px;
		}

		.btn-primary {
			background-color: #0066cc;
			border: none;
		}

		.btn-primary:hover {
			background-color: #005bb5;
		}

		/* Responsive Styles */
		@media (min-width: 768px) {
			.auth-card {
				flex-direction: row;
				height: auto;
			}

			.auth-image {
				height: auto;
				width: 40%;
			}

			.auth-form {
				width: 60%;
			}
		}

		@media (max-width: 767px) {
			.auth-card {
				flex-direction: column;
			}

			.auth-form .form-group {
				width: 100%;
			}

			.auth-form .form-control {
				width: 285px;
				max-width: 100%;
			}
		}
	</style>

	<script type="text/javascript">
		var base_url = '<?php echo base_url(); ?>';
	</script>
</head>
<body>

	<div class="auth-container">
		<div class="auth-card">
			<!-- Side Image -->
			<div class="auth-image"></div>

			<!-- Forgot Password Form -->
			<div class="auth-form">
				<div class="text-center mb-4">
					<img src="<?php echo base_url('uploads/app_image/logo.png'); ?>" height="60" alt="RamomCoder School" class="mb-3">
					<h4 class="text-dark"><?php echo $global_config['institute_name']; ?></h4>
				</div>

				<?php 
					if($this->session->flashdata('reset_res')) {
						if($this->session->flashdata('reset_res') == TRUE) {
							echo '<div class="alert-msg">Password reset email sent successfully. Check email</div>';
						} elseif($this->session->flashdata('reset_res') == FALSE) {
							echo '<div class="alert-msg danger">You entered the wrong email address</div>';
						}
					}
				?>

				<div class="forgot-header text-center">
					<h4><?php echo translate('password_restoration');?></h4>
					<p>Enter your email and you will receive reset instructions</p>
				</div>

				<?php echo form_open($this->uri->uri_string()); ?>
				<div class="form-group <?php if (form_error('username')) echo 'has-error'; ?>">
					<div class="input-group input-group-icon">
						<div class="input-group-prepend">
							<span class="input-group-text">
								<!-- Removed icon -->
							</span>
						</div>
						<input type="text" class="form-control" name="username" value="<?=set_value('username')?>" autocomplete="off" placeholder="<?php echo translate('email');?>" />
					</div>
					<span class="text-danger"><?php echo form_error('username'); ?></span>
				</div>

				<div class="form-group">
					<button type="submit" id="btn_submit" class="btn btn-primary btn-circular">
						<?php echo translate('forgot'); ?>
					</button>
				</div>

				<div class="text-center">
					<a href="<?php echo base_url('authentication'); ?>"><?php echo translate('back_to_login'); ?></a>
				</div>

				<?php echo form_close(); ?>
			</div>
		</div>
	</div>

	<script src="<?php echo base_url('assets/vendor/bootstrap/js/bootstrap.bundle.min.js'); ?>"></script>
	<script src="<?php echo base_url('assets/login_page/js/custom.js'); ?>"></script>

	<!-- Alert Notification -->
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
				icon: '<?php echo $alertclass; ?>',
				title: '<?php echo $alert_message; ?>',
				showConfirmButton: false,
				timer: 8000
			});
		</script>
	<?php endif; ?>
</body>
</html>
