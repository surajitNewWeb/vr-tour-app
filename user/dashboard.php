<?php
// user/dashboard.php
require_once '../includes/config.php';
require_once '../includes/user-auth.php';
require_once '../includes/database.php';

// Check if user is logged in, redirect if not
if (!isUserLoggedIn()) {
    header("Location: ../login.php");
    exit();
}

// Get user data
$user_data = getUserData();

// Get user's favorite tours
$stmt = $pdo->prepare("
    SELECT t.* 
    FROM tours t 
    INNER JOIN favorites f ON t.id = f.tour_id 
    WHERE f.user_id = :user_id AND t.published = 1
    ORDER BY f.created_at DESC 
    LIMIT 6
");
$stmt->execute([':user_id' => $_SESSION['user_id']]);
$favorite_tours = $stmt->fetchAll();

// Get user's progress
$stmt = $pdo->prepare("
    SELECT t.id, t.title, t.thumbnail, p.last_scene_id, p.completed, p.updated_at,
           (SELECT COUNT(*) FROM scenes s WHERE s.tour_id = t.id) as total_scenes
    FROM progress p 
    INNER JOIN tours t ON p.tour_id = t.id 
    WHERE p.user_id = :user_id 
    ORDER BY p.updated_at DESC 
    LIMIT 6
");
$stmt->execute([':user_id' => $_SESSION['user_id']]);
$user_progress = $stmt->fetchAll();

$page_title = "User Dashboard - VR Tour Application";
include '../includes/user-header.php';
?>

<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>Welcome, <?php echo htmlspecialchars($user_data['username']); ?>!</h1>
                <a href="profile.php" class="btn btn-outline-primary">
                    <i class="fas fa-user"></i> Edit Profile
                </a>
            </div>
        </div>
    </div>

    <!-- User Stats -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title">Favorite Tours</h5>
                    <h3 class="text-primary"><?php echo count($favorite_tours); ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title">Tours in Progress</h5>
                    <h3 class="text-warning"><?php echo count(array_filter($user_progress, function($p) { return !$p['completed']; })); ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title">Completed Tours</h5>
                    <h3 class="text-success"><?php echo count(array_filter($user_progress, function($p) { return $p['completed']; })); ?></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Favorite Tours -->
    <?php if (!empty($favorite_tours)): ?>
    <div class="row mb-4">
        <div class="col-md-12">
            <h3>Your Favorite Tours</h3>
            <div class="row">
                <?php foreach ($favorite_tours as $tour): ?>
                <div class="col-md-4 mb-3">
                    <div class="card h-100">
                        <?php if ($tour['thumbnail']): ?>
                            <img src="../assets/images/uploads/<?php echo $tour['thumbnail']; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($tour['title']); ?>">
                        <?php else: ?>
                            <div class="card-img-top bg-secondary d-flex align-items-center justify-content-center" style="height: 150px;">
                                <i class="fas fa-image fa-2x text-light"></i>
                            </div>
                        <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($tour['title']); ?></h5>
                            <p class="card-text"><?php echo htmlspecialchars(substr($tour['description'], 0, 100)); ?>...</p>
                        </div>
                        <div class="card-footer">
                            <a href="../vr/tour.php?id=<?php echo $tour['id']; ?>" class="btn btn-primary btn-sm">
                                <i class="fas fa-play"></i> Continue Tour
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Recent Progress -->
    <?php if (!empty($user_progress)): ?>
    <div class="row">
        <div class="col-md-12">
            <h3>Recent Activity</h3>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Tour</th>
                            <th>Progress</th>
                            <th>Status</th>
                            <th>Last Activity</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($user_progress as $progress): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($progress['title']); ?></td>
                            <td>
                                <div class="progress" style="height: 10px;">
                                    <?php 
                                    $progress_percent = $progress['total_scenes'] > 0 
                                        ? (($progress['last_scene_id'] / $progress['total_scenes']) * 100) 
                                        : 0;
                                    ?>
                                    <div class="progress-bar" style="width: <?php echo min($progress_percent, 100); ?>%"></div>
                                </div>
                                <small><?php echo round(min($progress_percent, 100)); ?>% complete</small>
                            </td>
                            <td>
                                <span class="badge bg-<?php echo $progress['completed'] ? 'success' : 'warning'; ?>">
                                    <?php echo $progress['completed'] ? 'Completed' : 'In Progress'; ?>
                                </span>
                            </td>
                            <td><?php echo time_ago($progress['updated_at']); ?></td>
                            <td>
                                <a href="../vr/tour.php?id=<?php echo $progress['id']; ?>" class="btn btn-primary btn-sm">
                                    <i class="fas fa-play"></i> Continue
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php else: ?>
    <div class="row">
        <div class="col-md-12">
            <div class="alert alert-info">
                <h4>No activity yet!</h4>
                <p>Start exploring VR tours to see your progress here.</p>
                <a href="../tours.php" class="btn btn-primary">Browse Tours</a>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php include '../includes/user-footer.php'; ?>