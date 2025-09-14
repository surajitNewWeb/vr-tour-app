<?php
// tours.php
require_once 'includes/config.php';
require_once 'includes/user-auth.php';
require_once 'includes/database.php';

// Get filter parameters
$category = $_GET['category'] ?? '';
$search = $_GET['search'] ?? '';
$min_rating = isset($_GET['min_rating']) ? floatval($_GET['min_rating']) : 0;
$sort_by = $_GET['sort_by'] ?? 'newest';
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

// Add rating filter
if ($min_rating > 0) {
    $where_conditions[] = "(SELECT AVG(rating) FROM reviews r WHERE r.tour_id = t.id AND r.approved = 1) >= :min_rating";
    $params[':min_rating'] = $min_rating;
}

$where_clause = $where_conditions ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Determine sort order
$order_by = "t.featured DESC, ";
switch ($sort_by) {
    case 'popular':
        $order_by .= "favorite_count DESC";
        break;
    case 'rating':
        $order_by .= "avg_rating DESC";
        break;
    case 'scenes':
        $order_by .= "scene_count DESC";
        break;
    case 'newest':
    default:
        $order_by .= "t.created_at DESC";
        break;
}

// Get total count for pagination
$count_sql = "SELECT COUNT(*) as total FROM tours t $where_clause";
$stmt = $pdo->prepare($count_sql);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$total_tours = $stmt->fetch()['total'];
$total_pages = ceil($total_tours / $limit);

// Get tours with pagination and review data
$tours_sql = "
    SELECT t.*, 
           (SELECT COUNT(*) FROM scenes s WHERE s.tour_id = t.id) as scene_count,
           (SELECT COUNT(*) FROM favorites f WHERE f.tour_id = t.id) as favorite_count,
           (SELECT AVG(rating) FROM reviews r WHERE r.tour_id = t.id AND r.approved = 1) as avg_rating,
           (SELECT COUNT(*) FROM reviews r WHERE r.tour_id = t.id AND r.approved = 1) as review_count
    FROM tours t 
    $where_clause
    ORDER BY $order_by
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

