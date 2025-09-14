<?php
// admin/manage-tours.php

// Include config first
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/database.php';

// Then page-specific code
$page_title = "VR Tour Admin - Manage Tours";
redirectIfNotLoggedIn();

// Handle form actions
$action = $_GET['action'] ?? '';
$tour_id = $_GET['id'] ?? 0;

// Delete tour
if ($action === 'delete' && $tour_id) {
    $stmt = $pdo->prepare("DELETE FROM tours WHERE id = ?");
    if ($stmt->execute([$tour_id])) {
        $_SESSION['success'] = "Tour deleted successfully.";
    } else {
        $_SESSION['error'] = "Error deleting tour.";
    }
    header("Location: manage-tours.php");
    exit();
}

// Toggle publish status
if ($action === 'toggle_publish' && $tour_id) {
    $stmt = $pdo->prepare("UPDATE tours SET published = NOT published WHERE id = ?");
    if ($stmt->execute([$tour_id])) {
        $status = $pdo->query("SELECT published FROM tours WHERE id = $tour_id")->fetchColumn();
        $_SESSION['success'] = "Tour " . ($status ? "published" : "unpublished") . " successfully.";
    } else {
        $_SESSION['error'] = "Error updating tour status.";
    }
    header("Location: manage-tours.php");
    exit();
}

// Toggle featured status
if ($action === 'toggle_featured' && $tour_id) {
    $stmt = $pdo->prepare("UPDATE tours SET featured = NOT featured WHERE id = ?");
    if ($stmt->execute([$tour_id])) {
        $status = $pdo->query("SELECT featured FROM tours WHERE id = $tour_id")->fetchColumn();
        $_SESSION['success'] = "Tour " . ($status ? "added to" : "removed from") . " featured successfully.";
    } else {
        $_SESSION['error'] = "Error updating featured status.";
    }
    header("Location: manage-tours.php");
    exit();
}

// Handle create action - show form
if ($action === 'create') {
    // Check if form was submitted
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Process form submission
        $title = trim($_POST['title']);
        $description = trim($_POST['description']);
        $category = trim($_POST['category']);
        $tags = trim($_POST['tags']);
        $featured = isset($_POST['featured']) ? 1 : 0;
        $published = isset($_POST['published']) ? 1 : 0;
        
        // Validate input
        if (empty($title)) {
            $_SESSION['error'] = "Tour title is required.";
        } else {
            // Handle file upload
            $thumbnail = null;
            if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = '../assets/images/uploads/';
                // Create directory if it doesn't exist
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                $file_extension = pathinfo($_FILES['thumbnail']['name'], PATHINFO_EXTENSION);
                $file_name = 'tour_' . time() . '_' . uniqid() . '.' . $file_extension;
                $file_path = $upload_dir . $file_name;
                
                // Check if file is an image
                $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
                if (in_array(strtolower($file_extension), $allowed_types)) {
                    if (move_uploaded_file($_FILES['thumbnail']['tmp_name'], $file_path)) {
                        $thumbnail = $file_name;
                    } else {
                        $_SESSION['error'] = "Failed to upload thumbnail.";
                    }
                } else {
                    $_SESSION['error'] = "Only JPG, PNG, and GIF files are allowed.";
                }
            }
            
            // Insert into database if no errors
            if (!isset($_SESSION['error'])) {
                // Use NULL for created_by since admin is creating the tour
                $stmt = $pdo->prepare("
                    INSERT INTO tours (title, description, thumbnail, category, tags, featured, published, created_by, created_at, updated_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, NULL, NOW(), NOW())
                ");
                
                if ($stmt->execute([$title, $description, $thumbnail, $category, $tags, $featured, $published])) {
                    $_SESSION['success'] = "Tour created successfully!";
                    header("Location: manage-tours.php");
                    exit();
                } else {
                    $_SESSION['error'] = "Error creating tour. Please try again.";
                    error_log("Tour creation error: " . print_r($stmt->errorInfo(), true));
                }
            }
        }
    }
    
    // Show create form
    include '../includes/header.php';
    ?>
    <!-- Begin Page Content -->
    <div class="container-fluid">
        <!-- Page Heading -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Create New Tour</h1>
            <a href="manage-tours.php" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
                <i class="fas fa-arrow-left fa-sm text-white-50"></i> Back to Tours
            </a>
        </div>

        <!-- Messages -->
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>

        <!-- Create Tour Form -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Tour Information</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="?action=create" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="title">Tour Title *</label>
                                <input type="text" class="form-control" id="title" name="title" required 
                                       value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="description">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="4"><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="category">Category</label>
                                        <select class="form-control" id="category" name="category">
                                            <option value="Museum">Museum</option>
                                            <option value="Landmark">Landmark</option>
                                            <option value="Historical">Historical</option>
                                            <option value="Nature">Nature</option>
                                            <option value="Educational">Educational</option>
                                            <option value="Entertainment">Entertainment</option>
                                            <option value="Religious">Religious</option>
                                            <option value="Other">Other</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="tags">Tags (comma separated)</label>
                                        <input type="text" class="form-control" id="tags" name="tags" 
                                               value="<?php echo isset($_POST['tags']) ? htmlspecialchars($_POST['tags']) : ''; ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="thumbnail">Thumbnail Image</label>
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="thumbnail" name="thumbnail" accept="image/*">
                                    <label class="custom-file-label" for="thumbnail">Choose file</label>
                                </div>
                                <small class="form-text text-muted">Recommended size: 400x300px</small>
                            </div>
                            
                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="featured" name="featured" value="1">
                                    <label class="custom-control-label" for="featured">Featured Tour</label>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="published" name="published" value="1" checked>
                                    <label class="custom-control-label" for="published">Publish Immediately</label>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary btn-block">
                                    <i class="fas fa-save"></i> Create Tour
                                </button>
                                <a href="manage-tours.php" class="btn btn-secondary btn-block">Cancel</a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- /.container-fluid -->

    <script>
    // Show selected file name
    document.querySelector('.custom-file-input').addEventListener('change', function(e) {
        var fileName = document.getElementById("thumbnail").files[0].name;
        var nextSibling = e.target.nextElementSibling;
        nextSibling.innerText = fileName;
    });
    </script>

    <?php
    include '../includes/footer.php';
    exit();
}

