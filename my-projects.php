<?php
require_once 'config.php';
session_start();

// Check if user is logged in and is a developer
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'developer') {
    header('Location: login.php');
    exit();
}

// Get developer's projects
$sql = "SELECT p.*, COUNT(DISTINCT f.id) as favorite_count, p.view_count as views,
        pi.image_path as main_image
        FROM properties p 
        LEFT JOIN favorites f ON p.id = f.property_id 
        LEFT JOIN property_images pi ON p.id = pi.property_id AND pi.is_primary = 1
        WHERE p.seller_id = ? 
        GROUP BY p.id 
        ORDER BY p.created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$projects = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Projects - BrickbyBrick</title>
    <link rel="stylesheet" href="css/vendor.css">
    <style>
        .project-card {
            transition: transform 0.3s ease;
            margin-bottom: 20px;
        }
        .project-card:hover {
            transform: translateY(-5px);
        }
        .status-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        .project-stats {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-top: 1px solid #eee;
            margin-top: 10px;
        }
        .filter-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>My Projects</h2>
            <a href="add-property.php" class="btn btn-primary">Add New Project</a>
        </div>

        <div class="filter-section">
            <div class="row">
                <div class="col-md-4">
                    <select class="form-select" id="statusFilter">
                        <option value="all">All Status</option>
                        <option value="available">Available</option>
                        <option value="sold">Sold</option>
                        <option value="under_construction">Under Construction</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <select class="form-select" id="sortBy">
                        <option value="newest">Newest First</option>
                        <option value="oldest">Oldest First</option>
                        <option value="price_high">Price High to Low</option>
                        <option value="price_low">Price Low to High</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <input type="text" class="form-control" id="searchProject" placeholder="Search projects...">
                </div>
            </div>
        </div>

        <div class="row" id="projectsContainer">
            <?php foreach ($projects as $project): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card project-card">
                        <img src="<?php echo $project['main_image'] ? 'uploads/properties/' . $project['main_image'] : 'images/default-property.svg'; ?>" class="card-img-top" alt="Project Image" style="height: 200px; object-fit: cover;">
                        <div class="status-badge bg-<?php echo getStatusColor($project['status']); ?>">
                            <?php echo ucfirst($project['status']); ?>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($project['title']); ?></h5>
                            <p class="card-text"><?php echo substr(htmlspecialchars($project['description']), 0, 100); ?>...</p>
                            <div class="project-stats">
                                <span><i class="fas fa-heart"></i> <?php echo $project['favorite_count']; ?> favorites</span>
                                <span><i class="fas fa-eye"></i> <?php echo $project['views']; ?> views</span>
                            </div>
                            <div class="mt-3">
                                <a href="edit-property.php?id=<?php echo $project['id']; ?>" class="btn btn-outline-primary btn-sm">Edit</a>
                                <a href="property.php?id=<?php echo $project['id']; ?>" class="btn btn-outline-secondary btn-sm">View</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script>
        // Helper function to get status color
        function getStatusColor(status) {
            switch(status.toLowerCase()) {
                case 'available': return 'success';
                case 'sold': return 'danger';
                case 'under_construction': return 'warning';
                default: return 'secondary';
            }
        }

        // Filter and sort functionality
        document.getElementById('statusFilter').addEventListener('change', filterProjects);
        document.getElementById('sortBy').addEventListener('change', filterProjects);
        document.getElementById('searchProject').addEventListener('input', filterProjects);

        function filterProjects() {
            const status = document.getElementById('statusFilter').value;
            const sort = document.getElementById('sortBy').value;
            const search = document.getElementById('searchProject').value.toLowerCase();

            const projects = document.querySelectorAll('.project-card');
            projects.forEach(project => {
                const projectStatus = project.querySelector('.status-badge').textContent.toLowerCase();
                const projectTitle = project.querySelector('.card-title').textContent.toLowerCase();
                const projectDesc = project.querySelector('.card-text').textContent.toLowerCase();

                const statusMatch = status === 'all' || projectStatus === status;
                const searchMatch = projectTitle.includes(search) || projectDesc.includes(search);

                project.parentElement.style.display = statusMatch && searchMatch ? 'block' : 'none';
            });
        }
    </script>
</body>
</html>
<?php
function getStatusColor($status) {
    switch(strtolower($status)) {
        case 'available': return 'success';
        case 'sold': return 'danger';
        case 'under_construction': return 'warning';
        default: return 'secondary';
    }
}
?>