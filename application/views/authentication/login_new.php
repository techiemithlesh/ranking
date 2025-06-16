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
        
        body {
            margin: 0;
            padding: 0;
            background: url('<?php echo base_url('assets/login_page/image/bg_new.gif'); ?>');
            background-size: cover;
            background-position: center;
            min-height: 100vh;
            width: 100%;
            display: flex;
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
            color: #ffff;
        }
        
        .form-group {
            margin-bottom: 1.25rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            font-size: 1.5rem;
            color: #ffff;
        }
        
        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1.3rem;
            transition: border-color 0.2s ease;
        }
        
        .form-control:focus {
            border-color: #007bff;
            outline: none;
            box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.25);
        }
        
        .password-field {
            position: relative;
        }
        
        .password-toggle {
            position: absolute;
            top: 50%;
            right: 10px;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: #6c757d;
            font-size: 1.2rem;
        }
        
        .password-toggle:focus {
            outline: none;
        }
        
        .form-control::placeholder {
            color: #6c757d;
            font-size: 1.3rem;
            opacity: 0.8;
        }
        
        .form-check {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }
        
        .form-check-label {
            font-size: 1.2rem;
            color: #ffffff;
        }
        
        .btn-primary {
            width: 100%;
            padding: 0.75rem;
            font-size: 1.3rem;
            font-weight: 500;
            border-radius: 25px;
            background-color: #26268e;
            border: none;
            color: white;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .btn-primary:hover {
            background-color: #1a1a70;
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

        .support-link:hover{
            color: yellow;
            text-decoration: none;
        }
        
        .support-link i {
            margin-right: 5px;
        }
               
        /* Media queries */
        @media (max-width: 992px) {
            .content-wrapper {
                flex-direction: column;
            }
            
            .left-side {
                display: none;
            }
            
            .right-side {
                width: 100%;
            }
            
            .login-box {
                margin-left: 0;
                max-width: 500px;
                width: 100%;
            }

            .header-text {
                margin-bottom: 40px;
            }
            
            .support-link {
                position: relative;
                bottom: 0;
                right: auto;
                margin-top: 30px;
                text-align: right;
                justify-content: right;
            }
        }
        
        @media (max-width: 576px) {
            .header-text h1 {
                font-size: 26px;
            }
            
            .login-form {
                padding: 20px 15px;
            }
            
            .form-group label {
                font-size: 1.1rem;
            }
            
            .form-control {
                font-size: 1.1rem;
            }
            
            .form-check-label {
                font-size: 1rem;
            }
        }
        
        @media (max-height: 600px) {
            body {
                min-height: auto;
                padding: 2rem 0;
            }
        }
    </style>
</head>
<body>
    <div class="main-container">
        <!-- Header text -->
        <div class="header-text">
            <h1>Welcome Back to Your Learning Dashboard</h1>
        </div>
        
        <div class="content-wrapper">
            <!-- Left side illustration -->
            <div class="left-side">
               
            </div>
            
            <!-- Right side login form -->
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
            </div>
            
            <!-- Support link -->
            <a href="#" class="support-link">
                <i class="fas fa-headset"></i> Need help? Visit our Support Center
            </a>
        </div>
    </div>

    <script src="<?php echo base_url('assets/vendor/bootstrap/js/bootstrap.bundle.min.js'); ?>"></script>
    <script>
        $(document).ready(function() {
            // Password toggle functionality
            $('#passwordToggle').on('click', function() {
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