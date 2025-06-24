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

// Handle token verification
if (isset($_GET['token']) && !empty($_GET['token'])) {
    $token = $_GET['token'];
    
    // Check if token exists and is valid
    $sql = "SELECT id, email, reset_expiry FROM users WHERE reset_token = ? AND reset_token IS NOT NULL";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $error = 'Invalid reset token. Please request a new password reset.';
    } else {
        $user = $result->fetch_assoc();
        if (strtotime($user['reset_expiry']) < time()) {
            // Clear expired token
            $sql = "UPDATE users SET reset_token = NULL, reset_expiry = NULL WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $user['id']);
            $stmt->execute();
            $error = 'Your reset token has expired. Please request a new password reset.';
        }
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'request') {
        // Handle password reset request
        $email = trim($_POST['email']);
        
        if (empty($email)) {
            $error = 'Please enter your email address';
        } else {
            // Check if email exists
            $sql = "SELECT id, email FROM users WHERE email = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();
                
                // Generate reset token
                $token = generateResetToken();
                $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
                
                // Save token to database
                $sql = "UPDATE users SET reset_token = ?, reset_expiry = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssi", $token, $expiry, $user['id']);
                $stmt->execute();
                
                // Send reset email
                if (sendPasswordResetEmail($user['email'], $token)) {
                    $success = 'Password reset instructions have been sent to your email';
                } else {
                    $error = 'Failed to send reset email. Please try again later.';
                }
            } else {
                // Don't reveal that email doesn't exist for security reasons
                $success = 'If your email exists in our system, you will receive password reset instructions';
            }
        }
    } elseif ($_POST['action'] === 'reset' && isset($_POST['token'])) {
        // Handle password reset
        $token = $_POST['token'];
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        
        // Validate input
        if (empty($password) || empty($confirm_password)) {
            $error = 'Please fill all required fields';
        } elseif ($password !== $confirm_password) {
            $error = 'Passwords do not match';
        } elseif (strlen($password) < 6) {
            $error = 'Password must be at least 6 characters long';
        } else {
            // Check if token exists and is valid
            $sql = "SELECT id FROM users WHERE reset_token = ? AND reset_expiry > NOW() AND reset_token IS NOT NULL";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $token);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();
                
                // Hash the new password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Update password and clear reset token
                $sql = "UPDATE users SET password = ?, reset_token = NULL, reset_expiry = NULL WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("si", $hashed_password, $user['id']);
                
                if ($stmt->execute()) {
                    $success = 'Your password has been reset successfully. You can now login with your new password.';
                } else {
                    $error = 'Failed to reset password. Please try again.';
                }
            } else {
                $error = 'Invalid or expired reset token. Please request a new password reset.';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - BrickbyBrick</title>
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
    </style>
</head>
<body>
    <!-- Back to top button removed -->
    
    <?php include 'includes/navbar.php'; ?>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="logo-container text-center mb-4">
                    <img src="uploads/images/cb3b60f425e44730ac66256c65b2e882.png" alt="BrickbyBrick Logo" style="max-width: 200px; height: auto;">
                </div>
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0"><?php echo isset($_GET['token']) ? 'Reset Your Password' : 'Forgot Password'; ?></h4>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        <?php if (!empty($success)): ?>
                            <div class="alert alert-success"><?php echo $success; ?></div>
                        <?php endif; ?>
                        
                        <?php if (isset($_GET['token']) && !empty($_GET['token']) && empty($error)): ?>
                            <!-- Reset Password Form -->
                            <form action="reset_password.php" method="post">
                                <input type="hidden" name="action" value="reset">
                                <input type="hidden" name="token" value="<?php echo htmlspecialchars($_GET['token']); ?>">
                                
                                <div class="mb-3">
                                    <label for="password" class="form-label">New Password</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" id="password" name="password" required>
                                        <button class="btn btn-outline-secondary toggle-password" type="button" data-target="password">
                                            <i class="far fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">Confirm New Password</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                        <button class="btn btn-outline-secondary toggle-password" type="button" data-target="confirm_password">
                                            <i class="far fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">Reset Password</button>
                                </div>
                            </form>
                        <?php else: ?>
                            <!-- Request Reset Form -->
                            <form action="reset_password.php" method="post">
                                <input type="hidden" name="action" value="request">
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email Address</label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">Send Reset Link</button>
                                </div>
                            </form>
                        <?php endif; ?>
                        
                        <div class="mt-3 text-center">
                            <p>Remember your password? <a href="login.php">Login here</a></p>
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