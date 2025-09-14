<?php
// user/dashboard.php
require_once '../includes/config.php';
require_once '../includes/user-auth.php';

// Redirect if not logged in
if (!isUserLoggedIn()) {
    header("Location: ../login.php");
    exit();
}

$user = getUserData();

// Get user statistics
try {
    global $pdo;
    
    // Get completed tours count
    $completedTours = $pdo->prepare("
        SELECT COUNT(DISTINCT tour_id) 
        FROM progress 
        WHERE user_id = ? AND completed = 1
    ");
    $completedTours->execute([$user['id']]);
    $completedCount = $completedTours->fetchColumn();
    
    // Get favorites count
    $favorites = $pdo->prepare("
        SELECT COUNT(*) 
        FROM favorites 
        WHERE user_id = ?
    ");
    $favorites->execute([$user['id']]);
    $favoritesCount = $favorites->fetchColumn();
    
    // Get reviews count
    $reviews = $pdo->prepare("
        SELECT COUNT(*) 
        FROM reviews 
        WHERE user_id = ?
    ");
    $reviews->execute([$user['id']]);
    $reviewsCount = $reviews->fetchColumn();
    
    // Get recent progress
    $recentProgress = $pdo->prepare("
        SELECT t.id, t.title, t.thumbnail, p.last_scene_id, p.updated_at
        FROM progress p
        JOIN tours t ON p.tour_id = t.id
        WHERE p.user_id = ? AND p.completed = 0
        ORDER BY p.updated_at DESC
        LIMIT 5
    ");
    $recentProgress->execute([$user['id']]);
    $recentTours = $recentProgress->fetchAll();
    
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    $completedCount = 0;
    $favoritesCount = 0;
    $reviewsCount = 0;
    $recentTours = [];
}

// Get recommended tours
try {
    $recommended = $pdo->prepare("
        SELECT t.*, COUNT(f.id) as favorite_count
        FROM tours t
        LEFT JOIN favorites f ON t.id = f.tour_id
        WHERE t.published = 1
        GROUP BY t.id
        ORDER BY favorite_count DESC, t.created_at DESC
        LIMIT 3
    ");
    $recommended->execute();
    $recommendedTours = $recommended->fetchAll();
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    $recommendedTours = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?php echo SITE_NAME; ?></title>
    
    <!-- CSS -->
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .welcome-message h1 {
            font-size: 28px;
            margin-bottom: 5px;
            color: #333;
        }
        
        .welcome-message p {
            color: #666;
            font-size: 16px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: #fff;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
        }
        
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-size: 24px;
        }
        
        .completed .stat-icon {
            background: #e8f5e9;
            color: #4caf50;
        }
        
        .favorites .stat-icon {
            background: #fff3e0;
            color: #ff9800;
        }
        
        .reviews .stat-icon {
            background: #e3f2fd;
            color: #2196f3;
        }
        
        .stat-info h3 {
            font-size: 24px;
            margin: 0;
            color: #333;
        }
        
        .stat-info p {
            margin: 5px 0 0;
            color: #666;
        }
        
        .dashboard-section {
            background: #fff;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .section-header h2 {
            font-size: 20px;
            margin: 0;
            color: #333;
        }
        
        .view-all {
            color: #2196f3;
            text-decoration: none;
            font-weight: 500;
        }
        
        .view-all:hover {
            text-decoration: underline;
        }
        
        .tour-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
        }
        
        .tour-card {
            background: #f9f9f9;
            border-radius: 8px;
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .tour-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
        
        .tour-image {
            height: 160px;
            overflow: hidden;
        }
        
        .tour-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s;
        }
        
        .tour-card:hover .tour-image img {
            transform: scale(1.05);
        }
        
        .tour-info {
            padding: 15px;
        }
        
        .tour-title {
            font-size: 16px;
            font-weight: 600;
            margin: 0 0 8px;
            color: #333;
        }
        
        .tour-meta {
            display: flex;
            justify-content: space-between;
            color: #666;
            font-size: 14px;
        }
        
        .continue-btn {
            display: block;
            width: 100%;
            text-align: center;
            background: #2196f3;
            color: white;
            padding: 8px;
            border-radius: 4px;
            text-decoration: none;
            margin-top: 10px;
            font-weight: 500;
        }
        
        .continue-btn:hover {
            background: #1976d2;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #666;
        }
        
        .empty-state i {
            font-size: 48px;
            margin-bottom: 15px;
            color: #ccc;
        }
        
        .empty-state p {
            margin: 0;
        }
        
        @media (max-width: 768px) {
            .dashboard-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .tour-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php include '../includes/user-header.php'; ?>
    
    <div class="dashboard-container">
        <div class="dashboard-header">
            <div class="welcome-message">
                <h1>Welcome back, <?php echo htmlspecialchars($user['username']); ?>!</h1>
                <p>Here's what you've been exploring lately</p>
            </div>
            <div class="user-actions">
                <a href="profile.php" class="btn btn-outline">Edit Profile</a>
            </div>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card completed">
                <div class="stat-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $completedCount; ?></h3>
                    <p>Tours Completed</p>
                </div>
            </div>
            
            <div class="stat-card favorites">
                <div class="stat-icon">
                    <i class="fas fa-heart"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $favoritesCount; ?></h3>
                    <p>Favorite Tours</p>
                </div>
            </div>
            
            <div class="stat-card reviews">
                <div class="stat-icon">
                    <i class="fas fa-star"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $reviewsCount; ?></h3>
                    <p>Reviews Written</p>
                </div>
            </div>
        </div>
        
        <div class="dashboard-section">
            <div class="section-header">
                <h2>Continue Exploring</h2>
                <a href="../tours.php" class="view-all">View All Tours</a>
            </div>
            
            <?php if (!empty($recentTours)): ?>
                <div class="tour-grid">
                    <?php foreach ($recentTours as $tour): ?>
                        <div class="tour-card">
                            <div class="tour-image">
                                <img src="../assets/images/uploads/<?php echo htmlspecialchars($tour['thumbnail'] ?? 'default.jpg'); ?>" alt="<?php echo htmlspecialchars($tour['title']); ?>">
                            </div>
                            <div class="tour-info">
                                <h3 class="tour-title"><?php echo htmlspecialchars($tour['title']); ?></h3>
                                <div class="tour-meta">
                                    <span>Last visited: <?php echo time_ago($tour['updated_at']); ?></span>
                                </div>
                                <a href="../vr/tour.php?id=<?php echo $tour['id']; ?>" class="continue-btn">Continue Tour</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-compass"></i>
                    <p>You haven't started any tours yet</p>
                    <a href="../tours.php" class="btn btn-primary" style="margin-top: 15px;">Explore Tours</a>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="dashboard-section">
            <div class="section-header">
                <h2>Recommended For You</h2>
            </div>
            
            <?php if (!empty($recommendedTours)): ?>
                <div class="tour-grid">
                    <?php foreach ($recommendedTours as $tour): ?>
                        <div class="tour-card">
                            <div class="tour-image">
                                <img src="../assets/images/uploads/<?php echo htmlspecialchars($tour['thumbnail'] ?? 'default.jpg'); ?>" alt="<?php echo htmlspecialchars($tour['title']); ?>">
                            </div>
                            <div class="tour-info">
                                <h3 class="tour-title"><?php echo htmlspecialchars($tour['title']); ?></h3>
                                <div class="tour-meta">
                                    <span><?php echo htmlspecialchars($tour['category']); ?></span>
                                    <span><i class="fas fa-heart"></i> <?php echo $tour['favorite_count']; ?></span>
                                </div>
                                <a href="../vr/tour.php?id=<?php echo $tour['id']; ?>" class="continue-btn">Start Tour</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-star"></i>
                    <p>No recommendations available yet</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <?php include '../includes/user-footer.php'; ?>
</body>
</html>