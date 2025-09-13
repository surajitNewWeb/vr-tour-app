<?php
// user/favorites.php
require_once '../includes/config.php';
require_once '../includes/user-auth.php';
require_once '../includes/database.php';

if (!isUserLoggedIn()) {
    header('Location: ../login.php');
    exit;
}

// Get user's favorite tours
$stmt = $pdo->prepare("
    SELECT t.*, 
           (SELECT COUNT(*) FROM scenes s WHERE s.tour_id = t.id) as scene_count,
           (SELECT AVG(rating) FROM reviews r WHERE r.tour_id = t.id AND r.approved = 1) as avg_rating
    FROM tours t 
    INNER JOIN favorites f ON t.id = f.tour_id 
    WHERE f.user_id = :user_id AND t.published = 1
    ORDER BY f.created_at DESC
");
$stmt->execute([':user_id' => $_SESSION['user_id']]);
$favorite_tours = $stmt->fetchAll();

$page_title = "My Favorites";
include '../includes/user-header.php';
?>

<div class="container">
    <h1>My Favorite Tours</h1>
    
    <?php if (!empty($favorite_tours)): ?>
        <div class="row">
            <?php foreach ($favorite_tours as $tour): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <!-- Tour card content similar to tours.php -->
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-info">
            <h4>No favorites yet!</h4>
            <p>Start exploring tours and add them to your favorites.</p>
            <a href="../tours.php" class="btn btn-primary">Browse Tours</a>
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/user-footer.php'; ?>