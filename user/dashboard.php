<?php
// user/dashboard.php
require_once '../includes/config.php';
require_once '../includes/user-auth.php';
require_once '../includes/database.php';

redirectIfUserNotLoggedIn();

// Get user stats
$user_id = $_SESSION['user_id'];
$user_tours = $pdo->prepare("SELECT COUNT(*) FROM tours WHERE created_by = ?");
$user_tours->execute([$user_id]);
$tours_count = $user_tours->fetchColumn();

$user_favorites = $pdo->prepare("SELECT COUNT(*) FROM favorites WHERE user_id = ?");
$user_favorites->execute([$user_id]);
$favorites_count = $user_favorites->fetchColumn();

$user_reviews = $pdo->prepare("SELECT COUNT(*) FROM reviews WHERE user_id = ?");
$user_reviews->execute([$user_id]);
$reviews_count = $user_reviews->fetchColumn();

// Get recent favorites
$recent_favorites = $pdo->prepare("
    SELECT t.*, f.created_at as favorited_at 
    FROM favorites f 
    JOIN tours t ON f.tour_id = t.id 
    WHERE f.user_id = ? 
    ORDER BY f.created_at DESC 
    LIMIT 5
");
$recent_favorites->execute([$user_id]);
$favorites = $recent_favorites->fetchAll();

$page_title = "User Dashboard - VR Tour Application";
include '../includes/user-header.php';
?>

<div class="row">
    <div class="col-md-12">
        <h1>Welcome, <?php echo htmlspecialchars($_SESSION['user_username']); ?>!</h1>
        <p class="lead">Here's your activity overview</p>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body">
                <h2><?php echo $tours_count; ?></h2>
                <p class="card-text">Tours Created</p>
                <a href="my-tours.php" class="btn btn-outline-primary">View Tours</a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body">
                <h2><?php echo $favorites_count; ?></h2>
                <p class="card-text">Favorites</p>
                <a href="favorites.php" class="btn btn-outline-primary">View Favorites</a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body">
                <h2><?php echo $reviews_count; ?></h2>
                <p class="card-text">Reviews</p>
                <a href="reviews.php" class="btn btn-outline-primary">View Reviews</a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5>Recent Favorites</h5>
            </div>
            <div class="card-body">
                <?php if (count($favorites) > 0): ?>
                    <div class="list-group">
                        <?php foreach ($favorites as $favorite): ?>
                            <a href="../vr/tour.php?id=<?php echo $favorite['id']; ?>" class="list-group-item list-group-item-action">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1"><?php echo htmlspecialchars($favorite['title']); ?></h6>
                                    <small><?php echo date('M j', strtotime($favorite['favorited_at'])); ?></small>
                                </div>
                                <small class="text-muted"><?php echo htmlspecialchars($favorite['category']); ?></small>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-muted">You haven't favorited any tours yet.</p>
                    <a href="../tours.php" class="btn btn-primary">Browse Tours</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5>Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="../tours.php" class="btn btn-outline-primary">
                        <i class="fas fa-search me-2"></i>Browse Tours
                    </a>
                    <a href="profile.php" class="btn btn-outline-secondary">
                        <i class="fas fa-user me-2"></i>Edit Profile
                    </a>
                    <a href="settings.php" class="btn btn-outline-secondary">
                        <i class="fas fa-cog me-2"></i>Account Settings
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/user-footer.php'; ?>