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
    $where_conditions[] = "t.category = :category";
    $params[':category'] = $category;
}

if (!empty($search)) {
    $where_conditions[] = "(t.title LIKE :search OR t.description LIKE :search_desc OR t.tags LIKE :search_tags)";
    $params[':search'] = "%$search%";
    $params[':search_desc'] = "%$search%";
    $params[':search_tags'] = "%$search%";
}

$where_clause = $where_conditions ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Get total count for pagination
$count_sql = "SELECT COUNT(*) as total FROM tours t $where_clause";
$stmt = $pdo->prepare($count_sql);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$total_tours = $stmt->fetch()['total'];
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
    LIMIT :limit OFFSET :offset
";

$stmt = $pdo->prepare($tours_sql);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$tours = $stmt->fetchAll();

// Get all categories for filter
$stmt = $pdo->query("
    SELECT DISTINCT category 
    FROM tours 
    WHERE published = 1 
    ORDER BY category
");
$categories = $stmt->fetchAll(PDO::FETCH_COLUMN);

$page_title = "Browse VR Tours - VR Tour Application";
include 'includes/user-header.php';
?>

<style>
/* Main Page Layout */
.tours-page {
    background: #f8f9fc;
    min-height: 100vh;
    padding: 2rem 0;
}

/* Header */
.page-header {
    text-align: center;
    margin-bottom: 2rem;
}
.page-header h1 {
    font-size: 2.5rem;
    font-weight: 700;
    color: #343a40;
}
.page-header p {
    color: #6c757d;
    font-size: 1.1rem;
}

/* Sidebar */
.sidebar {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}
.sidebar .card {
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
}

/* Tours Container */
.tours-container {
    background: #fff;
    border-radius: 16px;
    padding: 2rem;
    box-shadow: 0 4px 16px rgba(0,0,0,0.08);
}
.tours-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    border-bottom: 1px solid #eee;
    padding-bottom: 1rem;
}
.tours-header h2 {
    font-weight: 700;
    font-size: 1.5rem;
    color: #343a40;
}
.tours-count {
    color: #6c757d;
}

/* Grid for tours */
.tours-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 1.5rem;
}

/* Tour Cards */
.tour-card {
    border: none;
    border-radius: 16px;
    overflow: hidden;
    background: #fff;
    display: flex;
    flex-direction: column;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
}
.tour-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 12px 24px rgba(0,0,0,0.15);
}
.tour-card img {
    width: 100%;
    height: 200px;
    object-fit: cover;
}
.tour-card .card-body {
    padding: 1rem 1.25rem;
    flex-grow: 1;
}
.tour-card .card-title {
    font-size: 1.1rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
}
.tour-card .card-text {
    font-size: 0.9rem;
    color: #6c757d;
}
.tour-card .badge {
    border-radius: 20px;
    padding: 0.4rem 0.8rem;
    font-size: 0.75rem;
}
.featured-badge {
    position: absolute;
    top: 12px;
    left: 12px;
    background: #f5576c;
    color: #fff;
    border-radius: 20px;
    padding: 0.3rem 0.8rem;
    font-size: 0.75rem;
    font-weight: 600;
}

