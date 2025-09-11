<?php
// tours.php
require_once 'includes/config.php';
require_once 'includes/user-auth.php';
require_once 'includes/database.php';

// Get filter parameters
$category = $_GET['category'] ?? '';
$search = $_GET['search'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 12;
$offset = ($page - 1) * $limit;

// Build query with filters
$where_conditions = ["t.published = 1"];
$params = [];

if (!empty($category)) {
    $where_conditions[] = "t.category = ?";
    $params[] = $category;
}

if (!empty($search)) {
    $where_conditions[] = "(t.title LIKE ? OR t.description LIKE ? OR t.tags LIKE ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
}

$where_clause = $where_conditions ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Get total count for pagination
$count_sql = "SELECT COUNT(*) FROM tours t $where_clause";
$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute($params);
$total_tours = $count_stmt->fetchColumn();
$total_pages = ceil($total_tours / $limit);

// Get tours with pagination
$tours_sql = "
    SELECT t.*, 
           (SELECT COUNT(*) FROM scenes s WHERE s.tour_id = t.id) as scene_count,
           (SELECT COUNT(*) FROM favorites f WHERE f.tour_id = t.id) as favorite_count,
           (SELECT AVG(rating) FROM reviews r WHERE r.tour_id = t.id AND r.approved = 1) as avg_rating
    FROM tours t 
    $where_clause
    ORDER BY t.featured DESC, t.created_at DESC
    LIMIT $limit OFFSET $offset
";

$tours_stmt = $pdo->prepare($tours_sql);
$tours_stmt->execute($params);
$tours = $tours_stmt->fetchAll();

// Get all categories for filter
$categories = $pdo->query("
    SELECT DISTINCT category 
    FROM tours 
    WHERE published = 1 
    ORDER BY category
")->fetchAll(PDO::FETCH_COLUMN);

$page_title = "Browse VR Tours - VR Tour Application";
include 'includes/user-header.php';
?>

<div class="row">
    <div class="col-md-3">
        <!-- Filters Sidebar -->
        <div class="card mb-4">
            <div class="card-header">
                <h5>Filters</h5>
            </div>
            <div class="card-body">
                <form method="GET" action="tours.php">
                    <div class="mb-3">
                        <label for="search" class="form-label">Search</label>
                        <input type="text" class="form-control" id="search" name="search" 
                               value="<?php echo htmlspecialchars($search); ?>" 
                               placeholder="Search tours...">
                    </div>
                    
                    <div class="mb-3">
                        <label for="category" class="form-label">Category</label>
                        <select class="form-control" id="category" name="category">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo htmlspecialchars($cat); ?>" 
                                    <?php echo $category === $cat ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100">Apply Filters</button>
                    <?php if ($category || $search): ?>
                        <a href="tours.php" class="btn btn-outline-secondary w-100 mt-2">Clear Filters</a>
                    <?php endif; ?>
                </form>
            </div>
        </div>
        
        <!-- Categories List -->
        <div class="card">
            <div class="card-header">
                <h5>Categories</h5>
            </div>
            <div class="card-body">
                <div class="list-group">
                    <a href="tours.php" class="list-group-item list-group-item-action 
                        <?php echo !$category ? 'active' : ''; ?>">
                        All Categories
                    </a>
                    <?php foreach ($categories as $cat): ?>
                        <a href="tours.php?category=<?php echo urlencode($cat); ?>" 
                           class="list-group-item list-group-item-action 
                           <?php echo $category === $cat ? 'active' : ''; ?>">
                            <?php echo htmlspecialchars($cat); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-9">
        <!-- Tours Grid -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>VR Tours</h2>
            <span class="text-muted"><?php echo $total_tours; ?> tours found</span>
        </div>
        
        <?php if (count($tours) > 0): ?>
            <div class="row">
                <?php foreach ($tours as $tour): ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card h-100 tour-card">
                            <?php if ($tour['thumbnail']): ?>
                                <img src="assets/images/uploads/<?php echo $tour['thumbnail']; ?>" 
                                     class="card-img-top" alt="<?php echo htmlspecialchars($tour['title']); ?>"
                                     style="height: 200px; object-fit: cover;">
                            <?php else: ?>
                                <div class="card-img-top bg-secondary d-flex align-items-center justify-content-center" 
                                     style="height: 200px;">
                                    <i class="fas fa-image fa-3x text-light"></i>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($tour['featured']): ?>
                                <div class="position-absolute top-0 start-0 m-2">
                                    <span class="badge bg-warning">Featured</span>
                                </div>
                            <?php endif; ?>
                            
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($tour['title']); ?></h5>
                                <p class="card-text text-muted small">
                                    <?php echo htmlspecialchars(substr($tour['description'], 0, 100)); ?>
                                    <?php if (strlen($tour['description']) > 100): ?>...<?php endif; ?>
                                </p>
                                
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="badge bg-primary">
                                        <i class="fas fa-image me-1"></i><?php echo $tour['scene_count']; ?>
                                    </span>
                                    <span class="badge bg-secondary"><?php echo htmlspecialchars($tour['category']); ?></span>
                                </div>
                                
                                <?php if ($tour['avg_rating']): ?>
                                    <div class="mb-2">
                                        <div class="text-warning small">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <i class="fas fa-star<?php echo $i <= round($tour['avg_rating']) ? '' : '-half-alt'; ?>"></i>
                                            <?php endfor; ?>
                                            <span class="text-muted ms-1">(<?php echo number_format($tour['avg_rating'], 1); ?>)</span>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="card-footer bg-transparent">
                                <div class="d-grid gap-2">
                                    <a href="vr/tour.php?id=<?php echo $tour['id']; ?>" class="btn btn-primary">
                                        <i class="fas fa-play-circle me-1"></i>Start Tour
                                    </a>
                                    <?php if (isUserLoggedIn()): ?>
                                        <button class="btn btn-outline-secondary btn-sm favorite-btn" 
                                                data-tour-id="<?php echo $tour['id']; ?>">
                                            <i class="far fa-heart"></i> Add to Favorites
                                        </button>
                                    <?php else: ?>
                                        <a href="login.php" class="btn btn-outline-secondary btn-sm">
                                            <i class="far fa-heart"></i> Login to Favorite
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <nav aria-label="Tours pagination" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>">
                                    Previous
                                </a>
                            </li>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                        
                        <?php if ($page < $total_pages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>">
                                    Next
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>
            
        <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-search fa-3x text-muted mb-3"></i>
                <h4 class="text-muted">No tours found</h4>
                <p class="text-muted">Try adjusting your search filters or browse all categories.</p>
                <a href="tours.php" class="btn btn-primary">Browse All Tours</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Favorite functionality
document.addEventListener('DOMContentLoaded', function() {
    const favoriteButtons = document.querySelectorAll('.favorite-btn');
    
    favoriteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const tourId = this.getAttribute('data-tour-id');
            const button = this;
            
            fetch('user/favorite.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'tour_id=' + tourId + '&action=toggle'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (data.status === 'added') {
                        button.innerHTML = '<i class="fas fa-heart"></i> Remove from Favorites';
                        button.classList.add('btn-danger');
                        button.classList.remove('btn-outline-secondary');
                    } else {
                        button.innerHTML = '<i class="far fa-heart"></i> Add to Favorites';
                        button.classList.remove('btn-danger');
                        button.classList.add('btn-outline-secondary');
                    }
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            });
        });
    });
});
</script>

<?php include 'includes/user-footer.php'; ?>