<?php
require_once 'config.php';
require_once 'mail_config.php';
session_start();

$error = '';
$success = '';

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

// Process OTP verification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'verify_otp') {
    $email = $_POST['email'];
    $otp = $_POST['otp'];
    
    // Check if OTP is valid
    $sql = "SELECT id, username, full_name, user_type FROM users WHERE email = ? AND verification_code = ? AND verification_expiry > NOW()";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $email, $otp);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // Mark email as verified
        $sql = "UPDATE users SET email_verified = 1, verification_code = NULL, verification_expiry = NULL WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user['id']);
        
        if ($stmt->execute()) {
            // Auto login after verification
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_type'] = $user['user_type'];
            $_SESSION['full_name'] = $user['full_name'];
            
            header('Location: index.php');
            exit();
        } else {
            $error = 'Failed to verify email. Please try again.';
        }
    } else {
        $error = 'Invalid or expired OTP. Please try again.';
    }
}

// Process registration form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (!isset($_POST['action']) || $_POST['action'] === 'register')) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $email = trim($_POST['email']);
    $full_name = trim($_POST['full_name']);
    $phone = trim($_POST['phone'] ?? '');
    $company_name = trim($_POST['company_name'] ?? '');
    $license_number = trim($_POST['license_number'] ?? '');
    $years_experience = trim($_POST['years_experience'] ?? '');
    $user_type = $_POST['user_type'] ?? 'user';
    
    // Validate input
    if (empty($username) || empty($password) || empty($confirm_password) || empty($email) || empty($full_name) ||
        ($user_type === 'developer' && (empty($company_name) || empty($license_number) || empty($years_experience)))) {
        $error = 'Please fill all required fields';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long';
    } elseif (!in_array($user_type, ['user', 'developer'])) {
        $error = 'Invalid user type';
    } else {
        // Check if username already exists
        $sql = "SELECT id FROM users WHERE username = ? OR email = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ss", $username, $email);
            $stmt->execute();
            $stmt->store_result();
            
            if ($stmt->num_rows > 0) {
                $error = 'Username or email already exists';
            } else {
                // Username is available, insert new account
                $sql = "INSERT INTO users (username, password, email, full_name, phone, user_type, company_name, license_number, years_experience) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                
                if ($stmt = $conn->prepare($sql)) {
                    // Hash the password
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    
                    // Bind variables to the prepared statement as parameters
                    $stmt->bind_param("sssssssss", $username, $hashed_password, $email, $full_name, $phone, $user_type, $company_name, $license_number, $years_experience);
                    
                    // Attempt to execute the prepared statement
                    if ($stmt->execute()) {
                        $user_id = $conn->insert_id;
                        
                        // Generate OTP for email verification
                        $otp = generateOTP();
                        $expiry = date('Y-m-d H:i:s', strtotime('+10 minutes'));
                        
                        // Save OTP to database
                        $sql = "UPDATE users SET verification_code = ?, verification_expiry = ? WHERE id = ?";
                        $update_stmt = $conn->prepare($sql);
                        $update_stmt->bind_param("ssi", $otp, $expiry, $user_id);
                        $update_stmt->execute();
                        
                        // Send OTP email
                        if (sendOTPEmail($email, $otp)) {
                            $success = 'Registration successful! Please check your email for verification code.';
                        } else {
                            $error = 'Registration successful but failed to send verification email. Please contact support.';
                        }
                    } else {
                        $error = 'Something went wrong. Please try again later.';
                    }
                }
            }
            
            // Close statement
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - BrickbyBrick</title>
    <link rel="stylesheet" href="css/vendor.css">
    <style>
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
        .card-header {
            border-radius: 10px 10px 0 0 !important;
            background: linear-gradient(135deg, #2c3e50, #4ca1af);
        }
        .btn-primary {
            background: linear-gradient(135deg, #2c3e50, #4ca1af);
            border: none;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #1e2b37, #3a7b85);
        }
        .form-control:focus {
            border-color: #4ca1af;
            box-shadow: 0 0 0 0.25rem rgba(76, 161, 175, 0.25);
        }
        .user-type-selector {
            display: flex;
            margin-bottom: 1.5rem;
            gap: 15px;
        }
        .user-type-selector label {
            flex: 1;
            text-align: center;
            padding: 15px;
            border: 2px solid #dee2e6;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s;
        }
        .user-type-selector label:hover {
            border-color: #4ca1af;
        }
        .user-type-selector input[type="radio"]:checked + label {
            border-color: #2c3e50;
            background-color: rgba(44, 62, 80, 0.1);
        }
        .user-type-selector input[type="radio"] {
            display: none;
        }
        .user-type-selector i {
            display: block;
            font-size: 2rem;
            margin-bottom: 10px;
            color: #2c3e50;
        }
    </style>
</head>
<body>
    
    <?php include 'includes/navbar.php'; ?>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">Create an Account</h4>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        <?php if (!empty($success)): ?>
                            <div class="alert alert-success"><?php echo $success; ?></div>
                            <!-- OTP Verification Form -->
                            <form action="register.php" method="post" class="mt-4">
                                <input type="hidden" name="action" value="verify_otp">
                                <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
                                <div class="mb-3">
                                    <label for="otp" class="form-label">Enter Verification Code</label>
                                    <input type="text" class="form-control" id="otp" name="otp" required>
                                    <div class="form-text">Please enter the 6-digit code sent to your email.</div>
                                </div>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">Verify Email</button>
                                </div>
                            </form>
                        <?php else: ?>
                            <!-- Registration Form -->
                            <form action="register.php" method="post">
                                <input type="hidden" name="action" value="register">
                                
                                <h5 class="mb-3">I am registering as:</h5>
                                <div class="user-type-selector">
                                    <input type="radio" id="user-type-user" name="user_type" value="user" checked>
                                    <label for="user-type-user">
                                        <i class="fas fa-user"></i>
                                        <span>Regular User</span>
                                        <div class="small text-muted">Looking to buy or sell properties</div>
                                    </label>
                                    
                                    <input type="radio" id="user-type-developer" name="user_type" value="developer">
                                    <label for="user-type-developer">
                                        <i class="fas fa-building"></i>
                                        <span>Developer/Company</span>
                                        <div class="small text-muted">Property developer or real estate company</div>
                                    </label>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="username" class="form-label">Username</label>
                                        <input type="text" class="form-control" id="username" name="username" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="email" class="form-control" id="email" name="email" required>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="full_name" class="form-label">Full Name</label>
                                    <input type="text" class="form-control" id="full_name" name="full_name" required>
                                </div>
                                <div class="mb-3">
                                    <label for="phone" class="form-label">Phone Number</label>
                                    <input type="text" class="form-control" id="phone" name="phone">
                                </div>

                                <!-- Developer-specific fields -->
                                <div class="developer-fields" style="display: none;">
                                    <div class="mb-3">
                                        <label for="company_name" class="form-label">Company Name *</label>
                                        <input type="text" class="form-control" id="company_name" name="company_name">
                                    </div>
                                    <div class="mb-3">
                                        <label for="license_number" class="form-label">License/Registration Number *</label>
                                        <input type="text" class="form-control" id="license_number" name="license_number">
                                    </div>
                                    <div class="mb-3">
                                        <label for="years_experience" class="form-label">Years of Experience *</label>
                                        <input type="number" class="form-control" id="years_experience" name="years_experience" min="0">
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="password" class="form-label">Password</label>
                                        <div class="input-group">
                                            <input type="password" class="form-control" id="password" name="password" required>
                                            <button class="btn btn-outline-secondary toggle-password" type="button" data-target="password">
                                                <i class="far fa-eye"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="confirm_password" class="form-label">Confirm Password</label>
                                        <div class="input-group">
                                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                            <button class="btn btn-outline-secondary toggle-password" type="button" data-target="confirm_password">
                                                <i class="far fa-eye"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">Register</button>
                                </div>
                            </form>
                        <?php endif; ?>
                        <div class="mt-3 text-center">
                            <p>Already have an account? <a href="login.php">Login here</a></p>
                            <?php if (empty($success)): ?>
                                <div class="alert alert-info mt-3">
                                <p class="mb-0"><i class="fas fa-info-circle me-2"></i> If you haven't verified your email yet, please <a href="validate_otp.php" class="alert-link">click here to validate your account</a>.</p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5>BrickbyBrick</h5>
                    <p>Find your dream property with us.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p>&copy; <?php echo date('Y'); ?> BrickbyBrick. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Toggle developer fields visibility
        const developerFields = document.querySelector('.developer-fields');
        document.querySelectorAll('input[name="user_type"]').forEach(radio => {
            radio.addEventListener('change', function() {
                developerFields.style.display = this.value === 'developer' ? 'block' : 'none';
                const developerInputs = developerFields.querySelectorAll('input');
                developerInputs.forEach(input => {
                    input.required = this.value === 'developer';
                });
            });
        });

        // Password visibility toggle
        document.querySelectorAll('.toggle-password').forEach(button => {
            button.addEventListener('click', function() {
                const targetId = this.getAttribute('data-target');
                const input = document.getElementById(targetId);
                const icon = this.querySelector('i');
                
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                } else {
                    input.type = 'password';
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            });
        });
    </script>
</body>
</html>