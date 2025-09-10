<?php
// admin/manage-hotspots.php

// Include config first
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/database.php';

// Then page-specific code
$page_title = "VR Tour Admin - Manage Hotspots";
redirectIfNotLoggedIn();

// Handle form actions
$action = $_GET['action'] ?? '';
$hotspot_id = $_GET['id'] ?? 0;
$scene_id = $_GET['scene_id'] ?? 0;
$tour_id = 0;

// Delete hotspot
if ($action === 'delete' && $hotspot_id) {
    $stmt = $pdo->prepare("DELETE FROM hotspots WHERE id = ?");
    if ($stmt->execute([$hotspot_id])) {
        $_SESSION['success'] = "Hotspot deleted successfully.";
    } else {
        $_SESSION['error'] = "Error deleting hotspot.";
    }
    header("Location: manage-hotspots.php?scene_id=" . ($_GET['scene_id'] ?? ''));
    exit();
}

// Handle create action - show form
if ($action === 'create') {
    $scene_id = $_GET['scene_id'] ?? 0;
    
    // Check if form was submitted
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Process form submission
        $type = $_POST['type'];
        $title = trim($_POST['title']);
        $content = trim($_POST['content']);
        $x = floatval($_POST['x']);
        $y = floatval($_POST['y']);
        $z = floatval($_POST['z']);
        $target_scene_id = ($type === 'navigation') ? $_POST['target_scene_id'] : null;
        $icon = $_POST['icon'] ?? 'info';
        $scene_id = $_POST['scene_id'];
        
        // Validate input
        if (empty($title) || empty($scene_id)) {
            $_SESSION['error'] = "Hotspot title and scene selection are required.";
        } else {
            // Insert into database
            $stmt = $pdo->prepare("
                INSERT INTO hotspots (scene_id, target_scene_id, type, title, content, x, y, z, icon, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            
            if ($stmt->execute([$scene_id, $target_scene_id, $type, $title, $content, $x, $y, $z, $icon])) {
                $_SESSION['success'] = "Hotspot created successfully!";
                header("Location: manage-hotspots.php?scene_id=" . $scene_id);
                exit();
            } else {
                $_SESSION['error'] = "Error creating hotspot. Please try again.";
                error_log("Hotspot creation error: " . print_r($stmt->errorInfo(), true));
            }
        }
    }
    
    // Get scene information for the form
    $scene = null;
    if ($scene_id) {
        $stmt = $pdo->prepare("
            SELECT s.*, t.title as tour_title, t.id as tour_id 
            FROM scenes s 
            JOIN tours t ON s.tour_id = t.id 
            WHERE s.id = ?
        ");
        $stmt->execute([$scene_id]);
        $scene = $stmt->fetch();
        
        if ($scene) {
            $tour_id = $scene['tour_id'];
        }
    }
    
    // Get all scenes for navigation targets
    $all_scenes = [];
    if ($tour_id) {
        $stmt = $pdo->prepare("SELECT id, name FROM scenes WHERE tour_id = ? AND id != ? ORDER BY name");
        $stmt->execute([$tour_id, $scene_id]);
        $all_scenes = $stmt->fetchAll();
    }
    
    // Show create form
    include '../includes/header.php';
    ?>
    <!-- Begin Page Content -->
    <div class="container-fluid">
        <!-- Page Heading -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Add New Hotspot</h1>
            <a href="manage-hotspots.php?scene_id=<?php echo $scene_id; ?>" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
                <i class="fas fa-arrow-left fa-sm text-white-50"></i> Back to Hotspots
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

        <!-- Create Hotspot Form -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Hotspot Information</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="?action=create">
                    <input type="hidden" name="scene_id" value="<?php echo $scene_id; ?>">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="type">Hotspot Type *</label>
                                <select class="form-control" id="type" name="type" required onchange="toggleTargetScene()">
                                    <option value="info">Information</option>
                                    <option value="navigation">Navigation</option>
                                    <option value="media">Media</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="title">Title *</label>
                                <input type="text" class="form-control" id="title" name="title" required 
                                       value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="content">Content (for Info hotspots)</label>
                                <textarea class="form-control" id="content" name="content" rows="3"><?php echo isset($_POST['content']) ? htmlspecialchars($_POST['content']) : ''; ?></textarea>
                            </div>
                            
                            <div id="targetSceneGroup" class="form-group" style="display: none;">
                                <label for="target_scene_id">Target Scene (for Navigation hotspots) *</label>
                                <select class="form-control" id="target_scene_id" name="target_scene_id">
                                    <option value="">-- Select Target Scene --</option>
                                    <?php foreach ($all_scenes as $target_scene): ?>
                                        <option value="<?php echo $target_scene['id']; ?>">
                                            <?php echo htmlspecialchars($target_scene['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="x">X Position</label>
                                <input type="number" step="0.01" class="form-control" id="x" name="x" 
                                       value="<?php echo isset($_POST['x']) ? htmlspecialchars($_POST['x']) : '0'; ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="y">Y Position</label>
                                <input type="number" step="0.01" class="form-control" id="y" name="y" 
                                       value="<?php echo isset($_POST['y']) ? htmlspecialchars($_POST['y']) : '0'; ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="z">Z Position</label>
                                <input type="number" step="0.01" class="form-control" id="z" name="z" 
                                       value="<?php echo isset($_POST['z']) ? htmlspecialchars($_POST['z']) : '0'; ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="icon">Icon</label>
                                <select class="form-control" id="icon" name="icon">
                                    <option value="info">Info</option>
                                    <option value="arrow">Arrow</option>
                                    <option value="image">Image</option>
                                    <option value="video">Video</option>
                                    <option value="audio">Audio</option>
                                </select>
                            </div>
                            
                            <?php if ($scene): ?>
                                <div class="alert alert-info">
                                    <strong>Scene:</strong> <?php echo htmlspecialchars($scene['name']); ?>
                                </div>
                            <?php endif; ?>
                            
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary btn-block">
                                    <i class="fas fa-save"></i> Create Hotspot
                                </button>
                                <a href="manage-hotspots.php?scene_id=<?php echo $scene_id; ?>" class="btn btn-secondary btn-block">Cancel</a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- /.container-fluid -->

    <script>
    function toggleTargetScene() {
        var type = document.getElementById('type').value;
        var targetSceneGroup = document.getElementById('targetSceneGroup');
        var targetSceneSelect = document.getElementById('target_scene_id');
        
        if (type === 'navigation') {
            targetSceneGroup.style.display = 'block';
            targetSceneSelect.required = true;
        } else {
            targetSceneGroup.style.display = 'none';
            targetSceneSelect.required = false;
        }
    }
    
    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        toggleTargetScene();
    });
    </script>

    <?php
    include '../includes/footer.php';
    exit();
}

// Handle edit action - show edit form
if ($action === 'edit' && $hotspot_id) {
    // Get hotspot data
    $stmt = $pdo->prepare("SELECT * FROM hotspots WHERE id = ?");
    $stmt->execute([$hotspot_id]);
    $hotspot = $stmt->fetch();
    
    if (!$hotspot) {
        $_SESSION['error'] = "Hotspot not found.";
        header("Location: manage-hotspots.php");
        exit();
    }
    
    $scene_id = $hotspot['scene_id'];
    
    // Get scene and tour information
    $scene = null;
    $tour_id = 0;
    if ($scene_id) {
        $stmt = $pdo->prepare("
            SELECT s.*, t.title as tour_title, t.id as tour_id 
            FROM scenes s 
            JOIN tours t ON s.tour_id = t.id 
            WHERE s.id = ?
        ");
        $stmt->execute([$scene_id]);
        $scene = $stmt->fetch();
        
        if ($scene) {
            $tour_id = $scene['tour_id'];
        }
    }
    
    // Get all scenes for navigation targets
    $all_scenes = [];
    if ($tour_id) {
        $stmt = $pdo->prepare("SELECT id, name FROM scenes WHERE tour_id = ? AND id != ? ORDER BY name");
        $stmt->execute([$tour_id, $scene_id]);
        $all_scenes = $stmt->fetchAll();
    }
    
    // Check if form was submitted
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Process form submission
        $type = $_POST['type'];
        $title = trim($_POST['title']);
        $content = trim($_POST['content']);
        $x = floatval($_POST['x']);
        $y = floatval($_POST['y']);
        $z = floatval($_POST['z']);
        $target_scene_id = ($type === 'navigation') ? $_POST['target_scene_id'] : null;
        $icon = $_POST['icon'] ?? 'info';
        
        // Validate input
        if (empty($title)) {
            $_SESSION['error'] = "Hotspot title is required.";
        } else {
            // Update database
            $stmt = $pdo->prepare("
                UPDATE hotspots 
                SET type = ?, title = ?, content = ?, x = ?, y = ?, z = ?, 
                    target_scene_id = ?, icon = ?, updated_at = NOW() 
                WHERE id = ?
            ");
            
            if ($stmt->execute([$type, $title, $content, $x, $y, $z, $target_scene_id, $icon, $hotspot_id])) {
                $_SESSION['success'] = "Hotspot updated successfully!";
                header("Location: manage-hotspots.php?scene_id=" . $scene_id);
                exit();
            } else {
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
            <h1 class="h3 mb-0 text-gray-800">Edit Hotspot: <?php echo htmlspecialchars($hotspot['title']); ?></h1>
            <a href="manage-hotspots.php?scene_id=<?php echo $scene_id; ?>" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
                <i class="fas fa-arrow-left fa-sm text-white-50"></i> Back to Hotspots
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

        <!-- Edit Hotspot Form -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Hotspot Information</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="?action=edit&id=<?php echo $hotspot_id; ?>&scene_id=<?php echo $scene_id; ?>">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="type">Hotspot Type *</label>
                                <select class="form-control" id="type" name="type" required onchange="toggleTargetScene()">
                                    <option value="info" <?php echo $hotspot['type'] === 'info' ? 'selected' : ''; ?>>Information</option>
                                    <option value="navigation" <?php echo $hotspot['type'] === 'navigation' ? 'selected' : ''; ?>>Navigation</option>
                                    <option value="media" <?php echo $hotspot['type'] === 'media' ? 'selected' : ''; ?>>Media</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="title">Title *</label>
                                <input type="text" class="form-control" id="title" name="title" required 
                                       value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : htmlspecialchars($hotspot['title']); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="content">Content (for Info hotspots)</label>
                                <textarea class="form-control" id="content" name="content" rows="3"><?php echo isset($_POST['content']) ? htmlspecialchars($_POST['content']) : htmlspecialchars($hotspot['content']); ?></textarea>
                            </div>
                            
                            <div id="targetSceneGroup" class="form-group" style="<?php echo $hotspot['type'] === 'navigation' ? '' : 'display: none;'; ?>">
                                <label for="target_scene_id">Target Scene (for Navigation hotspots) *</label>
                                <select class="form-control" id="target_scene_id" name="target_scene_id" <?php echo $hotspot['type'] === 'navigation' ? 'required' : ''; ?>>
                                    <option value="">-- Select Target Scene --</option>
                                    <?php foreach ($all_scenes as $target_scene): ?>
                                        <option value="<?php echo $target_scene['id']; ?>" 
                                            <?php echo $hotspot['target_scene_id'] == $target_scene['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($target_scene['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="x">X Position</label>
                                <input type="number" step="0.01" class="form-control" id="x" name="x" 
                                       value="<?php echo isset($_POST['x']) ? htmlspecialchars($_POST['x']) : htmlspecialchars($hotspot['x']); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="y">Y Position</label>
                                <input type="number" step="0.01" class="form-control" id="y" name="y" 
                                       value="<?php echo isset($_POST['y']) ? htmlspecialchars($_POST['y']) : htmlspecialchars($hotspot['y']); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="z">Z Position</label>
                                <input type="number" step="0.01" class="form-control" id="z" name="z" 
                                       value="<?php echo isset($_POST['z']) ? htmlspecialchars($_POST['z']) : htmlspecialchars($hotspot['z']); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="icon">Icon</label>
                                <select class="form-control" id="icon" name="icon">
                                    <option value="info" <?php echo $hotspot['icon'] === 'info' ? 'selected' : ''; ?>>Info</option>
                                    <option value="arrow" <?php echo $hotspot['icon'] === 'arrow' ? 'selected' : ''; ?>>Arrow</option>
                                    <option value="image" <?php echo $hotspot['icon'] === 'image' ? 'selected' : ''; ?>>Image</option>
                                    <option value="video" <?php echo $hotspot['icon'] === 'video' ? 'selected' : ''; ?>>Video</option>
                                    <option value="audio" <?php echo $hotspot['icon'] === 'audio' ? 'selected' : ''; ?>>Audio</option>
                                </select>
                            </div>
                            
                            <?php if ($scene): ?>
                                <div class="alert alert-info">
                                    <strong>Scene:</strong> <?php echo htmlspecialchars($scene['name']); ?>
                                </div>
                            <?php endif; ?>
                            
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary btn-block">
                                    <i class="fas fa-save"></i> Update Hotspot
                                </button>
                                <a href="manage-hotspots.php?scene_id=<?php echo $scene_id; ?>" class="btn btn-secondary btn-block">Cancel</a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- /.container-fluid -->

    <script>
    function toggleTargetScene() {
        var type = document.getElementById('type').value;
        var targetSceneGroup = document.getElementById('targetSceneGroup');
        var targetSceneSelect = document.getElementById('target_scene_id');
        
        if (type === 'navigation') {
            targetSceneGroup.style.display = 'block';
            targetSceneSelect.required = true;
        } else {
            targetSceneGroup.style.display = 'none';
            targetSceneSelect.required = false;
        }
    }
    
    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        toggleTargetScene();
    });
    </script>

    <?php
    include '../includes/footer.php';
    exit();
}

// Get scene and tour information
$scene = null;
$tour = null;
if ($scene_id) {
    $stmt = $pdo->prepare("
        SELECT s.*, t.title as tour_title, t.id as tour_id 
        FROM scenes s 
        JOIN tours t ON s.tour_id = t.id 
        WHERE s.id = ?
    ");
    $stmt->execute([$scene_id]);
    $scene = $stmt->fetch();
    
    if ($scene) {
        $tour_id = $scene['tour_id'];
    }
}

// Get hotspots for this scene
$hotspots = [];
if ($scene_id) {
    $stmt = $pdo->prepare("
        SELECT h.*, s.name as target_scene_name 
        FROM hotspots h 
        LEFT JOIN scenes s ON h.target_scene_id = s.id 
        WHERE h.scene_id = ? 
        ORDER BY h.type, h.id
    ");
    $stmt->execute([$scene_id]);
    $hotspots = $stmt->fetchAll();
}

// Get all scenes for navigation hotspots
$all_scenes = [];
if ($tour_id) {
    $stmt = $pdo->prepare("SELECT id, name FROM scenes WHERE tour_id = ? ORDER BY name");
    $stmt->execute([$tour_id]);
    $all_scenes = $stmt->fetchAll();
}

include '../includes/header.php';
?>

<!-- Begin Page Content -->
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            Manage Hotspots
            <?php if ($scene): ?>
                <small class="text-muted">for: <?php echo htmlspecialchars($scene['name']); ?></small>
            <?php endif; ?>
        </h1>
        <?php if ($scene_id && $tour_id): ?>
            <div>
                <a href="manage-scenes.php?tour_id=<?php echo $tour_id; ?>" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Scenes
                </a>
                <a href="?action=create&scene_id=<?php echo $scene_id; ?>" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add Hotspot
                </a>
            </div>
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

    <!-- Breadcrumb -->
    <?php if ($scene && $tour_id): ?>
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="manage-tours.php">Tours</a></li>
                <li class="breadcrumb-item"><a href="manage-scenes.php?tour_id=<?php echo $tour_id; ?>"><?php echo htmlspecialchars($scene['tour_title']); ?></a></li>
                <li class="breadcrumb-item active"><?php echo htmlspecialchars($scene['name']); ?> Hotspots</li>
            </ol>
        </nav>
    <?php endif; ?>

    <?php if ($scene_id && $tour_id): ?>
        <!-- Hotspots Table -->
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">
                    Hotspots for: <?php echo htmlspecialchars($scene['name']); ?>
                </h6>
                <span class="badge badge-primary"><?php echo count($hotspots); ?> hotspots</span>
            </div>
            <div class="card-body">
                <?php if (count($hotspots) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="thead-light">
                                <tr>
                                    <th>Type</th>
                                    <th>Title/Content</th>
                                    <th>Position</th>
                                    <th>Target</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($hotspots as $hotspot): ?>
                                    <tr>
                                        <td>
                                            <span class="badge badge-<?php 
                                                echo $hotspot['type'] === 'navigation' ? 'primary' : 
                                                    ($hotspot['type'] === 'info' ? 'info' : 'success'); 
                                            ?>">
                                                <?php echo ucfirst($hotspot['type']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($hotspot['title'] ?? 'No Title'); ?></strong>
                                            <?php if ($hotspot['type'] === 'info' && $hotspot['content']): ?>
                                                <br>
                                                <small class="text-muted">
                                                    <?php echo strlen($hotspot['content']) > 50 ? 
                                                        substr($hotspot['content'], 0, 50) . '...' : 
                                                        $hotspot['content']; ?>
                                                </small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <small>
                                                X: <?php echo $hotspot['x']; ?><br>
                                                Y: <?php echo $hotspot['y']; ?><br>
                                                Z: <?php echo $hotspot['z']; ?>
                                            </small>
                                        </td>
                                        <td>
                                            <?php if ($hotspot['type'] === 'navigation' && $hotspot['target_scene_name']): ?>
                                                <i class="fas fa-arrow-right"></i> 
                                                <?php echo htmlspecialchars($hotspot['target_scene_name']); ?>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="?action=edit&id=<?php echo $hotspot['id']; ?>&scene_id=<?php echo $scene_id; ?>" 
                                                   class="btn btn-warning" title="Edit Hotspot">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="?action=delete&id=<?php echo $hotspot['id']; ?>&scene_id=<?php echo $scene_id; ?>" 
                                                   class="btn btn-danger" 
                                                   onclick="return confirm('Are you sure you want to delete this hotspot?')"
                                                   title="Delete Hotspot">
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
                        <i class="fas fa-dot-circle fa-4x text-muted mb-3"></i>
                        <h5 class="text-muted">No hotspots found for this scene</h5>
                        <p class="text-muted">Add hotspots to make your scene interactive</p>
                        <a href="?action=create&scene_id=<?php echo $scene_id; ?>" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add First Hotspot
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Hotspot Types Summary -->
        <div class="row">
            <div class="col-md-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Navigation Hotspots</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <?php echo count(array_filter($hotspots, function($h) { return $h['type'] === 'navigation'; })); ?>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-arrow-right fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                    Info Hotspots</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <?php echo count(array_filter($hotspots, function($h) { return $h['type'] === 'info'; })); ?>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-info-circle fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    Media Hotspots</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <?php echo count(array_filter($hotspots, function($h) { return $h['type'] === 'media'; })); ?>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-play-circle fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="text-center py-5">
            <i class="fas fa-dot-circle fa-4x text-muted mb-3"></i>
            <h5 class="text-muted">Select a scene to manage hotspots</h5>
            <p class="text-muted">Go to the scenes management page and select a scene to view and manage its hotspots</p>
            <a href="manage-scenes.php" class="btn btn-primary">
                <i class="fas fa-image"></i> Manage Scenes
            </a>
        </div>
    <?php endif; ?>
</div>
<!-- /.container-fluid -->

<?php
include '../includes/footer.php';
?>