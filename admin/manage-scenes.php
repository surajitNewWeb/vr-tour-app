<?php
// Include config first
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/database.php';

// Then your page-specific code
$page_title = "Page Title";
redirectIfNotLoggedIn();
// ... rest of your code

$tour_id = $_GET['tour_id'] ?? 0;

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
                                    <div class="card-img-top bg-dark text-center py-3">
                                        <i class="fas fa-image fa-3x text-light"></i>
                                    </div>
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
                                                   onclick="return confirm('Are you sure you want to delete this scene?')"
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