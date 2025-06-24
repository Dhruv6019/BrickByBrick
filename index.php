<?php
require_once 'config.php';
session_start();

// Fetch approved properties
$sql = "SELECT p.*, pi.image_path, u.full_name as seller_name 
FROM properties p 
LEFT JOIN property_images pi ON p.id = pi.property_id AND pi.is_primary = 1 
LEFT JOIN users u ON p.seller_id = u.id 
WHERE p.status = 'approved' 
ORDER BY p.created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BrickByBrick - Find Your Dream Property</title>
 
    <link rel="stylesheet" href="css/vendor.css">
    <style>
        .property-slider {
            position: relative;
            overflow: hidden;
        }
        .property-slide-btn {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            z-index: 10;
            padding: 10px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.9);
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            display: block !important;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .property-slide-btn:hover {
            background: rgba(255, 255, 255, 1);
            box-shadow: 0 4px 8px rgba(0,0,0,0.3);
        }
        .slide-prev {
            left: 10px;
        }
        .slide-next {
            right: 10px;
        }
        .property-slider .row {
            transition: transform 0.3s ease;
        }
    </style>
 
</head>
<body>
    
    <?php include 'includes/navbar.php'; ?>

    <div class="hero-section text-center" style="background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.7)), url('http://localhost/newreal/uploads/images/hero-bg.old.jpg'); background-size: cover; background-position: center; background-attachment: fixed;">
        <div class="container">
            <div class="hero-content">
                <h1 class=" fw-bold mb-4 text-white">Find Your Dream Property</h1>
                <p class="lead mb-5 display-3 text-white-50">Discover the perfect home that matches your lifestyle and preferences with our extensive collection of premium properties</p>
                <div class="search-box bg-white p-4 rounded-lg shadow-lg">
                    <form action="search.php" method="GET" class="row g-3">
                        <div class="col-md-4">
                            <div class="input-group">
                                <span class="input-group-text bg-transparent border-0"><i class="fas fa-map-marker-alt text-primary"></i></span>
                                <input type="text" class="form-control border-0 shadow-none" name="location" placeholder="Enter Location">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="input-group">
                                <span class="input-group-text bg-transparent border-0"><i class="fas fa-home text-primary"></i></span>
                                <select class="form-select border-0 shadow-none" name="property_type">
                                    <option value="">Property Type</option>
                                    <option value="house">House</option>
                                    <option value="apartment">Apartment</option>
                                    
                                    <option value="land">Land</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="input-group">
                                <span class="input-group-text bg-transparent border-0"><i class="fas fa-indian-rupee-sign text-primary"></i></span>
                                <select class="form-select border-0 shadow-none" name="price_range">
                                    <option value="">Price Range</option>
                                    <option value="0-100000">₹0 - ₹100,000</option>
                                    <option value="100000-300000">₹100,000 - ₹300,000</option>
                                    <option value="300000-500000">₹300,000 - ₹500,000</option>
                                    <option value="500000-1000000">₹500,000 - ₹1,000,000</option>
                                    <option value="1000000+">₹1,000,000+</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100 d-flex align-items-center justify-content-center gap-2">
                                <i class="fas fa-search"></i> Search
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Stats Section -->
    <div class="container py-5">
        <div class="row text-center">
            <div class="col-md-3 mb-4 mb-md-0">
                <div class="stat-item">
                    <i class="fas fa-home fa-3x mb-3 text-primary"></i>
                    <h3 class="counter" data-count="1500">0</h3>
                    <p>Properties Available</p>
                </div>
            </div>
            <div class="col-md-3 mb-4 mb-md-0">
                <div class="stat-item">
                    <i class="fas fa-users fa-3x mb-3 text-primary"></i>
                    <h3 class="counter" data-count="750">0</h3>
                    <p>Happy Customers</p>
                </div>
            </div>
            <div class="col-md-3 mb-4 mb-md-0">
                <div class="stat-item">
                    <i class="fas fa-award fa-3x mb-3 text-primary"></i>
                    <h3 class="counter" data-count="25">0</h3>
                    <p>Years of Experience</p>
                </div>
            </div>
            <div class="col-md-3 mb-4 mb-md-0">
                <div class="stat-item">
                    <i class="fas fa-city fa-3x mb-3 text-primary"></i>
                    <h3 class="counter" data-count="120">0</h3>
                    <p>Cities Covered</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Featured Properties Section -->
    <div class="container py-5">
        <div class="row mb-4 ">
            <div class="col-md-8 ">
                <h2 class="section-title">Featured Properties</h2>
                <p class="text-muted">Explore our handpicked selection of premium properties</p>
            </div>
            <div class="col-md-4 text-md-end">
                <div class="property-type-filter-container">
                    <button class="btn btn-sm btn-outline-primary me-2 property-type-filter active" data-filter="all">All</button>
                    <button class="btn btn-sm btn-outline-primary me-2 property-type-filter" data-filter="house">Houses</button>
                    <button class="btn btn-sm btn-outline-primary me-2 property-type-filter" data-filter="apartment">Apartments</button>
                    <button class="btn btn-sm btn-outline-primary property-type-filter" data-filter="land">Land</button>
                </div>
            </div>
        </div>
   
    
        <div class="position-relative">
            
            <div class="property-slider">
                <div class="row flex-nowrap" style="overflow-x: hidden;">
                   
            <?php 
            if ($result && $result->num_rows > 0) {
                while($property = $result->fetch_assoc()) {
                    $image = $property['image_path'] ? 'uploads/properties/' . $property['image_path'] : 'https://via.placeholder.com/800x600.png?text=No+Image';
                    $propertyType = isset($property['property_type']) ? $property['property_type'] : 'house';
            ?>
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="property-card card h-100" data-type="<?php echo htmlspecialchars($propertyType); ?>">
                    <div class="position-relative overflow-hidden">
                        <img src="<?php echo htmlspecialchars($image); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($property['title']); ?>">
                        <span class="badge bg-primary position-absolute top-0 start-0 m-3">
                            <?php echo ucfirst(htmlspecialchars($property['listing_type'] ?? 'For Sale')); ?>
                        </span>
                        <?php if(isset($property['created_at']) && (time() - strtotime($property['created_at'])) < 604800): ?>
                        <span class="badge bg-success position-absolute top-0 end-0 m-3">New</span>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($property['title']); ?></h5>
                        <p class="card-text text-muted mb-2"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($property['location']); ?></p>
                        <div class="price mb-3">₹<?php echo number_format($property['price']); ?></div>
                        <div class="property-features">
                            <?php if(isset($property['bedrooms'])): ?>
                            <span><i class="fas fa-bed"></i> <?php echo htmlspecialchars($property['bedrooms']); ?> Beds</span>
                            <?php endif; ?>
                            <?php if(isset($property['bathrooms'])): ?>
                            <span><i class="fas fa-bath"></i> <?php echo htmlspecialchars($property['bathrooms']); ?> Baths</span>
                            <?php endif; ?>
                            <?php if(isset($property['area'])): ?>
                            <span><i class="fas fa-ruler-combined"></i> <?php echo htmlspecialchars($property['area']); ?> sqft</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="card-footer d-flex justify-content-between align-items-center">
                        <a href="property.php?id=<?php echo $property['id']; ?>" class="btn btn-sm btn-outline-primary">View Details</a>                        
                    </div>
                </div>
            </div>
            
            <?php 
                }
            } else {
            ?>
            <div class="col-12 text-center py-5">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i> No properties found. Check back soon for new listings!
                </div>
            </div>
            <?php
            }
            ?>
        </div>
    </div>
    
    <!-- Property Statistics Section -->
    <div class="container py-5">
        <div class="text-center mb-5">
            <h2 class="section-title">Real Estate Market Insights</h2>
            <p class="text-muted">Stay informed with the latest trends and statistics in the real estate market</p>
        </div>
        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-chart-line text-primary me-2"></i>Price Trends</h5>
                        <div class="market-stats-container">
                            <div class="d-flex justify-content-between mb-3">
                                <span>Average Home Price:</span>
                                <span class="fw-bold">₹425,000</span>
                            </div>
                            <div class="d-flex justify-content-between mb-3">
                                <span>Monthly Change:</span>
                                <span class="text-success fw-bold">+1.2%</span>
                            </div>
                            <div class="d-flex justify-content-between mb-3">
                                <span>Annual Appreciation:</span>
                                <span class="text-success fw-bold">+8.5%</span>
                            </div>
                            <div class="progress mt-2 mb-4" style="height: 8px;">
                                <div class="progress-bar bg-primary" role="progressbar" style="width: 85%" aria-valuenow="85" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>
                        <a href="#" class="btn btn-sm btn-outline-primary">View Full Report</a>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-home text-primary me-2"></i>Inventory Analysis</h5>
                        <div class="market-stats-container">
                            <div class="d-flex justify-content-between mb-3">
                                <span>Available Properties:</span>
                                <span class="fw-bold">1,245</span>
                            </div>
                            <div class="d-flex justify-content-between mb-3">
                                <span>New Listings (Monthly):</span>
                                <span class="fw-bold">320</span>
                            </div>
                            <div class="d-flex justify-content-between mb-3">
                                <span>Average Days on Market:</span>
                                <span class="fw-bold">32 days</span>
                            </div>
                            <div class="progress mt-2 mb-4" style="height: 8px;">
                                <div class="progress-bar bg-success" role="progressbar" style="width: 65%" aria-valuenow="65" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>
                        <a href="#" class="btn btn-sm btn-outline-primary">View Inventory</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
  </div>  
  </div>
    <!-- Property Investment Calculator -->
    <div class="bg-light py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title">Property Investment Calculator</h2>
                <p class="text-muted">Estimate your potential returns and make informed investment decisions</p>
            </div>
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="card shadow">
                        <div class="card-body p-4">
                            <form id="investment-calculator-form" class="row g-3">
                                <div class="col-md-6">
                                    <label for="property-price" class="form-label">Property Price (₹)</label>
                                    <input type="number" class="form-control" id="property-price" value="300000">
                                </div>
                                <div class="col-md-6">
                                    <label for="down-payment" class="form-label">Down Payment (%)</label>
                                    <input type="number" class="form-control" id="down-payment" value="20">
                                </div>
                                <div class="col-md-4">
                                    <label for="interest-rate" class="form-label">Interest Rate (%)</label>
                                    <input type="number" step="0.1" class="form-control" id="interest-rate" value="4.5">
                                </div>
                                <div class="col-md-4">
                                    <label for="loan-term" class="form-label">Loan Term (years)</label>
                                    <input type="number" class="form-control" id="loan-term" value="30">
                                </div>
                                <div class="col-md-4">
                                    <label for="rental-income" class="form-label">Monthly Rental Income ($)</label>
                                    <input type="number" class="form-control" id="rental-income" value="2000">
                                </div>
                                <div class="col-12 text-center mt-4">
                                    <button type="button" id="calculate-investment" class="btn btn-primary px-4">Calculate</button>
                                </div>
                            </form>
                            
                            <div id="investment-results" class="mt-4 pt-4 border-top" style="display: none;">
                                <h5 class="mb-4">Investment Analysis</h5>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="result-item mb-3">
                                            <div class="d-flex justify-content-between">
                                                <span>Monthly Mortgage:</span>
                                                <span id="monthly-mortgage" class="fw-bold">₹1,520</span>
                                            </div>
                                        </div>
                                        <div class="result-item mb-3">
                                            <div class="d-flex justify-content-between">
                                                <span>Cash Flow:</span>
                                                <span id="cash-flow" class="fw-bold text-success">₹480</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="result-item mb-3">
                                            <div class="d-flex justify-content-between">
                                                <span>Cap Rate:</span>
                                                <span id="cap-rate" class="fw-bold">8.0%</span>
                                            </div>
                                        </div>
                                        <div class="result-item mb-3">
                                            <div class="d-flex justify-content-between">
                                                <span>ROI (Annual):</span>
                                                <span id="annual-roi" class="fw-bold text-success">9.6%</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    
    
    <!-- Why Choose Us Section -->