// Handle edit action - show edit form
if ($action === 'edit' && $tour_id) {
    // Get tour data
    $stmt = $pdo->prepare("SELECT * FROM tours WHERE id = ?");
    $stmt->execute([$tour_id]);
    $tour = $stmt->fetch();
    
    if (!$tour) {
        $_SESSION['error'] = "Tour not found.";
        header("Location: manage-tours.php");
        exit();
    }
    
    // Check if form was submitted
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Process form submission
        $title = trim($_POST['title']);
        $description = trim($_POST['description']);
        $category = trim($_POST['category']);
        $tags = trim($_POST['tags']);
        $featured = isset($_POST['featured']) ? 1 : 0;
        $published = isset($_POST['published']) ? 1 : 0;
        $remove_thumbnail = isset($_POST['remove_thumbnail']) ? 1 : 0;
        
        // Validate input
        if (empty($title)) {
            $_SESSION['error'] = "Tour title is required.";
        } else {
            // Handle file upload and thumbnail management
            $thumbnail = $tour['thumbnail']; // Keep existing thumbnail by default
            
            if ($remove_thumbnail && $thumbnail) {
                // Remove current thumbnail
                $upload_dir = '../assets/images/uploads/';
                if (file_exists($upload_dir . $thumbnail)) {
                    unlink($upload_dir . $thumbnail);
                }
                $thumbnail = null;
            }
            
            if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = '../assets/images/uploads/';
                // Create directory if it doesn't exist
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                $file_extension = pathinfo($_FILES['thumbnail']['name'], PATHINFO_EXTENSION);
                $file_name = 'tour_' . time() . '_' . uniqid() . '.' . $file_extension;
                $file_path = $upload_dir . $file_name;
                
                // Check if file is an image
                $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
                if (in_array(strtolower($file_extension), $allowed_types)) {
                    if (move_uploaded_file($_FILES['thumbnail']['tmp_name'], $file_path)) {
                        // Delete old thumbnail if it exists
                        if ($tour['thumbnail'] && file_exists($upload_dir . $tour['thumbnail'])) {
                            unlink($upload_dir . $tour['thumbnail']);
                        }
                        $thumbnail = $file_name;
                    } else {
                        $_SESSION['error'] = "Failed to upload thumbnail.";
                    }
                } else {
                    $_SESSION['error'] = "Only JPG, PNG, and GIF files are allowed.";
                }
            }
            
            // Update database if no errors
            if (!isset($_SESSION['error'])) {
                $stmt = $pdo->prepare("
                    UPDATE tours 
                    SET title = ?, description = ?, thumbnail = ?, category = ?, tags = ?, 
                        featured = ?, published = ?, updated_at = NOW() 
                    WHERE id = ?
                ");
                
                if ($stmt->execute([$title, $description, $thumbnail, $category, $tags, $featured, $published, $tour_id])) {
                    $_SESSION['success'] = "Tour updated successfully!";
                    header("Location: manage-tours.php");
                    exit();
                } else {
                    $_SESSION['error'] = "Error updating tour. Please try again.";
                    error_log("Tour update error: " . print_r($stmt->errorInfo(), true));
                }
            }
        }
    }
    
    // Show edit form
    include '../includes/header.php';
    ?>
    <!-- Begin Page Content -->
    <div class="container-fluid">
        <!-- Page Heading -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Edit Tour: <?php echo htmlspecialchars($tour['title']); ?></h1>
            <a href="manage-tours.php" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
                <i class="fas fa-arrow-left fa-sm text-white-50"></i> Back to Tours
            </a>
        </div>

        <!-- Messages -->
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>

        <!-- Edit Tour Form -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Tour Information</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="?action=edit&id=<?php echo $tour_id; ?>" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="title">Tour Title *</label>
                                <input type="text" class="form-control" id="title" name="title" required 
                                       value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : htmlspecialchars($tour['title']); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="description">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="4"><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : htmlspecialchars($tour['description']); ?></textarea>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="category">Category</label>
                                        <select class="form-control" id="category" name="category">
                                            <option value="Museum" <?php echo ($tour['category'] === 'Museum') ? 'selected' : ''; ?>>Museum</option>
                                            <option value="Landmark" <?php echo ($tour['category'] === 'Landmark') ? 'selected' : ''; ?>>Landmark</option>
                                            <option value="Historical" <?php echo ($tour['category'] === 'Historical') ? 'selected' : ''; ?>>Historical</option>
                                            <option value="Nature" <?php echo ($tour['category'] === 'Nature') ? 'selected' : ''; ?>>Nature</option>
                                            <option value="Educational" <?php echo ($tour['category'] === 'Educational') ? 'selected' : ''; ?>>Educational</option>
                                            <option value="Entertainment" <?php echo ($tour['category'] === 'Entertainment') ? 'selected' : ''; ?>>Entertainment</option>
                                            <option value="Religious" <?php echo ($tour['category'] === 'Religious') ? 'selected' : ''; ?>>Religious</option>
                                            <option value="Other" <?php echo ($tour['category'] === 'Other') ? 'selected' : ''; ?>>Other</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="tags">Tags (comma separated)</label>
                                        <input type="text" class="form-control" id="tags" name="tags" 
                                               value="<?php echo isset($_POST['tags']) ? htmlspecialchars($_POST['tags']) : htmlspecialchars($tour['tags']); ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="thumbnail">Thumbnail Image</label>
                                <?php if ($tour['thumbnail']): ?>
                                    <div class="mb-3">
                                        <img src="../assets/images/uploads/<?php echo htmlspecialchars($tour['thumbnail']); ?>" 
                                             alt="Current thumbnail" class="img-thumbnail" style="max-height: 150px;">
                                        <div class="form-check mt-2">
                                            <input class="form-check-input" type="checkbox" id="remove_thumbnail" name="remove_thumbnail" value="1">
                                            <label class="form-check-label" for="remove_thumbnail">
                                                Remove current thumbnail
                                            </label>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="thumbnail" name="thumbnail" accept="image/*">
                                    <label class="custom-file-label" for="thumbnail">Choose new file</label>
                                </div>
                                <small class="form-text text-muted">Leave empty to keep current image</small>
                            </div>
                            
                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="featured" name="featured" value="1" <?php echo $tour['featured'] ? 'checked' : ''; ?>>
                                    <label class="custom-control-label" for="featured">Featured Tour</label>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="published" name="published" value="1" <?php echo $tour['published'] ? 'checked' : ''; ?>>
                                    <label class="custom-control-label" for="published">Published</label>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary btn-block">
                                    <i class="fas fa-save"></i> Update Tour
                                </button>
                                <a href="manage-tours.php" class="btn btn-secondary btn-block">Cancel</a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- /.container-fluid -->

    <script>
    // Show selected file name
    document.querySelector('.custom-file-input').addEventListener('change', function(e) {
        var fileName = document.getElementById("thumbnail").files[0].name;
        var nextSibling = e.target.nextElementSibling;
        nextSibling.innerText = fileName;
    });
    </script>

    <?php
    include '../includes/footer.php';
    exit();
}

