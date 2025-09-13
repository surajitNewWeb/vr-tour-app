<?php
// categories.php
require_once 'includes/config.php';
require_once 'includes/user-auth.php';
require_once 'includes/database.php';

// Get all categories with tour counts
$stmt = $pdo->query("
    SELECT category, COUNT(*) as tour_count 
    FROM tours 
    WHERE published = 1 
    GROUP BY category 
    ORDER BY tour_count DESC
");
$categories = $stmt->fetchAll();

// Get featured tours from each category
$featured_tours = [];
foreach ($categories as $category) {
    $stmt = $pdo->prepare("
        SELECT t.*, 
               (SELECT COUNT(*) FROM scenes s WHERE s.tour_id = t.id) as scene_count,
               (SELECT AVG(rating) FROM reviews r WHERE r.tour_id = t.id AND r.approved = 1) as avg_rating
        FROM tours t 
        WHERE t.category = :category AND t.published = 1 AND t.featured = 1
        ORDER BY t.created_at DESC 
        LIMIT 3
    ");
    $stmt->execute([':category' => $category['category']]);
    $featured_tours[$category['category']] = $stmt->fetchAll();
}

$page_title = "Tour Categories - VR Tour Application";
include 'includes/user-header.php';
?>

<div class="categories-page container py-5">
    <!-- Header -->
    <div class="text-center mb-5">
        <h1 class="page-title">Explore Categories</h1>
        <p class="page-subtitle text-muted">Discover virtual tours organized by categories</p>
    </div>

    <!-- Categories Grid -->
    <div class="categories-grid">
        <?php foreach ($categories as $category): ?>
            <div class="category-card">
                <!-- Header -->
                <div class="category-header">
                    <h3 class="category-title"><?php echo htmlspecialchars($category['category']); ?></h3>
                    <span class="badge-count"><?php echo (int)$category['tour_count']; ?> Tours</span>
                </div>
                
                <!-- Featured Tours -->
                <div class="category-body">
                    <?php if (!empty($featured_tours[$category['category']])): ?>
                        <?php foreach ($featured_tours[$category['category']] as $tour): ?>
                            <?php
                                $avg_raw = $tour['avg_rating'];
                                $rating = is_numeric($avg_raw) ? (float)$avg_raw : null;
                                $scenes = (int)($tour['scene_count'] ?? 0);
                                $scene_label = $scenes === 1 ? 'scene' : 'scenes';
                            ?>
                            <div class="featured-tour">
                                <?php if (!empty($tour['thumbnail'])): ?>
                                    <img src="<?php echo BASE_URL; ?>assets/images/uploads/<?php echo htmlspecialchars($tour['thumbnail']); ?>" 
                                         alt="<?php echo htmlspecialchars($tour['title']); ?>" class="tour-thumb">
                                <?php else: ?>
                                    <div class="tour-thumb placeholder">
                                        <i class="fas fa-image"></i>
                                    </div>
                                <?php endif; ?>

                                <div class="tour-info">
                                    <h6 class="tour-title"><?php echo htmlspecialchars($tour['title']); ?></h6>
                                    <div class="tour-meta">
                                        <span class="tour-rating">
                                            <?php
                                            if ($rating === null) {
                                                for ($si = 1; $si <= 5; $si++) echo '<i class="far fa-star"></i>';
                                            } else {
                                                $r = round($rating * 2) / 2;
                                                for ($si = 1; $si <= 5; $si++) {
                                                    if ($r >= $si) echo '<i class="fas fa-star"></i>';
                                                    elseif ($r >= $si - 0.5) echo '<i class="fas fa-star-half-alt"></i>';
                                                    else echo '<i class="far fa-star"></i>';
                                                }
                                            }
                                            ?>
                                        </span>
                                        <small><?php echo $scenes . ' ' . $scene_label; ?></small>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted">No featured tours yet</p>
                    <?php endif; ?>
                </div>

                <!-- Footer -->
                <div class="category-footer">
                    <a href="<?php echo BASE_URL; ?>tours.php?category=<?php echo urlencode($category['category']); ?>" 
                       class="btn btn-view w-100">
                        <i class="fas fa-eye me-2"></i> View All Tours
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Statistics Section -->
    <div class="stats-card text-center mt-5">
        <h2 class="stats-title">Start Your Virtual Journey</h2>
        <p class="stats-subtitle">Join thousands exploring the world through immersive VR experiences</p>
        <div class="stats-grid">
            <div>
                <h3 class="stat-number"><?php echo array_sum(array_column($categories, 'tour_count')); ?></h3>
                <p>Total Tours</p>
            </div>
            <div>
                <h3 class="stat-number"><?php echo count($categories); ?></h3>
                <p>Categories</p>
            </div>
            <div>
                <h3 class="stat-number">24/7</h3>
                <p>Available</p>
            </div>
        </div>
    </div>
</div>

<style>
/* Grid Layout */
.categories-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 1.8rem;
}

/* Category Card */
.category-card {
    border-radius: 16px;
    background: #fff;
    box-shadow: 0 8px 22px rgba(0,0,0,0.08);
    display: flex;
    flex-direction: column;
    transition: transform .25s ease, box-shadow .25s ease;
}
.category-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(0,0,0,0.12);
}

/* Header */
.category-header {
    background: linear-gradient(135deg,#667eea,#764ba2);
    color: #fff;
    padding: 1rem 1.25rem;
    border-radius: 16px 16px 0 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.category-title { font-size: 1.05rem; font-weight: 600; margin: 0; }
.badge-count {
    background: rgba(255,255,255,0.2);
    padding: .35rem .75rem;
    border-radius: 999px;
    font-size: .9rem;
}

/* Body */
.category-body { padding: 1rem 1.25rem; flex-grow: 1; }
.featured-tour {
    display: flex; gap: .9rem; align-items: center; margin-bottom: 1rem;
}
.tour-thumb {
    width: 60px; height: 60px; border-radius: 10px; object-fit: cover; flex-shrink: 0;
}
.tour-thumb.placeholder {
    background: #eef2ff; display: flex; align-items: center; justify-content: center;
    color: #777;
}
.tour-title { font-size: .95rem; font-weight: 600; margin: 0 0 .25rem; }
.tour-meta { font-size: .82rem; color: #6c757d; display: flex; gap: .5rem; align-items: center; }
.tour-rating i { color: #f6c84a; }

/* Footer */
.category-footer { padding: 1rem 1.25rem; }
.btn-view {
    border-radius: 10px;
    background: linear-gradient(135deg,#667eea,#764ba2);
    border: none;
    color: #fff;
    font-weight: 600;
    padding: .55rem .6rem;
    transition: background .25s ease;
}
.btn-view:hover { background: linear-gradient(135deg,#5a67d8,#6b46c1); }

/* Stats */
.stats-card {
    border-radius: 16px;
    background: linear-gradient(135deg,#667eea,#764ba2);
    padding: 2.5rem 1.5rem;
    color: #fff;
    box-shadow: 0 12px 34px rgba(0,0,0,0.12);
}
.stats-title { font-size: 1.6rem; font-weight: 700; margin-bottom: .5rem; }
.stats-subtitle { opacity: .9; margin-bottom: 1.8rem; }
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 1.2rem;
}
.stat-number { font-size: 2rem; font-weight: 800; margin-bottom: .2rem; }
</style>

<?php include 'includes/user-footer.php'; ?>