// Get popular tags
$stmt = $pdo->query("
    SELECT tags FROM tours WHERE published = 1 AND tags IS NOT NULL
");
$all_tags = [];
while ($row = $stmt->fetch()) {
    $tags = explode(',', $row['tags']);
    foreach ($tags as $tag) {
        $tag = trim($tag);
        if (!empty($tag)) {
            $all_tags[$tag] = isset($all_tags[$tag]) ? $all_tags[$tag] + 1 : 1;
        }
    }
}
arsort($all_tags);
$popular_tags = array_slice(array_keys($all_tags), 0, 15);

$page_title = "Browse VR Tours - VR Tour Application";
include 'includes/user-header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    
    <!-- Preload critical resources -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preload" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" as="style">
    <link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" as="style">
    
    <!-- CSS Libraries -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
    :root {
        --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        --success-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        --warning-gradient: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        
        --glass-bg: rgba(255, 255, 255, 0.25);
        --glass-border: rgba(255, 255, 255, 0.18);
        --glass-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
        
        --card-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        --card-shadow-hover: 0 20px 40px rgba(0, 0, 0, 0.12);
        
        --border-radius-sm: 8px;
        --border-radius-md: 12px;
        --border-radius-lg: 16px;
        --border-radius-xl: 24px;
        
        --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        --transition-bounce: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }

    * {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
    }

    body {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        line-height: 1.6;
        color: #2d3748;
        overflow-x: hidden;
    }

    /* Layout */
    .tours-page {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        min-height: 100vh;
        position: relative;
        padding: 3rem 0 4rem;
    }

    .tours-page::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: 
            radial-gradient(circle at 20% 50%, rgba(120, 119, 198, 0.3) 0%, transparent 50%),
            radial-gradient(circle at 80% 20%, rgba(255, 119, 198, 0.3) 0%, transparent 50%),
            radial-gradient(circle at 40% 80%, rgba(120, 219, 255, 0.3) 0%, transparent 50%);
        pointer-events: none;
    }

    .container {
        position: relative;
        z-index: 1;
        max-width: 1400px;
        margin: 0 auto;
        padding: 0 15px;
    }

    /* Header */
    .page-header {
        text-align: center;
        margin-bottom: 3rem;
        padding: 2rem 0;
    }

    .page-header h1 {
        font-size: clamp(2.5rem, 5vw, 4rem);
        font-weight: 800;
        background: linear-gradient(135deg, #fff 0%, rgba(255,255,255,0.8) 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        margin-bottom: 1rem;
        text-shadow: 0 4px 20px rgba(0,0,0,0.1);
        letter-spacing: -0.02em;
    }

    .page-header p {
        color: rgba(255, 255, 255, 0.9);
        font-size: 1.2rem;
        font-weight: 400;
        max-width: 600px;
        margin: 0 auto;
        text-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    /* Grid Layout */
    .tours-layout {
        display: grid;
        grid-template-columns: 300px 1fr;
        gap: 2rem;
    }

    /* Sidebar */
    .sidebar {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    .sidebar .card {
        background: var(--glass-bg);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border: 1px solid var(--glass-border);
        border-radius: var(--border-radius-lg);
        box-shadow: var(--glass-shadow);
        overflow: hidden;
    }

    .sidebar .card-header {
        background: linear-gradient(135deg, rgba(255,255,255,0.2) 0%, rgba(255,255,255,0.1) 100%);
        border-bottom: 1px solid rgba(255,255,255,0.2);
        color: #fff;
        font-weight: 600;
        padding: 1.25rem;
    }

    .sidebar .card-body {
        padding: 1.5rem;
    }

    .sidebar .form-control, .sidebar .form-select {
        background: rgba(255, 255, 255, 0.9);
        border: 1px solid rgba(255, 255, 255, 0.3);
        border-radius: var(--border-radius-sm);
        transition: var(--transition);
        font-weight: 500;
        padding: 0.75rem 1rem;
    }

    .sidebar .form-control:focus, .sidebar .form-select:focus {
        background: rgba(255, 255, 255, 1);
        border-color: rgba(102, 126, 234, 0.5);
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }

    .sidebar .btn {
        border-radius: var(--border-radius-sm);
        font-weight: 600;
        padding: 0.75rem 1rem;
        transition: var(--transition-bounce);
        border: none;
    }

    .sidebar .btn-primary {
        background: var(--primary-gradient);
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
    }

    .sidebar .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(102, 126, 234, 0.6);
    }

    .sidebar .btn-outline-secondary {
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.3);
        color: #fff;
    }

    .sidebar .btn-outline-secondary:hover {
        background: rgba(255, 255, 255, 0.2);
        transform: translateY(-1px);
        color: #fff;
    }

    /* Category List */
    .list-group-item {
        background: rgba(255, 255, 255, 0.05);
        border: none;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        color: rgba(255, 255, 255, 0.9);
        transition: var(--transition);
        font-weight: 500;
        padding: 0.75rem 1.25rem;
    }

    .list-group-item:hover {
        background: rgba(255, 255, 255, 0.15);
        color: #fff;
        transform: translateX(5px);
    }

    .list-group-item.active {
        background: var(--primary-gradient);
        color: #fff;
        border-color: transparent;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
    }

    /* Tags Cloud */
    .tags-cloud {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-top: 1rem;
    }

    .tag {
        background: rgba(255, 255, 255, 0.1);
        color: rgba(255, 255, 255, 0.9);
        padding: 0.35rem 0.75rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 500;
        transition: var(--transition);
        cursor: pointer;
    }

    .tag:hover {
        background: rgba(255, 255, 255, 0.2);
        transform: translateY(-2px);
    }

    /* Tours Container */
    .tours-container {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border-radius: var(--border-radius-xl);
        padding: 2.5rem;
        box-shadow: var(--card-shadow);
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .tours-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2.5rem;
        padding-bottom: 1.5rem;
        border-bottom: 2px solid rgba(102, 126, 234, 0.1);
    }

    .tours-header h2 {
        font-weight: 700;
        font-size: 1.75rem;
        background: var(--primary-gradient);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        margin: 0;
    }

    .tours-count {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        color: #fff;
        padding: 0.5rem 1rem;
        border-radius: var(--border-radius-lg);
        font-weight: 600;
        font-size: 0.9rem;
        box-shadow: 0 4px 15px rgba(245, 87, 108, 0.3);
    }

    /* Sort and Filter Controls */
    .tours-controls {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .sort-filter {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .sort-select {
        min-width: 150px;
    }

    .view-toggle {
        display: flex;
        gap: 0.5rem;
    }

    .view-btn {
        background: rgba(102, 126, 234, 0.1);
        border: 1px solid rgba(102, 126, 234, 0.2);
        color: #667eea;
        padding: 0.5rem;
        border-radius: var(--border-radius-sm);
        cursor: pointer;
        transition: var(--transition);
    }

    .view-btn.active {
        background: var(--primary-gradient);
        color: white;
    }

    .view-btn:hover {
        background: rgba(102, 126, 234, 0.2);
    }

    /* Enhanced Grid */
    .tours-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 2rem;
    }

    .tours-grid.list-view {
        grid-template-columns: 1fr;
    }

    /* Premium Tour Cards */
    .tour-card {
        border: none;
        border-radius: var(--border-radius-lg);
        overflow: hidden;
        background: #fff;
        display: flex;
        flex-direction: column;
        transition: var(--transition-bounce);
        box-shadow: var(--card-shadow);
        position: relative;
        height: 100%;
    }

    .tour-card.list-view {
        flex-direction: row;
        height: auto;
    }

    .tour-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
        opacity: 0;
        transition: var(--transition);
        z-index: 0;
    }

    .tour-card:hover::before {
        opacity: 1;
    }

    .tour-card:hover {
        transform: translateY(-8px) scale(1.02);
        box-shadow: var(--card-shadow-hover);
    }

    .tour-card.list-view:hover {
        transform: translateY(-4px) scale(1.01);
    }

    .tour-image {
        width: 100%;
        height: 220px;
        object-fit: cover;
        transition: var(--transition);
    }

    .tour-card.list-view .tour-image {
        width: 300px;
        height: auto;
    }

    .tour-card:hover .tour-image {
        transform: scale(1.1);
    }

    .tour-card.list-view:hover .tour-image {
        transform: scale(1.05);
    }

    .tour-card .card-body {
        padding: 1.5rem;
        flex-grow: 1;
        position: relative;
        z-index: 1;
        display: flex;
        flex-direction: column;
    }

    .tour-card .card-title {
        font-size: 1.25rem;
        font-weight: 700;
        margin-bottom: 0.75rem;
        color: #2d3748;
        line-height: 1.3;
    }

    .tour-card .card-text {
        font-size: 0.95rem;
        color: #718096;
        margin-bottom: 1rem;
        line-height: 1.6;
        flex-grow: 1;
    }

    /* Enhanced Badges */
    .tour-card .badge {
        border-radius: var(--border-radius-lg);
        padding: 0.5rem 1rem;
        font-size: 0.8rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .badge.bg-primary {
        background: var(--primary-gradient);
        box-shadow: 0 2px 10px rgba(102, 126, 234, 0.3);
    }

    .badge.bg-info {
        background: var(--success-gradient);
        box-shadow: 0 2px 10px rgba(79, 172, 254, 0.3);
    }

    .featured-badge {
        position: absolute;
        top: 15px;
        left: 15px;
        background: var(--secondary-gradient);
        color: #fff;
        border-radius: var(--border-radius-lg);
        padding: 0.5rem 1rem;
        font-size: 0.8rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        box-shadow: 0 4px 15px rgba(245, 87, 108, 0.4);
        z-index: 2;
    }

    /* Rating Stars */
    .text-warning i {
        color: #ffd700;
        text-shadow: 0 1px 3px rgba(255, 215, 0, 0.3);
    }

    /* Enhanced Card Footer */
    .tour-card .card-footer {
        padding: 1.5rem;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        position: relative;
        z-index: 1;
    }

    .tour-card.list-view .card-footer {
        flex-direction: row;
        justify-content: space-between;
        align-items: center;
    }

    .tour-card .btn {
        border-radius: var(--border-radius-sm);
        font-weight: 600;
        padding: 0.75rem 1rem;
        transition: var(--transition-bounce);
        border: none;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-size: 0.85rem;
    }

    .tour-card .btn-primary {
        background: var(--primary-gradient);
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
    }

    .tour-card .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(102, 126, 234, 0.6);
    }

    .tour-card .btn-outline-secondary {
        background: rgba(108, 117, 125, 0.1);
        border: 2px solid rgba(108, 117, 125, 0.2);
        color: #6c757d;
        transition: var(--transition);
    }

    .tour-card .btn-outline-secondary:hover {
        background: rgba(108, 117, 125, 0.1);
        border-color: #6c757d;
        color: #6c757d;
        transform: translateY(-1px);
    }

    .tour-card .btn-danger {
        background: var(--secondary-gradient);
        box-shadow: 0 4px 15px rgba(245, 87, 108, 0.4);
    }

    .tour-card .btn-danger:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(245, 87, 108, 0.6);
    }

    /* Review-specific styles */
    .review-badge {
        background: var(--success-gradient);
        color: white;
        padding: 0.25rem 0.5rem;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
    }

    .rating-display {
        display: flex;
        align-items: center;
        gap: 0.25rem;
        margin: 0.5rem 0;
    }

    .rating-display .stars {
        color: #ffd700;
        font-size: 0.9rem;
    }

    .rating-display .count {
        font-size: 0.8rem;
        color: #718096;
        margin-left: 0.5rem;
    }

    .review-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 0.5rem;
        padding-top: 0.75rem;
        border-top: 1px solid rgba(0,0,0,0.1);
    }

    .tour-card.list-view .review-actions {
        margin-top: 1rem;
        justify-content: flex-start;
        gap: 1rem;
    }

    .review-btn {
        background: var(--primary-gradient);
        color: white;
        border: none;
        border-radius: 20px;
        padding: 0.5rem 1rem;
        font-size: 0.8rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 0.25rem;
        transition: var(--transition);
        text-decoration: none;
    }

    .review-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        color: white;
    }

    /* Enhanced rating display in card */
    .rating-summary {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin: 0.75rem 0;
    }

    .rating-progress {
        flex-grow: 1;
        background: rgba(0,0,0,0.1);
        height: 4px;
        border-radius: 2px;
        overflow: hidden;
    }

    .rating-progress-bar {
        height: 100%;
        background: var(--primary-gradient);
        border-radius: 2px;
    }

    /* Review preview in tour cards */
    .review-preview {
        margin-top: 0.75rem;
        padding-top: 0.75rem;
        border-top: 1px solid rgba(0,0,0,0.05);
    }

    .review-preview-content {
        font-size: 0.85rem;
        color: #718096;
        line-height: 1.4;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        margin-bottom: 0.5rem;
    }

    .review-preview-author {
        font-size: 0.75rem;
        color: #a0aec0;
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }

    /* Enhanced empty state for reviews */
    .no-reviews {
        text-align: center;
        padding: 1rem;
        color: #a0aec0;
        font-size: 0.9rem;
    }

    .no-reviews i {
        font-size: 1.5rem;
        margin-bottom: 0.5rem;
        display: block;
    }

    /* Enhanced Empty State */
    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
        color: #718096;
        grid-column: 1 / -1;
    }

    .empty-state i {
        font-size: 4rem;
        margin-bottom: 1.5rem;
        background: var(--primary-gradient);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .empty-state h4 {
        font-weight: 700;
        color: #2d3748;
        margin-bottom: 1rem;
    }

    .empty-state .btn-primary {
        background: var(--primary-gradient);
        border: none;
        border-radius: var(--border-radius-sm);
        padding: 0.75rem 2rem;
        font-weight: 600;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        transition: var(--transition-bounce);
    }

    .empty-state .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(102, 126, 234, 0.6);
    }

    /* Enhanced Pagination */
    .pagination {
        margin-top: 3rem;
        justify-content: center;
    }

    .page-link {
        border-radius: var(--border-radius-sm);
        border: none;
        color: #667eea;
        font-weight: 600;
        margin: 0 0.25rem;
        background: rgba(255, 255, 255, 0.8);
        backdrop-filter: blur(10px);
        transition: var(--transition);
        padding: 0.5rem 1rem;
    }

    .page-link:hover {
        background: var(--primary-gradient);
        color: #fff;
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
    }

    .page-item.active .page-link {
        background: var(--primary-gradient);
        border: none;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
    }

    /* Loading Animation */
    @keyframes shimmer {
        0% { transform: translateX(-100%); }
        100% { transform: translateX(100%); }
    }

    .loading {
        position: relative;
        overflow: hidden;
    }

    .loading::after {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        bottom: 0;
        left: 0;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
        animation: shimmer 1.5s infinite;
    }

    /* Toast notifications */
    .toast-notification {
        position: fixed;
        top: 20px;
        right: 20px;
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border-radius: 12px;
        padding: 1rem 1.5rem;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
        z-index: 1000;
        display: flex;
        align-items: center;
        justify-content: space-between;
        min-width: 300px;
        animation: slideInRight 0.3s ease;
        border-left: 4px solid #667eea;
    }
    
    .toast-error {
        border-left-color: #f5576c;
    }
    
    .toast-success {
        border-left-color: #28a745;
    }
    
    .toast-content {
        display: flex;
        align-items: center;
        color: #2d3748;
        font-weight: 500;
    }
    
    .toast-close {
        background: none;
        border: none;
        color: #718096;
        cursor: pointer;
        padding: 0.25rem;
        margin-left: 1rem;
        border-radius: 4px;
        transition: background-color 0.2s;
    }
    
    .toast-close:hover {
        background-color: rgba(0, 0, 0, 0.1);
    }
    
    @keyframes slideInRight {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        25% { transform: translateX(-5px); }
        75% { transform: translateX(5px); }
    }

    /* Responsive Design */
    @media (max-width: 1200px) {
        .tours-layout {
            grid-template-columns: 250px 1fr;
        }
    }

    @media (max-width: 992px) {
        .tours-layout {
            grid-template-columns: 1fr;
        }
        
        .sidebar {
            order: 2;
        }
        
        .tours-container {
            order: 1;
        }
    }

    @media (max-width: 768px) {
        .tours-page {
            padding: 2rem 0;
        }
        
        .page-header {
            margin-bottom: 2rem;
            padding: 1rem 0;
        }
        
        .tours-container {
            padding: 1.5rem;
        }
        
        .tours-grid {
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }
        
        .tour-card.list-view {
            flex-direction: column;
        }
        
        .tour-card.list-view .tour-image {
            width: 100%;
            height: 200px;
        }
        
        .tours-controls {
            flex-direction: column;
            align-items: flex-start;
        }
    }

    @media (max-width: 576px) {
        .page-header h1 {
            font-size: 2rem;
        }
        
        .tours-header {
            flex-direction: column;
            gap: 1rem;
            align-items: flex-start;
        }
        
        .tour-card.list-view .card-footer {
            flex-direction: column;
        }
    }

    /* Accessibility */
    .btn:focus {
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.5);
    }

    .tour-card:focus-within {
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.3), var(--card-shadow-hover);
    }

    /* High contrast mode */
    @media (prefers-contrast: high) {
        .tours-page {
            background: #000;
        }
        
        .tours-container {
            background: #fff;
            border: 2px solid #000;
        }
        
        .tour-card {
            border: 2px solid #000;
        }
    }

    /* Reduced motion */
    @media (prefers-reduced-motion: reduce) {
        * {
            animation-duration: 0.01ms !important;
            animation-iteration-count: 1 !important;
            transition-duration: 0.01ms !important;
        }
        
        .tour-card:hover {
            transform: none;
        }
    }
    </style>
