<?php
require_once 'config.php';
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Developers & Builders - BrickByBrick</title>
    <link rel="stylesheet" href="css/vendor.css">
    <style>
        .developer-hero {
            background: linear-gradient(rgba(44, 62, 80, 0.8), rgba(44, 62, 80, 0.8)), url('https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?ixlib=rb-4.0.3');
            background-size: cover;
            background-position: center;
            padding: 100px 0;
            color: white;
        }
        .developer-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            transition: transform 0.3s;
            margin-bottom: 30px;
        }
        .developer-card:hover {
            transform: translateY(-10px);
        }
        .developer-card .card-img {
            height: 200px;
            object-fit: cover;
        }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <!-- Hero Section -->
    <div class="developer-hero text-center">
        <div class="container">
            <h1 class="display-4 fw-bold mb-4">Premier Developers & Builders</h1>
            <p class="lead mb-5">Partner with the most trusted names in real estate development</p>
        </div>
    </div>

    <!-- Featured Developers Section -->
    <div class="container py-5">
        <h2 class="section-title text-center mb-5">Featured Developers</h2>
        <div class="row">
            <!-- Developer Card 1 -->
            <div class="col-md-4">
                <div class="developer-card">
                    <img src="https://images.unsplash.com/photo-1486406146926-c627a92ad1ab" class="card-img" alt="Developer 1">
                    <div class="card-body">
                        <h3 class="card-title">Skyline Developers</h3>
                        <p class="card-text">Leading luxury residential and commercial property developer with 25 years of excellence.</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-primary">50+ Projects</span>
                            <button class="btn btn-outline-primary btn-sm">View Profile</button>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Developer Card 2 -->
            <div class="col-md-4">
                <div class="developer-card">
                    <img src="https://images.unsplash.com/photo-1486406146926-c627a92ad1ab" class="card-img" alt="Developer 2">
                    <div class="card-body">
                        <h3 class="card-title">Urban Builders</h3>
                        <p class="card-text">Specializing in sustainable urban development and modern living spaces.</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-primary">35+ Projects</span>
                            <button class="btn btn-outline-primary btn-sm">View Profile</button>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Developer Card 3 -->
            <div class="col-md-4">
                <div class="developer-card">
                    <img src="https://images.unsplash.com/photo-1486406146926-c627a92ad1ab" class="card-img" alt="Developer 3">
                    <div class="card-body">
                        <h3 class="card-title">Premium Constructions</h3>
                        <p class="card-text">Award-winning developer known for innovative design and quality construction.</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-primary">40+ Projects</span>
                            <button class="btn btn-outline-primary btn-sm">View Profile</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Contact Section -->
    <div class="container py-5" id="contact-section">
        <div class="row justify-content-center">
            <div class="col-md-8 text-center">
                <h2 class="mb-4">Partner With Us</h2>
                <p class="lead mb-4">Join our network of premier developers and showcase your projects.</p>
                <button class="btn btn-primary btn-lg" id="show-contact-form">Contact Us</button>
                
                <!-- Contact Form -->
                <div class="contact-form mt-4" style="display: none;">
                    <?php if (isset($_POST['submit_contact'])) {
                        $name = trim($_POST['name']);
                        $email = trim($_POST['email']);
                        $company = trim($_POST['company']);
                        $phone = trim($_POST['phone']);
                        $message = trim($_POST['message']);
                        
                        if (empty($name) || empty($email) || empty($message)) {
                            echo '<div class="alert alert-danger">Please fill all required fields.</div>';
                        } else {
                            $sql = "INSERT INTO developer_contacts (name, email, company_name, phone, message) VALUES (?, ?, ?, ?, ?)";
                            $stmt = $conn->prepare($sql);
                            $stmt->bind_param("sssss", $name, $email, $company, $phone, $message);
                            
                            if ($stmt->execute()) {
                                echo '<div class="alert alert-success">Thank you for your interest! We will contact you soon.</div>';
                            } else {
                                echo '<div class="alert alert-danger">Something went wrong. Please try again later.</div>';
                            }
                        }
                    } ?>
                    
                    <form action="#contact-section" method="POST" class="text-start">
                        <div class="mb-3">
                            <label for="name" class="form-label">Full Name *</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address *</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="company" class="form-label">Company Name</label>
                            <input type="text" class="form-control" id="company" name="company">
                        </div>
                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" id="phone" name="phone">
                        </div>
                        <div class="mb-3">
                            <label for="message" class="form-label">Message *</label>
                            <textarea class="form-control" id="message" name="message" rows="4" required></textarea>
                        </div>
                        <button type="submit" name="submit_contact" class="btn btn-primary">Submit</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="js/main.js"></script>
    <script>
        document.getElementById('show-contact-form').addEventListener('click', function() {
            document.querySelector('.contact-form').style.display = 'block';
            this.style.display = 'none';
        });
    </script>
</body>
</html>