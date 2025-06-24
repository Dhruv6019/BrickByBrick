<?php
require_once 'config.php';
session_start();

// Initialize search parameters
$location = isset($_GET['location']) ? trim($_GET['location']) : '';
$property_type = isset($_GET['property_type']) ? trim($_GET['property_type']) : '';
$max_price = isset($_GET['max_price']) && is_numeric($_GET['max_price']) ? (float)$_GET['max_price'] : 0;
$bedrooms = isset($_GET['bedrooms']) && is_numeric($_GET['bedrooms']) ? (int)$_GET['bedrooms'] : 0;
$bathrooms = isset($_GET['bathrooms']) && is_numeric($_GET['bathrooms']) ? (int)$_GET['bathrooms'] : 0;

// Build the SQL query
$sql = "SELECT p.*, pi.image_path, u.full_name as seller_name 
        FROM properties p 
        LEFT JOIN property_images pi ON p.id = pi.property_id AND pi.is_primary = 1 
        LEFT JOIN users u ON p.seller_id = u.id 
        WHERE p.status = 'approved'";
$params = [];
$types = "";

// Add search conditions
if (!empty($location)) {
    $sql .= " AND p.location LIKE ?";
    $location_param = "%$location%";
    $params[] = &$location_param;
    $types .= "s";
}

if (!empty($property_type)) {
    $sql .= " AND p.property_type = ?";
    $params[] = &$property_type;
    $types .= "s";
}

if ($max_price > 0) {
    $sql .= " AND p.price <= ?";
    $params[] = &$max_price;
    $types .= "d";
}

if ($bedrooms > 0) {
    $sql .= " AND p.bedrooms >= ?";
    $params[] = &$bedrooms;
    $types .= "i";
}

if ($bathrooms > 0) {
    $sql .= " AND p.bathrooms >= ?";
    $params[] = &$bathrooms;
    $types .= "i";
}

// Order by newest first
$sql .= " ORDER BY p.created_at DESC";

// Prepare and execute the statement
$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();
$properties = $result->fetch_all(MYSQLI_ASSOC);
$total_results = count($properties);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results - Real Estate</title>
    <link rel="stylesheet" href="css/vendor.css">
</head>
<body>
    <!-- Back to top button removed -->
    
    <?php include 'includes/navbar.php'; ?>

    <div class="container py-5">
        <h1 class="mb-4">Search Results</h1>
        
        <!-- Search filters -->
        <div class="card mb-4">
            <div class="card-body">
                <form action="search.php" method="GET" class="row g-3">
                    <div class="col-md-3">
                        <input type="text" class="form-control" name="location" placeholder="Location" value="<?php echo htmlspecialchars($location); ?>">
                    </div>
                    <div class="col-md-2">
                        <select class="form-select" name="property_type">
                            <option value="">Property Type</option>
                            <option value="house" <?php echo $property_type === 'house' ? 'selected' : ''; ?>>House</option>
                            <option value="apartment" <?php echo $property_type === 'apartment' ? 'selected' : ''; ?>>Apartment</option>
                            <option value="land" <?php echo $property_type === 'land' ? 'selected' : ''; ?>>Land</option>
                            <option value="commercial" <?php echo $property_type === 'commercial' ? 'selected' : ''; ?>>Commercial</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="number" class="form-control" name="max_price" placeholder="Max Price" value="<?php echo $max_price > 0 ? $max_price : ''; ?>">
                    </div>
                    <div class="col-md-2">
                        <select class="form-select" name="bedrooms">
                            <option value="">Bedrooms</option>
                            <option value="1" <?php echo $bedrooms === 1 ? 'selected' : ''; ?>>1+</option>
                            <option value="2" <?php echo $bedrooms === 2 ? 'selected' : ''; ?>>2+</option>
                            <option value="3" <?php echo $bedrooms === 3 ? 'selected' : ''; ?>>3+</option>
                            <option value="4" <?php echo $bedrooms === 4 ? 'selected' : ''; ?>>4+</option>
                            <option value="5" <?php echo $bedrooms === 5 ? 'selected' : ''; ?>>5+</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select class="form-select" name="bathrooms">
                            <option value="">Bathrooms</option>
                            <option value="1" <?php echo $bathrooms === 1 ? 'selected' : ''; ?>>1+</option>
                            <option value="2" <?php echo $bathrooms === 2 ? 'selected' : ''; ?>>2+</option>
                            <option value="3" <?php echo $bathrooms === 3 ? 'selected' : ''; ?>>3+</option>
                            <option value="4" <?php echo $bathrooms === 4 ? 'selected' : ''; ?>>4+</option>
                        </select>
                    </div>
                    <div class="col-md-1">
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Results count -->
        <p class="mb-4"><?php echo $total_results; ?> properties found</p>
        
        <!-- Results -->
        <?php if ($total_results > 0): ?>
            <div class="row row-cols-1 row-cols-md-3 g-4">
                <?php foreach ($properties as $property): ?>
                <div class="col">
                    <div class="card h-100 carousel-inner rounded-3 overflow-hidden shadow">
                    <img src="http://localhost/newreal/uploads/properties/<?php echo htmlspecialchars($property['image_path'] ?? 'default.jpg'); ?>"
                             class="card-img-top" alt="<?php echo htmlspecialchars($property['title']); ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($property['title']); ?></h5>
                            <p class="card-text text-muted"><?php echo htmlspecialchars($property['location']); ?></p>
                            <p class="card-text"><strong>$<?php echo number_format($property['price']); ?></strong></p>
                            <div class="property-features">
                                <?php if (isset($property['bedrooms']) && $property['bedrooms'] > 0): ?>
                                <span><i class="fas fa-bed"></i> <?php echo $property['bedrooms']; ?> bd</span>
                                <?php endif; ?>
                                <?php if (isset($property['bathrooms']) && $property['bathrooms'] > 0): ?>
                                <span><i class="fas fa-bath"></i> <?php echo $property['bathrooms']; ?> ba</span>
                                <?php endif; ?>
                                <?php if (isset($property['area']) && $property['area'] > 0): ?>
                                <span><i class="fas fa-vector-square"></i> <?php echo $property['area']; ?> sqft</span>
                                <?php endif; ?>
                            </div>
                            <div class="mt-3">
                                <a href="property.php?id=<?php echo $property['id']; ?>" class="btn btn-primary">View Details</a>
                                
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-info">No properties found matching your criteria. Try adjusting your search filters.</div>
        <?php endif; ?>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/main.js"></script>
</body>
</html>