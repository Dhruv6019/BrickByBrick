<?php
require_once 'config.php';
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - BrickByBrick</title>
    <link rel="stylesheet" href="css/vendor.css">
    <style>
        .about-hero {
            background: linear-gradient(rgba(44, 62, 80, 0.8), rgba(44, 62, 80, 0.8)), url('https://images.unsplash.com/photo-1560518883-ce09059eeffa?ixlib=rb-4.0.3');
            background-size: cover;
            background-position: center;
            padding: 100px 0;
            color: white;
        }
        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            transition: transform 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .team-member {
            text-align: center;
            margin-bottom: 30px;
        }
        .team-member img {
            width: 200px;
            height: 200px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 15px;
        }
        .testimonial-card {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin: 15px 0;
        }
        .testimonial-card img {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            margin-right: 15px;
        }
    </style>
</head>
<body>
<?php include 'includes/navbar.php'; ?>
    <!-- Hero Section -->
    <section class="about-hero text-center">
        <div class="container">
            <h1 class="display-4 mb-4">About BrickByBrick</h1>
            <p class="lead">Your Trusted Partner in Real Estate Excellence</p>
        </div>
    </section>

    <!-- Mission Section -->
    <section class="py-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h2 class="mb-4">Our Mission</h2>
                    <p class="lead">At BrickByBrick, we're committed to helping you find the perfect property that matches your dreams and lifestyle.</p>
                    <p>We believe that everyone deserves to find their ideal home, and we're here to make that journey as smooth and enjoyable as possible. Our team of dedicated professionals works tirelessly to provide exceptional service and expert guidance throughout your real estate journey.</p>
                </div>
                <div class="col-md-6">
                    <img src="https://images.unsplash.com/photo-1560520653-9e0e4c89eb11" class="img-fluid rounded" alt="Our Mission">
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-md-3 mb-4">
                    <div class="stat-card">
                        <i class="fas fa-home fa-3x mb-3 text-primary"></i>
                        <h3>1000+</h3>
                        <p class="mb-0">Properties Sold</p>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="stat-card">
                        <i class="fas fa-users fa-3x mb-3 text-primary"></i>
                        <h3>5000+</h3>
                        <p class="mb-0">Happy Clients</p>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="stat-card">
                        <i class="fas fa-star fa-3x mb-3 text-primary"></i>
                        <h3>4.8/5</h3>
                        <p class="mb-0">Average Rating</p>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="stat-card">
                        <i class="fas fa-award fa-3x mb-3 text-primary"></i>
                        <h3>15+</h3>
                        <p class="mb-0">Years Experience</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Team Section -->
    <section class="py-5">
        <div class="container">
            <h2 class="text-center mb-5">Meet Our Team</h2>
            <div class="row">
                <div class="col-md-4">
                    <div class="team-member">
                        <img src="https://a.storyblok.com/f/178900/960x540/01bf42c586/jinwoo-sung-solo-leveling.jpg/m/filters:quality(95)format(webp)" alt="Dhruv Teli">
                        <h4>Dhruv Teli</h4>
                        <p class="text-muted">CEO & Founder</p>
                        <p>With over 20 years of experience in real estate, John leads our team with passion and expertise.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="team-member">
                        <img src="https://i.redd.it/what-do-my-top-anime-guys-say-about-me-not-in-order-v0-uf3mqymog63c1.jpg?width=700&format=pjpg&auto=webp&s=cd546c599ed9e6e2d4c6b06303581ae50847a923" alt="Dhruv Vishwakarma">
                        <h4>Dhruv Vishwakarma</h4>
                        <p class="text-muted">Head of Sales</p>
                        <p>Jane's dedication to client satisfaction has made her one of our most valuable team members.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="team-member">
                        <img src="https://preview.redd.it/if-demon-slayer-characters-were-in-a-yearbook-what-would-v0-zl4rzyp23ogd1.jpeg?width=640&crop=smart&auto=webp&s=2eae16720436488619795c6d21de23c461b326db" alt="Shah Bhavy">
                        <h4>Shah Bhavy</h4>
                        <p class="text-muted">Head of Sales</p>
                        <p>Jane's dedication to client satisfaction has made her one of our most valuable team members.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="team-member">
                        <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRz-2MVGTaluJdhDzyMMpwrhv2AVw3NfnDLbw&s" alt="Shah Zeel">
                        <h4>Shah Zeel</h4>
                        <p class="text-muted">Head of Sales</p>
                        <p>Jane's dedication to client satisfaction has made her one of our most valuable team members.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="team-member">
                        <img src="https://img.freepik.com/premium-photo/cute-handsome-anime-boy-illustration_962508-55170.jpg" alt="Dev">
                        <h4>Dev</h4>
                        <p class="text-muted">Head of Sales</p>
                        <p>Jane's dedication to client satisfaction has made her one of our most valuable team members.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="team-member">
                        <img src="https://img.freepik.com/premium-photo/cute-handsome-anime-boy-illustration_962508-54151.jpg" alt="Jay Thakkar">
                        <h4>Jay Thakkar</h4>
                        <p class="text-muted">Senior Property Consultant</p>
                        <p>Mike's market insight and negotiation skills have helped countless clients find their dream homes.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <h2 class="text-center mb-5">What Our Clients Say</h2>
            <div class="row">
                <div class="col-md-6 mb-4">
                    <div class="testimonial-card">
                        <div class="d-flex align-items-center mb-3">
                            <img src="https://images.unsplash.com/photo-1438761681033-6461ffad8d80" alt="Client 1">
                            <div>
                                <h5 class="mb-0">Sarah Williams</h5>
                                <small class="text-muted">First-time Homebuyer</small>
                            </div>
                        </div>
                        <p>"BrickByBrick made my first home buying experience incredibly smooth. Their team was supportive throughout the entire process."</p>
                    </div>
                </div>
                <div class="col-md-6 mb-4">
                    <div class="testimonial-card">
                        <div class="d-flex align-items-center mb-3">
                            <img src="https://images.unsplash.com/photo-1500648767791-00dcc994a43e" alt="Client 2">
                            <div>
                                <h5 class="mb-0">Robert Chen</h5>
                                <small class="text-muted">Property Investor</small>
                            </div>
                        </div>
                        <p>"As a property investor, I appreciate BrickByBrick's market knowledge and professional approach. They've helped me build a successful portfolio."</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>