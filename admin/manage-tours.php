<?php
// manage-tours.php
session_start();
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Check if user is logged in and is admin
if (!is_logged_in() || !is_admin()) {
    header('Location: ../login.php');
    exit;
}

// Handle form submissions
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create_tour'])) {
        if (create_tour($_POST, $_FILES)) {
            $message = 'Tour created successfully';
        } else {
            $error = 'Error creating tour';
        }
    } elseif (isset($_POST['update_tour'])) {
        if (update_tour($_POST['id'], $_POST, $_FILES)) {
            $message = 'Tour updated successfully';
        } else {
            $error = 'Error updating tour';
        }
    } elseif (isset($_POST['delete_tour'])) {
        if (delete_tour($_POST['id'])) {
            $message = 'Tour deleted successfully';
        } else {
            $error = 'Error deleting tour';
        }
    }
}

// Get all tours from database
$tours = get_all_tours();

// Function to get all tours
function get_all_tours() {
    global $conn;
    $tours = [];
    $query = "SELECT t.*, u.username as created_by_name 
              FROM tours t 
              LEFT JOIN users u ON t.created_by = u.id 
              ORDER BY t.created_at DESC";
    $result = mysqli_query($conn, $query);
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $tours[] = $row;
        }
    }
    return $tours;
}

// Function to create a new tour
function create_tour($data, $files) {
    global $conn;
    
    $title = mysqli_real_escape_string($conn, $data['title']);
    $description = mysqli_real_escape_string($conn, $data['description']);
    $category = mysqli_real_escape_string($conn, $data['category']);
    $tags = mysqli_real_escape_string($conn, $data['tags']);
    $featured = isset($data['featured']) ? 1 : 0;
    $published = isset($data['published']) ? 1 : 0;
    $created_by = $_SESSION['user_id'];
    
    // Handle thumbnail upload
    $thumbnail = '';
    if (isset($files['thumbnail']) && $files['thumbnail']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../assets/images/uploads/';
        $file_extension = pathinfo($files['thumbnail']['name'], PATHINFO_EXTENSION);
        $filename = 'tour_' . time() . '.' . $file_extension;
        $destination = $upload_dir . $filename;
        
        if (move_uploaded_file($files['thumbnail']['tmp_name'], $destination)) {
            $thumbnail = $filename;
        }
    }
    
    $query = "INSERT INTO tours (title, description, thumbnail, category, tags, featured, published, created_by) 
              VALUES ('$title', '$description', '$thumbnail', '$category', '$tags', $featured, $published, $created_by)";
    
    return mysqli_query($conn, $query);
}

// Function to update a tour
function update_tour($id, $data, $files) {
    global $conn;
    
    $id = mysqli_real_escape_string($conn, $id);
    $title = mysqli_real_escape_string($conn, $data['title']);
    $description = mysqli_real_escape_string($conn, $data['description']);
    $category = mysqli_real_escape_string($conn, $data['category']);
    $tags = mysqli_real_escape_string($conn, $data['tags']);
    $featured = isset($data['featured']) ? 1 : 0;
    $published = isset($data['published']) ? 1 : 0;
    
    // Handle thumbnail upload if new file is provided
    $thumbnail_update = '';
    if (isset($files['thumbnail']) && $files['thumbnail']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../assets/images/uploads/';
        $file_extension = pathinfo($files['thumbnail']['name'], PATHINFO_EXTENSION);
        $filename = 'tour_' . time() . '.' . $file_extension;
        $destination = $upload_dir . $filename;
        
        if (move_uploaded_file($files['thumbnail']['tmp_name'], $destination)) {
            $thumbnail_update = ", thumbnail = '$filename'";
        }
    }
    
    $query = "UPDATE tours SET 
              title = '$title', 
              description = '$description', 
              category = '$category', 
              tags = '$tags', 
              featured = $featured, 
              published = $published 
              $thumbnail_update 
              WHERE id = $id";
    
    return mysqli_query($conn, $query);
}