/* Footer buttons */
.tour-card .card-footer {
    padding: 1rem;
    background: #f8f9fa;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}
.tour-card .btn {
    border-radius: 8px;
    font-weight: 600;
}

/* Empty state */
.empty-state {
    text-align: center;
    padding: 3rem 2rem;
    color: #6c757d;
}
.empty-state i {
    font-size: 3rem;
    margin-bottom: 1rem;
}

/* Pagination */
.pagination {
    margin-top: 2rem;
}
.page-link {
    border-radius: 8px;
}
</style>

<div class="tours-page">
    <div class="container">
        <!-- Header -->
        <div class="page-header">
            <h1>Discover Amazing VR Tours</h1>
            <p>Explore immersive virtual reality experiences from around the world</p>
        </div>

        <div class="row">
            <!-- Sidebar -->
            <div class="col-lg-3 col-md-4 mb-4">
                <div class="sidebar">
                    <!-- Filters -->
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <i class="fas fa-filter me-2"></i> Filters
                        </div>
                        <div class="card-body">
                            <form method="GET" action="tours.php">
                                <div class="mb-3">
                                    <label for="search" class="form-label fw-semibold">Search</label>
                                    <input type="text" class="form-control" id="search" name="search"
                                           value="<?php echo htmlspecialchars($search); ?>" 
                                           placeholder="Search tours...">
                                </div>
                                <div class="mb-3">
                                    <label for="category" class="form-label fw-semibold">Category</label>
                                    <select class="form-control" id="category" name="category">
                                        <option value="">All Categories</option>
                                        <?php foreach ($categories as $cat): ?>
                                        <option value="<?php echo htmlspecialchars($cat); ?>" 
                                            <?php echo $category===$cat ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($cat); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary w-100 mb-2">
                                    <i class="fas fa-search me-2"></i> Apply
                                </button>
                                <?php if ($category || $search): ?>
                                <a href="tours.php" class="btn btn-outline-secondary w-100">
                                    <i class="fas fa-times me-2"></i> Clear
                                </a>
                                <?php endif; ?>
                            </form>
                        </div>
                    </div>

                    <!-- Categories -->
                    <div class="card">
                        <div class="card-header bg-secondary text-white">
                            <i class="fas fa-list me-2"></i> Categories
                        </div>
                        <div class="list-group list-group-flush">
                            <a href="tours.php" class="list-group-item list-group-item-action <?php echo !$category ? 'active' : ''; ?>">
                                All Categories
                            </a>
                            <?php foreach ($categories as $cat): ?>
                            <a href="tours.php?category=<?php echo urlencode($cat); ?>" 
                               class="list-group-item list-group-item-action <?php echo $category === $cat ? 'active' : ''; ?>">
                                <?php echo htmlspecialchars($cat); ?>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tours List -->
            <div class="col-lg-9 col-md-8">
                <div class="tours-container">
                    <div class="tours-header">
                        <h2><i class="fas fa-vr-cardboard me-2"></i>VR Tours</h2>
                        <span class="tours-count"><?php echo $total_tours; ?> tours found</span>
                    </div>

                    <?php if (count($tours) > 0): ?>
                    <div class="tours-grid">
                        <?php foreach ($tours as $tour): ?>
                        <div class="tour-card position-relative">
                            <?php if ($tour['thumbnail']): ?>
                                <img src="assets/images/uploads/<?php echo $tour['thumbnail']; ?>" 
                                     alt="<?php echo htmlspecialchars($tour['title']); ?>">
                            <?php else: ?>
                                <div class="d-flex align-items-center justify-content-center bg-light" style="height:200px;">
                                    <i class="fas fa-image fa-3x text-muted"></i>
                                </div>
                            <?php endif; ?>

                            <?php if ($tour['featured']): ?>
                            <div class="featured-badge"><i class="fas fa-star me-1"></i> Featured</div>
                            <?php endif; ?>

                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($tour['title']); ?></h5>
                                <p class="card-text">
                                    <?php echo htmlspecialchars(substr($tour['description'], 0, 90)); ?>
                                    <?php if (strlen($tour['description']) > 90) echo "..."; ?>
                                </p>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="badge bg-primary"><?php echo $tour['scene_count']; ?> Scenes</span>
                                    <span class="badge bg-info"><?php echo htmlspecialchars($tour['category']); ?></span>
                                </div>
                                <?php if ($tour['avg_rating']): ?>
                                <div class="text-warning small">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star<?php echo $i <= round($tour['avg_rating']) ? '' : '-half-alt'; ?>"></i>
                                    <?php endfor; ?>
                                    <span class="text-muted ms-1">(<?php echo number_format($tour['avg_rating'], 1); ?>)</span>
                                </div>
                                <?php endif; ?>
                            </div>

                            <div class="card-footer">
                                <a href="vr/tour.php?id=<?php echo $tour['id']; ?>" class="btn btn-primary w-100">
                                    <i class="fas fa-play-circle me-1"></i> Start Tour
                                </a>
                                <?php if (isUserLoggedIn()): ?>
                                <button class="btn btn-outline-secondary favorite-btn w-100" data-tour-id="<?php echo $tour['id']; ?>">
                                    <i class="far fa-heart me-1"></i> Add to Favorites
                                </button>
                                <?php else: ?>
                                <a href="login.php" class="btn btn-outline-secondary w-100">
                                    <i class="far fa-heart me-1"></i> Login to Favorite
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                    <nav class="mt-4">
                        <ul class="pagination justify-content-center">
                            <?php if ($page > 1): ?>
                            <li class="page-item"><a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page'=>$page-1])); ?>">Previous</a></li>
                            <?php endif; ?>
                            <?php for ($i=1;$i<=$total_pages;$i++): ?>
                            <li class="page-item <?php echo $i==$page?'active':''; ?>">
                                <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page'=>$i])); ?>"><?php echo $i; ?></a>
                            </li>
                            <?php endfor; ?>
                            <?php if ($page < $total_pages): ?>
                            <li class="page-item"><a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page'=>$page+1])); ?>">Next</a></li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                    <?php endif; ?>

                    <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-search"></i>
                        <h4>No tours found</h4>
                        <p>Try changing filters or search again.</p>
                        <a href="tours.php" class="btn btn-primary mt-2">Browse All</a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function(){
    document.querySelectorAll('.favorite-btn').forEach(btn=>{
        btn.addEventListener('click', function(){
            const tourId = this.dataset.tourId;
            const button = this;
            button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Loading...';
            button.disabled = true;

            fetch('user/favorite.php', {
                method:'POST',
                headers:{'Content-Type':'application/x-www-form-urlencoded'},
                body:'tour_id='+tourId+'&action=toggle'
            }).then(r=>r.json()).then(data=>{
                if(data.success){
                    if(data.status==='added'){
                        button.innerHTML = '<i class="fas fa-heart me-1"></i> Remove from Favorites';
                        button.classList.add('btn-danger');
                        button.classList.remove('btn-outline-secondary');
                    } else {
                        button.innerHTML = '<i class="far fa-heart me-1"></i> Add to Favorites';
                        button.classList.remove('btn-danger');
                        button.classList.add('btn-outline-secondary');
                    }
                } else {
                    alert(data.message);
                }
            }).finally(()=>button.disabled=false);
        });
    });
});
</script>

<?php include 'includes/user-footer.php'; ?>