// Get all tours for display
$tours = $pdo->query("
    SELECT t.*, 
           (SELECT COUNT(*) FROM scenes s WHERE s.tour_id = t.id) as scene_count
    FROM tours t 
    ORDER BY t.created_at DESC
")->fetchAll();

include '../includes/header.php';
?>

<!-- Begin Page Content -->
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Manage Tours</h1>
        <a href="?action=create" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-plus fa-sm text-white-50"></i> Create New Tour
        </a>
    </div>

    <!-- Messages -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <!-- Tours Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">All Tours</h6>
        </div>
        <div class="card-body">
            <?php if (count($tours) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Thumbnail</th>
                                <th>Category</th>
                                <th>Scenes</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tours as $tour): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($tour['title']); ?></strong>
                                        <?php if ($tour['featured']): ?>
                                            <span class="badge badge-warning ml-1">Featured</span>
                                        <?php endif; ?>
                                        <br>
                                        <small class="text-muted">Created by Admin</small>
                                    </td>
                                     <td>
                                        <?php if ($tour['thumbnail']): ?>
                                            <img src="../assets/images/uploads/<?php echo $tour['thumbnail']; ?>" alt="Thumbnail" class="thumbnail-preview">
                                        <?php else: ?>
                                            <div class="thumbnail-placeholder">
                                                <i class="fas fa-image"></i>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($tour['category']); ?></td>
                                    <td>
                                        <span class="badge badge-info"><?php echo $tour['scene_count']; ?> scenes</span>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?php echo $tour['published'] ? 'success' : 'secondary'; ?>">
                                            <?php echo $tour['published'] ? 'Published' : 'Draft'; ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M j, Y', strtotime($tour['created_at'])); ?></td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="../vr/tour.php?id=<?php echo $tour['id']; ?>" 
                                               class="btn btn-info" target="_blank" title="View Tour">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="manage-scenes.php?tour_id=<?php echo $tour['id']; ?>" 
                                               class="btn btn-primary" title="Manage Scenes">
                                                <i class="fas fa-image"></i>
                                            </a>
                                            <a href="?action=edit&id=<?php echo $tour['id']; ?>" 
                                               class="btn btn-warning" title="Edit Tour">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="?action=toggle_publish&id=<?php echo $tour['id']; ?>" 
                                               class="btn btn-<?php echo $tour['published'] ? 'secondary' : 'success'; ?>" 
                                               title="<?php echo $tour['published'] ? 'Unpublish' : 'Publish'; ?>">
                                                <i class="fas fa-<?php echo $tour['published'] ? 'times' : 'check'; ?>"></i>
                                            </a>
                                            <a href="?action=toggle_featured&id=<?php echo $tour['id']; ?>" 
                                               class="btn btn-<?php echo $tour['featured'] ? 'warning' : 'secondary'; ?>" 
                                               title="<?php echo $tour['featured'] ? 'Remove from Featured' : 'Add to Featured'; ?>">
                                                <i class="fas fa-star"></i>
                                            </a>
                                            <a href="?action=delete&id=<?php echo $tour['id']; ?>" 
                                               class="btn btn-danger" 
                                               onclick="return confirm('Are you sure you want to delete this tour? This will also delete all associated scenes and hotspots.')"
                                               title="Delete Tour">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-map-marked fa-4x text-muted mb-3"></i>
                    <h5 class="text-muted">No tours found</h5>
                    <p class="text-muted">Get started by creating your first virtual tour</p>
                    <a href="?action=create" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Create First Tour
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<!-- /.container-fluid -->

<?php
include '../includes/footer.php';
?>