// Function to delete a tour
function delete_tour($id) {
    global $conn;
    
    $id = mysqli_real_escape_string($conn, $id);
    $query = "DELETE FROM tours WHERE id = $id";
    
    return mysqli_query($conn, $query);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Tours - VR Tour Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4e54c8;
            --secondary-color: #8f94fb;
            --dark-color: #2c3e50;
            --light-color: #f8f9fa;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fb;
            color: var(--dark-color);
            overflow-x: hidden;
        }
        
        /* Header Styles */
        .header {
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 15px 0;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .header .navbar-brand {
            font-weight: 700;
            font-size: 1.8rem;
        }
        
        .header .nav-link {
            color: rgba(255, 255, 255, 0.85) !important;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .header .nav-link:hover {
            color: #fff !important;
            transform: translateY(-2px);
        }
        
        /* Sidebar Styles */
        .sidebar {
            min-height: calc(100vh - 76px);
            background: linear-gradient(to bottom, var(--primary-color), var(--secondary-color));
            color: white;
            box-shadow: 3px 0 10px rgba(0, 0, 0, 0.1);
            width: 250px;
        }
        
        .sidebar .sidebar-header {
            padding: 20px;
            background: rgba(0, 0, 0, 0.1);
        }
        
        .sidebar ul.components {
            padding: 20px 0;
        }
        
        .sidebar ul li a {
            padding: 15px 30px;
            display: block;
            color: rgba(255, 255, 255, 0.9);
            text-decoration: none;
            transition: all 0.3s;
            font-size: 1.1em;
        }
        
        .sidebar ul li a:hover {
            background: rgba(0, 0, 0, 0.1);
            color: #fff;
        }
        
        .sidebar ul li a i {
            margin-right: 10px;
            font-size: 1.2em;
        }
        
        .sidebar ul li.active > a {
            background: rgba(0, 0, 0, 0.2);
            color: #fff;
            border-left: 4px solid #fff;
        }
        
        /* Content Styles */
        .content {
            flex: 1;
            padding: 20px;
        }
        
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            margin-bottom: 24px;
            transition: transform 0.3s;
        }
        
        .card:hover {
            transform: translateY(-5px);
        }
        
        .btn-primary {
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            border: none;
            border-radius: 6px;
            padding: 10px 20px;
        }
        
        .btn-primary:hover {
            background: linear-gradient(to right, var(--secondary-color), var(--primary-color));
        }
        
        .table th {
            border-top: none;
            font-weight: 600;
            color: var(--dark-color);
        }
        
        .badge-success {
            background-color: var(--success-color);
        }
        
        .badge-warning {
            background-color: var(--warning-color);
        }
        
        .badge-danger {
            background-color: var(--danger-color);
        }
        
        /* Form Styling */
        .form-control, .form-select {
            border-radius: 6px;
            padding: 12px 15px;
            border: 1px solid #e1e5eb;
        }
        
        .form-control:focus, .form-select:focus {
            box-shadow: 0 0 0 3px rgba(78, 84, 200, 0.2);
            border-color: var(--primary-color);
        }
        
        /* Image Upload */
        .image-upload {
            border: 2px dashed #ccc;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .image-upload:hover {
            border-color: var(--primary-color);
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                min-height: auto;
                margin-bottom: 20px;
                width: 100%;
            }
            
            .sidebar ul li a {
                padding: 10px 20px;
            }
            
            .card {
                margin-bottom: 15px;
            }
        }
    </style>
