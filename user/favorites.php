<?php
// user/favorites.php
require_once '../includes/config.php';
require_once '../includes/user-auth.php';
require_once '../includes/database.php';
require_once '../includes/user-header.php';

if (!isUserLoggedIn()) {
    header("Location: ../login.php");
    exit();
}

$user = getUserData();

// Get user's favorite tours
$stmt = $pdo->prepare("
    SELECT t.*, 
           (SELECT COUNT(*) FROM scenes s WHERE s.tour_id = t.id) as scene_count,
           (SELECT AVG(rating) FROM reviews r WHERE r.tour_id = t.id AND r.approved = 1) as avg_rating,
           (SELECT COUNT(*) FROM reviews r WHERE r.tour_id = t.id AND r.approved = 1) as review_count
    FROM tours t
    INNER JOIN favorites f ON t.id = f.tour_id
    WHERE f.user_id = ? AND t.published = 1
    ORDER BY f.created_at DESC
");
$stmt->execute([$user['id']]);
$favorite_tours = $stmt->fetchAll();

$page_title = "My Favorites - VR Tour Application";
?>

<div class="container py-5">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h2"><i class="fas fa-heart text-danger me-2"></i>My Favorites</h1>
                <a href="tours.php" class="btn btn-outline-primary">
                    <i class="fas fa-arrow-left me-1"></i> Back to Tours
                </a>
            </div>
            
            <?php if (count($favorite_tours) > 0): ?>
            <div class="row">
                <?php foreach ($favorite_tours as $tour): ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100 tour-card">
                        <?php if ($tour['thumbnail']): ?>
                            <img src="../assets/images/uploads/<?php echo $tour['thumbnail']; ?>" 
                                 alt="<?php echo htmlspecialchars($tour['title']); ?>"
                                 class="card-img-top tour-image" style="height: 200px; object-fit: cover;">
                        <?php else: ?>
                            <div class="card-img-top d-flex align-items-center justify-content-center bg-light" style="height: 200px;">
                                <i class="fas fa-image fa-3x text-muted"></i>
                            </div>
                        <?php endif; ?>
                        
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($tour['title']); ?></h5>
                            <p class="card-text text-muted">
                                <?php echo htmlspecialchars(substr($tour['description'], 0, 100)); ?>
                                <?php if (strlen($tour['description']) > 100) echo '...'; ?>
                            </p>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="badge bg-primary"><?php echo $tour['scene_count']; ?> Scenes</span>
                                <span class="badge bg-info"><?php echo htmlspecialchars($tour['category']); ?></span>
                            </div>
                            
                            <?php if ($tour['avg_rating']): ?>
                            <div class="rating-display mb-2">
                                <div class="stars">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star<?php echo $i <= round($tour['avg_rating']) ? '' : '-half-alt'; ?> text-warning"></i>
                                    <?php endfor; ?>
                                </div>
                                <small class="text-muted">(<?php echo $tour['review_count']; ?> reviews)</small>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="card-footer bg-transparent">
                            <div class="d-flex justify-content-between">
                                <a href="../vr/tour.php?id=<?php echo $tour['id']; ?>" class="btn btn-primary btn-sm">
                                    <i class="fas fa-play-circle me-1"></i> Start Tour
                                </a>
                                <button class="btn btn-danger btn-sm favorite-btn" data-tour-id="<?php echo $tour['id']; ?>">
                                    <i class="fas fa-heart me-1"></i> Remove
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-heart fa-4x text-muted mb-3"></i>
                <h3>No favorites yet</h3>
                <p class="text-muted">Start exploring tours and add them to your favorites!</p>
                <a href="../tours.php" class="btn btn-primary">
                    <i class="fas fa-compass me-1"></i> Explore Tours
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Reuse the favorite button functionality from tours.php
document.querySelectorAll('.favorite-btn').forEach(btn => {
    btn.addEventListener('click', function(){
        const tourId = this.dataset.tourId;
        const button = this;
        const card = button.closest('.tour-card');
        
        button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>';
        button.disabled = true;

        fetch('favorite.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'tour_id=' + tourId + '&action=remove'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Remove the card with animation
                card.style.opacity = '0';
                card.style.transform = 'scale(0.9)';
                card.style.transition = 'all 0.3s ease';
                
                setTimeout(() => {
                    card.remove();
                    
                    // Check if no favorites left
                    if (document.querySelectorAll('.tour-card').length === 0) {
                        location.reload(); // Reload to show empty state
                    }
                }, 300);
                
                showToast('Removed from favorites', 'success');
            } else {
                button.innerHTML = '<i class="fas fa-heart me-1"></i> Remove';
                button.disabled = false;
                showToast(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            button.innerHTML = '<i class="fas fa-heart me-1"></i> Remove';
            button.disabled = false;
            showToast('An error occurred. Please try again.', 'error');
        });
    });
});
</script>

<?php require_once '../includes/user-footer.php'; ?>