<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="keywords" content="school management system, schoolxcel, pune school">
	<meta name="description" content="Top Achievements and Performance Highlights">
	<meta name="author" content="schoolexcel.tech">
    <title><?php echo translate('reset_password');?></title>
    <link rel="shortcut icon" href="<?php echo base_url('assets/images/favicon.png');?>">

    <!-- Web Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Sans+Serif:300,400,600,700" rel="stylesheet"> 
    <link rel="stylesheet" href="<?php echo base_url('assets/vendor/bootstrap/css/bootstrap.css');?>">
    <script src="<?php echo base_url('assets/vendor/jquery/jquery.js');?>"></script>

    <!-- SweetAlert js/css -->
    <link rel="stylesheet" href="<?php echo base_url('assets/vendor/sweetalert/sweetalert-custom.css');?>">
    <script src="<?php echo base_url('assets/vendor/sweetalert/sweetalert.min.js');?>"></script>

    <!-- Custom CSS -->
    <style>
        body {
            background-color: #f0f4f7;
            font-family: 'Sans Serif', sans-serif; /* Apply Sans-serif font */
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

        @media (max-width: 767px) {
            .auth-form .form-control {
                width: 100%; /* Full width on smaller screens */
                max-width: 100%; /* Remove any width restriction */
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

            <!-- Reset Password Form -->
            <div class="auth-form">
                <div class="text-center mb-4">
                    <img src="<?php echo base_url('uploads/app_image/logo.png');?>" height="60" alt="RamomCoder School" class="mb-3">
                    <h4 class="text-dark"><?php echo $global_config['institute_name'];?></h4>
                </div>

                <?php echo form_open($this->uri->uri_string()); ?>
                    <div class="form-group <?php if (form_error('password')) echo 'has-error'; ?>">
                        <label for="password"><?php echo translate('new_password');?></label>
                        <input type="password" class="form-control" name="password" placeholder="New Password" required />
                        <span class="text-danger"><?php echo form_error('password'); ?></span>
                    </div>
                    <div class="form-group <?php if (form_error('c_password')) echo 'has-error'; ?>">
                        <label for="c_password"><?php echo translate('confirm_new_password');?></label>
                        <input type="password" class="form-control" name="c_password" placeholder="Confirm New Password" required />
                        <span class="text-danger"><?php echo form_error('c_password'); ?></span>
                    </div>

                    <div class="form-group">
                        <button type="submit" id="btn_submit" class="btn btn-primary btn-circular">
                            Confirm
                        </button>
                    </div>
                    <div class="sign-footer">
                        <p><?php echo $global_config['footer_text'];?></p>
                    </div>
                <?php echo form_close(); ?>
            </div>
        </div>
    </div>

    <script src="<?php echo base_url('assets/vendor/bootstrap/js/bootstrap.js');?>"></script>
    <script src="<?php echo base_url('assets/vendor/jquery-placeholder/jquery-placeholder.js');?>"></script>
    <script src="<?php echo base_url('assets/login_page/js/jquery.backstretch.min.js');?>"></script>
    <script src="<?php echo base_url('assets/login_page/js/custom.js');?>"></script>

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
