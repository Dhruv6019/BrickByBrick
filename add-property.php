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
            // Insert property
            $sql = "INSERT INTO properties (title, description, price, location, property_type, bedrooms, bathrooms, area, seller_id) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssdssiidi", $title, $description, $price, $location, $property_type, $bedrooms, $bathrooms, $area, $user_id);
            $stmt->execute();
            
            $property_id = $conn->insert_id;
            
            // Handle image uploads
            $upload_dir = 'uploads/properties/';
            
            // Create directory if it doesn't exist
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            // Check if images were uploaded
            if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
                $total_files = count($_FILES['images']['name']);
                
                for ($i = 0; $i < $total_files; $i++) {
                    // Check if file was uploaded without errors
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
            
            // Commit transaction
            $conn->commit();
            $success = 'Property submitted successfully! It will be reviewed by an admin before being listed.';
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
    <title>Add Property - BrickByBrick</title>
    <link rel="stylesheet" href="css/vendor.css">
</head>
<body>
 
    <?php include 'includes/navbar.php'; ?>

    <div class="container py-5">
        <h1 class="mb-4">Add New Property</h1>
        
        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="card shadow">
            <div class="card-body">
                <form action="add-property.php" method="post" enctype="multipart/form-data">
                    <div class="row mb-3">
                        <div class="col-md-8">
                            <label for="title" class="form-label">Title *</label>
                            <input type="text" class="form-control" id="title" name="title" required>
                        </div>
                        <div class="col-md-4">
                            <label for="price" class="form-label">Price (₹) *</label>
                            <input type="number" class="form-control" id="price" name="price" min="0" step="0.01" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description *</label>
                        <textarea class="form-control" id="description" name="description" rows="5" required></textarea>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="location" class="form-label">Location *</label>
                            <input type="text" class="form-control" id="location" name="location" required>
                        </div>
                        <div class="col-md-6">
                            <label for="property_type" class="form-label">Property Type *</label>
                            <select class="form-select" id="property_type" name="property_type" required>
                                <option value="">Select Type</option>
                                <option value="house">House</option>
                                <option value="apartment">Apartment</option>
                                <option value="land">Land</option>
                                <option value="commercial">Commercial</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="bedrooms" class="form-label">Bedrooms</label>
                            <input type="number" class="form-control" id="bedrooms" name="bedrooms" min="0">
                        </div>
                        <div class="col-md-4">
                            <label for="bathrooms" class="form-label">Bathrooms</label>
                            <input type="number" class="form-control" id="bathrooms" name="bathrooms" min="0">
                        </div>
                        <div class="col-md-4">
                            <label for="area" class="form-label">Area (sqft)</label>
                            <input type="number" class="form-control" id="area" name="area" min="0" step="0.01">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="images" class="form-label">Property Images</label>
                        <input type="file" class="form-control" id="images" name="images[]" multiple accept="image/*">
                        <div class="form-text">You can upload multiple images. The first image will be used as the main image.</div>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">Submit Property</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="js/main.js"></script>
    <script src="js/enhanced-main.js"></script>
</body>
</html>