<?php
// Include config first
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/database.php';

// Then your page-specific code
$page_title = "Page Title";
redirectIfNotLoggedIn();
// ... rest of your code

// Handle actions
$action = $_GET['action'] ?? '';
$user_id = $_GET['id'] ?? 0;

// Delete user
if ($action === 'delete' && $user_id) {
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    if ($stmt->execute([$user_id])) {
        $_SESSION['success'] = "User deleted successfully.";
    } else {
        $_SESSION['error'] = "Error deleting user.";
    }
    header("Location: manage-users.php");
    exit();
}

// Get all users
$users = $pdo->query("
    SELECT u.*, 
           (SELECT COUNT(*) FROM favorites f WHERE f.user_id = u.id) as favorites_count,
           (SELECT COUNT(*) FROM reviews r WHERE r.user_id = u.id) as reviews_count
    FROM users u 
    ORDER BY u.created_at DESC
")->fetchAll();

include '../includes/header.php';
?>

<!-- Begin Page Content -->
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Manage Users</h1>
        <span class="badge badge-primary"><?php echo count($users); ?> users</span>
    </div>

    <!-- Users Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">All Users</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="usersTable" width="100%" cellspacing="0">
                    <thead class="thead-light">
                        <tr>
                            <th>User</th>
                            <th>Email</th>
                            <th>Joined</th>
                            <th>Activity</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img class="img-profile rounded-circle mr-3" 
                                             src="https://via.placeholder.com/40" 
                                             width="40" height="40" alt="Avatar">
                                        <div>
                                            <strong><?php echo htmlspecialchars($user['username']); ?></strong>
                                            <div class="small text-muted">ID: <?php echo $user['id']; ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                                <td>
                                    <div class="d-flex">
                                        <span class="badge badge-info mr-2" title="Favorites">
                                            <i class="fas fa-heart"></i> <?php echo $user['favorites_count']; ?>
                                        </span>
                                        <span class="badge badge-warning" title="Reviews">
                                            <i class="fas fa-star"></i> <?php echo $user['reviews_count']; ?>
                                        </span>
                                    </div>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="../profile.php?id=<?php echo $user['id']; ?>" 
                                           class="btn btn-info" target="_blank" title="View Profile">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="?action=delete&id=<?php echo $user['id']; ?>" 
                                           class="btn btn-danger" 
                                           onclick="return confirm('Are you sure you want to delete this user?')"
                                           title="Delete User">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Users Statistics -->
    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Users</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo count($users); ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Active Today</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">0</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-check fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                New This Week</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php
                                $one_week_ago = date('Y-m-d H:i:s', strtotime('-1 week'));
                                $new_users = array_filter($users, function($user) use ($one_week_ago) {
                                    return $user['created_at'] > $one_week_ago;
                                });
                                echo count($new_users);
                                ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-plus fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Total Reviews</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php
                                $total_reviews = array_sum(array_column($users, 'reviews_count'));
                                echo $total_reviews;
                                ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-star fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- /.container-fluid -->

<script>
// Initialize DataTables
document.addEventListener('DOMContentLoaded', function() {
    $('#usersTable').DataTable({
        pageLength: 10,
        ordering: true,
        order: [[2, 'desc']], // Sort by join date descending
        responsive: true
    });
});
</script>

<?php
include '../includes/footer.php';
?>