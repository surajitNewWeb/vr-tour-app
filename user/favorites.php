<?php
// user/favorites.php
require_once '../includes/config.php';
require_once '../includes/user-auth.php';

// Redirect if not logged in
if (!isUserLoggedIn()) {
    header("Location: ../login.php");
    exit();
}

$user = getUserData();

// Get user's favorite tours
try {
    global $pdo;
    
    $favorites = $pdo->prepare("
        SELECT t.* 
        FROM tours t
        JOIN favorites f ON t.id = f.tour_id
        WHERE f.user_id = ? AND t.published = 1
        ORDER BY f.created_at DESC
    ");
    $favorites->execute([$user['id']]);
    $favoriteTours = $favorites->fetchAll();
    
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    $favoriteTours = [];
}

// Handle remove from favorites
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_favorite'])) {
    $tourId = filter_input(INPUT_POST, 'tour_id', FILTER_VALIDATE_INT);
    
    if ($tourId) {
        try {
            $remove = $pdo->prepare("
                DELETE FROM favorites 
                WHERE user_id = ? AND tour_id = ?
            ");
            $remove->execute([$user['id'], $tourId]);
            
            // Refresh page to show updated list
            header("Location: favorites.php");
            exit();
            
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            $error = "Failed to remove from favorites.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Favorites - <?php echo SITE_NAME; ?></title>
    
    <!-- CSS -->
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        .page-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            display: grid;
            grid-template-columns: 250px 1fr;
            gap: 30px;
        }
        
        .main-content {
            background: #fff;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .page-header {
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .page-header h1 {
            font-size: 24px;
            margin: 0;
            color: #333;
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
            position: relative;
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
            margin-bottom: 10px;
        }
        
        .tour-actions {
            display: flex;
            justify-content: space-between;
        }
        
        .btn {
            padding: 8px 15px;
            border-radius: 4px;
            text-decoration: none;
            font-weight: 500;
            display: inline-block;
            text-align: center;
        }
        
        .btn-primary {
            background: #2196f3;
            color: white;
        }
        
        .btn-primary:hover {
            background: #1976d2;
        }
        
        .btn-danger {
            background: #f44336;
            color: white;
            border: none;
            cursor: pointer;
            font-size: 13px;
        }
        
        .btn-danger:hover {
            background: #d32f2f;
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
            margin: 0 0 20px;
        }
        
        @media (max-width: 768px) {
            .page-container {
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
    
    <div class="page-container">
        <?php include 'sidebar.php'; ?>
        
        <div class="main-content">
            <div class="page-header">
                <h1>My Favorite Tours</h1>
            </div>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if (!empty($favoriteTours)): ?>
                <div class="tour-grid">
                    <?php foreach ($favoriteTours as $tour): ?>
                        <div class="tour-card">
                            <div class="tour-image">
                                <img src="../assets/images/uploads/<?php echo htmlspecialchars($tour['thumbnail'] ?? 'default.jpg'); ?>" alt="<?php echo htmlspecialchars($tour['title']); ?>">
                            </div>
                            <div class="tour-info">
                                <h3 class="tour-title"><?php echo htmlspecialchars($tour['title']); ?></h3>
                                <div class="tour-meta">
                                    <span><?php echo htmlspecialchars($tour['category']); ?></span>
                                    <span>
                                        <i class="fas fa-heart" style="color: #e91e63;"></i> Favorited
                                    </span>
                                </div>
                                <div class="tour-actions">
                                    <a href="../vr/tour.php?id=<?php echo $tour['id']; ?>" class="btn btn-primary">View Tour</a>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="tour_id" value="<?php echo $tour['id']; ?>">
                                        <button type="submit" name="remove_favorite" class="btn btn-danger" onclick="return confirm('Remove from favorites?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-heart"></i>
                    <p>You haven't favorited any tours yet</p>
                    <a href="../tours.php" class="btn btn-primary">Explore Tours</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <?php include '../includes/user-footer.php'; ?>
</body>
</html>