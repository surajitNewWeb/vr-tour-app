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
$review_id = $_GET['id'] ?? 0;

// Approve review
if ($action === 'approve' && $review_id) {
    $stmt = $pdo->prepare("UPDATE reviews SET approved = 1 WHERE id = ?");
    if ($stmt->execute([$review_id])) {
        $_SESSION['success'] = "Review approved successfully.";
    } else {
        $_SESSION['error'] = "Error approving review.";
    }
    header("Location: manage-reviews.php");
    exit();
}

// Delete review
if ($action === 'delete' && $review_id) {
    $stmt = $pdo->prepare("DELETE FROM reviews WHERE id = ?");
    if ($stmt->execute([$review_id])) {
        $_SESSION['success'] = "Review deleted successfully.";
    } else {
        $_SESSION['error'] = "Error deleting review.";
    }
    header("Location: manage-reviews.php");
    exit();
}

// Get all reviews with user and tour information
$reviews = $pdo->query("
    SELECT r.*, u.username, u.email, t.title as tour_title 
    FROM reviews r 
    JOIN users u ON r.user_id = u.id 
    JOIN tours t ON r.tour_id = t.id 
    ORDER BY r.created_at DESC
")->fetchAll();

// Count approved and pending reviews
$approved_reviews = array_filter($reviews, function($review) { return $review['approved']; });
$pending_reviews = array_filter($reviews, function($review) { return !$review['approved']; });

include '../includes/header.php';
?>

<!-- Begin Page Content -->
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Manage Reviews</h1>
        <div>
            <span class="badge badge-success mr-2"><?php echo count($approved_reviews); ?> Approved</span>
            <span class="badge badge-warning"><?php echo count($pending_reviews); ?> Pending</span>
        </div>
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

    <!-- Reviews Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">All Reviews</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="reviewsTable" width="100%" cellspacing="0">
                    <thead class="thead-light">
                        <tr>
                            <th>Tour</th>
                            <th>User</th>
                            <th>Rating</th>
                            <th>Comment</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reviews as $review): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($review['tour_title']); ?></strong>
                                </td>
                                <td>
                                    <div><?php echo htmlspecialchars($review['username']); ?></div>
                                    <small class="text-muted"><?php echo htmlspecialchars($review['email']); ?></small>
                                </td>
                                <td>
                                    <div class="text-warning">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="fas fa-star<?php echo $i <= $review['rating'] ? '' : '-half-alt'; ?>"></i>
                                        <?php endfor; ?>
                                        <br>
                                        <small class="text-muted">(<?php echo $review['rating']; ?>/5)</small>
                                    </div>
                                </td>
                                <td>
                                    <?php echo strlen($review['comment']) > 100 ? 
                                        substr($review['comment'], 0, 100) . '...' : 
                                        $review['comment']; ?>
                                </td>
                                <td><?php echo date('M j, Y', strtotime($review['created_at'])); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo $review['approved'] ? 'success' : 'warning'; ?>">
                                        <?php echo $review['approved'] ? 'Approved' : 'Pending'; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <?php if (!$review['approved']): ?>
                                            <a href="?action=approve&id=<?php echo $review['id']; ?>" 
                                               class="btn btn-success" title="Approve Review">
                                                <i class="fas fa-check"></i>
                                            </a>
                                        <?php endif; ?>
                                        <a href="?action=delete&id=<?php echo $review['id']; ?>" 
                                           class="btn btn-danger" 
                                           onclick="return confirm('Are you sure you want to delete this review?')"
                                           title="Delete Review">
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

    <!-- Reviews Statistics -->
    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Reviews</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo count($reviews); ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-star fa-2x text-gray-300"></i>
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
                                Approved Reviews</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo count($approved_reviews); ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
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
                                Pending Reviews</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo count($pending_reviews); ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
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
                                Average Rating</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php
                                if (count($reviews) > 0) {
                                    $total_rating = array_sum(array_column($reviews, 'rating'));
                                    echo number_format($total_rating / count($reviews), 1);
                                } else {
                                    echo '0.0';
                                }
                                ?>/5
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-line fa-2x text-gray-300"></i>
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
    $('#reviewsTable').DataTable({
        pageLength: 10,
        ordering: true,
        order: [[4, 'desc']], // Sort by date descending
        responsive: true
    });
});
</script>

<?php
include '../includes/footer.php';
?>