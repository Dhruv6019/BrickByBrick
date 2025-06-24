<?php
require_once 'config.php';
session_start();

// Check if property ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit();
}

$property_id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'] ?? 0;

// Fetch property details
$sql = "SELECT p.*, u.full_name as seller_name, u.email as seller_email, u.phone as seller_phone 
        FROM properties p 
        LEFT JOIN users u ON p.seller_id = u.id 
        WHERE p.id = ? AND p.status = 'approved'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $property_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: index.php');
    exit();
}

$property = $result->fetch_assoc();

// Fetch property images
$sql = "SELECT * FROM property_images WHERE property_id = ? ORDER BY is_primary DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $property_id);
$stmt->execute();
$images_result = $stmt->get_result();
$images = $images_result->fetch_all(MYSQLI_ASSOC);

// Check if property is in user's favorites
$is_favorite = false;
if ($user_id > 0) {
    $sql = "SELECT id FROM favorites WHERE user_id = ? AND property_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user_id, $property_id);
    $stmt->execute();
    $favorite_result = $stmt->get_result();
    $is_favorite = ($favorite_result->num_rows > 0);
}

// Fetch similar properties based on property type and price range
$price_range = 0.2; // 20% price range
$min_price = $property['price'] * (1 - $price_range);
$max_price = $property['price'] * (1 + $price_range);

$similar_properties_sql = "SELECT p.*, pi.image_path 
                          FROM properties p 
                          LEFT JOIN property_images pi ON p.id = pi.property_id AND pi.is_primary = 1 
                          WHERE p.id != ? 
                          AND p.property_type = ? 
                          AND p.price BETWEEN ? AND ? 
                          AND p.status = 'approved' 
                          LIMIT 2";
$stmt = $conn->prepare($similar_properties_sql);
$stmt->bind_param("isdd", $property_id, $property['property_type'], $min_price, $max_price);
$stmt->execute();
$similar_properties_result = $stmt->get_result();