<div class="container my-5">
    <h2 class="text-center mb-4 py-5">Why Choose Us?</h2>
    <div class="row text-center">
        
        <!-- Verified Listings -->
        <div class="col-md-3">
            <i class="fas fa-check-circle fa-3x text-primary"></i>
            <h4 class="mt-3">Verified Listings</h4>
            <p>All properties are legally verified and approved for a hassle-free experience.</p>
        </div>

        <!-- 24/7 Support -->
        <div class="col-md-3">
            <i class="fas fa-headset fa-3x text-success"></i>
            <h4 class="mt-3">24/7 Support</h4>
            <p>Get instant assistance anytime with our dedicated support team.</p>
        </div>

        <!-- Expert Agents -->
        <div class="col-md-3">
            <i class="fas fa-user-tie fa-3x text-warning"></i>
            <h4 class="mt-3">Expert Agents</h4>
            <p>Our real estate experts guide you through every step of the process.</p>
        </div>

        <!-- Affordable Pricing -->
        <div class="col-md-3">
            <i class="fas fa-hand-holding-usd fa-3x text-danger"></i>
            <h4 class="mt-3">Affordable Pricing</h4>
            <p>Get the best deals with competitive pricing and flexible payment options.</p>
        </div>

    </div>
</div>

</div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
    <script src="js/main.js"></script>
    <script src="js/enhanced-main.js"></script>
    <script src="js/virtual-tour-enhanced.js"></script>
    <script>
    $(document).ready(function() {
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
        
        // Property Investment Calculator
        $('#calculate-investment').click(function() {
            // Get input values
            const propertyPrice = parseFloat($('#property-price').val());
            const downPaymentPercent = parseFloat($('#down-payment').val());
            const interestRate = parseFloat($('#interest-rate').val());
            const loanTerm = parseInt($('#loan-term').val());
            const rentalIncome = parseFloat($('#rental-income').val());
            
            // Calculate mortgage and investment metrics
            const downPayment = propertyPrice * (downPaymentPercent / 100);
            const loanAmount = propertyPrice - downPayment;
            const monthlyInterest = interestRate / 100 / 12;
            const totalPayments = loanTerm * 12;
            
            // Calculate monthly mortgage payment
            const monthlyMortgage = loanAmount * monthlyInterest * Math.pow(1 + monthlyInterest, totalPayments) / 
                                  (Math.pow(1 + monthlyInterest, totalPayments) - 1);
            
            // Calculate cash flow
            const cashFlow = rentalIncome - monthlyMortgage;
            
            // Calculate cap rate
            const annualRentalIncome = rentalIncome * 12;
            const capRate = (annualRentalIncome / propertyPrice) * 100;
            
            // Calculate ROI
            const annualCashFlow = cashFlow * 12;
            const roi = (annualCashFlow / downPayment) * 100;
            
            // Update results
            $('#monthly-mortgage').text('$' + monthlyMortgage.toFixed(2));
            $('#cash-flow').text('$' + cashFlow.toFixed(2));
            $('#cap-rate').text(capRate.toFixed(1) + '%');
            $('#annual-roi').text(roi.toFixed(1) + '%');
            
            // Show results
            $('#investment-results').slideDown();
        });
        
        // Handle favorite button clicks
        $('.favorite-btn').click(function() {
            var propertyId = $(this).data('property-id');
            var button = $(this);
        });
        
        // Property type filter
        $('.property-type-filter').click(function() {
            var filter = $(this).data('filter');
            $('.property-type-filter').removeClass('active');
            $(this).addClass('active');
            
            if(filter === 'all') {
                $('.property-card').parent().show();
            } else {
                $('.property-card').parent().hide();
                $('.property-card[data-type="' + filter + '"]').parent().show();
            }
        });
        
        // Advanced filter toggle
        $('#advanced-filter-toggle').click(function(e) {
            e.preventDefault();
            $('.advanced-filters').slideToggle();
            var icon = $(this).find('i');
            if(icon.hasClass('fa-chevron-down')) {
                icon.removeClass('fa-chevron-down').addClass('fa-chevron-up');
            } else {
                icon.removeClass('fa-chevron-up').addClass('fa-chevron-down');
            }
        });
        
        // Counter animation for stats
        $('.counter').each(function() {
            var $this = $(this);
            var countTo = parseInt($this.attr('data-count'));
            
            $({ countNum: 0 }).animate({
                countNum: countTo
            }, {
                duration: 2000,
                easing: 'swing',
                step: function() {
                    $this.text(Math.floor(this.countNum));
                },
                complete: function() {
                    $this.text(this.countNum);
                }
            });
        });
    });
    </script>    
    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
    <script src="js/main.js"></script>
    <script src="js/enhanced-main.js"></script>
    <script src="js/virtual-tour-enhanced.js"></script>
    <script>
    $(document).ready(function() {
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
        
        // Property Investment Calculator
        $('#calculate-investment').click(function() {
            // Get input values
            const propertyPrice = parseFloat($('#property-price').val());
            const downPaymentPercent = parseFloat($('#down-payment').val());
            const interestRate = parseFloat($('#interest-rate').val());
            const loanTerm = parseInt($('#loan-term').val());
            const rentalIncome = parseFloat($('#rental-income').val());
            
            // Calculate mortgage and investment metrics
            const downPayment = propertyPrice * (downPaymentPercent / 100);
            const loanAmount = propertyPrice - downPayment;
            const monthlyInterest = interestRate / 100 / 12;
            const totalPayments = loanTerm * 12;
            
            // Calculate monthly mortgage payment
            const monthlyMortgage = loanAmount * monthlyInterest * Math.pow(1 + monthlyInterest, totalPayments) / 
                                  (Math.pow(1 + monthlyInterest, totalPayments) - 1);
            
            // Calculate cash flow
            const cashFlow = rentalIncome - monthlyMortgage;
            
            // Calculate cap rate
            const annualRentalIncome = rentalIncome * 12;
            const capRate = (annualRentalIncome / propertyPrice) * 100;
            
            // Calculate ROI
            const annualCashFlow = cashFlow * 12;
            const roi = (annualCashFlow / downPayment) * 100;
            
            // Update results
            $('#monthly-mortgage').text('$' + monthlyMortgage.toFixed(2));
            $('#cash-flow').text('$' + cashFlow.toFixed(2));
            $('#cap-rate').text(capRate.toFixed(1) + '%');
            $('#annual-roi').text(roi.toFixed(1) + '%');
            
            // Show results
            $('#investment-results').slideDown();
        });
        
        // Property type filter
        $('.property-type-filter').click(function() {
            var filter = $(this).data('filter');
            $('.property-type-filter').removeClass('active');
            $(this).addClass('active');
            
            if(filter === 'all') {
                $('.property-card').parent().show();
            } else {
                $('.property-card').parent().hide();
                $('.property-card[data-type="' + filter + '"]').parent().show();
            }
        });
        
        // Advanced filter toggle
        $('#advanced-filter-toggle').click(function(e) {
            e.preventDefault();
            $('.advanced-filters').slideToggle();
            var icon = $(this).find('i');
            if(icon.hasClass('fa-chevron-down')) {
                icon.removeClass('fa-chevron-down').addClass('fa-chevron-up');
            } else {
                icon.removeClass('fa-chevron-up').addClass('fa-chevron-down');
            }
        });
        
        // Counter animation for stats
        $('.counter').each(function() {
            var $this = $(this);
            var countTo = parseInt($this.attr('data-count'));
            
            $({ countNum: 0 }).animate({
                countNum: countTo
            }, {
                duration: 2000,
                easing: 'swing',
                step: function() {
                    $this.text(Math.floor(this.countNum));
                },
                complete: function() {
                    $this.text(this.countNum);
                }
            });
        });
    });
    </script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const slider = document.querySelector('.property-slider .row');
    const prevBtn = document.querySelector('.slide-prev');
    const nextBtn = document.querySelector('.slide-next');
    const propertyCards = document.querySelectorAll('.property-card');

    if (propertyCards.length > 3) {
        prevBtn.style.display = 'block';
        nextBtn.style.display = 'block';

        let currentPosition = 0;
        const cardWidth = propertyCards[0].offsetWidth;
        const maxScroll = (propertyCards.length - 3) * cardWidth;

        prevBtn.addEventListener('click', () => {
            currentPosition = Math.min(currentPosition + cardWidth, 0);
            slider.style.transform = `translateX(${currentPosition}px)`;
        });

        nextBtn.addEventListener('click', () => {
            currentPosition = Math.max(currentPosition - cardWidth, -maxScroll);
            slider.style.transform = `translateX(${currentPosition}px)`;
        });
    }
});
</script>
<?php include 'includes/footer.php'; ?>
</body>
</html>