<?php
// admin/manage-reviews.php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/database.php';
require_once '../includes/header.php';
require_once '../includes/sidebar.php';

// Check permission
redirectIfNotLoggedIn();

// Handle actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $reviewId = intval($_GET['id']);
    
    if ($_GET['action'] == 'approve') {
        $stmt = $pdo->prepare("UPDATE reviews SET approved = 1 WHERE id = ?");
        $stmt->execute([$reviewId]);
        $_SESSION['success'] = "Review approved successfully.";
    } 
    elseif ($_GET['action'] == 'delete') {
        $stmt = $pdo->prepare("DELETE FROM reviews WHERE id = ?");
        $stmt->execute([$reviewId]);
        $_SESSION['success'] = "Review deleted successfully.";
    }
}

// Pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Filters
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$whereClause = "";
$params = [];

if ($filter == 'pending') {
    $whereClause = "WHERE r.approved = 0";
} elseif ($filter == 'approved') {
    $whereClause = "WHERE r.approved = 1";
}

// Get total count
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM reviews r $whereClause");
$countStmt->execute($params);
$totalReviews = $countStmt->fetchColumn();
$totalPages = ceil($totalReviews / $limit);

// Get reviews
$stmt = $pdo->prepare("
    SELECT r.*, u.username, u.email, t.title as tour_title
    FROM reviews r
    LEFT JOIN users u ON r.user_id = u.id
    LEFT JOIN tours t ON r.tour_id = t.id
    $whereClause
    ORDER BY r.created_at DESC
    LIMIT $limit OFFSET $offset
");
$stmt->execute($params);
$reviews = $stmt->fetchAll();
?>

<div class="content">
    <div class="header">
        <h1>Manage Reviews</h1>
        <div class="actions">
            <a href="manage-reviews.php?filter=all" class="btn <?= $filter == 'all' ? 'btn-primary' : 'btn-secondary' ?>">All</a>
            <a href="manage-reviews.php?filter=pending" class="btn <?= $filter == 'pending' ? 'btn-primary' : 'btn-secondary' ?>">Pending</a>
            <a href="manage-reviews.php?filter=approved" class="btn <?= $filter == 'approved' ? 'btn-primary' : 'btn-secondary' ?>">Approved</a>
        </div>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <?= $_SESSION['success']; unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger">
            <?= $_SESSION['error']; unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <?php if (count($reviews) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Tour</th>
                                <th>User</th>
                                <th>Rating</th>
                                <th>Comment</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reviews as $review): ?>
                                <tr>
                                    <td><?= $review['id'] ?></td>
                                    <td><?= htmlspecialchars($review['tour_title']) ?></td>
                                    <td><?= htmlspecialchars($review['username']) ?><br><small><?= htmlspecialchars($review['email']) ?></small></td>
                                    <td>
                                        <div class="rating">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <span class="star <?= $i <= $review['rating'] ? 'filled' : '' ?>">★</span>
                                            <?php endfor; ?>
                                        </div>
                                    </td>
                                    <td><?= nl2br(htmlspecialchars(substr($review['comment'], 0, 100) . (strlen($review['comment']) > 100 ? '...' : ''))) ?></td>
                                    <td>
                                        <span class="badge badge-<?= $review['approved'] ? 'success' : 'warning' ?>">
                                            <?= $review['approved'] ? 'Approved' : 'Pending' ?>
                                        </span>
                                    </td>
                                    <td><?= date('M j, Y g:i A', strtotime($review['created_at'])) ?></td>
                                    <td>
                                        <div class="btn-group">
                                            <?php if (!$review['approved']): ?>
                                                <a href="manage-reviews.php?action=approve&id=<?= $review['id'] ?>" class="btn btn-sm btn-success" title="Approve">
                                                    <i class="fas fa-check"></i>
                                                </a>
                                            <?php endif; ?>
                                            <a href="manage-reviews.php?action=delete&id=<?= $review['id'] ?>" class="btn btn-sm btn-danger" title="Delete" onclick="return confirm('Are you sure you want to delete this review?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                    <nav aria-label="Page navigation">
                        <ul class="pagination">
                            <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="manage-reviews.php?page=<?= $page-1 ?>&filter=<?= $filter ?>" aria-label="Previous">
                                        <span aria-hidden="true">&laquo;</span>
                                    </a>
                                </li>
                            <?php endif; ?>

                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                    <a class="page-link" href="manage-reviews.php?page=<?= $i ?>&filter=<?= $filter ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>

                            <?php if ($page < $totalPages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="manage-reviews.php?page=<?= $page+1 ?>&filter=<?= $filter ?>" aria-label="Next">
                                        <span aria-hidden="true">&raquo;</span>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-comments fa-3x text-muted mb-3"></i>
                    <h4>No reviews found</h4>
                    <p class="text-muted">There are no reviews to display.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.rating {
    color: #ddd;
    font-size: 16px;
}
.rating .star.filled {
    color: #ffc107;
}
</style>

<?php require_once '../includes/footer.php'; ?>