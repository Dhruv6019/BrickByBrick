<?php
require_once 'config.php';
require_once 'mail_config.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = htmlspecialchars($_POST['name']);
    $email = htmlspecialchars($_POST['email']);
    $subject= "Contact form";
    $message = htmlspecialchars($_POST['message']);
    
    $emailBody = "
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                .email-container { font-family: 'Arial', sans-serif; max-width: 600px; margin: 0 auto; background: #ffffff; }
                .email-header { background: linear-gradient(135deg, #007bff, #0056b3); padding: 40px 20px; text-align: center; }
                .email-header img { width: 150px; height: auto; margin-bottom: 20px; }
                .email-header h2 { color: #ffffff; margin: 0; font-size: 28px; font-weight: 600; }
                .email-content { padding: 40px 30px; background: #f8f9fa; }
                .message-box { background: #ffffff; border-radius: 10px; padding: 30px; box-shadow: 0 2px 15px rgba(0,0,0,0.05); }
                .field { margin-bottom: 25px; }
                .field:last-child { margin-bottom: 0; }
                .field-label { color: #0056b3; font-weight: 600; font-size: 16px; margin-bottom: 8px; }
                .field-value { color: #2c3e50; font-size: 15px; line-height: 1.6; padding: 15px; background: #f8f9fa; border-radius: 6px; }
                .email-footer { text-align: center; padding: 30px; background: #ffffff; }
                .footer-logo { margin-bottom: 20px; }
                .footer-text { color: #6c757d; font-size: 14px; margin-bottom: 10px; }
                .social-links { margin-top: 20px; }
                .social-links a { color: #007bff; text-decoration: none; margin: 0 10px; }
            </style>
        </head>
        <body>
            <div class='email-container'>
                <div class='email-header'>
                    <img src='http://localhost/newreal/uploads/properties/property_67daf750b11fb.webp' alt='BrickByBrick Logo'>
                    <h2>New Contact Inquiry</h2>
                </div>
                
                <div class='email-content'>
                    <div class='message-box'>
                        <div class='field'>
                            <div class='field-label'>👤 From</div>
                            <div class='field-value'>{$name}</div>
                        </div>
                        <div class='field'>
                            <div class='field-label'>📧 Email Address</div>
                            <div class='field-value'>{$email}</div>
                        </div>
                        <div class='field'>
                            <div class='field-label'>💬 Message</div>
                            <div class='field-value'>{$message}</div>
                        </div>
                    </div>
                </div>

                <div class='email-footer'>
                    <div class='footer-logo'>
                        <img src='http://localhost/newreal/uploads/properties/property_67daf750b11fb.webp' alt='BrickByBrick' width='120'>
                    </div>
                    <div class='footer-text'>
                        123, Real Estate, Ahmedabad<br>
                        Phone: 8758541064
                    </div>
                    <div class='footer-text'>
                        This is an automated message from BrickByBrick Contact Form.
                    </div>
                    <div class='social-links'>
                       | <a href='http://localhost/newreal/index.php'>Visit Website</a> |
                    </div>
                    <div class='footer-text' style='margin-top: 20px;'>
                        © " . date('Y') . " BrickByBrick. All rights reserved.
                    </div>
                </div>
            </div>
        </body>
        </html>
    ";
    
    $altBody = "
=== Contact Form Submission ===

From: {$name}
Email: {$email}

Message:
{$message}

-------------------
Sent via BrickByBrick Contact Form
Contact us: 8758541064
Address: 123, Real Estate, Ahmedabad
";
    
    if(sendMail('dhruvteli6019@gmail.com', "Contact Form: $subject", $emailBody, $altBody)) {
        $success_message = "Thank you for your message. We'll get back to you soon!";
    } else {
        $error_message = "Sorry, there was an error sending your message.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - BrickByBrick</title>
    <link rel="stylesheet" href="css/vendor.css">
    <style>
        .form-control:focus, .input-group-text {
            box-shadow: none !important;
        }
        .input-group-text {
            width: 45px;
            justify-content: center;
        }
        .form-control {
            padding: 0.75rem 1rem;
        }
        .btn-lg {
            font-weight: 500;
            letter-spacing: 0.5px;
        }
        .contact-hero {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            padding: 80px 0;
            margin-bottom: 80px;
        }
        .contact-icon {
            font-size: 2.5rem;
            color: var(--primary-color);
            opacity: 0.8;
        }
        .info-card {
            transition: transform 0.3s ease;
        }
        .info-card:hover {
            transform: translateY(-5px);
        }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <!-- Hero Section -->
    <div class="contact-hero text-white">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1 class="display-4 fw-bold mb-4">Get in Touch</h1>
                    <p class="lead mb-0">Let us help you find your perfect property. Our team is here to assist you every step of the way.</p>
                </div>
                <div class="col-lg-6 text-end d-none d-lg-block">
                    <i class="fas fa-envelope-open-text fa-5x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="row">
            <!-- Contact Form -->
            <div class="col-lg-7 mb-5">
                <?php if (isset($success_message)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo $success_message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo $error_message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <div class="card shadow-lg border-0 rounded-3">
                    <div class="card-body p-4 p-md-5">
                        <div class="text-center mb-4">
                            <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex p-3 mb-3">
                                <i class="fas fa-paper-plane text-primary fa-2x"></i>
                            </div>
                            <h2 class="h3">Send us a Message</h2>
                            <p class="text-muted">We'll get back to you as soon as possible</p>
                        </div>

                        <form method="POST" action="" class="needs-validation" novalidate>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="name" class="form-label">Your Name</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-0">
                                            <i class="fas fa-user text-muted"></i>
                                        </span>
                                        <input type="text" class="form-control border-0 bg-light" id="name" name="name" required>
                                    </div>
                                    <div class="invalid-feedback">Please enter your name.</div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email Address</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-0">
                                            <i class="fas fa-envelope text-muted"></i>
                                        </span>
                                        <input type="email" class="form-control border-0 bg-light" id="email" name="email" required>
                                    </div>
                                    <div class="invalid-feedback">Please enter a valid email address.</div>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label for="message" class="form-label">Message</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-0">
                                        <i class="fas fa-comment text-muted"></i>
                                    </span>
                                    <textarea class="form-control border-0 bg-light" id="message" name="message" rows="5" required></textarea>
                                </div>
                                <div class="invalid-feedback">Please enter your message.</div>
                            </div>
                            <div class="text-center">
                                <button type="submit" class="btn btn-primary btn-lg px-5 py-3 rounded-pill">
                                    <i class="fas fa-paper-plane me-2"></i>Send Message
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Contact Information -->
            <div class="col-lg-5">
                <div class="card shadow-lg border-0 rounded-3 mb-4 info-card">
                    <div class="card-body p-4">
                        <h3 class="h4 mb-4">Contact Information</h3>
                        <div class="d-flex mb-4">
                            <div class="flex-shrink-0">
                                <div class="bg-primary bg-opacity-10 p-3 rounded-circle">
                                    <i class="fas fa-map-marker-alt text-primary"></i>
                                </div>
                            </div>
                            <div class="ms-3">
                                <h5 class="mb-1">Our Location</h5>
                                <p class="mb-0 text-muted">123, Real Estate, Ahmedabad</p>
                            </div>
                        </div>
                        <div class="d-flex mb-4">
                            <div class="flex-shrink-0">
                                <div class="bg-primary bg-opacity-10 p-3 rounded-circle">
                                    <i class="fas fa-phone text-primary"></i>
                                </div>
                            </div>
                            <div class="ms-3">
                                <h5 class="mb-1">Phone Number</h5>
                                <p class="mb-0 text-muted">8758541064</p>
                            </div>
                        </div>
                        <div class="d-flex">
                            <div class="flex-shrink-0">
                                <div class="bg-primary bg-opacity-10 p-3 rounded-circle">
                                    <i class="fas fa-envelope text-primary"></i>
                                </div>
                            </div>
                            <div class="ms-3">
                                <h5 class="mb-1">Email Address</h5>
                                <p class="mb-0 text-muted">info@brickbybrick.com</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card shadow-lg border-0 rounded-3 info-card">
                    <div class="card-body p-4">
                        <h3 class="h4 mb-4">Office Hours</h3>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Monday - Friday:</span>
                            <span>9:00 AM - 6:00 PM</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Saturday:</span>
                            <span>10:00 AM - 4:00 PM</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Sunday:</span>
                            <span>Closed</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="py-5"></div>

    <?php include 'includes/footer.php'; ?>

    <script>
    (function () {
        'use strict'
        var forms = document.querySelectorAll('.needs-validation')
        Array.prototype.slice.call(forms).forEach(function (form) {
            form.addEventListener('submit', function (event) {
                if (!form.checkValidity()) {
                    event.preventDefault()
                    event.stopPropagation()
                }
                form.classList.add('was-validated')
            }, false)
        })
    })()
    </script>
</body>
</html>