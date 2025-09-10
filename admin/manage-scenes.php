<?php
// admin/manage-scenes.php

// Include config first
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/database.php';

// Then page-specific code
$page_title = "VR Tour Admin - Manage Scenes";
redirectIfNotLoggedIn();

// Handle form actions
$action = $_GET['action'] ?? '';
$scene_id = $_GET['id'] ?? 0;
$tour_id = $_GET['tour_id'] ?? 0;

// Delete scene
if ($action === 'delete' && $scene_id) {
    $stmt = $pdo->prepare("DELETE FROM scenes WHERE id = ?");
    if ($stmt->execute([$scene_id])) {
        $_SESSION['success'] = "Scene deleted successfully.";
    } else {
        $_SESSION['error'] = "Error deleting scene.";
    }
    header("Location: manage-scenes.php?tour_id=" . ($_GET['tour_id'] ?? ''));
    exit();
}

// Handle create action - show form
if ($action === 'create') {
    $tour_id = $_GET['tour_id'] ?? 0;
    
    // Check if form was submitted
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Process form submission
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        $initial_view = trim($_POST['initial_view']);
        $tour_id = $_POST['tour_id'];
        
        // Validate input
        if (empty($name) || empty($tour_id)) {
            $_SESSION['error'] = "Scene name and tour selection are required.";
        } else {
            // Handle panorama file upload
            $panorama = null;
            if (isset($_FILES['panorama']) && $_FILES['panorama']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = '../assets/panoramas/uploads/';
                
                // Create directory if it doesn't exist (with proper permissions)
                if (!file_exists($upload_dir)) {
                    if (!mkdir($upload_dir, 0755, true)) {
                        $_SESSION['error'] = "Failed to create upload directory. Please check permissions.";
                    }
                }
                
                // Check if directory exists and is writable
                if (file_exists($upload_dir) && is_writable($upload_dir)) {
                    $file_extension = pathinfo($_FILES['panorama']['name'], PATHINFO_EXTENSION);
                    $file_name = 'panorama_' . time() . '_' . uniqid() . '.' . $file_extension;
                    $file_path = $upload_dir . $file_name;
                    
                    // Check if file is an image
                    $allowed_types = ['jpg', 'jpeg', 'png'];
                    if (in_array(strtolower($file_extension), $allowed_types)) {
                        if (move_uploaded_file($_FILES['panorama']['tmp_name'], $file_path)) {
                            $panorama = $file_name;
                        } else {
                            $_SESSION['error'] = "Failed to upload panorama image. Please try again.";
                            error_log("File upload failed. Temp: " . $_FILES['panorama']['tmp_name'] . " -> Dest: " . $file_path);
                        }
                    } else {
                        $_SESSION['error'] = "Only JPG and PNG files are allowed for panoramas.";
                    }
                } else {
                    $_SESSION['error'] = "Upload directory is not writable. Please check permissions.";
                    error_log("Upload directory not writable: " . $upload_dir);
                }
            } else {
                // Check if there was a file upload error
                if (isset($_FILES['panorama']) && $_FILES['panorama']['error'] !== UPLOAD_ERR_OK) {
                    $upload_errors = [
                        UPLOAD_ERR_INI_SIZE => 'File is too large (server limit)',
                        UPLOAD_ERR_FORM_SIZE => 'File is too large (form limit)',
                        UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
                        UPLOAD_ERR_NO_FILE => 'No file was uploaded',
                        UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
                        UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
                        UPLOAD_ERR_EXTENSION => 'File upload stopped by extension'
                    ];
                    $error_msg = $upload_errors[$_FILES['panorama']['error']] ?? 'Unknown upload error';
                    $_SESSION['error'] = "File upload error: " . $error_msg;
                } else {
                    $_SESSION['error'] = "Panorama image is required.";
                }
            }
            
            // Insert into database if no errors
            if (!isset($_SESSION['error'])) {
                $stmt = $pdo->prepare("
                    INSERT INTO scenes (tour_id, name, description, panorama, initial_view, created_at) 
                    VALUES (?, ?, ?, ?, ?, NOW())
                ");
                
                if ($stmt->execute([$tour_id, $name, $description, $panorama, $initial_view])) {
                    $_SESSION['success'] = "Scene created successfully!";
                    header("Location: manage-scenes.php?tour_id=" . $tour_id);
                    exit();
                } else {
                    $_SESSION['error'] = "Error creating scene. Please try again.";
                    error_log("Scene creation error: " . print_r($stmt->errorInfo(), true));
                }
            }
        }
    }
    
    // Get tour information for the form
    $tour = null;
    if ($tour_id) {
        $stmt = $pdo->prepare("SELECT * FROM tours WHERE id = ?");
        $stmt->execute([$tour_id]);
        $tour = $stmt->fetch();
    }
    
    // Show create form
    include '../includes/header.php';
    ?>
    <!-- Begin Page Content -->
    <div class="container-fluid">
        <!-- Page Heading -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Add New Scene</h1>
            <a href="manage-scenes.php?tour_id=<?php echo $tour_id; ?>" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
                <i class="fas fa-arrow-left fa-sm text-white-50"></i> Back to Scenes
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

        <!-- Create Scene Form -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Scene Information</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="?action=create" enctype="multipart/form-data">
                    <input type="hidden" name="tour_id" value="<?php echo $tour_id; ?>">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Scene Name *</label>
                                <input type="text" class="form-control" id="name" name="name" required 
                                       value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="description">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="3"><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label for="initial_view">Initial View (Optional)</label>
                                <input type="text" class="form-control" id="initial_view" name="initial_view" 
                                       value="<?php echo isset($_POST['initial_view']) ? htmlspecialchars($_POST['initial_view']) : '0 0 0'; ?>"
                                       placeholder="0 0 0">
                                <small class="form-text text-muted">Format: "yaw pitch roll" (e.g., "0 0 0" for default view)</small>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="panorama">Panorama Image *</label>
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="panorama" name="panorama" accept="image/*" required>
                                    <label class="custom-file-label" for="panorama">Choose panorama image</label>
                                </div>
                                <small class="form-text text-muted">360° equirectangular image (JPG/PNG). Max size: <?php echo round(MAX_PANORAMA_SIZE / 1024 / 1024, 1); ?>MB</small>
                            </div>
                            
                            <?php if ($tour): ?>
                                <div class="alert alert-info">
                                    <strong>Tour:</strong> <?php echo htmlspecialchars($tour['title']); ?>
                                </div>
                            <?php endif; ?>
                            
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary btn-block">
                                    <i class="fas fa-save"></i> Create Scene
                                </button>
                                <a href="manage-scenes.php?tour_id=<?php echo $tour_id; ?>" class="btn btn-secondary btn-block">Cancel</a>
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
        var fileName = document.getElementById("panorama").files[0].name;
        var nextSibling = e.target.nextElementSibling;
        nextSibling.innerText = fileName;
    });
    </script>

    <?php
    include '../includes/footer.php';
    exit();
}

