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
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone'] ?? '');
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        // Validate input
        if (empty($full_name) || empty($email)) {
            $error = 'Full name and email are required';
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
                // Update basic info
                $update_sql = "UPDATE users SET full_name = ?, email = ?, phone = ? WHERE id = ?";
                $update_stmt = $conn->prepare($update_sql);
                $update_stmt->bind_param("sssi", $full_name, $email, $phone, $user_id);
                
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
        }
    } elseif (isset($_POST['action']) && $_POST['action'] === 'delete_account') {
        // Handle account deletion
        $password = $_POST['delete_password'] ?? '';
        
        // Verify password
        if (empty($password)) {
            $error = 'Password is required to delete your account';
        } elseif (!password_verify($password, $user['password'])) {
            $error = 'Incorrect password. Account deletion failed';
        } else {
            // Delete user's properties and related data first
            // 1. Get all user's properties
            $prop_sql = "SELECT id FROM properties WHERE seller_id = ?";
            $prop_stmt = $conn->prepare($prop_sql);
            $prop_stmt->bind_param("i", $user_id);
            $prop_stmt->execute();
            $prop_result = $prop_stmt->get_result();
            
            // Begin transaction
            $conn->begin_transaction();
            
            try {
                // Delete property-related data
                while ($property = $prop_result->fetch_assoc()) {
                    $property_id = $property['id'];
                    
                    // Delete property images
                    $del_img_sql = "DELETE FROM property_images WHERE property_id = ?";
                    $del_img_stmt = $conn->prepare($del_img_sql);
                    $del_img_stmt->bind_param("i", $property_id);
                    $del_img_stmt->execute();
                    
                    // Delete property favorites
                    $del_fav_sql = "DELETE FROM favorites WHERE property_id = ?";
                    $del_fav_stmt = $conn->prepare($del_fav_sql);
                    $del_fav_stmt->bind_param("i", $property_id);
                    $del_fav_stmt->execute();
                }
                
                // Delete all user's properties
                $del_prop_sql = "DELETE FROM properties WHERE seller_id = ?";
                $del_prop_stmt = $conn->prepare($del_prop_sql);
                $del_prop_stmt->bind_param("i", $user_id);
                $del_prop_stmt->execute();
                
                // Delete user's favorites
                $del_user_fav_sql = "DELETE FROM favorites WHERE user_id = ?";
                $del_user_fav_stmt = $conn->prepare($del_user_fav_sql);
                $del_user_fav_stmt->bind_param("i", $user_id);
                $del_user_fav_stmt->execute();
                
                // Finally, delete the user
                $del_user_sql = "DELETE FROM users WHERE id = ?";
                $del_user_stmt = $conn->prepare($del_user_sql);
                $del_user_stmt->bind_param("i", $user_id);
                $del_user_stmt->execute();
                
                // Commit transaction
                $conn->commit();
                
                // Clear session and redirect to home page
                session_unset();
                session_destroy();
                header('Location: index.php?account_deleted=1');
                exit();
            } catch (Exception $e) {
                // Rollback transaction on error
                $conn->rollback();
                $error = 'Failed to delete account: ' . $e->getMessage();
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
    <title>My Account - BrickByBrick</title>
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
        .btn-danger {
            background: linear-gradient(135deg, #d9534f, #c9302c);
            border: none;
        }
        .btn-danger:hover {
            background: linear-gradient(135deg, #c9302c, #a02622);
        }
        .form-control:focus {
            border-color: #4ca1af;
            box-shadow: 0 0 0 0.25rem rgba(76, 161, 175, 0.25);
        }
        .nav-pills .nav-link.active {
            background-color: #4ca1af;
        }
        .nav-pills .nav-link {
            color: #2c3e50;
        }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container py-5">
        <div class="row">
        <div class="col-md-3 mb-4">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Account Menu</h5>
        </div>
        <div class="card-body p-0">
            <div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                <button class="nav-link active" id="profile-tab" data-bs-toggle="pill" data-bs-target="#profile" type="button" role="tab">
                    Profile Information
                </button>
                <button class="nav-link" id="password-tab" data-bs-toggle="pill" data-bs-target="#password" type="button" role="tab">
                    Change Password
                </button>
                <button class="nav-link" id="delete-tab" data-bs-toggle="pill" data-bs-target="#delete" type="button" role="tab">
                    Delete Account
                </button>
                <button class="nav-link" id="favorites-tab" onclick="location.href='favorites.php';" type="button">
                    <i class="fas fa-heart"></i> Favorites
                </button>
                <button class="nav-link text-danger" id="logout-tab" onclick="location.href='logout.php';" type="button">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </button>
            </div>
        </div>
    </div>
</div>
            
            <div class="col-md-9">
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
                
                <div class="tab-content" id="v-pills-tabContent">
                    <!-- Profile Information Tab -->
                    <div class="tab-pane fade show active" id="profile" role="tabpanel" aria-labelledby="profile-tab">
                        <div class="card shadow">
                            <div class="card-header bg-primary text-white">
                                <h4 class="mb-0">Profile Information</h4>
                            </div>
                            <div class="card-body">
                                <form method="post" action="my_account.php">
                                    <input type="hidden" name="action" value="update_profile">
                                    
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
                                    
                                    <div class="d-grid gap-2">
                                        <button type="submit" class="btn btn-primary">Update Profile</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Change Password Tab -->
                    <div class="tab-pane fade" id="password" role="tabpanel" aria-labelledby="password-tab">
                        <div class="card shadow">
                            <div class="card-header bg-primary text-white">
                                <h4 class="mb-0">Change Password</h4>
                            </div>
                            <div class="card-body">
                                <form method="post" action="my_account.php">
                                    <input type="hidden" name="action" value="update_profile">
                                    
                                    <div class="mb-3">
                                        <label for="current_password" class="form-label">Current Password</label>
                                        <input type="password" class="form-control" id="current_password" name="current_password" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="new_password" class="form-label">New Password</label>
                                        <input type="password" class="form-control" id="new_password" name="new_password" required>
                                        <small class="text-muted">Password must be at least 6 characters long</small>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="confirm_password" class="form-label">Confirm New Password</label>
                                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                    </div>
                                    
                                    <div class="d-grid gap-2">
                                        <button type="submit" class="btn btn-primary">Change Password</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Delete Account Tab -->
                    <div class="tab-pane fade" id="delete" role="tabpanel" aria-labelledby="delete-tab">
                        <div class="card shadow">
                            <div class="card-header bg-danger text-white">
                                <h4 class="mb-0">Delete Account</h4>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-warning">
                                    <h5><i class="fas fa-exclamation-triangle me-2"></i> Warning!</h5>
                                    <p>Deleting your account is permanent and cannot be undone. All your data, including properties, favorites, and personal information will be permanently removed.</p>
                                </div>
                                
                                <form method="post" action="my_account.php" onsubmit="return confirm('Are you sure you want to delete your account? This action cannot be undone.');">
                                    <input type="hidden" name="action" value="delete_account">
                                    
                                    <div class="mb-3">
                                        <label for="delete_password" class="form-label">Enter Your Password to Confirm</label>
                                        <input type="password" class="form-control" id="delete_password" name="delete_password" required>
                                    </div>
                                    
                                    <div class="d-grid gap-2">
                                        <button type="submit" class="btn btn-danger">Delete My Account</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/main.js"></script>
</body>
</html>