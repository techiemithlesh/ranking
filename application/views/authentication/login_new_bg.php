<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="keywords" content="school management system, schoolxcel, login">
    <meta name="description" content="Login to your account">
    <meta name="author" content="schoolexcel.tech">
    <title>Login</title>
    <link rel="shortcut icon" href="<?php echo base_url('assets/images/favicon.png'); ?>">

    <!-- Web Fonts -->
    <link href="<?php echo is_secure('fonts.googleapis.com/css?family=Sans+Serif:300,400,600,700'); ?>" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo base_url('assets/vendor/bootstrap/css/bootstrap.min.css'); ?>">
    <script src="<?php echo base_url('assets/vendor/jquery/jquery.min.js'); ?>"></script>

    <!-- SweetAlert js/css -->
    <link rel="stylesheet" href="<?php echo base_url('assets/vendor/sweetalert/sweetalert-custom.css'); ?>">
    <script src="<?php echo base_url('assets/vendor/sweetalert/sweetalert.min.js'); ?>"></script>

    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Sans Serif', Arial, sans-serif;
        }

        body, html {
            height: 100%;
            width: 100%;
            overflow: hidden;
            margin: 0;
            padding: 0;
        }

        .video-bg {
            position: fixed;
            right: 0;
            bottom: 0;
            min-width: 100%;
            min-height: 100%;
            z-index: -1;
            object-fit:cover;
            
        }

        .main-container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            position: relative;
            z-index: 2;
        }

        .header-text {
            text-align: center;
            margin-bottom: 30px;
            color: #fff;
        }

        .header-text h1 {
            font-size: clamp(2rem, 5vw, 3rem);
            font-weight: 700;
            margin-bottom: 5px;
        }

        .content-wrapper {
            display: flex;
            width: 100%;
            justify-content: space-between;
            align-items: center;
            position: relative;
        }

        .left-side {
            flex: 1;
            display: flex;
            position: relative;
            max-width: 450px;
        }

        .right-side {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
        }

        .login-box {
            width: 100%;
            margin-top: 170px;
            max-width: 450px;
            margin-left: 30px;
        }

        .login-form h2 {
            text-align: center;
            margin-bottom: 1.5rem;
            font-weight: bold;
            font-size: clamp(2.5rem, 3vw, 2.75rem);
            color: #fff;
        }

        .form-group label,
        .form-check-label {
            color: #fff;
        }

        .form-control,
        .btn-primary {
            font-size: 1.3rem;
        }

        .support-link {
            position: absolute;
            bottom: -60px;
            right: 0;
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            font-size: 14px;
        }

        .support-link:hover {
            color: yellow;
            text-decoration: none;
        }

        @media (max-width: 992px) {
            .content-wrapper {
                flex-direction: column;
            }

            .left-side {
                display: none;
            }

            .login-box {
                margin-left: 0;
            }

            .support-link {
                position: relative;
                margin-top: 30px;
                justify-content: right;
            }
        }
    </style>
</head>
<body>

    <!-- Background video -->
    <video autoplay muted loop class="video-bg">
        <source src="<?php echo base_url('assets/login_page/image/bg_new.mp4'); ?>" type="video/mp4">
        Your browser does not support the video tag.
    </video>

    <div class="main-container">
        <div class="header-text">
            <h1>Welcome Back to Your Learning Dashboard</h1>
        </div>

        <div class="content-wrapper">
            <div class="left-side"></div>
            <div class="right-side">
                <div class="login-box">
                    <div class="login-form">
                        <h2>Sign in to Your Account</h2>
                        <?php echo form_open($this->uri->uri_string()); ?>
                        <div class="form-group">
                            <label for="email"><?php echo translate('email'); ?></label>
                            <input type="text" class="form-control" name="email" id="email"
                                value="<?php echo set_value('email'); ?>"
                                placeholder="<?php echo translate('email'); ?>" required>
                            <span class="text-danger"><?php echo form_error('email'); ?></span>
                        </div>
                        <div class="form-group">
                            <label for="password"><?php echo translate('password'); ?></label>
                            <div class="password-field">
                                <input type="password" class="form-control" name="password" id="password"
                                    placeholder="<?php echo translate('password'); ?>" required>
                                <button type="button" class="password-toggle" id="passwordToggle">
                                    <i class="fa fa-eye"></i>
                                </button>
                            </div>
                            <span class="text-danger"><?php echo form_error('password'); ?></span>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="remember" name="remember">
                            <label class="form-check-label" for="remember"><?php echo translate('remember'); ?></label>
                        </div>

                        <button type="submit" class="btn btn-primary"><?php echo translate('login'); ?></button>
                        <div class="form-footer">
                            <a href="<?php echo base_url('authentication/forgot'); ?>">
                                <?php echo translate('forgot_password'); ?>
                            </a>
                        </div>
                        </form>
                    </div>
                </div>
            </div>
            <a href="#" class="support-link">
                <i class="fas fa-headset"></i> Need help? Visit our Support Center
            </a>
        </div>
    </div>

    <script src="<?php echo base_url('assets/vendor/bootstrap/js/bootstrap.bundle.min.js'); ?>"></script>
    <script>
        $(document).ready(function () {
            $('#passwordToggle').on('click', function () {
                const passwordInput = $('#password');
                const passwordIcon = $(this).find('i');
                if (passwordInput.attr('type') === 'password') {
                    passwordInput.attr('type', 'text');
                    passwordIcon.removeClass('fa-eye').addClass('fa-eye-slash');
                } else {
                    passwordInput.attr('type', 'password');
                    passwordIcon.removeClass('fa-eye-slash').addClass('fa-eye');
                }
            });
        });
    </script>

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
