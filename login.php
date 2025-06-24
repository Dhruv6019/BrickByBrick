<?php
require_once 'config.php';
session_start();

$error = '';

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

// Process login form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password';
    } else {
        // Prepare a select statement
        $sql = "SELECT id, username, password, user_type, full_name, email_verified FROM users WHERE username = ?";
        
        if ($stmt = $conn->prepare($sql)) {
            // Bind variables to the prepared statement as parameters
            $stmt->bind_param("s", $username);
            
            // Attempt to execute the prepared statement
            if ($stmt->execute()) {
                // Store result
                $stmt->store_result();
                
                // Check if username exists, if yes then verify password
                if ($stmt->num_rows == 1) {                    
                    // Bind result variables
                    $stmt->bind_result($id, $username, $hashed_password, $user_type, $full_name, $email_verified);
                    if ($stmt->fetch()) {
                        if (password_verify($password, $hashed_password)) {
                            // Check if email is verified
                            if ($email_verified == 0) {
                                $error = "Please verify your email before logging in.";
                            } else {
                                // Password is correct, start a new session
                                session_start();
                                
                                // Store data in session variables
                                $_SESSION['user_id'] = $id;
                                $_SESSION['username'] = $username;
                                $_SESSION['user_type'] = $user_type;
                                $_SESSION['full_name'] = $full_name;
                                
                                // Redirect user to appropriate page
                                if ($user_type === 'admin') {
                                    header("location: admin/dashboard.php");
                                } else {
                                    header("location: index.php");
                                }
                                exit();
                            }
                        } else {
                            // Password is not valid
                            $error = "Invalid username or password";
                        }
                    }
                } else {
                    // Username doesn't exist
                    $error = "Invalid username or password";
                }
            } else {
                $error = "Oops! Something went wrong. Please try again later.";
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
    <title>Login - BrickbyBrick</title>
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
    
    <?php include 'includes/navbar.php'; ?>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">Login</h4>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        <form action="login.php" method="post">
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="password" name="password" required>
                                    <button class="btn btn-outline-secondary toggle-password" type="button" data-target="password">
                                        <i class="far fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="mb-3 d-flex justify-content-between">
                                
                                <div>
                                    <a href="reset_password.php" class="text-decoration-none">Forgot Password?</a>
                                </div>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Login</button>
                            </div>
                        </form>
                        <div class="mt-3 text-center">
                            <p>Don't have an account? <a href="register.php">Register here</a></p>
                            <div class="alert alert-info mt-3">
                                <p class="mb-0"><i class="fas fa-info-circle me-2"></i> If you haven't verified your email yet, please <a href="validate_otp.php" class="alert-link">click here to validate your account</a>.</p>
                            </div>
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