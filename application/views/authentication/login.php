<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="keywords" content="school management system, schoolxcel, login">
    <meta name="description" content="Login to your account">
    <meta name="author" content="schoolexcel.tech">
    <title><?php echo translate('login'); ?></title>
    <link rel="shortcut icon" href="<?php echo base_url('assets/images/favicon.png'); ?>">

    <!-- Web Fonts -->
    <link href="<?php echo is_secure('fonts.googleapis.com/css?family=Sans+Serif:300,400,600,700'); ?>" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo base_url('assets/vendor/bootstrap/css/bootstrap.min.css'); ?>">
    <script src="<?php echo base_url('assets/vendor/jquery/jquery.min.js'); ?>"></script>

    <!-- SweetAlert js/css -->
    <link rel="stylesheet" href="<?php echo base_url('assets/vendor/sweetalert/sweetalert-custom.css'); ?>">
    <script src="<?php echo base_url('assets/vendor/sweetalert/sweetalert.min.js'); ?>"></script>

    <!-- Custom CSS -->
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background: url('<?php echo base_url('assets/login_page/image/bg.svg'); ?>');
            background-size: cover;
            background-position: center;
            min-height: 100vh;
            width: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .heading_container {
            text-align: center;
            margin-bottom: 2rem;
            color: #fff;
            padding: 0 1rem;
        }

        .heading_container h1 {
            font-size: clamp(2rem, 5vw, 4rem);
            font-weight: 600;
            margin-bottom: 0.5rem;
            line-height: 1.2;
        }

        .heading_container p {
            font-size: clamp(1.25rem, 3vw, 2rem);
            font-weight: 400;
            margin: 0;
        }

        .auth_container {
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            padding: 1rem;
            width: 100%;
        }

        .login_card {
            background: rgba(255, 255, 255, 0.95);
            padding: clamp(1.5rem, 4vw, 2.5rem);
            border-radius: 8px;
            box-shadow: 0px 4px 20px rgba(0, 0, 0, 0.15);
            width: 100%;
            max-width: 550px;
            margin: 0 auto;
        }

        .login_card h3 {
            text-align: center;
            margin-bottom: 1.5rem;
            font-weight: bold;
            font-size: clamp(1.5rem, 3vw, 1.75rem);
        }

        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            font-size: 1.3rem;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1.3rem;
            transition: border-color 0.2s ease;
        }

        /* Password Field Container */
        .password-field-container {
            position: relative;
        }

        /* Password Toggle Button */
        .password-toggle {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            font-size: 1.2rem;
            color: #6c757d;
        }

        .password-toggle:focus {
            outline: none;
        }

        /* Placeholder Styles */
        .form-control::placeholder {
            color: #6c757d;
            font-size: 1.3rem;
            opacity: 0.8;
        }

        /* For Firefox */
        .form-control::-moz-placeholder {
            color: #6c757d;
            font-size: 1.3rem;
            opacity: 0.8;
        }

        /* For Internet Explorer */
        .form-control:-ms-input-placeholder {
            color: #6c757d;
            font-size: 1.3rem;
            opacity: 0.8;
        }

        /* For Edge */
        .form-control::-ms-input-placeholder {
            color: #6c757d;
            font-size: 1.3rem;
            opacity: 0.8;
        }

        .form-control:focus {
            border-color: #007bff;
            outline: none;
            box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.25);
        }

        .form-control:focus::placeholder {
            opacity: 0.6;
        }

        .btn-primary {
            width: 100%;
            padding: 0.75rem;
            font-size: 1.1rem;
            font-weight: 500;
            border-radius: 5px;
            margin-top: 1rem;
        }

        .form-check {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-check-label {
            font-size: 1.2rem;
        }

        .form-footer {
            text-align: center;
            margin-top: 1.5rem;
        }

        .form-footer a {
            color: #007bff;
            text-decoration: none;
            font-weight: 500;
            font-size: 1.1rem;
            transition: color 0.2s ease;
        }

        .form-footer a:hover {
            color: #0056b3;
            text-decoration: underline;
        }

        .text-danger {
            color: #dc3545;
            font-size: 0.875rem;
            margin-top: 0.25rem;
            display: block;
        }

        /* Responsive adjustments */
        @media (max-width: 480px) {
            .login_card {
                padding: 1.25rem;
            }

            .heading_container {
                margin-bottom: 1.5rem;
            }

            .form-group {
                margin-bottom: 1rem;
            }

            .form-control {
                font-size: 1.3rem;
            }

            .form-control::placeholder {
                font-size: 1.1rem;
            }
        }

        @media (max-height: 600px) {
            body {
                min-height: auto;
                padding: 2rem 0;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            * {
                transition: none !important;
            }
        }
    </style>

    <script type="text/javascript">
        var base_url = '<?php echo base_url(); ?>';
    </script>
</head>

<body>
    <div class="auth_container">
        <div class="heading_container">
            <h1>Empowering Education</h1>
            <p>Step Into the Future of Learning</p>
        </div>

        <div class="login_card">
            <h3><?php echo translate('login_to_your_account'); ?></h3>
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
                    <div class="password-field-container">
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
                    <label class="form-check-label" for="remember">
                        <?php echo translate('remember'); ?>
                    </label>
                </div>

                <button type="submit" class="btn btn-primary">
                    <?php echo translate('login'); ?>
                </button>
                <div class="form-footer">
                    <a href="<?php echo base_url('authentication/forgot'); ?>">
                        <?php echo translate('forgot_password'); ?>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script src="<?php echo base_url('assets/vendor/bootstrap/js/bootstrap.bundle.min.js'); ?>"></script>
    <script src="<?php echo base_url('assets/login_page/js/custom.js'); ?>"></script>
    
    <!-- Font Awesome for eye icon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    
    <!-- Password Toggle Script -->
    <script>
        $(document).ready(function() {
            const passwordInput = $('#password');
            const passwordToggle = $('#passwordToggle');
            
            passwordToggle.on('click', function() {
                // Toggle password visibility
                if (passwordInput.attr('type') === 'password') {
                    passwordInput.attr('type', 'text');
                    passwordToggle.html('<i class="fa fa-eye-slash"></i>');
                } else {
                    passwordInput.attr('type', 'password');
                    passwordToggle.html('<i class="fa fa-eye"></i>');
                }
            });
        });
    </script>

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