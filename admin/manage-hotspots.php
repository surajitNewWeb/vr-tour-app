<?php
// admin/manage-hotspots.php

// Include config first
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/database.php';

// Then page-specific code
$page_title = "VR Tour Admin - Manage Hotspots";
redirectIfNotLoggedIn();

$scene_id = $_GET['scene_id'] ?? 0;
$tour_id = 0; // Initialize with default value

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