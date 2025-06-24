<?php
require_once 'config.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user's properties
$sql = "SELECT p.*, pi.image_path 
        FROM properties p 
        LEFT JOIN property_images pi ON p.id = pi.property_id AND pi.is_primary = 1 
        WHERE p.seller_id = ? 
        ORDER BY p.created_at DESC";
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
    <title>My Properties - BrickByBrick</title>

    <link rel="stylesheet" href="css/vendor.css">
</head>
<body>

    <?php include 'includes/navbar.php'; ?>

    <div class="container py-5">
        <h1 class="mb-4">My Properties</h1>
        
        <?php if ($result->num_rows > 0): ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Title</th>
                            <th>Price</th>
                            <th>Location</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Date Added</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($property = $result->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <img src="<?php echo !empty($property['image_path']) ? 'uploads/properties/' . $property['image_path'] : 'images/default-property.svg'; ?>"
                                         alt="Property" class="img-thumbnail" style="width: 80px; height: 60px; object-fit: cover;">
                                </td>
                                <td><?php echo htmlspecialchars($property['title']); ?></td>
                                <td>$<?php echo number_format($property['price']); ?></td>
                                <td><?php echo htmlspecialchars($property['location']); ?></td>
                                <td><?php echo ucfirst($property['property_type']); ?></td>
                                <td>
                                    <?php if ($property['status'] === 'pending'): ?>
                                        <span class="badge bg-warning">Pending</span>
                                    <?php elseif ($property['status'] === 'approved'): ?>
                                        <span class="badge bg-success">Approved</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Rejected</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($property['created_at'])); ?></td>
                                <td>
                                    <a href="property.php?id=<?php echo $property['id']; ?>" class="btn btn-sm btn-primary">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <?php if ($property['status'] !== 'approved'): ?>
                                        <a href="edit-property.php?id=<?php echo $property['id']; ?>" class="btn btn-sm btn-secondary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                <p>You haven't added any properties yet.</p>
                <a href="add-property.php" class="btn btn-primary mt-2">Add Your First Property</a>
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