// Handle add/remove from favorites
if (isset($_POST['toggle_favorite']) && $user_id > 0) {
    if ($is_favorite) {
        // Remove from favorites
        $sql = "DELETE FROM favorites WHERE user_id = ? AND property_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $user_id, $property_id);
        $stmt->execute();
        $is_favorite = false;
    } else {
        // Add to favorites
        $sql = "INSERT INTO favorites (user_id, property_id) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $user_id, $property_id);
        $stmt->execute();
        $is_favorite = true;
    }
    
    // Redirect to avoid form resubmission
    header("Location: property.php?id=$property_id");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($property['title']); ?> - BrickByBrick</title>
    <link rel="stylesheet" href="css/vendor.css">
    
</head>
<body>
    
    <?php include 'includes/navbar.php'; ?>

    <div class="container py-5">
        <div class="row">
            <div class="col-md-8 mb-4">
                <?php if (count($images) > 0): ?>
                <div id="propertyCarousel" class="property-images carousel slide mb-4" data-bs-ride="carousel">
                    <div class="carousel-inner rounded-3 overflow-hidden shadow">
                        <?php foreach ($images as $index => $image): ?>
                            <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                                <img src="http://localhost/newreal/uploads/properties/<?php echo htmlspecialchars($image['image_path']); ?>" class="d-block w-100 property-detail-img" alt="Property Image">
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php if (count($images) > 1): ?>
                        <button class="carousel-control-prev" type="button" data-bs-target="#propertyCarousel" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Previous</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#propertyCarousel" data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Next</span>
                        </button>
                        <div class="carousel-indicators custom-indicators">
                            <?php foreach ($images as $index => $image): ?>
                                <button type="button" data-bs-target="#propertyCarousel" data-bs-slide-to="<?php echo $index; ?>" class="<?php echo $index === 0 ? 'active' : ''; ?>" aria-current="<?php echo $index === 0 ? 'true' : 'false'; ?>" aria-label="Slide <?php echo $index + 1; ?>"></button>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="rounded-3 overflow-hidden shadow mb-4">
                    <img src="https://via.placeholder.com/800x500.png?text=No+Image+Available" class="img-fluid w-100 property-detail-img" alt="Property Image">
                </div>
            <?php endif; ?>

            <div class="property-details-card card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h1 class="property-title mb-0"><?php echo htmlspecialchars($property['title']); ?></h1>
                        <div class="d-flex align-items-center">
                            <?php if(isset($_SESSION['user_id'])): ?>
                            <div class="action-buttons me-3">
                                <!-- Favorite button -->
                                <form method="post" class="d-inline-block">
                                    <input type="hidden" name="toggle_favorite" value="1">
                                    <button type="submit" class="btn btn-sm btn-outline-danger favorite-btn rounded-circle" data-bs-toggle="tooltip" title="<?php echo $is_favorite ? 'Remove from Favorites' : 'Add to Favorites'; ?>">
                                        <i class="<?php echo $is_favorite ? 'fas' : 'far'; ?> fa-heart"></i>
                                    </button>
                                </form>
                                
                                <!-- Compare button -->
                                <button class="btn btn-sm btn-outline-primary compare-property-btn rounded-circle ms-2" data-property-id="<?php echo $property_id; ?>" data-bs-toggle="tooltip" title="Add to Compare">
                                    <i class="fas fa-balance-scale"></i>
                                </button>
                            </div>
                            <?php endif; ?>
                            <div class="price-badge badge bg-primary fs-5 px-3 py-2">₹<?php echo number_format($property['price']); ?></div>
                        </div>
                    </div>
                    <p class="location mb-4"><i class="fas fa-map-marker-alt me-2 text-primary"></i><?php echo htmlspecialchars($property['location']); ?></p>

                    <div class="row mb-4">
                        <div class="col-md-3 col-6 mb-3">
                            <div class="property-feature text-center p-3 border rounded h-100">
                                <i class="fas fa-home fa-2x mb-2 text-primary"></i>
                                <p class="mb-0 text-muted">Type</p>
                                <h5 class="mt-1"><?php echo ucfirst($property['property_type']); ?></h5>
                            </div>
                        </div>
                        <?php if (isset($property['bedrooms']) && $property['bedrooms'] > 0): ?>
                        <div class="col-md-3 col-6 mb-3">
                            <div class="property-feature text-center p-3 border rounded h-100">
                                <i class="fas fa-bed fa-2x mb-2 text-primary"></i>
                                <p class="mb-0 text-muted">Bedrooms</p>
                                <h5 class="mt-1"><?php echo $property['bedrooms']; ?></h5>
                            </div>
                        </div>
                        <?php endif; ?>
                        <?php if (isset($property['bathrooms']) && $property['bathrooms'] > 0): ?>
                        <div class="col-md-3 col-6 mb-3">
                            <div class="property-feature text-center p-3 border rounded h-100">
                                <i class="fas fa-bath fa-2x mb-2 text-primary"></i>
                                <p class="mb-0 text-muted">Bathrooms</p>
                                <h5 class="mt-1"><?php echo $property['bathrooms']; ?></h5>
                            </div>
                        </div>
                        <?php endif; ?>
                        <?php if (isset($property['area']) && $property['area'] > 0): ?>
                        <div class="col-md-3 col-6 mb-3">
                            <div class="property-feature text-center p-3 border rounded h-100">
                                <i class="fas fa-vector-square fa-2x mb-2 text-primary"></i>
                                <p class="mb-0 text-muted">Area</p>
                                <h5 class="mt-1"><?php echo $property['area']; ?> sqft</h5>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <div class="description-section mb-4">
                        <h4 class="section-title mb-3">Description</h4>
                        <div class="description-content">
                            <?php echo nl2br(htmlspecialchars($property['description'])); ?>
                        </div>
                    </div>

                    <?php if (!empty($property['amenities'])): ?>
                    <div class="amenities-section mb-4">
                        <h4 class="section-title mb-3">Amenities</h4>
                        <div class="row">
                            <?php 
                            $amenities = explode(',', $property['amenities']);
                            foreach ($amenities as $amenity): 
                                $amenity = trim($amenity);
                                if (!empty($amenity)):
                            ?>
                            <div class="col-md-4 col-6 mb-2">
                                <div class="amenity-item">
                                    <i class="fas fa-check-circle text-primary me-2"></i>
                                    <?php echo htmlspecialchars($amenity); ?>
                                </div>
                            </div>
                            <?php 
                                endif;
                            endforeach; 
                            ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Location Map Section -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h4 class="section-title mb-3">Location</h4>
                    <div class="map-container rounded overflow-hidden">
                        <iframe width="100%" height="400" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" 
                            src="https://maps.google.com/maps?q=<?php echo urlencode($property['location']); ?>&output=embed">
                        </iframe>
                    </div>
                </div>
            </div>

            
                <?php if ($similar_properties_result->num_rows > 0): ?>
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <h4 class="card-title mb-4">Similar Properties</h4>
                        <?php while ($similar_property = $similar_properties_result->fetch_assoc()): ?>
                        <div class="similar-property mb-3">
                            <a href="property.php?id=<?php echo $similar_property['id']; ?>" class="text-decoration-none">
                                <div class="card border-0">
                                    <img src="http://localhost/newreal/uploads/properties/<?php echo htmlspecialchars($similar_property['image_path'] ?? 'default.jpg'); ?>" class="card-img-top rounded" alt="Similar Property">
                                    <div class="card-body px-0 pb-0">
                                        <h6 class="card-title text-dark mb-1"><?php echo htmlspecialchars($similar_property['title']); ?></h6>
                                        <p class="text-primary mb-0">₹<?php echo number_format($similar_property['price']); ?></p>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            <div class="col-md-4">
                <!-- Seller Information Card -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <h4 class="section-title mb-3">Seller Information</h4>
                        <div class="seller-info">
                            <div class="d-flex align-items-center mb-3">
                                <div class="seller-avatar me-3">
                                    <i class="fas fa-user-circle fa-3x text-secondary"></i>
                                </div>
                                <div>
                                    <h5 class="mb-1"><?php echo htmlspecialchars($property['seller_name']); ?></h5>
                                    <p class="text-muted mb-0"><i class="fas fa-phone-alt me-2 small"></i><?php echo htmlspecialchars($property['seller_phone']); ?></p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Contact Form -->
                        <h5 class="mt-4 mb-3">Interested in this property?</h5>
                        <form id="propertyInquiryForm">
                            <input type="hidden" name="property_id" value="<?php echo $property_id; ?>">
                            <div class="mb-3">
                                <input type="text" class="form-control" name="name" placeholder="Your Name" required>
                            </div>
                            <div class="mb-3">
                                <input type="email" class="form-control" name="email" placeholder="Your Email" required>
                            </div>
                            <div class="mb-3">
                                <input type="tel" class="form-control" name="phone" placeholder="Your Phone">
                            </div>
                            <div class="mb-3">
                                <textarea class="form-control" name="message" rows="4" placeholder="I'm interested in this property. Please contact me." required></textarea>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Send Inquiry</button>
                            </div>
                        </form>
                    </div>
                </div>
                
                            
                <!-- Visit Enquiry Form -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <h4 class="section-title mb-3">Schedule a Visit</h4>
                        <form id="visitEnquiryForm">
                            <input type="hidden" name="property_id" value="<?php echo $property_id; ?>">
                            <div class="mb-3">
                                <input type="text" class="form-control" name="visit_name" placeholder="Your Name" required>
                            </div>
                            <div class="mb-3">
                                <input type="email" class="form-control" name="visit_email" placeholder="Your Email" required>
                            </div>
                            <div class="mb-3">
                                <input type="tel" class="form-control" name="visit_phone" placeholder="Your Phone" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Preferred Date</label>
                                <input type="date" class="form-control" name="visit_date" min="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Preferred Time</label>
                                <select class="form-select" name="visit_time" required>
                                    <option value="">Select Time</option>
                                    <option value="morning">Morning (9AM - 12PM)</option>
                                    <option value="afternoon">Afternoon (12PM - 4PM)</option>
                                    <option value="evening">Evening (4PM - 7PM)</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <textarea class="form-control" name="visit_notes" rows="2" placeholder="Any special requests or questions?"></textarea>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-success">Request Visit</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <!-- Comparison Modal -->
    <div class="modal fade" id="comparisonModal" tabindex="-1" aria-labelledby="comparisonModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="comparisonModalLabel">Property Comparison</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="comparisonContent">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-3">Loading comparison data...</p>
                    </div>
                </div>
            </div>
                <?php if ($similar_properties_result->num_rows > 0): ?>
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <h4 class="card-title mb-4">Similar Properties</h4>
                        <?php while ($similar_property = $similar_properties_result->fetch_assoc()): ?>
                        <div class="similar-property mb-3">
                            <a href="property.php?id=<?php echo $similar_property['id']; ?>" class="text-decoration-none">
                                <div class="card border-0">
                                    <img src="uploads/<?php echo htmlspecialchars($similar_property['image_path'] ?? 'default.jpg'); ?>" class="card-img-top rounded" alt="Similar Property">
                                    <div class="card-body px-0 pb-0">
                                        <h6 class="card-title text-dark mb-1"><?php echo htmlspecialchars($similar_property['title']); ?></h6>
                                        <p class="text-primary mb-0">$<?php echo number_format($similar_property['price']); ?></p>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Comparison Bar -->
    <?php include 'includes/comparison-bar.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="js/virtual-tour-enhanced.js"></script>
    <script src="js/visit-enquiry.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            });
            
            // Initialize property comparison
            initPropertyComparison();
            
            // Check if property is in comparison list
            const propertyId = <?php echo $property_id; ?>;
            const comparedProperties = getComparedProperties();
            if (comparedProperties.includes(propertyId.toString())) {
                $('.compare-property-btn').addClass('active');
            }
        });
    </script>
</body>
</html>