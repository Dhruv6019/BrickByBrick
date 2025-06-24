<?php
require_once 'config.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Check if property ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: my-properties.php');
    exit();
}

$property_id = (int)$_GET['id'];

// Fetch property details and verify ownership
$sql = "SELECT * FROM properties WHERE id = ? AND seller_id = ? AND status != 'approved'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $property_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: my-properties.php');
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

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $price = (float)$_POST['price'];
    $location = trim($_POST['location']);
    $property_type = $_POST['property_type'];
    $bedrooms = isset($_POST['bedrooms']) ? (int)$_POST['bedrooms'] : null;
    $bathrooms = isset($_POST['bathrooms']) ? (int)$_POST['bathrooms'] : null;
    $area = isset($_POST['area']) ? (float)$_POST['area'] : null;
    
    // Validate input
    if (empty($title) || empty($description) || $price <= 0 || empty($location) || empty($property_type)) {
        $error = 'Please fill all required fields';
    } else {
        // Begin transaction
        $conn->begin_transaction();
        
        try {
            // Update property
            $sql = "UPDATE properties SET title = ?, description = ?, price = ?, location = ?, 
                    property_type = ?, bedrooms = ?, bathrooms = ?, area = ?, status = 'pending' 
                    WHERE id = ? AND seller_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssdssiidii", $title, $description, $price, $location, $property_type, 
                            $bedrooms, $bathrooms, $area, $property_id, $user_id);
            $stmt->execute();
            
            // Handle image uploads
            if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
                $upload_dir = 'uploads/properties/';
                
                // Create directory if it doesn't exist
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                $total_files = count($_FILES['images']['name']);
                
                // If new images are uploaded, delete old ones
                if ($total_files > 0) {
                    // First, get existing image paths to delete files
                    $sql = "SELECT image_path FROM property_images WHERE property_id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("i", $property_id);
                    $stmt->execute();
                    $old_images_result = $stmt->get_result();
                    
                    // Delete old image files
                    while ($old_image = $old_images_result->fetch_assoc()) {
                        $old_file_path = $upload_dir . $old_image['image_path'];
                        if (file_exists($old_file_path)) {
                            unlink($old_file_path);
                        }
                    }
                    
                    // Delete old image records
                    $sql = "DELETE FROM property_images WHERE property_id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("i", $property_id);
                    $stmt->execute();
                    
                    // Upload and insert new images
                    for ($i = 0; $i < $total_files; $i++) {
                        if ($_FILES['images']['error'][$i] === UPLOAD_ERR_OK) {
                            $tmp_name = $_FILES['images']['tmp_name'][$i];
                            $name = basename($_FILES['images']['name'][$i]);
                            $extension = pathinfo($name, PATHINFO_EXTENSION);
                            
                            // Generate unique filename
                            $new_filename = uniqid('property_') . '.' . $extension;
                            $upload_path = $upload_dir . $new_filename;
                            
                            // Move uploaded file
                            if (move_uploaded_file($tmp_name, $upload_path)) {
                                // Set first image as primary
                                $is_primary = ($i === 0) ? 1 : 0;
                                
                                // Insert image record with just the filename
                                $sql = "INSERT INTO property_images (property_id, image_path, is_primary) VALUES (?, ?, ?)";
                                $stmt = $conn->prepare($sql);
                                $stmt->bind_param("isi", $property_id, $new_filename, $is_primary);
                                $stmt->execute();
                            } else {
                                throw new Exception('Failed to upload image');
                            }
                        }
                    }
                }
            }
            
            // Commit transaction
            $conn->commit();
            $success = 'Property updated successfully! It will be reviewed by an admin before being listed.';
            
            // Refresh property data
            $sql = "SELECT * FROM properties WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $property_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $property = $result->fetch_assoc();
            
            // Refresh images
            $sql = "SELECT * FROM property_images WHERE property_id = ? ORDER BY is_primary DESC";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $property_id);
            $stmt->execute();
            $images_result = $stmt->get_result();
            $images = $images_result->fetch_all(MYSQLI_ASSOC);
            
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollback();
            $error = 'Error: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Property - Real Estate</title>
   <link rel="stylesheet" href="css/vendor.css">
</head>
<body>
    <!-- Back to top button removed -->
    
    <?php include 'includes/navbar.php'; ?>

    <div class="container py-5">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="my-properties.php">My Properties</a></li>
                <li class="breadcrumb-item active" aria-current="page">Edit Property</li>
            </ol>
        </nav>
        
        <h1 class="mb-4">Edit Property</h1>
        
        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="card shadow">
            <div class="card-body">
                <form action="edit-property.php?id=<?php echo $property_id; ?>" method="post" enctype="multipart/form-data">
                    <div class="row mb-3">
                        <div class="col-md-8">
                            <label for="title" class="form-label">Title *</label>
                            <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($property['title']); ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label for="price" class="form-label">Price (₹) *</label>
                            <input type="number" class="form-control" id="price" name="price" min="0" step="0.01" value="<?php echo $property['price']; ?>" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description *</label>
                        <textarea class="form-control" id="description" name="description" rows="5" required><?php echo htmlspecialchars($property['description']); ?></textarea>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="location" class="form-label">Location *</label>
                            <input type="text" class="form-control" id="location" name="location" value="<?php echo htmlspecialchars($property['location']); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="property_type" class="form-label">Property Type *</label>
                            <select class="form-select" id="property_type" name="property_type" required>
                                <option value="">Select Type</option>
                                <option value="house" <?php echo $property['property_type'] === 'house' ? 'selected' : ''; ?>>House</option>
                                <option value="apartment" <?php echo $property['property_type'] === 'apartment' ? 'selected' : ''; ?>>Apartment</option>
                                <option value="land" <?php echo $property['property_type'] === 'land' ? 'selected' : ''; ?>>Land</option>
                                <option value="commercial" <?php echo $property['property_type'] === 'commercial' ? 'selected' : ''; ?>>Commercial</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="bedrooms" class="form-label">Bedrooms</label>
                            <input type="number" class="form-control" id="bedrooms" name="bedrooms" min="0" value="<?php echo $property['bedrooms']; ?>">
                        </div>
                        <div class="col-md-4">
                            <label for="bathrooms" class="form-label">Bathrooms</label>
                            <input type="number" class="form-control" id="bathrooms" name="bathrooms" min="0" value="<?php echo $property['bathrooms']; ?>">
                        </div>
                        <div class="col-md-4">
                            <label for="area" class="form-label">Area (sqft)</label>
                            <input type="number" class="form-control" id="area" name="area" min="0" step="0.01" value="<?php echo $property['area']; ?>">
                        </div>
                    </div>
                    
                    <?php if (count($images) > 0): ?>
                    <div class="mb-3">
                        <label class="form-label">Current Images</label>
                        <div class="row">
                            <?php foreach ($images as $image): ?>
                            <div class="col-md-3 mb-3">
                                <img src="<?php echo htmlspecialchars($image['image_path']); ?>" class="img-thumbnail" alt="Property Image">
                                <?php if ($image['is_primary']): ?>
                                <div class="mt-1 text-center"><span class="badge bg-primary">Primary</span></div>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="mb-3">
                        <label for="images" class="form-label">Upload New Images</label>
                        <input type="file" class="form-control" id="images" name="images[]" multiple accept="image/*">
                        <div class="form-text">Uploading new images will replace all current images. The first image will be used as the main image.</div>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn