<?php
// admin/dashboard.php
$page_title = "VR Tour Admin - Dashboard";
require_once '../includes/auth.php';
require_once '../includes/database.php';
redirectIfNotLoggedIn();

// Get stats for dashboard
$tours_count = $pdo->query("SELECT COUNT(*) FROM tours")->fetchColumn();
$scenes_count = $pdo->query("SELECT COUNT(*) FROM scenes")->fetchColumn();
$hotspots_count = $pdo->query("SELECT COUNT(*) FROM hotspots")->fetchColumn();
$users_count = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();

include '../includes/header.php';
?>

<!-- Begin Page Content -->
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
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
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Tours</h6>
                </div>
                <div class="card-body">
                    <?php
                    $recent_tours = $pdo->query("SELECT * FROM tours ORDER BY created_at DESC LIMIT 5")->fetchAll();
                    
                    if (count($recent_tours) > 0) {
                        foreach ($recent_tours as $tour) {
                            echo '<div class="mb-3">';
                            echo '<div class="font-weight-bold">' . htmlspecialchars($tour['title']) . '</div>';
                            echo '<div class="small text-gray-500">Created: ' . date('M j, Y', strtotime($tour['created_at'])) . '</div>';
                            echo '</div>';
                        }
                    } else {
                        echo '<p class="text-center">No tours found. <a href="manage-tours.php">Create your first tour</a></p>';
                    }
                    ?>
                </div>
            </div>
        </div>

        <!-- System Info -->
        <div class="col-xl-6 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">System Information</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-2">
                                <span class="font-weight-bold">PHP Version:</span> <?php echo phpversion(); ?>
                            </div>
                            <div class="mb-2">
                                <span class="font-weight-bold">Server Software:</span> <?php echo $_SERVER['SERVER_SOFTWARE']; ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-2">
                                <span class="font-weight-bold">Database:</span> MySQL
                            </div>
                            <div class="mb-2">
                                <span class="font-weight-bold">VR Framework:</span> A-Frame
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- /.container-fluid -->

<?php
include '../includes/footer.php';
?>