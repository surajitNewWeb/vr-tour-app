<?php
// index.php
require_once 'includes/config.php';
require_once 'includes/user-auth.php';
require_once 'includes/database.php';

// Get featured tours
$stmt = $pdo->prepare("
    SELECT t.*, 
           (SELECT COUNT(*) FROM scenes s WHERE s.tour_id = t.id) as scene_count,
           (SELECT AVG(rating) FROM reviews r WHERE r.tour_id = t.id AND r.approved = 1) as avg_rating,
           t.category as category_name
    FROM tours t 
    WHERE t.published = 1 AND t.featured = 1
    ORDER BY t.created_at DESC 
    LIMIT 6
");
$stmt->execute();
$featured_tours = $stmt->fetchAll();

// Get recent tours
$stmt = $pdo->prepare("
    SELECT t.*, 
           (SELECT COUNT(*) FROM scenes s WHERE s.tour_id = t.id) as scene_count,
           (SELECT AVG(rating) FROM reviews r WHERE r.tour_id = t.id AND r.approved = 1) as avg_rating,
           t.category as category_name
    FROM tours t 
    WHERE t.published = 1
    ORDER BY t.created_at DESC 
    LIMIT 6
");
$stmt->execute();
$recent_tours = $stmt->fetchAll();

// Get popular categories from tours table
$stmt = $pdo->prepare("
    SELECT category as name, COUNT(*) as tour_count
    FROM tours 
    WHERE published = 1
    GROUP BY category 
    ORDER BY tour_count DESC 
    LIMIT 6
");
$stmt->execute();
$categories = $stmt->fetchAll();

// Get tour statistics
$stmt = $pdo->prepare("
    SELECT 
        (SELECT COUNT(*) FROM tours WHERE published = 1) as total_tours,
        (SELECT COUNT(*) FROM scenes) as total_scenes,
        (SELECT COUNT(*) FROM users) as total_users,
        (SELECT COUNT(*) FROM favorites) as total_favorites,
        (SELECT COUNT(*) FROM tours WHERE published = 1 AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)) as weekly_tours
");
$stmt->execute();
$stats = $stmt->fetch();

// Get trending tours (based on views/favorites in last 30 days)
$stmt = $pdo->prepare("
    SELECT t.*, 
           (SELECT COUNT(*) FROM scenes s WHERE s.tour_id = t.id) as scene_count,
           (SELECT AVG(rating) FROM reviews r WHERE r.tour_id = t.id AND r.approved = 1) as avg_rating,
           t.category as category_name,
           COUNT(DISTINCT f.id) as favorite_count
    FROM tours t
    LEFT JOIN favorites f ON t.id = f.tour_id AND f.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    WHERE t.published = 1
    GROUP BY t.id
    ORDER BY favorite_count DESC, t.views DESC
    LIMIT 3
");
$stmt->execute();
$trending_tours = $stmt->fetchAll();

$page_title = "Explore Immersive VR Tours";
$show_page_header = false;
$body_class = isUserLoggedIn() ? 'user-logged-in' : 'user-guest';

include 'includes/user-header.php';
?>

<style>
    /* Enhanced Homepage Styles */
    :root {
        --hero-gradient-1: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        --hero-gradient-2: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        --hero-gradient-3: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        --card-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        --card-shadow-hover: 0 30px 60px rgba(0, 0, 0, 0.15);
        --glow-primary: 0 0 30px rgba(99, 102, 241, 0.3);
        --glow-secondary: 0 0 30px rgba(236, 72, 153, 0.3);
        --vr-accent: #00d4ff;
    }

    /* Hero Section with 360° Preview */
    .hero {
        min-height: 100vh;
        background: linear-gradient(135deg, #0f172a, #1e293b);
        position: relative;
        display: flex;
        align-items: center;
        overflow: hidden;
        isolation: isolate;
    }

    .hero::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background:
            radial-gradient(circle at 20% 80%, rgba(99, 102, 241, 0.15) 0%, transparent 50%),
            radial-gradient(circle at 80% 20%, rgba(236, 72, 153, 0.15) 0%, transparent 50%);
        animation: heroGlow 8s ease-in-out infinite alternate;
        z-index: -1;
    }

    @keyframes heroGlow {
        0% {
            opacity: 0.5;
            transform: scale(1);
        }

        100% {
            opacity: 1;
            transform: scale(1.05);
        }
    }

    .hero-content {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 4rem;
        align-items: center;
        max-width: 1400px;
        margin: 0 auto;
        padding: 2rem;
        position: relative;
        z-index: 2;
    }

    .hero-text {
        color: white;
    }

    .hero-title {
        font-size: 3.5rem;
        font-weight: 800;
        line-height: 1.1;
        margin-bottom: 1.5rem;
        background: linear-gradient(135deg, #ffffff, #cbd5e1);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        text-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    }

    .hero-description {
        font-size: 1.25rem;
        line-height: 1.8;
        margin-bottom: 2.5rem;
        opacity: 0.9;
        max-width: 500px;
    }

    .hero-actions {
        display: flex;
        gap: 1.5rem;
        flex-wrap: wrap;
    }

    .btn-lg {
        padding: 1rem 2.5rem;
        font-size: 1.1rem;
        border-radius: 50px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }

    .btn-lg::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
        transition: left 0.5s;
    }

    .btn-lg:hover::before {
        left: 100%;
    }

    .btn-outline-light {
        background: transparent;
        color: white;
        border: 2px solid rgba(255, 255, 255, 0.3);
        backdrop-filter: blur(10px);
    }

    .btn-outline-light:hover {
        background: rgba(255, 255, 255, 0.1);
        border-color: white;
        transform: translateY(-2px);
        box-shadow: 0 10px 30px rgba(255, 255, 255, 0.2);
    }

    /* 360° Preview Container */
    .vr-preview-container {
        position: relative;
        width: 100%;
        height: 400px;
        border-radius: 24px;
        overflow: hidden;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        transform-style: preserve-3d;
        perspective: 1000px;
    }

    .vr-preview {
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, #1e293b, #0f172a);
        border-radius: 24px;
        overflow: hidden;
        position: relative;
    }

    .vr-scene {
        width: 100%;
        height: 100%;
        background-size: cover;
        background-position: center;
        transition: transform 0.1s linear;
        cursor: grab;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .vr-scene:active {
        cursor: grabbing;
    }

    .vr-scene-placeholder {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #334155, #475569);
        color: white;
        font-size: 1.2rem;
        text-align: center;
        padding: 2rem;
    }

    .vr-controls {
        position: absolute;
        bottom: 20px;
        left: 50%;
        transform: translateX(-50%);
        display: flex;
        gap: 1rem;
        background: rgba(0, 0, 0, 0.7);
        backdrop-filter: blur(10px);
        padding: 0.75rem 1.5rem;
        border-radius: 50px;
        z-index: 10;
    }

    .vr-control-btn {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .vr-control-btn:hover {
        background: rgba(255, 255, 255, 0.2);
        transform: scale(1.1);
    }

    .vr-control-btn:active {
        transform: scale(0.95);
    }

    .vr-info {
        position: absolute;
        top: 20px;
        left: 20px;
        background: rgba(0, 0, 0, 0.7);
        backdrop-filter: blur(10px);
        padding: 0.75rem 1.5rem;
        border-radius: 50px;
        color: white;
        font-weight: 600;
        z-index: 10;
    }

    .vr-headset-indicator {
        position: absolute;
        top: 20px;
        right: 20px;
        background: rgba(0, 0, 0, 0.7);
        backdrop-filter: blur(10px);
        padding: 0.5rem 1rem;
        border-radius: 50px;
        color: white;
        font-size: 0.875rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        z-index: 10;
    }

    .vr-headset-indicator i {
        color: var(--vr-accent);
    }

    /* Stats Section */
    .stats-section {
        padding: 6rem 0;
        background: linear-gradient(135deg, #f8fafc, #e2e8f0);
        position: relative;
    }

    .stats-section::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 1px;
        background: linear-gradient(90deg, transparent, var(--primary), transparent);
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 3rem;
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 2rem;
    }

    .stat-item {
        text-align: center;
        padding: 2rem;
        background: white;
        border-radius: 20px;
        box-shadow: var(--card-shadow);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }

    .stat-item::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(135deg, var(--primary), var(--secondary));
    }

    .stat-item:hover {
        transform: translateY(-10px);
        box-shadow: var(--card-shadow-hover);
    }

    .stat-number {
        font-size: 3rem;
        font-weight: 800;
        color: var(--primary);
        margin-bottom: 0.5rem;
        background: linear-gradient(135deg, var(--primary), var(--secondary));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .stat-label {
        font-size: 1.1rem;
        color: var(--gray-600);
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    /* Section Styles */
    .featured-section,
    .recent-section,
    .categories-section,
    .trending-section {
        padding: 8rem 0;
        position: relative;
    }

    .featured-section {
        background: linear-gradient(135deg, #ffffff, #f8fafc);
    }

    .recent-section {
        background: linear-gradient(135deg, #f8fafc, #e2e8f0);
    }

    .categories-section {
        background: linear-gradient(135deg, #ffffff, #f8fafc);
    }

    .trending-section {
        background: linear-gradient(135deg, #f8fafc, #e2e8f0);
    }

    .section-header {
        text-align: center;
        margin-bottom: 4rem;
        max-width: 800px;
        margin-left: auto;
        margin-right: auto;
        position: relative;
    }

    .section-header h2 {
        font-size: 3rem;
        font-weight: 800;
        margin-bottom: 1rem;
        background: linear-gradient(135deg, var(--gray-900), var(--gray-700));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        position: relative;
        display: inline-block;
    }

    .section-header h2::after {
        content: '';
        position: absolute;
        bottom: -10px;
        left: 50%;
        transform: translateX(-50%);
        width: 60px;
        height: 4px;
        background: linear-gradient(135deg, var(--primary), var(--secondary));
        border-radius: 2px;
    }

    .section-header p {
        font-size: 1.25rem;
        color: var(--gray-600);
        line-height: 1.8;
        margin-top: 1.5rem;
    }

    /* Tours Grid */
    .tours-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
        gap: 2.5rem;
        max-width: 1400px;
        margin: 0 auto;
        padding: 0 2rem;
    }

    .tour-card {
        background: white;
        border-radius: 24px;
        overflow: hidden;
        box-shadow: var(--card-shadow);
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        group: hover;
    }

    .tour-card:hover {
        transform: translateY(-15px) scale(1.02);
        box-shadow: var(--card-shadow-hover);
    }

    .tour-image {
        position: relative;
        height: 250px;
        overflow: hidden;
        background: linear-gradient(135deg, var(--gray-200), var(--gray-300));
    }

    .tour-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .tour-card:hover .tour-image img {
        transform: scale(1.1);
    }

    .tour-image-placeholder {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--gray-500);
        font-size: 3rem;
        background: linear-gradient(135deg, var(--gray-100), var(--gray-200));
    }

    .tour-badges {
        position: absolute;
        top: 1rem;
        left: 1rem;
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
        z-index: 3;
    }

    .badge {
        padding: 0.5rem 1rem;
        border-radius: 50px;
        font-size: 0.875rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        backdrop-filter: blur(10px);
    }

    .badge-primary {
        background: rgba(99, 102, 241, 0.9);
        color: white;
        box-shadow: var(--glow-primary);
    }

    .badge-secondary {
        background: rgba(107, 114, 128, 0.9);
        color: white;
    }

    .badge-success {
        background: rgba(16, 185, 129, 0.9);
        color: white;
    }

    .badge-accent {
        background: rgba(0, 212, 255, 0.9);
        color: white;
        box-shadow: 0 0 20px rgba(0, 212, 255, 0.5);
    }

    .tour-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.6);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        backdrop-filter: blur(5px);
    }

    .tour-card:hover .tour-overlay {
        opacity: 1;
    }

    .tour-overlay .btn {
        transform: translateY(20px);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border-radius: 50px;
        padding: 1rem 2rem;
        font-weight: 600;
    }

    .tour-card:hover .tour-overlay .btn {
        transform: translateY(0);
    }

    .tour-content {
        padding: 2rem;
    }

    .tour-title {
        font-size: 1.5rem;
        font-weight: 700;
        margin-bottom: 1rem;
        color: var(--gray-900);
        line-height: 1.3;
    }

    .tour-description {
        color: var(--gray-600);
        line-height: 1.6;
        margin-bottom: 1.5rem;
        font-size: 1rem;
    }

    .tour-meta {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .tour-rating {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .tour-rating .fas.fa-star {
        color: var(--gray-300);
        font-size: 1rem;
        transition: color 0.2s;
    }

    .tour-rating .fas.fa-star.active {
        color: #fbbf24;
    }

    .tour-rating span {
        font-size: 0.875rem;
        color: var(--gray-600);
        margin-left: 0.5rem;
    }

    .tour-date {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: var(--gray-600);
        font-size: 0.875rem;
    }

    .tour-category {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: var(--primary);
        font-size: 0.875rem;
        font-weight: 600;
    }

    /* Trending Tours */
    .trending-tours {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
        gap: 3rem;
        max-width: 1400px;
        margin: 0 auto;
        padding: 0 2rem;
    }

    .trending-card {
        background: white;
        border-radius: 24px;
        overflow: hidden;
        box-shadow: var(--card-shadow);
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
    }

    .trending-card:hover {
        transform: translateY(-10px);
        box-shadow: var(--card-shadow-hover);
    }

    .trending-badge {
        position: absolute;
        top: 1rem;
        right: 1rem;
        background: linear-gradient(135deg, #f59e0b, #d97706);
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 50px;
        font-size: 0.875rem;
        font-weight: 600;
        z-index: 3;
        box-shadow: 0 5px 15px rgba(245, 158, 11, 0.3);
    }

    /* Empty State */
    .empty-state {
        grid-column: 1 / -1;
        text-align: center;
        padding: 4rem 2rem;
        color: var(--gray-600);
    }

    .empty-state i {
        font-size: 4rem;
        margin-bottom: 2rem;
        color: var(--gray-400);
    }

    .empty-state h3 {
        font-size: 2rem;
        margin-bottom: 1rem;
        color: var(--gray-700);
    }

    .section-footer {
        text-align: center;
        margin-top: 4rem;
    }

    /* Features Section */
    .features-section {
        padding: 8rem 0;
        background: linear-gradient(135deg, var(--gray-900), var(--gray-800));
        color: white;
        position: relative;
        overflow: hidden;
    }

    .features-section::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background:
            radial-gradient(circle at 20% 20%, rgba(99, 102, 241, 0.1) 0%, transparent 50%),
            radial-gradient(circle at 80% 80%, rgba(236, 72, 153, 0.1) 0%, transparent 50%);
    }

    .features-section .section-header h2,
    .features-section .section-header p {
        color: white;
    }

    .features-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 3rem;
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 2rem;
    }

    .feature-item {
        text-align: center;
        padding: 3rem 2rem;
        background: rgba(255, 255, 255, 0.05);
        border-radius: 24px;
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.1);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }

    .feature-item::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.05), transparent);
        transform: rotate(45deg);
        transition: all 0.5s;
        opacity: 0;
    }

    .feature-item:hover::before {
        opacity: 1;
        animation: shimmer 1.5s ease-in-out;
    }

    @keyframes shimmer {
        0% {
            transform: translateX(-100%) translateY(-100%) rotate(45deg);
        }

        100% {
            transform: translateX(100%) translateY(100%) rotate(45deg);
        }
    }

    .feature-item:hover {
        transform: translateY(-10px);
        background: rgba(255, 255, 255, 0.1);
        border-color: rgba(255, 255, 255, 0.2);
    }

    .feature-icon {
        width: 100px;
        height: 100px;
        margin: 0 auto 2rem;
        background: linear-gradient(135deg, var(--primary), var(--secondary));
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.5rem;
        color: white;
        box-shadow: var(--glow-primary);
        position: relative;
        z-index: 2;
    }

    .feature-item h3 {
        font-size: 1.5rem;
        font-weight: 700;
        margin-bottom: 1rem;
        color: white;
    }

    .feature-item p {
        color: rgba(255, 255, 255, 0.8);
        line-height: 1.6;
        font-size: 1rem;
    }

    /* CTA Section */
    .cta-section {
        padding: 8rem 0;
        background: linear-gradient(135deg, var(--primary), var(--primary-dark));
        color: white;
        text-align: center;
        position: relative;
        overflow: hidden;
    }

    .cta-section::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background:
            radial-gradient(circle at 30% 30%, rgba(255, 255, 255, 0.1) 0%, transparent 50%),
            radial-gradient(circle at 70% 70%, rgba(255, 255, 255, 0.05) 0%, transparent 50%);
        animation: ctaGlow 10s ease-in-out infinite alternate;
    }

    @keyframes ctaGlow {
        0% {
            opacity: 0.5;
        }

        100% {
            opacity: 1;
        }
    }

    .cta-content {
        max-width: 800px;
        margin: 0 auto;
        padding: 0 2rem;
        position: relative;
        z-index: 2;
    }

    .cta-content h2 {
        font-size: 3.5rem;
        font-weight: 800;
        margin-bottom: 1.5rem;
        background: linear-gradient(135deg, #ffffff, #f0f0f0);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .cta-content p {
        font-size: 1.25rem;
        margin-bottom: 3rem;
        opacity: 0.9;
        line-height: 1.8;
    }

    .cta-actions {
        display: flex;
        gap: 1.5rem;
        justify-content: center;
        flex-wrap: wrap;
    }

    /* Responsive Design */
    @media (max-width: 1024px) {
        .hero-content {
            grid-template-columns: 1fr;
            gap: 3rem;
            text-align: center;
        }

        .hero-title {
            font-size: 3rem;
        }

        .vr-preview-container {
            height: 350px;
        }

        .tours-grid {
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }

        .trending-tours {
            grid-template-columns: 1fr;
        }

        .features-grid {
            grid-template-columns: 1fr;
            gap: 2rem;
        }
    }

    @media (max-width: 768px) {
        .hero {
            min-height: 80vh;
        }

        .hero-title {
            font-size: 2.5rem;
        }

        .hero-description {
            font-size: 1.1rem;
        }

        .hero-actions {
            flex-direction: column;
            align-items: center;
        }

        .btn-lg {
            width: 100%;
            max-width: 300px;
        }

        .vr-preview-container {
            height: 300px;
        }

        .vr-control-btn {
            width: 40px;
            height: 40px;
        }

        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 2rem;
        }

        .tours-grid {
            grid-template-columns: 1fr;
            gap: 2rem;
            padding: 0 1rem;
        }

        .categories-grid {
            grid-template-columns: 1fr;
        }

        .section-header h2 {
            font-size: 2.5rem;
        }

        .cta-content h2 {
            font-size: 2.5rem;
        }

        .cta-actions {
            flex-direction: column;
            align-items: center;
        }
    }

    @media (max-width: 480px) {
        .hero-title {
            font-size: 2rem;
        }

        .stats-grid {
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }

        .stat-number {
            font-size: 2.5rem;
        }

        .section-header h2 {
            font-size: 2rem;
        }

        .cta-content h2 {
            font-size: 2rem;
        }

        .vr-controls {
            padding: 0.5rem 1rem;
        }
    }

    /* Accessibility */
    @media (prefers-reduced-motion: reduce) {

        *,
        *::before,
        *::after {
            animation-duration: 0.01ms !important;
            animation-iteration-count: 1 !important;
            transition-duration: 0.01ms !important;
        }
    }

    /* High contrast mode */
    @media (prefers-contrast: high) {
        .tour-card {
            border: 2px solid var(--gray-900);
        }

        .btn {
            border: 2px solid currentColor;
        }
    }

    /* Dark mode support */
    @media (prefers-color-scheme: dark) {
        .featured-section {
            background: linear-gradient(135deg, #1f2937, #111827);
        }

        .recent-section {
            background: linear-gradient(135deg, #111827, #0f172a);
        }

        .tour-card {
            background: #1f2937;
            border: 1px solid #374151;
        }

        .tour-title {
            color: #f9fafb;
        }

        .tour-description {
            color: #d1d5db;
        }
    }
</style>

<!-- Hero Section with 360° Preview -->
<section class="hero">
    <div class="hero-content">
        <div class="hero-text">
            <h1 class="hero-title">Immerse Yourself in Virtual Worlds</h1>
            <p class="hero-description">Experience breathtaking 360° virtual tours of museums, landmarks, and hidden
                gems from around the globe. Our cutting-edge VR technology brings destinations to life like never
                before.</p>
            <div class="hero-actions">
                <a href="<?php echo BASE_URL; ?>tours.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-compass me-2"></i>Explore Tours
                </a>
                <?php if (!isUserLoggedIn()): ?>
                <a href="<?php echo BASE_URL; ?>register.php" class="btn btn-outline-light btn-lg">
                    <i class="fas fa-user-plus me-2"></i>Get Started
                </a>
                <?php else: ?>
                <a href="<?php echo BASE_URL; ?>user/dashboard.php" class="btn btn-outline-light btn-lg">
                    <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                </a>
                <?php endif; ?>
            </div>
        </div>
        <div class="hero-visual">
            <div class="vr-preview-container">
                <div class="vr-preview">
                    <div class="vr-scene" id="vr-scene"
                        style="background-image: url('<?php echo BASE_URL; ?>assets/images/360-sample.jpg')">
                        <div class="vr-scene-placeholder" style="display: none;">
                            <div>
                                <i class="fas fa-vr-cardboard mb-3" style="font-size: 3rem;"></i>
                                <p>Drag to explore this 360° view</p>
                            </div>
                        </div>
                    </div>
                    <div class="vr-info">
                        <i class="fas fa-map-marker-alt me-2"></i> Taj Mahal, India
                    </div>
                    <div class="vr-headset-indicator">
                        <i class="fas fa-vr-cardboard"></i> VR Compatible
                    </div>
                    <div class="vr-controls">
                        <button class="vr-control-btn" id="vr-zoom-in">
                            <i class="fas fa-search-plus"></i>
                        </button>
                        <button class="vr-control-btn" id="vr-fullscreen">
                            <i class="fas fa-expand"></i>
                        </button>
                        <button class="vr-control-btn" id="vr-zoom-out">
                            <i class="fas fa-search-minus"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Stats Section -->
<section class="stats-section">
    <div class="container">
        <div class="stats-grid">
            <div class="stat-item">
                <div class="stat-number" id="stat-tours">
                    <?= $stats['total_tours'] ?>
                </div>
                <div class="stat-label">VR Tours</div>
            </div>
            <div class="stat-item">
                <div class="stat-number" id="stat-scenes">
                    <?= $stats['total_scenes'] ?>
                </div>
                <div class="stat-label">360° Scenes</div>
            </div>
            <div class="stat-item">
                <div class="stat-number" id="stat-users">
                    <?= $stats['total_users'] ?>
                </div>
                <div class="stat-label">Explorers</div>
            </div>
            <div class="stat-item">
                <div class="stat-number" id="stat-favorites">
                    <?= $stats['total_favorites'] ?>
                </div>
                <div class="stat-label">Favorites</div>
            </div>
        </div>
    </div>
</section>

<!-- Trending Tours -->
<?php if (!empty($trending_tours)): ?>
<section class="trending-section">
    <div class="container">
        <div class="section-header">
            <h2>Trending Now</h2>
            <p>Most popular virtual experiences this week</p>
        </div>

        <div class="trending-tours">
            <?php foreach ($trending_tours as $tour): ?>
            <div class="trending-card">
                <div class="tour-image">
                    <?php if ($tour['thumbnail']): ?>
                    <img src="<?php echo BASE_URL; ?>assets/images/uploads/<?= htmlspecialchars($tour['thumbnail']) ?>"
                        alt="<?= htmlspecialchars($tour['title']) ?>" loading="lazy">
                    <?php else: ?>
                    <div class="tour-image-placeholder">
                        <i class="fas fa-image"></i>
                    </div>
                    <?php endif; ?>
                    <div class="tour-badges">
                        <span class="badge badge-primary">Trending</span>
                        <span class="badge badge-secondary">
                            <?= $tour['scene_count'] ?> scenes
                        </span>
                    </div>
                    <span class="trending-badge">
                        <i class="fas fa-fire me-1"></i> Hot
                    </span>
                    <div class="tour-overlay">
                        <a href="<?php echo BASE_URL; ?>vr/tour.php?id=<?= $tour['id'] ?>" class="btn btn-primary">
                            <i class="fas fa-play"></i> Start Tour
                        </a>
                    </div>
                </div>
                <div class="tour-content">
                    <h3 class="tour-title">
                        <?= htmlspecialchars($tour['title']) ?>
                    </h3>
                    <p class="tour-description">
                        <?= htmlspecialchars(substr($tour['description'], 0, 100)) ?>...
                    </p>
                    <div class="tour-meta">
                        <div class="tour-rating">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                            <i class="fas fa-star <?= $i <= round($tour['avg_rating']) ? 'active' : '' ?>"></i>
                            <?php endfor; ?>
                            <span>(
                                <?= $tour['avg_rating'] ? round($tour['avg_rating'], 1) : 'No ratings' ?>)
                            </span>
                        </div>
                        <div class="tour-category">
                            <i class="fas fa-tag"></i>
                            <?= htmlspecialchars($tour['category_name']) ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Featured Tours -->
<section class="featured-section">
    <div class="container">
        <div class="section-header">
            <h2>Featured Tours</h2>
            <p>Discover our most popular virtual experiences</p>
        </div>

        <div class="tours-grid">
            <?php if (empty($featured_tours)): ?>
            <div class="empty-state">
                <i class="fas fa-compass"></i>
                <h3>No Featured Tours Yet</h3>
                <p>Check back soon for amazing virtual tours!</p>
            </div>
            <?php else: ?>
            <?php foreach ($featured_tours as $tour): ?>
            <div class="tour-card">
                <div class="tour-image">
                    <?php if ($tour['thumbnail']): ?>
                    <img src="<?php echo BASE_URL; ?>assets/images/uploads/<?= htmlspecialchars($tour['thumbnail']) ?>"
                        alt="<?= htmlspecialchars($tour['title']) ?>" loading="lazy">
                    <?php else: ?>
                    <div class="tour-image-placeholder">
                        <i class="fas fa-image"></i>
                    </div>
                    <?php endif; ?>
                    <div class="tour-badges">
                        <span class="badge badge-primary">Featured</span>
                        <span class="badge badge-secondary">
                            <?= $tour['scene_count'] ?> scenes
                        </span>
                    </div>
                    <div class="tour-overlay">
                        <a href="<?php echo BASE_URL; ?>vr/tour.php?id=<?= $tour['id'] ?>" class="btn btn-primary">
                            <i class="fas fa-play"></i> Start Tour
                        </a>
                    </div>
                </div>
                <div class="tour-content">
                    <h3 class="tour-title">
                        <?= htmlspecialchars($tour['title']) ?>
                    </h3>
                    <p class="tour-description">
                        <?= htmlspecialchars(substr($tour['description'], 0, 100)) ?>...
                    </p>
                    <div class="tour-meta">
                        <div class="tour-rating">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                            <i class="fas fa-star <?= $i <= round($tour['avg_rating']) ? 'active' : '' ?>"></i>
                            <?php endfor; ?>
                            <span>(
                                <?= $tour['avg_rating'] ? round($tour['avg_rating'], 1) : 'No ratings' ?>)
                            </span>
                        </div>
                        <div class="tour-category">
                            <i class="fas fa-tag"></i>
                            <?= htmlspecialchars($tour['category_name']) ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="section-footer">
            <a href="<?php echo BASE_URL; ?>tours.php" class="btn btn-outline-primary">
                <i class="fas fa-eye me-2"></i>View All Tours
            </a>
        </div>
    </div>
</section>

<!-- Categories Section -->
<section class="categories-section">
    <div class="container">
        <div class="section-header">
            <h2>Explore Categories</h2>
            <p>Find tours by your interests</p>
        </div>

        <div class="categories-grid">
            <?php if (empty($categories)): ?>
            <div class="empty-state">
                <i class="fas fa-tags"></i>
                <h3>No Categories Yet</h3>
                <p>Categories will be added soon!</p>
            </div>
            <?php else: ?>
            <?php 
                // Define category colors and icons
                $category_styles = [
                    'Historical' => ['color' => '#8B4513', 'icon' => 'fa-landmark'],
                    'Nature' => ['color' => '#228B22', 'icon' => 'fa-mountain'],
                    'Cultural' => ['color' => '#DAA520', 'icon' => 'fa-globe-americas'],
                    'Religious' => ['color' => '#9370DB', 'icon' => 'fa-place-of-worship'],
                    'Museum' => ['color' => '#4169E1', 'icon' => 'fa-university'],
                    'Landmark' => ['color' => '#DC143C', 'icon' => 'fa-monument'],
                    'General' => ['color' => '#6366f1', 'icon' => 'fa-globe']
                ];
                
                foreach ($categories as $category): 
                    $category_name = $category['name'];
                    $style = $category_styles[$category_name] ?? $category_styles['General'];
                ?>
            <a href="<?php echo BASE_URL; ?>tours.php?category=<?= urlencode($category_name) ?>" class="category-card" style="opacity: 1;">
                <div class="category-icon">
                    <i class="fas <?= $style['icon'] ?>" style="color: <?= $style['color'] ?>;"></i>
                </div>
                <h3 class="category-title">
                    <?= htmlspecialchars($category_name) ?>
                </h3>
                <div class="category-count">
                    <?= $category['tour_count'] ?> tour
                    <?= $category['tour_count'] != 1 ? 's' : '' ?>
                </div>
            </a>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

<style>
    /* Categories Grid Styles */
    .categories-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 24px;
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 2rem;
    }

    .category-card {
        background: white;
        border-radius: 16px;
        padding: 30px 25px;
        text-align: center;
        text-decoration: none;
        color: #334155;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
        display: flex;
        flex-direction: column;
        align-items: center;
        border: 1px solid #e2e8f0;
        opacity: 1 !important; /* Force visibility */
        visibility: visible !important; /* Force visibility */
    }

    .category-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        text-decoration: none;
        color: #334155;
    }

    .category-icon {
        width: 70px;
        height: 70px;
        border-radius: 50%;
        background: #f1f5f9;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 20px;
        transition: all 0.3s ease;
    }

    .category-card:hover .category-icon {
        background: #6366f1;
        transform: scale(1.1);
    }

    .category-card:hover .category-icon i {
        color: white !important;
    }

    .category-icon i {
        font-size: 2rem;
        transition: all 0.3s ease;
    }

    .category-title {
        font-size: 1.4rem;
        font-weight: 700;
        margin-bottom: 12px;
        color: #1e293b;
    }

    .category-count {
        font-size: 0.95rem;
        color: #64748b;
        background: #f8fafc;
        padding: 6px 16px;
        border-radius: 20px;
        font-weight: 500;
    }

    .category-card:hover .category-count {
        background: #6366f1;
        color: white;
    }
</style>
</section>



<!-- Recent Tours -->
<section class="recent-section">
    <div class="container">
        <div class="section-header">
            <h2>Recently Added</h2>
            <p>Explore our latest virtual tour additions</p>
        </div>

        <div class="tours-grid">
            <?php if (empty($recent_tours)): ?>
            <div class="empty-state">
                <i class="fas fa-plus-circle"></i>
                <h3>No Tours Available</h3>
                <p>Be the first to create a tour!</p>
            </div>
            <?php else: ?>
            <?php foreach ($recent_tours as $tour): ?>
            <div class="tour-card">
                <div class="tour-image">
                    <?php if ($tour['thumbnail']): ?>
                    <img src="<?php echo BASE_URL; ?>assets/images/uploads/<?= htmlspecialchars($tour['thumbnail']) ?>"
                        alt="<?= htmlspecialchars($tour['title']) ?>" loading="lazy">
                    <?php else: ?>
                    <div class="tour-image-placeholder">
                        <i class="fas fa-image"></i>
                    </div>
                    <?php endif; ?>
                    <div class="tour-badges">
                        <span class="badge badge-secondary">
                            <?= $tour['scene_count'] ?> scenes
                        </span>
                        <span class="badge badge-success">New</span>
                    </div>
                    <div class="tour-overlay">
                        <a href="<?php echo BASE_URL; ?>vr/tour.php?id=<?= $tour['id'] ?>" class="btn btn-primary">
                            <i class="fas fa-play"></i> Start Tour
                        </a>
                    </div>
                </div>
                <div class="tour-content">
                    <h3 class="tour-title">
                        <?= htmlspecialchars($tour['title']) ?>
                    </h3>
                    <p class="tour-description">
                        <?= htmlspecialchars(substr($tour['description'], 0, 100)) ?>...
                    </p>
                    <div class="tour-meta">
                        <span class="tour-date">
                            <i class="fas fa-clock"></i>
                            <?= time_ago($tour['created_at']) ?>
                        </span>
                        <div class="tour-category">
                            <i class="fas fa-tag"></i>
                            <?= htmlspecialchars($tour['category_name']) ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="features-section">
    <div class="container">
        <div class="section-header">
            <h2>Why Choose VR Tours?</h2>
            <p>Experience the future of virtual exploration</p>
        </div>

        <div class="features-grid">
            <div class="feature-item">
                <div class="feature-icon">
                    <i class="fas fa-360-degrees"></i>
                </div>
                <h3>360° Immersion</h3>
                <p>Complete panoramic views with seamless navigation between scenes. Feel like you're really there with
                    our high-resolution 360° imagery.</p>
            </div>
            <div class="feature-item">
                <div class="feature-icon">
                    <i class="fas fa-vr-cardboard"></i>
                </div>
                <h3>VR Ready</h3>
                <p>Fully compatible with VR headsets for an immersive experience. Supports Oculus, HTC Vive, and Google
                    Cardboard.</p>
            </div>
            <div class="feature-item">
                <div class="feature-icon">
                    <i class="fas fa-mobile-alt"></i>
                </div>
                <h3>Mobile Friendly</h3>
                <p>Optimized for all devices - desktop, tablet, and mobile. Gyroscope support for mobile devices
                    enhances the experience.</p>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="cta-section">
    <div class="container">
        <div class="cta-content">
            <h2>Ready to Explore?</h2>
            <p>Join thousands of users experiencing the world through virtual reality. Start your journey today.</p>
            <div class="cta-actions">
                <?php if (!isUserLoggedIn()): ?>
                <a href="<?php echo BASE_URL; ?>register.php" class="btn btn-primary btn-lg">Create Account</a>
                <a href="<?php echo BASE_URL; ?>tours.php" class="btn btn-outline-light btn-lg">Browse Tours</a>
                <?php else: ?>
                <a href="<?php echo BASE_URL; ?>tours.php" class="btn btn-primary btn-lg">Explore Tours</a>
                <a href="<?php echo BASE_URL; ?>vr/tour.php?id=2" class="btn btn-outline-light btn-lg">Try Demo</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // 360° Image Interaction
        const vrScene = document.getElementById('vr-scene');
        let isDragging = false;
        let startX, startY;
        let currentX = 0, currentY = 0;
        let sensitivity = 0.5;

        if (vrScene) {
            // Mouse events for desktop
            vrScene.addEventListener('mousedown', function (e) {
                isDragging = true;
                startX = e.clientX;
                startY = e.clientY;
                vrScene.style.cursor = 'grabbing';
            });

            document.addEventListener('mousemove', function (e) {
                if (!isDragging) return;

                const dx = (e.clientX - startX) * sensitivity;
                const dy = (e.clientY - startY) * sensitivity;

                currentX += dx;
                currentY += dy;

                // Limit vertical movement for more natural experience
                currentY = Math.max(Math.min(currentY, 30), -30);

                vrScene.style.transform = `rotateY(${currentX}deg) rotateX(${currentY}deg)`;

                startX = e.clientX;
                startY = e.clientY;
            });

            document.addEventListener('mouseup', function () {
                isDragging = false;
                vrScene.style.cursor = 'grab';
            });

            // Touch events for mobile
            vrScene.addEventListener('touchstart', function (e) {
                isDragging = true;
                startX = e.touches[0].clientX;
                startY = e.touches[0].clientY;
                e.preventDefault();
            });

            document.addEventListener('touchmove', function (e) {
                if (!isDragging) return;

                const dx = (e.touches[0].clientX - startX) * sensitivity;
                const dy = (e.touches[0].clientY - startY) * sensitivity;

                currentX += dx;
                currentY += dy;

                // Limit vertical movement for more natural experience
                currentY = Math.max(Math.min(currentY, 30), -30);

                vrScene.style.transform = `rotateY(${currentX}deg) rotateX(${currentY}deg)`;

                startX = e.touches[0].clientX;
                startY = e.touches[0].clientY;

                e.preventDefault();
            });

            document.addEventListener('touchend', function () {
                isDragging = false;
            });

            // VR Controls
            const zoomInBtn = document.getElementById('vr-zoom-in');
            const zoomOutBtn = document.getElementById('vr-zoom-out');
            const fullscreenBtn = document.getElementById('vr-fullscreen');
            let zoomLevel = 1;

            if (zoomInBtn) {
                zoomInBtn.addEventListener('click', function () {
                    if (zoomLevel < 2) {
                        zoomLevel += 0.1;
                        vrScene.style.transform = `rotateY(${currentX}deg) rotateX(${currentY}deg) scale(${zoomLevel})`;
                    }
                });
            }

            if (zoomOutBtn) {
                zoomOutBtn.addEventListener('click', function () {
                    if (zoomLevel > 0.5) {
                        zoomLevel -= 0.1;
                        vrScene.style.transform = `rotateY(${currentX}deg) rotateX(${currentY}deg) scale(${zoomLevel})`;
                    }
                });
            }

            if (fullscreenBtn) {
                fullscreenBtn.addEventListener('click', function () {
                    const container = vrScene.closest('.vr-preview-container');
                    if (!document.fullscreenElement) {
                        if (container.requestFullscreen) {
                            container.requestFullscreen();
                        } else if (container.webkitRequestFullscreen) {
                            container.webkitRequestFullscreen();
                        } else if (container.msRequestFullscreen) {
                            container.msRequestFullscreen();
                        }
                    } else {
                        if (document.exitFullscreen) {
                            document.exitFullscreen();
                        } else if (document.webkitExitFullscreen) {
                            document.webkitExitFullscreen();
                        } else if (document.msExitFullscreen) {
                            document.msExitFullscreen();
                        }
                    }
                });
            }

            // Auto-rotation when not interacting
            let autoRotateInterval;

            function startAutoRotation() {
                autoRotateInterval = setInterval(() => {
                    if (!isDragging) {
                        currentX += 0.1;
                        vrScene.style.transform = `rotateY(${currentX}deg) rotateX(${currentY}deg) scale(${zoomLevel})`;
                    }
                }, 50);
            }

            function stopAutoRotation() {
                clearInterval(autoRotateInterval);
            }

            // Start auto-rotation after 5 seconds of inactivity
            let inactivityTimer;

            function resetInactivityTimer() {
                clearTimeout(inactivityTimer);
                stopAutoRotation();
                inactivityTimer = setTimeout(() => {
                    startAutoRotation();
                }, 5000);
            }

            // Set up event listeners for user activity
            ['mousedown', 'mousemove', 'touchstart', 'touchmove', 'keydown'].forEach(event => {
                document.addEventListener(event, resetInactivityTimer, { passive: true });
            });

            // Initialize
            resetInactivityTimer();
        }

        // Enhanced scroll animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-in');

                    // Special handling for stats section
                    if (entry.target.classList.contains('stats-section')) {
                        animateCounters();
                    }

                    // Stagger animation for tour cards
                    if (entry.target.classList.contains('tours-grid')) {
                        const cards = entry.target.querySelectorAll('.tour-card');
                        cards.forEach((card, index) => {
                            setTimeout(() => {
                                card.style.opacity = '1';
                                card.style.transform = 'translateY(0)';
                            }, index * 150);
                        });
                    }
                }
            });
        }, observerOptions);

        // Observe all animatable elements
        document.querySelectorAll('.stats-section, .featured-section, .recent-section, .features-section, .tours-grid, .categories-grid, .trending-tours').forEach(el => {
            observer.observe(el);
        });

        // Initialize tour cards with stagger effect
        document.querySelectorAll('.tour-card').forEach((card, index) => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(50px)';
            card.style.transition = 'all 0.6s cubic-bezier(0.4, 0, 0.2, 1)';
        });

        // Initialize category cards with stagger effect
        document.querySelectorAll('.category-card').forEach((card, index) => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(30px)';
            card.style.transition = 'all 0.6s cubic-bezier(0.4, 0, 0.2, 1)';
        });

        // Enhanced counter animation
        function animateCounters() {
            const counters = [
                { id: 'stat-tours', target: <?= $stats['total_tours'] ?> },
                { id: 'stat-scenes', target: <?= $stats['total_scenes'] ?> },
                { id: 'stat-users', target: <?= $stats['total_users'] ?> },
                { id: 'stat-favorites', target: <?= $stats['total_favorites'] ?> }
            ];

            counters.forEach(counter => {
                const element = document.getElementById(counter.id);
                if (!element) return;

                let current = 0;
                const increment = counter.target / 60;
                const duration = 2000;
                const stepTime = duration / 60;

                const timer = setInterval(() => {
                    current += increment;
                    if (current >= counter.target) {
                        element.textContent = counter.target.toLocaleString();
                        clearInterval(timer);
                    } else {
                        element.textContent = Math.floor(current).toLocaleString();
                    }
                }, stepTime);
            });
        }

        // Enhanced hover effects for tour cards
        document.querySelectorAll('.tour-card').forEach(card => {
            card.addEventListener('mouseenter', function () {
                this.style.zIndex = '10';
            });

            card.addEventListener('mouseleave', function () {
                this.style.zIndex = '1';
            });
        });

        // Parallax effect for hero section
        window.addEventListener('scroll', () => {
            const scrolled = window.pageYOffset;
            const parallax = document.querySelector('.hero');
            if (parallax) {
                const speed = scrolled * 0.5;
                parallax.style.transform = `translateY(${speed}px)`;
            }
        });

        // Intersection observer for fade-in animations
        const fadeElements = document.querySelectorAll('.section-header, .stat-item, .feature-item');
        fadeElements.forEach(el => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(30px)';
            el.style.transition = 'all 0.6s cubic-bezier(0.4, 0, 0.2, 1)';
        });

        const fadeObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, { threshold: 0.1 });

        fadeElements.forEach(el => fadeObserver.observe(el));

        // Enhanced button interactions
        document.querySelectorAll('.btn').forEach(btn => {
            btn.addEventListener('mouseenter', function () {
                this.style.transform = 'translateY(-2px) scale(1.02)';
            });

            btn.addEventListener('mouseleave', function () {
                this.style.transform = 'translateY(0) scale(1)';
            });

            btn.addEventListener('mousedown', function () {
                this.style.transform = 'translateY(1px) scale(0.98)';
            });

            btn.addEventListener('mouseup', function () {
                this.style.transform = 'translateY(-2px) scale(1.02)';
            });
        });

        // Loading performance optimization
        const images = document.querySelectorAll('img[data-src]');
        const imageObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.classList.remove('lazy');
                    imageObserver.unobserve(img);
                }
            });
        });

        images.forEach(img => imageObserver.observe(img));

        // Add smooth reveal animation for sections
        const sections = document.querySelectorAll('section');
        sections.forEach(section => {
            section.style.opacity = '0';
            section.style.transform = 'translateY(50px)';
            section.style.transition = 'all 0.8s cubic-bezier(0.4, 0, 0.2, 1)';
        });

        const sectionObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, { threshold: 0.1, rootMargin: '0px 0px -100px 0px' });

        sections.forEach(section => sectionObserver.observe(section));
    });

    // Add additional CSS animations
    const additionalStyles = `
    <style>
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(50px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes slideInLeft {
        from {
            opacity: 0;
            transform: translateX(-50px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    @keyframes slideInRight {
        from {
            opacity: 0;
            transform: translateX(50px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    .animate-in {
        animation: fadeInUp 0.8s cubic-bezier(0.4, 0, 0.2, 1) forwards;
    }

    .slide-left {
        animation: slideInLeft 0.8s cubic-bezier(0.4, 0, 0.2, 1) forwards;
    }

    .slide-right {
        animation: slideInRight 0.8s cubic-bezier(0.4, 0, 0.2, 1) forwards;
    }

    .tour-card:nth-child(even) {
        animation-delay: 0.2s;
    }

    .stat-item:nth-child(2) {
        animation-delay: 0.1s;
    }

    .stat-item:nth-child(3) {
        animation-delay: 0.2s;
    }

    .stat-item:nth-child(4) {
        animation-delay: 0.3s;
    }

    .feature-item:nth-child(2) {
        animation-delay: 0.2s;
    }

    .feature-item:nth-child(3) {
        animation-delay: 0.4s;
    }

    .category-card:nth-child(2) {
        animation-delay: 0.1s;
    }

    .category-card:nth-child(3) {
        animation-delay: 0.2s;
    }

    .category-card:nth-child(4) {
        animation-delay: 0.3s;
    }

    .category-card:nth-child(5) {
        animation-delay: 0.4s;
    }

    .category-card:nth-child(6) {
        animation-delay: 0.5s;
    }
    </style>
`;

    document.head.insertAdjacentHTML('beforeend', additionalStyles);
</script>

<?php include 'includes/user-footer.php'; ?>