</head>
<body>
    <div class="tours-page">
        <div class="container">
            <!-- Header -->
            <div class="page-header">
                <h1>Discover Amazing VR Tours</h1>
                <p>Explore immersive virtual reality experiences from around the world</p>
            </div>

            <div class="tours-layout">
                <!-- Sidebar -->
                <div class="sidebar">
                    <!-- Filters -->
                    <div class="card">
                        <div class="card-header">
                            <i class="fas fa-filter me-2"></i> Filters
                        </div>
                        <div class="card-body">
                            <form method="GET" action="tours.php" id="filter-form">
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
                                <div class="mb-3">
                                    <label for="min_rating" class="form-label fw-semibold">Minimum Rating</label>
                                    <select class="form-control" id="min_rating" name="min_rating">
                                        <option value="0" <?php echo $min_rating==0 ? 'selected' : ''; ?>>Any Rating</option>
                                        <option value="4" <?php echo $min_rating==4 ? 'selected' : ''; ?>>4+ Stars</option>
                                        <option value="3" <?php echo $min_rating==3 ? 'selected' : ''; ?>>3+ Stars</option>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary w-100 mb-2">
                                    <i class="fas fa-search me-2"></i> Apply Filters
                                </button>
                                <?php if ($category || $search || $min_rating): ?>
                                <a href="tours.php" class="btn btn-outline-secondary w-100">
                                    <i class="fas fa-times me-2"></i> Clear Filters
                                </a>
                                <?php endif; ?>
                            </form>
                        </div>
                    </div>

                    <!-- Categories -->
                    <div class="card">
                        <div class="card-header">
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

                    <!-- Popular Tags -->
                    <div class="card">
                        <div class="card-header">
                            <i class="fas fa-tags me-2"></i> Popular Tags
                        </div>
                        <div class="card-body">
                            <div class="tags-cloud">
                                <?php foreach ($popular_tags as $tag): ?>
                                <span class="tag" data-tag="<?php echo htmlspecialchars($tag); ?>">
                                    <?php echo htmlspecialchars($tag); ?>
                                </span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tours List -->
                <div class="tours-container">
                    <div class="tours-header">
                        <h2><i class="fas fa-vr-cardboard me-2"></i>VR Tours</h2>
                        <span class="tours-count"><?php echo $total_tours; ?> tours found</span>
                    </div>

                    <div class="tours-controls">
                        <div class="sort-filter">
                            <label for="sort_by" class="form-label me-2 fw-semibold">Sort by:</label>
                            <select class="form-control sort-select" id="sort_by" name="sort_by">
                                <option value="newest" <?php echo $sort_by === 'newest' ? 'selected' : ''; ?>>Newest</option>
                                <option value="popular" <?php echo $sort_by === 'popular' ? 'selected' : ''; ?>>Most Popular</option>
                                <option value="rating" <?php echo $sort_by === 'rating' ? 'selected' : ''; ?>>Highest Rated</option>
                                <option value="scenes" <?php echo $sort_by === 'scenes' ? 'selected' : ''; ?>>Most Scenes</option>
                            </select>
                        </div>
                        <div class="view-toggle">
                            <button class="view-btn active" data-view="grid">
                                <i class="fas fa-th"></i>
                            </button>
                            <button class="view-btn" data-view="list">
                                <i class="fas fa-list"></i>
                            </button>
                        </div>
                    </div>

                    <?php if (count($tours) > 0): ?>
                    <div class="tours-grid" id="tours-grid">
                        <?php foreach ($tours as $tour): 
                            // Get a sample review for preview
                            $review_stmt = $pdo->prepare("
                                SELECT r.comment, u.username 
                                FROM reviews r 
                                JOIN users u ON r.user_id = u.id 
                                WHERE r.tour_id = ? AND r.approved = 1 
                                ORDER BY r.created_at DESC 
                                LIMIT 1
                            ");
                            $review_stmt->execute([$tour['id']]);
                            $sample_review = $review_stmt->fetch();
                        ?>
                        <div class="tour-card">
                            <?php if ($tour['thumbnail']): ?>
                                <img src="assets/images/uploads/<?php echo $tour['thumbnail']; ?>" 
                                     alt="<?php echo htmlspecialchars($tour['title']); ?>"
                                     class="tour-image" loading="lazy">
                            <?php else: ?>
                                <div class="d-flex align-items-center justify-content-center bg-light tour-image">
                                    <i class="fas fa-image fa-3x text-muted"></i>
                                </div>
                            <?php endif; ?>

                            <?php if ($tour['featured']): ?>
                            <div class="featured-badge"><i class="fas fa-star me-1"></i> Featured</div>
                            <?php endif; ?>

                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($tour['title']); ?></h5>
                                <p class="card-text">
                                    <?php echo htmlspecialchars(substr($tour['description'], 0, 120)); ?>
                                    <?php if (strlen($tour['description']) > 120) echo "..."; ?>
                                </p>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="badge bg-primary"><?php echo $tour['scene_count']; ?> Scenes</span>
                                    <span class="badge bg-info"><?php echo htmlspecialchars($tour['category']); ?></span>
                                </div>
                                
                                <!-- Review Rating Display -->
                                <?php if ($tour['avg_rating']): ?>
                                <div class="rating-summary">
                                    <div class="rating-display">
                                        <div class="stars">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <i class="fas fa-star<?php echo $i <= round($tour['avg_rating']) ? '' : '-half-alt'; ?>"></i>
                                            <?php endfor; ?>
                                        </div>
                                        <span class="count">(<?php echo $tour['review_count']; ?> reviews)</span>
                                    </div>
                                    <div class="rating-progress">
                                        <div class="rating-progress-bar" style="width: <?php echo ($tour['avg_rating'] / 5) * 100; ?>%"></div>
                                    </div>
                                </div>
                                <?php endif; ?>
                                
                                <!-- Review Preview -->
                                <?php if ($sample_review): ?>
                                <div class="review-preview">
                                    <div class="review-preview-content">
                                        <i class="fas fa-quote-left text-muted me-1" style="font-size: 0.8rem;"></i>
                                        <?php echo htmlspecialchars(substr($sample_review['comment'], 0, 100)); ?>
                                        <?php if (strlen($sample_review['comment']) > 100) echo '...'; ?>
                                    </div>
                                    <div class="review-preview-author">
                                        <i class="fas fa-user"></i>
                                        <?php echo htmlspecialchars($sample_review['username']); ?>
                                    </div>
                                </div>
                                <?php elseif ($tour['review_count'] == 0): ?>
                                <div class="no-reviews">
                                    <i class="fas fa-comment-slash"></i>
                                    <div>No reviews yet</div>
                                </div>
                                <?php endif; ?>
                            </div>

                            <div class="card-footer">
                                <a href="vr/tour.php?id=<?php echo $tour['id']; ?>" class="btn btn-primary w-100 mb-2">
                                    <i class="fas fa-play-circle me-1"></i> Start Tour
                                </a>
                                <div class="review-actions">
                                    <?php if (isUserLoggedIn()): ?>
                                    <button class="btn btn-outline-secondary favorite-btn" data-tour-id="<?php echo $tour['id']; ?>">
                                        <i class="far fa-heart me-1"></i>Favorite
                                    </button>
                                    <?php else: ?>
                                    <a href="login.php" class="btn btn-outline-secondary">
                                        <i class="far fa-heart me-1"></i>Favorite
                                    </a>
                                    <?php endif; ?>
                                    
                                    <a href="user/submit-review.php?tour_id=<?php echo $tour['id']; ?>" class="review-btn">
                                        <i class="fas fa-star me-1"></i> Review
                                    </a>
                                </div>
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
                            <?php 
                            // Show limited pagination links
                            $start = max(1, $page - 2);
                            $end = min($total_pages, $start + 4);
                            if ($end - $start < 4) {
                                $start = max(1, $end - 4);
                            }
                            for ($i=$start;$i<=$end;$i++): ?>
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
                        <p>Try changing your filters or search again.</p>
                        <a href="tours.php" class="btn btn-primary mt-2">Browse All Tours</a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function(){
        // Enhanced favorite button functionality with animations
        document.querySelectorAll('.favorite-btn').forEach(btn=>{
            btn.addEventListener('click', function(){
                const tourId = this.dataset.tourId;
                const button = this;
                
                // Add loading animation
                button.classList.add('loading');
                button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Loading...';
                button.disabled = true;

                fetch('user/favorite.php', {
                    method:'POST',
                    headers:{'Content-Type':'application/x-www-form-urlencoded'},
                    body:'tour_id='+tourId+'&action=toggle'
                }).then(r=>r.json()).then(data=>{
                    if(data.success){
                        // Add success animation
                        button.style.transform = 'scale(0.95)';
                        setTimeout(() => {
                            button.style.transform = 'scale(1)';
                        }, 150);
                        
                        if(data.status==='added'){
                            button.innerHTML = '<i class="fas fa-heart me-1"></i> Remove';
                            button.classList.add('btn-danger');
                            button.classList.remove('btn-outline-secondary');
                        } else {
                            button.innerHTML = '<i class="far fa-heart me-1"></i> Favorite';
                            button.classList.remove('btn-danger');
                            button.classList.add('btn-outline-secondary');
                        }
                    } else {
                        // Show error with shake animation
                        button.style.animation = 'shake 0.5s ease-in-out';
                        setTimeout(() => {
                            button.style.animation = '';
                        }, 500);
                        
                        // Create and show toast notification
                        showToast(data.message, 'error');
                    }
                }).catch(error => {
                    console.error('Error:', error);
                    showToast('An error occurred. Please try again.', 'error');
                }).finally(()=>{
                    button.classList.remove('loading');
                    button.disabled = false;
                });
            });
        });
        
        // View toggle functionality
        const viewButtons = document.querySelectorAll('.view-btn');
        const toursGrid = document.getElementById('tours-grid');
        
        viewButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                const view = this.dataset.view;
                
                // Update active state
                viewButtons.forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                
                // Apply view class
                if (view === 'list') {
                    toursGrid.classList.add('list-view');
                    document.querySelectorAll('.tour-card').forEach(card => {
                        card.classList.add('list-view');
                    });
                } else {
                    toursGrid.classList.remove('list-view');
                    document.querySelectorAll('.tour-card').forEach(card => {
                        card.classList.remove('list-view');
                    });
                }
                
                // Save preference to localStorage
                localStorage.setItem('tourViewPreference', view);
            });
        });
        
        // Load saved view preference
        const savedView = localStorage.getItem('tourViewPreference') || 'grid';
        if (savedView === 'list') {
            document.querySelector('[data-view="list"]').click();
        }
        
        // Sort by functionality
        const sortSelect = document.getElementById('sort_by');
        if (sortSelect) {
            sortSelect.addEventListener('change', function() {
                document.getElementById('filter-form').submit();
            });
        }
        
        // Tag search functionality
        document.querySelectorAll('.tag').forEach(tag => {
            tag.addEventListener('click', function() {
                const tagName = this.dataset.tag;
                const searchInput = document.getElementById('search');
                searchInput.value = tagName;
                document.getElementById('filter-form').submit();
            });
        });
        
        // Smooth scroll for pagination
        document.querySelectorAll('.pagination a').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const href = this.getAttribute('href');
                
                // Add loading state to current page
                document.querySelector('.tours-grid').style.opacity = '0.6';
                document.querySelector('.tours-grid').style.pointerEvents = 'none';
                
                // Simulate page load (in real app, this would be handled by the server)
                setTimeout(() => {
                    window.location.href = href;
                }, 300);
            });
        });
        
        // Enhanced search functionality with debouncing
        const searchInput = document.getElementById('search');
        let searchTimeout;
        
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    // Add subtle animation to indicate search is happening
                    this.style.boxShadow = '0 0 0 2px rgba(102, 126, 234, 0.3)';
                    setTimeout(() => {
                        this.style.boxShadow = '';
                    }, 1000);
                }, 500);
            });
        }
        
        // Intersection Observer for card animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };
        
        const cardObserver = new IntersectionObserver((entries) => {
            entries.forEach((entry, index) => {
                if (entry.isIntersecting) {
                    setTimeout(() => {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }, index * 100);
                    cardObserver.unobserve(entry.target);
                }
            });
        }, observerOptions);
        
        // Initially hide cards and observe them
        document.querySelectorAll('.tour-card').forEach((card, index) => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(30px)';
            card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            cardObserver.observe(card);
        });
        
        // Lazy loading for images
        const imageObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    if (img.dataset.src) {
                        img.src = img.dataset.src;
                        img.classList.remove('lazy');
                        imageObserver.unobserve(img);
                    }
                }
            });
        });
        
        document.querySelectorAll('img[data-src]').forEach(img => {
            imageObserver.observe(img);
        });
        
        // Form auto-submit on filter change
        const filterForm = document.getElementById('filter-form');
        const categorySelect = document.getElementById('category');
        const minRatingSelect = document.getElementById('min_rating');
        
        [categorySelect, minRatingSelect].forEach(select => {
            if (select) {
                select.addEventListener('change', function() {
                    filterForm.submit();
                });
            }
        });
    });

    // Toast notification system
    function showToast(message, type = 'info') {
        // Remove existing toasts
        document.querySelectorAll('.toast-notification').forEach(toast => toast.remove());
        
        const toast = document.createElement('div');
        toast.className = `toast-notification toast-${type}`;
        toast.innerHTML = `
            <div class="toast-content">
                <i class="fas fa-${type === 'error' ? 'exclamation-circle' : type === 'success' ? 'check-circle' : 'info-circle'} me-2"></i>
                <span>${message}</span>
            </div>
            <button class="toast-close" onclick="this.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        `;
        
        document.body.appendChild(toast);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            if (toast.parentElement) {
                toast.style.animation = 'slideInRight 0.3s ease reverse';
                setTimeout(() => toast.remove(), 300);
            }
        }, 5000);
    }

    // Enhanced keyboard navigation
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            // Close any open modals or dropdowns
            document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
                menu.classList.remove('show');
            });
        }
        
        if (e.key === '/' && !e.target.matches('input, textarea')) {
            e.preventDefault();
            document.getElementById('search')?.focus();
        }
    });

    // Performance optimization: Throttle scroll events
    let ticking = false;

    function updateScrollProgress() {
        const scrollTop = window.pageYOffset;
        const docHeight = document.body.offsetHeight;
        const winHeight = window.innerHeight;
        const scrollPercent = scrollTop / (docHeight - winHeight);
        
        // You can use this for scroll progress indicators
        document.documentElement.style.setProperty('--scroll-progress', scrollPercent);
        
        ticking = false;
    }

    window.addEventListener('scroll', function() {
        if (!ticking) {
            requestAnimationFrame(updateScrollProgress);
            ticking = true;
        }
    });
    </script>
</body>
</html>

<?php include 'includes/user-footer.php'; ?>