// Handle edit action - show edit form
if ($action === 'edit' && $scene_id) {
    // Get scene data
    $stmt = $pdo->prepare("SELECT * FROM scenes WHERE id = ?");
    $stmt->execute([$scene_id]);
    $scene = $stmt->fetch();
    
    if (!$scene) {
        $_SESSION['error'] = "Scene not found.";
        header("Location: manage-scenes.php");
        exit();
    }
    
    $tour_id = $scene['tour_id'];
    
    // Check if form was submitted
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Process form submission
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        $initial_view = trim($_POST['initial_view']);
        $remove_panorama = isset($_POST['remove_panorama']) ? 1 : 0;
        
        // Validate input
        if (empty($name)) {
            $_SESSION['error'] = "Scene name is required.";
        } else {
            // Handle panorama file upload
            $panorama = $scene['panorama']; // Keep existing panorama by default
            
            if ($remove_panorama && $panorama) {
                // Remove current panorama
                $upload_dir = '../assets/panoramas/uploads/';
                if (file_exists($upload_dir . $panorama)) {
                    unlink($upload_dir . $panorama);
                }
                $panorama = null;
            }
            
            if (isset($_FILES['panorama']) && $_FILES['panorama']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = '../assets/panoramas/uploads/';
                
                // Create directory if it doesn't exist (with proper permissions)
                if (!file_exists($upload_dir)) {
                    if (!mkdir($upload_dir, 0755, true)) {
                        $_SESSION['error'] = "Failed to create upload directory. Please check permissions.";
                    }
                }
                
                // Check if directory exists and is writable
                if (file_exists($upload_dir) && is_writable($upload_dir)) {
                    $file_extension = pathinfo($_FILES['panorama']['name'], PATHINFO_EXTENSION);
                    $file_name = 'panorama_' . time() . '_' . uniqid() . '.' . $file_extension;
                    $file_path = $upload_dir . $file_name;
                    
                    // Check if file is an image
                    $allowed_types = ['jpg', 'jpeg', 'png'];
                    if (in_array(strtolower($file_extension), $allowed_types)) {
                        if (move_uploaded_file($_FILES['panorama']['tmp_name'], $file_path)) {
                            // Delete old panorama if it exists
                            if ($scene['panorama'] && file_exists($upload_dir . $scene['panorama'])) {
                                unlink($upload_dir . $scene['panorama']);
                            }
                            $panorama = $file_name;
                        } else {
                            $_SESSION['error'] = "Failed to upload panorama image. Please try again.";
                            error_log("File upload failed. Temp: " . $_FILES['panorama']['tmp_name'] . " -> Dest: " . $file_path);
                        }
                    } else {
                        $_SESSION['error'] = "Only JPG and PNG files are allowed for panoramas.";
                    }
                } else {
                    $_SESSION['error'] = "Upload directory is not writable. Please check permissions.";
                    error_log("Upload directory not writable: " . $upload_dir);
                }
            }
            
            // Update database if no errors
            if (!isset($_SESSION['error'])) {
                $stmt = $pdo->prepare("
                    UPDATE scenes 
                    SET name = ?, description = ?, panorama = ?, initial_view = ?, updated_at = NOW() 
                    WHERE id = ?
                ");
                
                if ($stmt->execute([$name, $description, $panorama, $initial_view, $scene_id])) {
                    $_SESSION['success'] = "Scene updated successfully!";
                    header("Location: manage-scenes.php?tour_id=" . $tour_id);
                    exit();
                } else {
                    $_SESSION['error'] = "Error updating scene. Please try again.";
                    error_log("Scene update error: " . print_r($stmt->errorInfo(), true));
                }
            }
        }
    }
    
    // Get tour information
    $tour = null;
    if ($tour_id) {
        $stmt = $pdo->prepare("SELECT * FROM tours WHERE id = ?");
        $stmt->execute([$tour_id]);
        $tour = $stmt->fetch();
    }
    
    // Show edit form
    include '../includes/header.php';
    ?>
    <!-- Begin Page Content -->
    <div class="container-fluid">
        <!-- Page Heading -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Edit Scene: <?php echo htmlspecialchars($scene['name']); ?></h1>
            <a href="manage-scenes.php?tour_id=<?php echo $tour_id; ?>" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
                <i class="fas fa-arrow-left fa-sm text-white-50"></i> Back to Scenes
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

        <!-- Edit Scene Form -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Scene Information</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="?action=edit&id=<?php echo $scene_id; ?>&tour_id=<?php echo $tour_id; ?>" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Scene Name *</label>
                                <input type="text" class="form-control" id="name" name="name" required 
                                       value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : htmlspecialchars($scene['name']); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="description">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="3"><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : htmlspecialchars($scene['description']); ?></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label for="initial_view">Initial View</label>
                                <input type="text" class="form-control" id="initial_view" name="initial_view" 
                                       value="<?php echo isset($_POST['initial_view']) ? htmlspecialchars($_POST['initial_view']) : htmlspecialchars($scene['initial_view']); ?>"
                                       placeholder="0 0 0">
                                <small class="form-text text-muted">Format: "yaw pitch roll" (e.g., "0 0 0" for default view)</small>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="panorama">Panorama Image</label>
                                <?php if ($scene['panorama']): ?>
                                    <div class="mb-3">
                                        <div class="alert alert-info">
                                            <strong>Current Panorama:</strong> <?php echo htmlspecialchars($scene['panorama']); ?>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="remove_panorama" name="remove_panorama" value="1">
                                            <label class="form-check-label" for="remove_panorama">
                                                Remove current panorama
                                            </label>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="panorama" name="panorama" accept="image/*">
                                    <label class="custom-file-label" for="panorama">Choose new panorama image</label>
                                </div>
                                <small class="form-text text-muted">360° equirectangular image (JPG/PNG). Leave empty to keep current image.</small>
                            </div>
                            
                            <?php if ($tour): ?>
                                <div class="alert alert-info">
                                    <strong>Tour:</strong> <?php echo htmlspecialchars($tour['title']); ?>
                                </div>
                            <?php endif; ?>
                            
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary btn-block">
                                    <i class="fas fa-save"></i> Update Scene
                                </button>
                                <a href="manage-scenes.php?tour_id=<?php echo $tour_id; ?>" class="btn btn-secondary btn-block">Cancel</a>
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
        var fileName = document.getElementById("panorama").files[0].name;
        var nextSibling = e.target.nextElementSibling;
        nextSibling.innerText = fileName;
    });
    </script>

    <?php
    include '../includes/footer.php';
    exit();
}

