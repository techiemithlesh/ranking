<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="keywords" content="school management system, schoolxcel, register">
    <meta name="description" content="Create your account">
    <meta name="author" content="schoolexcel.tech">
    <title>Register</title>
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
        
        .register-box {
            width: 100%;
            margin-top: 100px;
            max-width: 450px;
            margin-left: 30px;
        }
        
        .register-form h2 {
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
        
        .form-control::placeholder {
            color: #6c757d;
            font-size: 1.3rem;
            opacity: 0.8;
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

        .support-link:hover {
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
            
            .register-box {
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
            
            .register-form {
                padding: 20px 15px;
            }
            
            .form-group label {
                font-size: 1.1rem;
            }
            
            .form-control {
                font-size: 1.1rem;
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
            <h1>Create Your Account</h1>
        </div>
        
        <div class="content-wrapper">
            <!-- Left side illustration -->
            <div class="left-side">
               
            </div>
            
            <!-- Right side register form -->
            <div class="right-side">
                <div class="register-box">
                    <div class="register-form">
                        <h2>Register</h2>
                        <p style="text-align:center;color:#ffffffcc;margin-bottom:1.5rem;font-size:1.1rem;">
                            Submit your details and our team will get back to you shortly.
                        </p>
                        <?php echo form_open('authentication/register'); ?>
                            <div class="form-group">
                                <label for="name"><?php echo translate('name'); ?></label>
                                <input type="text" class="form-control" name="name" id="name" 
                                    value="<?php echo set_value('name'); ?>"
                                    placeholder="Enter your full name" required>
                                <span class="text-danger"><?php echo form_error('name'); ?></span>
                            </div>
                            <div class="form-group">
                                <label for="email"><?php echo translate('email'); ?></label>
                                <input type="email" class="form-control" name="email" id="email" 
                                    value="<?php echo set_value('email'); ?>"
                                    placeholder="<?php echo translate('email'); ?>" required>
                                <span class="text-danger"><?php echo form_error('email'); ?></span>
                            </div>
                            <div class="form-group">
                                <label for="mobile_no"><?php echo translate('phone'); ?></label>
                                <input type="tel" class="form-control" name="mobile_no" id="mobile_no" 
                                    value="<?php echo set_value('mobile_no'); ?>"
                                    placeholder="Enter your phone number">
                                <span class="text-danger"><?php echo form_error('mobile_no'); ?></span>
                            </div>
                            <div class="form-group">
                                <label for="address"><?php echo translate('address'); ?></label>
                                <textarea class="form-control" name="address" id="address" rows="2"
                                    placeholder="Enter your address"><?php echo set_value('address'); ?></textarea>
                                <span class="text-danger"><?php echo form_error('address'); ?></span>
                            </div>

                            <button type="submit" class="btn btn-primary">
                                Submit Request
                            </button>
                            <div class="form-footer">
                                <a href="<?php echo base_url('authentication'); ?>">
                                    Already have an account? Sign In
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