<?php
require_once 'config.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle remove from favorites
if (isset($_POST['remove_favorite']) && isset($_POST['property_id'])) {
    $property_id = (int)$_POST['property_id'];
    
    $sql = "DELETE FROM favorites WHERE user_id = ? AND property_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user_id, $property_id);
    $stmt->execute();
}

// Fetch user's favorite properties
$sql = "SELECT p.*, pi.image_path, u.full_name as seller_name, f.id as favorite_id 
        FROM favorites f 
        JOIN properties p ON f.property_id = p.id 
        LEFT JOIN property_images pi ON p.id = pi.property_id AND pi.is_primary = 1 
        LEFT JOIN users u ON p.seller_id = u.id 
        WHERE f.user_id = ? AND p.status = 'approved' 
        ORDER BY f.created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Favorites - BrickByBrick</title>
   <link rel="stylesheet" href="css/vendor.css">
    
</head>
<body>
    <!-- Back to top button removed -->
    
    <?php include 'includes/navbar.php'; ?>

    <div class="container py-5">
        <h1 class="mb-4">My Favorite Properties</h1>
        
        <?php if ($result->num_rows > 0): ?>
            <div class="row row-cols-1 row-cols-md-3 g-4">
                <?php while($property = $result->fetch_assoc()): ?>
                <div class="col">
                    <div class="card h-100">
                        <img src="<?php echo !empty($property['image_path']) ? 'uploads/properties/' . $property['image_path'] : 'images/default-property.svg'; ?>" 
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
                            <div class="mt-3 d-flex justify-content-between">
                                <a href="property.php?id=<?php echo $property['id']; ?>" class="btn btn-primary">View Details</a>
                                <div>
                                    <button class="btn btn-outline-secondary compare-property-btn me-2" data-property-id="<?php echo $property['id']; ?>" data-bs-toggle="tooltip" title="Add to Compare">
                                        <i class="fas fa-balance-scale"></i>
                                    </button>
                                    <form method="post" class="d-inline">
                                        <input type="hidden" name="property_id" value="<?php echo $property['id']; ?>">
                                        <button type="submit" name="remove_favorite" class="btn btn-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                <p>You don't have any favorite properties yet.</p>
                <a href="index.php" class="btn btn-primary mt-2">Browse Properties</a>
            </div>
        <?php endif; ?>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="js/main.js"></script>
    <script src="js/enhanced-main.js"></script>
</body>
</html>
<img src="<?php echo !empty($property['image_path']) ? 'uploads/properties/' . $property['image_path'] : 'images/default-property.svg'; ?>"