// Get tour information
$tour = null;
if ($tour_id) {
    $stmt = $pdo->prepare("SELECT * FROM tours WHERE id = ?");
    $stmt->execute([$tour_id]);
    $tour = $stmt->fetch();
}

// Get scenes for this tour
$scenes = [];
if ($tour_id) {
    $stmt = $pdo->prepare("
        SELECT s.*, 
               (SELECT COUNT(*) FROM hotspots h WHERE h.scene_id = s.id) as hotspot_count
        FROM scenes s 
        WHERE s.tour_id = ? 
        ORDER BY s.id
    ");
    $stmt->execute([$tour_id]);
    $scenes = $stmt->fetchAll();
}

// Get all tours for dropdown
$all_tours = $pdo->query("SELECT id, title FROM tours ORDER BY title")->fetchAll();

include '../includes/header.php';
?>

<!-- Begin Page Content -->
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            Manage Scenes
            <?php if ($tour): ?>
                <small class="text-muted">for: <?php echo htmlspecialchars($tour['title']); ?></small>
            <?php endif; ?>
        </h1>
        <?php if ($tour_id): ?>
            <a href="?action=create&tour_id=<?php echo $tour_id; ?>" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                <i class="fas fa-plus fa-sm text-white-50"></i> Add New Scene
            </a>
        <?php endif; ?>
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

    <!-- Tour Selection Form -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Select Tour</h6>
        </div>
        <div class="card-body">
            <form method="GET" class="form-inline">
                <div class="form-group mr-2">
                    <label for="tour_id" class="sr-only">Select Tour</label>
                    <select class="form-control" id="tour_id" name="tour_id" onchange="this.form.submit()">
                        <option value="">-- Select a Tour --</option>
                        <?php foreach ($all_tours as $t): ?>
                            <option value="<?php echo $t['id']; ?>" 
                                    <?php echo $tour_id == $t['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($t['title']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php if ($tour_id): ?>
                    <a href="manage-tours.php" class="btn btn-secondary">Back to Tours</a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <?php if ($tour_id): ?>
        <!-- Scenes Table -->
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">Scenes for: <?php echo htmlspecialchars($tour['title']); ?></h6>
                <span class="badge badge-primary"><?php echo count($scenes); ?> scenes</span>
            </div>
            <div class="card-body">
                <?php if (count($scenes) > 0): ?>
                    <div class="row">
                        <?php foreach ($scenes as $scene): ?>
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card h-100">
                                    <?php if ($scene['panorama']): ?>
                                        <img src="../assets/panoramas/uploads/<?php echo htmlspecialchars($scene['panorama']); ?>" 
                                             class="card-img-top" alt="<?php echo htmlspecialchars($scene['name']); ?>" 
                                             style="height: 150px; object-fit: cover;">
                                    <?php else: ?>
                                        <div class="card-img-top bg-dark text-center py-3" style="height: 150px;">
                                            <i class="fas fa-image fa-3x text-light"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div class="card-body">
                                        <h5 class="card-title"><?php echo htmlspecialchars($scene['name']); ?></h5>
                                        <p class="card-text text-muted small">
                                            <?php echo strlen($scene['description']) > 100 ? 
                                                substr($scene['description'], 0, 100) . '...' : 
                                                $scene['description']; ?>
                                        </p>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="badge badge-info">
                                                <?php echo $scene['hotspot_count']; ?> hotspots
                                            </span>
                                            <div class="btn-group">
                                                <a href="manage-hotspots.php?scene_id=<?php echo $scene['id']; ?>" 
                                                   class="btn btn-sm btn-primary" title="Manage Hotspots">
                                                    <i class="fas fa-dot-circle"></i>
                                                </a>
                                                <a href="?action=edit&id=<?php echo $scene['id']; ?>&tour_id=<?php echo $tour_id; ?>" 
                                                   class="btn btn-sm btn-warning" title="Edit Scene">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="?action=delete&id=<?php echo $scene['id']; ?>&tour_id=<?php echo $tour_id; ?>" 
                                                   class="btn btn-sm btn-danger" 
                                                   onclick="return confirm('Are you sure you want to delete this scene? This will also delete all associated hotspots.')"
                                                   title="Delete Scene">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-footer text-muted small">
                                        Created: <?php echo date('M j, Y', strtotime($scene['created_at'])); ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-image fa-4x text-muted mb-3"></i>
                        <h5 class="text-muted">No scenes found for this tour</h5>
                        <p class="text-muted">Get started by adding your first scene</p>
                        <a href="?action=create&tour_id=<?php echo $tour_id; ?>" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add First Scene
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php else: ?>
        <div class="text-center py-5">
            <i class="fas fa-image fa-4x text-muted mb-3"></i>
            <h5 class="text-muted">Select a tour to manage scenes</h5>
            <p class="text-muted">Choose a tour from the dropdown above to view and manage its scenes</p>
        </div>
    <?php endif; ?>
</div>
<!-- /.container-fluid -->

<?php
include '../includes/footer.php';
?>