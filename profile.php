<?php
require_once 'config.php';
require_once 'mail_config.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Fetch user data
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'update_profile') {
        $full_name = trim($_POST['full_name']);
        $phone = trim($_POST['phone'] ?? '');
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        // Validate input
        if (empty($full_name)) {
            $error = 'Full name is required';
        } else {
            // Update basic info
            $update_sql = "UPDATE users SET full_name = ?, phone = ? WHERE id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("ssi", $full_name, $phone, $user_id);
            
            if ($update_stmt->execute()) {
                $success = 'Profile updated successfully';
                
                // Update password if provided
                if (!empty($current_password) && !empty($new_password)) {
                    if ($new_password !== $confirm_password) {
                        $error = 'New passwords do not match';
                    } elseif (strlen($new_password) < 6) {
                        $error = 'Password must be at least 6 characters long';
                    } else {
                        // Verify current password
                        if (password_verify($current_password, $user['password'])) {
                            // Hash the new password
                            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                            
                            // Update password
                            $password_sql = "UPDATE users SET password = ? WHERE id = ?";
                            $password_stmt = $conn->prepare($password_sql);
                            $password_stmt->bind_param("si", $hashed_password, $user_id);
                            
                            if ($password_stmt->execute()) {
                                $success = 'Profile and password updated successfully';
                            } else {
                                $error = 'Failed to update password';
                            }
                        } else {
                            $error = 'Current password is incorrect';
                        }
                    }
                }
                
                // Refresh user data
                $stmt->execute();
                $result = $stmt->get_result();
                $user = $result->fetch_assoc();
            } else {
                $error = 'Failed to update profile';
            }
        }
    } elseif (isset($_POST['action']) && $_POST['action'] === 'request_verification') {
        // Add a notification that we're handling email verification
        // Handle email verification request
        if ($user['email_verified'] == 1) {
            $error = 'Your email is already verified';
        } else {
            // Generate new OTP
            $otp = generateOTP();
            $expiry = date('Y-m-d H:i:s', strtotime('+10 minutes'));
            
            // Update OTP in database
            $sql = "UPDATE users SET verification_code = ?, verification_expiry = ? WHERE id = ?";
            $update_stmt = $conn->prepare($sql);
            $update_stmt->bind_param("ssi", $otp, $expiry, $user_id);
            
            if ($update_stmt->execute()) {
                // Send OTP email
                if (sendOTPEmail($user['email'], $otp)) {
                    $success = 'A new verification code has been sent to your email';
                } else {
                    $error = 'Failed to send verification email. Please try again later.';
                }
            } else {
                $error = 'Something went wrong. Please try again later.';
            }
        }
    } elseif (isset($_POST['action']) && $_POST['action'] === 'verify_email') {
        // Handle email verification
        $otp = $_POST['otp'];
        
        // Check if OTP is valid
        $sql = "SELECT id FROM users WHERE id = ? AND verification_code = ? AND verification_expiry > NOW()";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $user_id, $otp);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            // Mark email as verified
            $sql = "UPDATE users SET email_verified = 1, verification_code = NULL, verification_expiry = NULL WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $user_id);
            
            if ($stmt->execute()) {
                $success = 'Your email has been verified successfully';
                
                // Refresh user data
                $sql = "SELECT * FROM users WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $user = $result->fetch_assoc();
            } else {
                $error = 'Failed to verify email. Please try again.';
            }
        } else {
            $error = 'Invalid or expired verification code. Please try again.';
        }
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate inputs
    if (empty($full_name) || empty($email)) {
        $error = 'Name and email are required fields';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address';
    } else {
        // Check if email is already in use by another user
        $check_sql = "SELECT id FROM users WHERE email = ? AND id != ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("si", $email, $user_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            $error = 'Email address is already in use by another account';
        } else {
            // If user wants to change password
            if (!empty($current_password) && !empty($new_password)) {
                // Verify current password
                if (!password_verify($current_password, $user['password'])) {
                    $error = 'Current password is incorrect';
                } elseif ($new_password !== $confirm_password) {
                    $error = 'New passwords do not match';
                } elseif (strlen($new_password) < 6) {
                    $error = 'New password must be at least 6 characters long';
                } else {
                    // Update user with new password
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $update_sql = "UPDATE users SET full_name = ?, email = ?, phone = ?, password = ? WHERE id = ?";
                    $update_stmt = $conn->prepare($update_sql);
                    $update_stmt->bind_param("ssssi", $full_name, $email, $phone, $hashed_password, $user_id);
                    
                    if ($update_stmt->execute()) {
                        $success = 'Profile updated successfully';
                        // Refresh user data
                        $stmt->execute();
                        $result = $stmt->get_result();
                        $user = $result->fetch_assoc();
                    } else {
                        $error = 'Something went wrong. Please try again.';
                    }
                }
            } else {
                // Update user without changing password
                $update_sql = "UPDATE users SET full_name = ?, email = ?, phone = ? WHERE id = ?";
                $update_stmt = $conn->prepare($update_sql);
                $update_stmt->bind_param("sssi", $full_name, $email, $phone, $user_id);
                
                if ($update_stmt->execute()) {
                    $success = 'Profile updated successfully';
                    // Refresh user data
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $user = $result->fetch_assoc();
                } else {
                    $error = 'Something went wrong. Please try again.';
                }
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
    <title>My Profile - Real Estate</title>
    <link rel="stylesheet" href="css/vendor.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container py-5">
        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">My Profile</h4>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($success)): ?>
                            <div class="alert alert-success">
                                <?php echo $success; ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger">
                                <?php echo $error; ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="post" action="profile.php">
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" value="<?php echo htmlspecialchars($user['username']); ?>" readonly>
                                <small class="text-muted">Username cannot be changed</small>
                            </div>
                            
                            <div class="mb-3">
                                <label for="full_name" class="form-label">Full Name</label>
                                <input type="text" class="form-control" id="full_name" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="text" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                            </div>
                            
                            <hr>
                            <h5>Change Password</h5>
                            <p class="text-muted small">Leave blank if you don't want to change your password</p>
                            
                            <div class="mb-3">
                                <label for="current_password" class="form-label">Current Password</label>
                                <input type="password" class="form-control" id="current_password" name="current_password">
                            </div>
                            
                            <div class="mb-3">
                                <label for="new_password" class="form-label">New Password</label>
                                <input type="password" class="form-control" id="new_password" name="new_password">
                            </div>
                            
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Confirm New Password</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" name="update_profile" class="btn btn-primary">Update Profile</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5>Real Estate</h5>
                    <p>Find your dream property with us.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p>&copy; <?php echo date('Y'); ?> Real Estate. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/main.js"></script>
</body>
</html>