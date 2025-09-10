<?php
// admin/dashboard.php

// Include config first
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/database.php';

// Then page-specific code
$page_title = "VR Tour Admin - Dashboard";
redirectIfNotLoggedIn();

// Get stats for dashboard
$tours_count = $pdo->query("SELECT COUNT(*) FROM tours")->fetchColumn();
$scenes_count = $pdo->query("SELECT COUNT(*) FROM scenes")->fetchColumn();
$hotspots_count = $pdo->query("SELECT COUNT(*) FROM hotspots")->fetchColumn();
$users_count = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();

// Get recent tours
$recent_tours = $pdo->query("
    SELECT t.*, u.username as creator 
    FROM tours t 
    LEFT JOIN users u ON t.created_by = u.id 
    ORDER BY t.created_at DESC 
    LIMIT 5
")->fetchAll();

// Get recent users
$recent_users = $pdo->query("
    SELECT * FROM users 
    ORDER BY created_at DESC 
    LIMIT 5
")->fetchAll();

// Get tours by status
$published_tours = $pdo->query("SELECT COUNT(*) FROM tours WHERE published = 1")->fetchColumn();
$draft_tours = $pdo->query("SELECT COUNT(*) FROM tours WHERE published = 0")->fetchColumn();

include '../includes/header.php';
?>

<!-- Begin Page Content -->
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
        <a href="manage-tours.php" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-plus fa-sm text-white-50"></i> Create New Tour
        </a>
    </div>

    <!-- Content Row -->
    <div class="row">
        <!-- Tours Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Tours</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $tours_count; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-map-marked fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <small><?php echo $published_tours; ?> Published, <?php echo $draft_tours; ?> Draft</small>
                </div>
            </div>
        </div>

        <!-- Scenes Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Total Scenes</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $scenes_count; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-image fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Hotspots Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Total Hotspots</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $hotspots_count; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dot-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Users Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Total Users</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $users_count; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Content Row -->
    <div class="row">
        <!-- Recent Tours -->
        <div class="col-xl-6 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Tours</h6>
                    <a href="manage-tours.php" class="text-decoration-none small">View All</a>
                </div>
                <div class="card-body">
                    <?php if (count($recent_tours) > 0): ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($recent_tours as $tour): ?>
                                <div class="list-group-item d-flex align-items-center px-0">
                                    <div class="mr-3">
                                        <div class="icon-circle bg-primary">
                                            <i class="fas fa-map-marked text-white"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="font-weight-bold text-gray-800"><?php echo htmlspecialchars($tour['title']); ?></div>
                                        <div class="small text-gray-500">
                                            Created by <?php echo htmlspecialchars($tour['creator'] ?? 'Admin'); ?> • 
                                            <?php echo date('M j, Y', strtotime($tour['created_at'])); ?>
                                        </div>
                                    </div>
                                    <span class="badge badge-<?php echo $tour['published'] ? 'success' : 'secondary'; ?>">
                                        <?php echo $tour['published'] ? 'Published' : 'Draft'; ?>
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-center text-muted">No tours found. <a href="manage-tours.php">Create your first tour</a></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Recent Users -->
        <div class="col-xl-6 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Users</h6>
                    <a href="manage-users.php" class="text-decoration-none small">View All</a>
                </div>
                <div class="card-body">
                    <?php if (count($recent_users) > 0): ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($recent_users as $user): ?>
                                <div class="list-group-item d-flex align-items-center px-0">
                                    <div class="mr-3">
                                        <img class="img-profile rounded-circle" width="40" height="40" 
                                             src="https://via.placeholder.com/40" alt="User Avatar">
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="font-weight-bold text-gray-800"><?php echo htmlspecialchars($user['username']); ?></div>
                                        <div class="small text-gray-500">
                                            <?php echo htmlspecialchars($user['email']); ?> • 
                                            Joined <?php echo date('M j, Y', strtotime($user['created_at'])); ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-center text-muted">No users registered yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Content Row -->
    <div class="row">
        <!-- Tours Status Chart -->
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Tours Overview</h6>
                </div>
                <div class="card-body">
                    <div class="chart-bar" style="height: 300px;">
                        <canvas id="toursChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Info -->
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">System Information</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <span class="font-weight-bold">PHP Version:</span> <?php echo phpversion(); ?>
                    </div>
                    <div class="mb-3">
                        <span class="font-weight-bold">Server Software:</span> <?php echo $_SERVER['SERVER_SOFTWARE']; ?>
                    </div>
                    <div class="mb-3">
                        <span class="font-weight-bold">Database:</span> MySQL
                    </div>
                    <div class="mb-3">
                        <span class="font-weight-bold">VR Framework:</span> A-Frame
                    </div>
                    <div class="mb-3">
                        <span class="font-weight-bold">Admin User:</span> <?php echo $_SESSION['admin_username']; ?>
                    </div>
                    <div class="mb-0">
                        <span class="font-weight-bold">Login Time:</span> 
                        <?php echo date('M j, Y g:i A', $_SESSION['login_time']); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- /.container-fluid -->

<script>
// Tours Chart
document.addEventListener('DOMContentLoaded', function() {
    var ctx = document.getElementById('toursChart').getContext('2d');
    var toursChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Published Tours', 'Draft Tours', 'Total Scenes', 'Total Hotspots'],
            datasets: [{
                label: 'Count',
                data: [
                    <?php echo $published_tours; ?>,
                    <?php echo $draft_tours; ?>,
                    <?php echo $scenes_count; ?>,
                    <?php echo $hotspots_count; ?>
                ],
                backgroundColor: [
                    'rgba(78, 115, 223, 0.7)',
                    'rgba(108, 117, 125, 0.7)',
                    'rgba(28, 200, 138, 0.7)',
                    'rgba(54, 185, 204, 0.7)'
                ],
                borderColor: [
                    'rgba(78, 115, 223, 1)',
                    'rgba(108, 117, 125, 1)',
                    'rgba(28, 200, 138, 1)',
                    'rgba(54, 185, 204, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0
                    }
                }
            }
        }
    });
});
</script>

<?php
include '../includes/footer.php';
?>