</head>
<body>
    <!-- Header Component -->
    <?php include '../includes/header.php'; ?>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar Component -->
            <?php include '../includes/sidebar.php'; ?>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 content">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
                    <h1 class="h2">Manage Tours</h1>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createTourModal">
                        <i class="fas fa-plus"></i> Create New Tour
                    </button>
                </div>

                <!-- Messages -->
                <?php if ($message): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo $error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <!-- Tours Table -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">All Tours</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Category</th>
                                        <th>Status</th>
                                        <th>Featured</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($tours) > 0): ?>
                                        <?php foreach ($tours as $tour): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($tour['title']); ?></td>
                                                <td><?php echo htmlspecialchars($tour['category']); ?></td>
                                                <td>
                                                    <?php if ($tour['published']): ?>
                                                        <span class="badge bg-success">Published</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-warning">Draft</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($tour['featured']): ?>
                                                        <span class="badge bg-info">Featured</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary">Regular</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo date('M j, Y', strtotime($tour['created_at'])); ?></td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editTourModal<?php echo $tour['id']; ?>">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <form method="POST" class="d-inline">
                                                        <input type="hidden" name="id" value="<?php echo $tour['id']; ?>">
                                                        <button type="submit" name="delete_tour" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this tour?')">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                    <a href="../vr/tour.php?id=<?php echo $tour['id']; ?>" class="btn btn-sm btn-outline-info">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="text-center">No tours found. Create your first tour!</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Tour Modal -->
    <div class="modal fade" id="createTourModal" tabindex="-1" aria-labelledby="createTourModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createTourModalLabel">Create New Tour</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="title" class="form-label">Tour Title</label>
                            <input type="text" class="form-control" id="title" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="category" class="form-label">Category</label>
                                    <select class="form-select" id="category" name="category" required>
                                        <option value="">Select Category</option>
                                        <option value="City">City</option>
                                        <option value="Nature">Nature</option>
                                        <option value="Cultural">Cultural</option>
                                        <option value="Historical">Historical</option>
                                        <option value="Museum">Museum</option>
                                        <option value="Religious">Religious</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="tags" class="form-label">Tags (comma separated)</label>
                                    <input type="text" class="form-control" id="tags" name="tags">
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="thumbnail" class="form-label">Thumbnail Image</label>
                            <input type="file" class="form-control" id="thumbnail" name="thumbnail" accept="image/*">
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="featured" name="featured">
                                    <label class="form-check-label" for="featured">Featured Tour</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="published" name="published" checked>
                                    <label class="form-check-label" for="published">Published</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="create_tour" class="btn btn-primary">Create Tour</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Tour Modals -->
    <?php foreach ($tours as $tour): ?>
        <div class="modal fade" id="editTourModal<?php echo $tour['id']; ?>" tabindex="-1" aria-labelledby="editTourModalLabel<?php echo $tour['id']; ?>" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editTourModalLabel<?php echo $tour['id']; ?>">Edit Tour: <?php echo htmlspecialchars($tour['title']); ?></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="id" value="<?php echo $tour['id']; ?>">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="title<?php echo $tour['id']; ?>" class="form-label">Tour Title</label>
                                <input type="text" class="form-control" id="title<?php echo $tour['id']; ?>" name="title" value="<?php echo htmlspecialchars($tour['title']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="description<?php echo $tour['id']; ?>" class="form-label">Description</label>
                                <textarea class="form-control" id="description<?php echo $tour['id']; ?>" name="description" rows="3" required><?php echo htmlspecialchars($tour['description']); ?></textarea>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="category<?php echo $tour['id']; ?>" class="form-label">Category</label>
                                        <select class="form-select" id="category<?php echo $tour['id']; ?>" name="category" required>
                                            <option value="City" <?php echo $tour['category'] == 'City' ? 'selected' : ''; ?>>City</option>
                                            <option value="Nature" <?php echo $tour['category'] == 'Nature' ? 'selected' : ''; ?>>Nature</option>
                                            <option value="Cultural" <?php echo $tour['category'] == 'Cultural' ? 'selected' : ''; ?>>Cultural</option>
                                            <option value="Historical" <?php echo $tour['category'] == 'Historical' ? 'selected' : ''; ?>>Historical</option>
                                            <option value="Museum" <?php echo $tour['category'] == 'Museum' ? 'selected' : ''; ?>>Museum</option>
                                            <option value="Religious" <?php echo $tour['category'] == 'Religious' ? 'selected' : ''; ?>>Religious</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="tags<?php echo $tour['id']; ?>" class="form-label">Tags (comma separated)</label>
                                        <input type="text" class="form-control" id="tags<?php echo $tour['id']; ?>" name="tags" value="<?php echo htmlspecialchars($tour['tags']); ?>">
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="thumbnail<?php echo $tour['id']; ?>" class="form-label">Thumbnail Image</label>
                                <input type="file" class="form-control" id="thumbnail<?php echo $tour['id']; ?>" name="thumbnail" accept="image/*">
                                <?php if ($tour['thumbnail']): ?>
                                    <div class="mt-2">
                                        <small>Current: <?php echo $tour['thumbnail']; ?></small>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" id="featured<?php echo $tour['id']; ?>" name="featured" <?php echo $tour['featured'] ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="featured<?php echo $tour['id']; ?>">Featured Tour</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" id="published<?php echo $tour['id']; ?>" name="published" <?php echo $tour['published'] ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="published<?php echo $tour['id']; ?>">Published</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" name="update_tour" class="btn btn-primary">Update Tour</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php endforeach; ?>

    <!-- Footer Component -->
    <?php include '../includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>