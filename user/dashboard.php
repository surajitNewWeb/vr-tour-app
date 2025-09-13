<?php
// user/dashboard.php
require_once '../includes/config.php';
require_once '../includes/user-auth.php';
require_once '../includes/database.php';

if (!isUserLoggedIn()) {
    header('Location: ../login.php');
    exit;
}

$user_data = getUserData();

// Get user statistics
$stmt = $pdo->prepare("
    SELECT 
        (SELECT COUNT(*) FROM favorites WHERE user_id = :user_id) as favorite_count,
        (SELECT COUNT(*) FROM progress WHERE user_id = :user_id AND completed = 1) as completed_tours,
        (SELECT COUNT(*) FROM progress WHERE user_id = :user_id AND completed = 0) as in_progress_tours,
        (SELECT COUNT(*) FROM reviews WHERE user_id = :user_id) as reviews_count
");
$stmt->execute([':user_id' => $_SESSION['user_id']]);
$user_stats = $stmt->fetch();

// Get recent activity
$stmt = $pdo->prepare("
    SELECT p.*, t.title, t.thumbnail 
    FROM progress p 
    JOIN tours t ON p.tour_id = t.id 
    WHERE p.user_id = :user_id 
    ORDER BY p.updated_at DESC 
    LIMIT 5
");
$stmt->execute([':user_id' => $_SESSION['user_id']]);
$recent_activity = $stmt->fetchAll();

$page_title = "Dashboard - " . $user_data['username'];
include '../includes/user-header.php';
?>

<style>
/* =============================================
   DESIGN SYSTEM VARIABLES
   ============================================= */
:root {
    /* Grid System */
    --grid-gap: 1.5rem;
    --grid-gap-lg: 2rem;
    --grid-gap-sm: 1rem;
    --max-width: 1400px;
    
    /* Color System */
    --color-primary: #6366f1;
    --color-primary-light: #a5b4fc;
    --color-primary-dark: #4338ca;
    --color-success: #10b981;
    --color-warning: #f59e0b;
    --color-danger: #ef4444;
    --color-info: #06b6d4;
    
    /* Background System */
    --bg-primary: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    --bg-success: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    --bg-warning: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
    --bg-info: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
    --bg-glass: rgba(255, 255, 255, 0.1);
    --bg-glass-strong: rgba(255, 255, 255, 0.95);
    
    /* Shadow System */
    --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.12), 0 1px 2px rgba(0, 0, 0, 0.24);
    --shadow-md: 0 4px 6px rgba(0, 0, 0, 0.12), 0 2px 4px rgba(0, 0, 0, 0.08);
    --shadow-lg: 0 10px 15px rgba(0, 0, 0, 0.12), 0 4px 6px rgba(0, 0, 0, 0.06);
    --shadow-xl: 0 20px 25px rgba(0, 0, 0, 0.15), 0 10px 10px rgba(0, 0, 0, 0.04);
    
    /* Border Radius System */
    --radius-sm: 8px;
    --radius-md: 12px;
    --radius-lg: 16px;
    --radius-xl: 20px;
    
    /* Typography System */
    --font-family: 'Inter', system-ui, -apple-system, sans-serif;
    --font-size-xs: 0.75rem;
    --font-size-sm: 0.875rem;
    --font-size-base: 1rem;
    --font-size-lg: 1.125rem;
    --font-size-xl: 1.25rem;
    --font-size-2xl: 1.5rem;
    --font-size-3xl: 1.875rem;
    --font-size-4xl: 2.25rem;
    
    /* Spacing System */
    --space-1: 0.25rem;
    --space-2: 0.5rem;
    --space-3: 0.75rem;
    --space-4: 1rem;
    --space-5: 1.25rem;
    --space-6: 1.5rem;
    --space-8: 2rem;
    --space-10: 2.5rem;
    --space-12: 3rem;
    --space-16: 4rem;
    
    /* Animation System */
    --duration-fast: 0.15s;
    --duration-normal: 0.3s;
    --duration-slow: 0.5s;
    --easing: cubic-bezier(0.4, 0, 0.2, 1);
    --easing-bounce: cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

/* =============================================
   BASE STYLES & RESET
   ============================================= */
* {
    box-sizing: border-box;
}

body {
    margin: 0;
    padding: 0;
    font-family: var(--font-family);
    background: var(--bg-primary);
    min-height: 100vh;
    line-height: 1.6;
    color: #374151;
    overflow-x: hidden;
}

/* Background Pattern Overlay */
body::before {
    content: '';
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: 
        radial-gradient(circle at 25% 25%, rgba(99, 102, 241, 0.15) 0%, transparent 50%),
        radial-gradient(circle at 75% 75%, rgba(236, 72, 153, 0.15) 0%, transparent 50%),
        radial-gradient(circle at 50% 50%, rgba(59, 130, 246, 0.1) 0%, transparent 50%);
    pointer-events: none;
    z-index: 0;
}

/* =============================================
   GRID LAYOUT SYSTEM
   ============================================= */
.dashboard {
    position: relative;
    z-index: 1;
    max-width: var(--max-width);
    margin: 0 auto;
    padding: var(--space-8) var(--space-4);
    min-height: 100vh;
}

.dashboard-grid {
    display: grid;
    grid-template-columns: 1fr;
    grid-template-rows: auto auto auto;
    gap: var(--grid-gap-lg);
    grid-template-areas:
        "header"
        "stats"
        "activity";
}

/* Header Grid Area */
.dashboard-header {
    grid-area: header;
    text-align: center;
    padding: var(--space-8) var(--space-4);
}

/* Stats Grid Area */
.dashboard-stats {
    grid-area: stats;
}

/* Activity Grid Area */
.dashboard-activity {
    grid-area: activity;
}

/* =============================================
   HEADER SECTION
   ============================================= */
.header-content {
    max-width: 800px;
    margin: 0 auto;
}

.header-title {
    font-size: var(--font-size-4xl);
    font-weight: 800;
    background: linear-gradient(135deg, #ffffff 0%, rgba(255,255,255,0.8) 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin-bottom: var(--space-4);
    letter-spacing: -0.025em;
    text-shadow: 0 4px 20px rgba(0,0,0,0.15);
}

.header-subtitle {
    font-size: var(--font-size-lg);
    color: rgba(255, 255, 255, 0.9);
    font-weight: 400;
    text-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin: 0;
}

/* =============================================
   STATISTICS GRID SYSTEM
   ============================================= */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: var(--grid-gap);
}

.stat-card {
    background: var(--bg-glass);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: var(--radius-xl);
    padding: var(--space-8);
    text-align: center;
    position: relative;
    overflow: hidden;
    transition: all var(--duration-normal) var(--easing);
    box-shadow: var(--shadow-lg);
    height: 100%;
}

.stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: var(--bg-primary);
    transform: scaleX(0);
    transition: transform var(--duration-normal) var(--easing);
}

.stat-card:hover {
    transform: translateY(-8px) scale(1.02);
    box-shadow: var(--shadow-xl);
}

.stat-card:hover::before {
    transform: scaleX(1);
}

/* Stat Card Variants */
.stat-card--favorites::before { background: var(--bg-primary); }
.stat-card--completed::before { background: var(--bg-success); }
.stat-card--progress::before { background: var(--bg-warning); }
.stat-card--reviews::before { background: var(--bg-info); }

.stat-icon {
    width: 64px;
    height: 64px;
    margin: 0 auto var(--space-4);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: var(--font-size-xl);
    color: #ffffff;
    box-shadow: var(--shadow-md);
}

.stat-card--favorites .stat-icon { background: var(--bg-primary); }
.stat-card--completed .stat-icon { background: var(--bg-success); }
.stat-card--progress .stat-icon { background: var(--bg-warning); }
.stat-card--reviews .stat-icon { background: var(--bg-info); }

.stat-label {
    font-size: var(--font-size-sm);
    font-weight: 600;
    color: rgba(255, 255, 255, 0.8);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: var(--space-2);
}

.stat-value {
    font-size: var(--font-size-3xl);
    font-weight: 800;
    color: #ffffff;
    margin: 0;
    text-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

/* =============================================
   ACTIVITY SECTION GRID
   ============================================= */
.activity-container {
    background: var(--bg-glass-strong);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border-radius: var(--radius-xl);
    padding: var(--space-8);
    box-shadow: var(--shadow-xl);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.activity-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: var(--space-8);
    padding-bottom: var(--space-4);
    border-bottom: 2px solid rgba(99, 102, 241, 0.1);
}

.activity-title {
    font-size: var(--font-size-2xl);
    font-weight: 700;
    color: #1f2937;
    margin: 0;
    display: flex;
    align-items: center;
    gap: var(--space-3);
}

.activity-title i {
    color: var(--color-primary);
}

.activity-grid {
    display: grid;
    gap: var(--grid-gap);
}

.activity-item {
    display: grid;
    grid-template-columns: auto 1fr auto;
    gap: var(--space-6);
    align-items: center;
    background: #ffffff;
    border-radius: var(--radius-lg);
    padding: var(--space-6);
    box-shadow: var(--shadow-sm);
    border: 1px solid rgba(0, 0, 0, 0.05);
    transition: all var(--duration-normal) var(--easing);
    position: relative;
    overflow: hidden;
}

.activity-item::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    width: 4px;
    height: 100%;
    background: var(--bg-primary);
    transform: scaleY(0);
    transition: transform var(--duration-normal) var(--easing);
}

.activity-item:hover {
    transform: translateX(8px);
    box-shadow: var(--shadow-lg);
}

.activity-item:hover::before {
    transform: scaleY(1);
}

.activity-thumbnail {
    width: 80px;
    height: 80px;
    border-radius: var(--radius-md);
    object-fit: cover;
    box-shadow: var(--shadow-md);
    transition: transform var(--duration-normal) var(--easing);
}

.activity-item:hover .activity-thumbnail {
    transform: scale(1.05);
}

.activity-content {
    display: grid;
    gap: var(--space-2);
}

.activity-tour-title {
    font-size: var(--font-size-lg);
    font-weight: 600;
    color: #1f2937;
    margin: 0;
    line-height: 1.4;
}

.activity-meta {
    display: flex;
    align-items: center;
    gap: var(--space-4);
    font-size: var(--font-size-sm);
    color: #6b7280;
}

.activity-badge {
    background: var(--bg-success);
    color: #ffffff;
    padding: var(--space-1) var(--space-3);
    border-radius: var(--radius-md);
    font-size: var(--font-size-xs);
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    box-shadow: 0 2px 10px rgba(16, 185, 129, 0.3);
}

.activity-action {
    display: flex;
    align-items: center;
}

.btn-continue {
    background: var(--bg-primary);
    color: #ffffff;
    border: none;
    border-radius: var(--radius-md);
    padding: var(--space-3) var(--space-6);
    font-size: var(--font-size-sm);
    font-weight: 600;
    text-decoration: none;
    transition: all var(--duration-normal) var(--easing-bounce);
    box-shadow: 0 4px 15px rgba(99, 102, 241, 0.4);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.btn-continue:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(99, 102, 241, 0.6);
    color: #ffffff;
    text-decoration: none;
}

/* =============================================
   EMPTY STATE
   ============================================= */
.empty-state {
    text-align: center;
    padding: var(--space-16) var(--space-8);
    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
    border-radius: var(--radius-xl);
    border: 2px dashed #cbd5e1;
}

.empty-icon {
    width: 96px;
    height: 96px;
    margin: 0 auto var(--space-6);
    background: var(--bg-primary);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #ffffff;
    font-size: var(--font-size-2xl);
    box-shadow: var(--shadow-lg);
}

.empty-title {
    font-size: var(--font-size-xl);
    font-weight: 700;
    color: #374151;
    margin-bottom: var(--space-4);
}

.empty-description {
    font-size: var(--font-size-base);
    color: #6b7280;
    margin-bottom: var(--space-8);
    max-width: 400px;
    margin-left: auto;
    margin-right: auto;
}

.btn-browse {
    background: var(--bg-primary);
    color: #ffffff;
    border: none;
    border-radius: var(--radius-md);
    padding: var(--space-4) var(--space-8);
    font-size: var(--font-size-base);
    font-weight: 600;
    text-decoration: none;
    transition: all var(--duration-normal) var(--easing-bounce);
    box-shadow: 0 4px 15px rgba(99, 102, 241, 0.4);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.btn-browse:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(99, 102, 241, 0.6);
    color: #ffffff;
    text-decoration: none;
}

/* =============================================
   RESPONSIVE GRID SYSTEM
   ============================================= */
@media (min-width: 768px) {
    .dashboard {
        padding: var(--space-12) var(--space-6);
    }
    
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .activity-item {
        grid-template-columns: auto 1fr auto;
    }
}

@media (min-width: 1024px) {
    .dashboard-grid {
        grid-template-areas:
            "header header"
            "stats stats"
            "activity activity";
    }
    
    .stats-grid {
        grid-template-columns: repeat(4, 1fr);
    }
}

@media (min-width: 1200px) {
    .dashboard {
        padding: var(--space-16) var(--space-8);
    }
    
    .dashboard-grid {
        gap: var(--space-12);
    }
}

/* Mobile Optimizations */
@media (max-width: 767px) {
    .dashboard {
        padding: var(--space-6) var(--space-3);
    }
    
    .header-title {
        font-size: var(--font-size-3xl);
    }
    
    .activity-item {
        grid-template-columns: 1fr;
        text-align: center;
        gap: var(--space-4);
    }
    
    .activity-content {
        order: 1;
    }
    
    .activity-action {
        order: 2;
        justify-content: center;
    }
    
    .activity-thumbnail {
        order: 0;
        justify-self: center;
        width: 60px;
        height: 60px;
    }
    
    .activity-header {
        flex-direction: column;
        gap: var(--space-4);
        text-align: center;
    }
}

/* =============================================
   ACCESSIBILITY & FOCUS STATES
   ============================================= */
.stat-card:focus,
.activity-item:focus,
.btn-continue:focus,
.btn-browse:focus {
    outline: 2px solid var(--color-primary);
    outline-offset: 2px;
}

@media (prefers-reduced-motion: reduce) {
    * {
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: 0.01ms !important;
    }
}

/* =============================================
   DARK MODE SUPPORT
   ============================================= */
@media (prefers-color-scheme: dark) {
    :root {
        --bg-glass-strong: rgba(0, 0, 0, 0.8);
    }
    
    .activity-container {
        color: #f9fafb;
    }
    
    .activity-item {
        background: rgba(255, 255, 255, 0.05);
        border-color: rgba(255, 255, 255, 0.1);
        color: #f9fafb;
    }
    
    .activity-tour-title {
        color: #f9fafb;
    }
    
    .activity-title {
        color: #f9fafb;
    }
}

/* =============================================
   LOADING STATES
   ============================================= */
.loading {
    opacity: 0.7;
    pointer-events: none;
    position: relative;
    overflow: hidden;
}

.loading::after {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
    animation: shimmer 1.5s infinite;
}

@keyframes shimmer {
    0% { left: -100%; }
    100% { left: 100%; }
}

/* =============================================
   ANIMATIONS
   ============================================= */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes slideInLeft {
    from {
        opacity: 0;
        transform: translateX(-30px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

@keyframes countUp {
    from {
        transform: translateY(10px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}
</style>

<div class="dashboard">
    <div class="dashboard-grid">
        <!-- Header Section -->
        <header class="dashboard-header">
            <div class="header-content">
                <h1 class="header-title">Welcome, <?php echo htmlspecialchars($user_data['username']); ?>!</h1>
                <p class="header-subtitle">Track your VR journey and discover new experiences</p>
            </div>
        </header>

        <!-- Statistics Section -->
        <section class="dashboard-stats">
            <div class="stats-grid">
                <div class="stat-card stat-card--favorites">
                    <div class="stat-icon">
                        <i class="fas fa-heart"></i>
                    </div>
                    <div class="stat-label">Favorites</div>
                    <div class="stat-value"><?php echo $user_stats['favorite_count']; ?></div>
                </div>

                <div class="stat-card stat-card--completed">
                    <div class="stat-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-label">Completed</div>
                    <div class="stat-value"><?php echo $user_stats['completed_tours']; ?></div>
                </div>

                <div class="stat-card stat-card--progress">
                    <div class="stat-icon">
                        <i class="fas fa-play-circle"></i>
                    </div>
                    <div class="stat-label">In Progress</div>
                    <div class="stat-value"><?php echo $user_stats['in_progress_tours']; ?></div>
                </div>

                <div class="stat-card stat-card--reviews">
                    <div class="stat-icon">
                        <i class="fas fa-star"></i>
                    </div>
                    <div class="stat-label">Reviews</div>
                    <div class="stat-value"><?php echo $user_stats['reviews_count']; ?></div>
                </div>
            </div>
        </section>

        <!-- Activity Section -->
        <section class="dashboard-activity">
            <div class="activity-container">
                <div class="activity-header">
                    <h2 class="activity-title">
                        <i class="fas fa-history"></i>
                        Recent Activity
                    </h2>
                </div>

                <?php if (!empty($recent_activity)): ?>
                    <div class="activity-grid">
                        <?php foreach ($recent_activity as $activity): ?>
                            <div class="activity-item">
                                <?php if ($activity['thumbnail']): ?>
                                    <img src="../assets/images/uploads/<?php echo $activity['thumbnail']; ?>" 
                                         class="activity-thumbnail" 
                                         alt="<?php echo htmlspecialchars($activity['title']); ?>">
                                <?php else: ?>
                                    <div class="activity-thumbnail" style="background: #f3f4f6; display: flex; align-items: center; justify-content: center;">
                                        <i class="fas fa-image" style="color: #9ca3af;"></i>
                                    </div>
                                <?php endif; ?>

                                <div class="activity-content">
                                    <h3 class="activity-tour-title"><?php echo htmlspecialchars($activity['title']); ?></h3>
                                    <div class="activity-meta">
                                        <span>
                                            <i class="fas fa-clock"></i>
                                            Last viewed: <?php echo time_ago($activity['updated_at']); ?>
                                        </span>
                                        <?php if ($activity['completed']): ?>
                                            <span class="activity-badge">
                                                <i class="fas fa-check"></i>
                                                Completed
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="activity-action">
                                    <a href="../vr/tour.php?id=<?php echo $activity['tour_id']; ?>" 
                                       class="btn-continue">
                                        <i class="fas fa-play"></i>
                                        Continue
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-icon">
                            <i class="fas fa-compass"></i>
                        </div>
                        <h3 class="empty-title">No Recent Activity</h3>
                        <p class="empty-description">Start exploring some tours to see your activity here!</p>
                        <a href="../tours.php" class="btn-browse">
                            <i class="fas fa-search"></i>
                            Browse Tours
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // =============================================
    // GRID LAYOUT ENHANCEMENTS
    // =============================================
    
    // Initialize grid animations
    function initializeGridAnimations() {
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const gridObserver = new IntersectionObserver((entries) => {
            entries.forEach((entry, index) => {
                if (entry.isIntersecting) {
                    setTimeout(() => {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }, index * 100);
                    gridObserver.unobserve(entry.target);
                }
            });
        }, observerOptions);

        // Animate stat cards
        document.querySelectorAll('.stat-card').forEach((card, index) => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(30px)';
            card.style.transition = `opacity 0.6s ease ${index * 0.1}s, transform 0.6s ease ${index * 0.1}s`;
            gridObserver.observe(card);
        });

        // Animate activity items
        document.querySelectorAll('.activity-item').forEach((item, index) => {
            item.style.opacity = '0';
            item.style.transform = 'translateX(-30px)';
            item.style.transition = `opacity 0.6s ease ${index * 0.15 + 0.3}s, transform 0.6s ease ${index * 0.15 + 0.3}s`;
            
            setTimeout(() => {
                item.style.opacity = '1';
                item.style.transform = 'translateX(0)';
            }, 800 + (index * 150));
        });
    }

    // =============================================
    // RESPONSIVE GRID ADJUSTMENTS
    // =============================================
    
    function handleResponsiveGrid() {
        const dashboard = document.querySelector('.dashboard');
        const statsGrid = document.querySelector('.stats-grid');
        const activityGrid = document.querySelector('.activity-grid');
        
        function adjustGrid() {
            const windowWidth = window.innerWidth;
            
            // Adjust grid gaps based on screen size
            if (windowWidth < 768) {
                dashboard.style.setProperty('--grid-gap', '1rem');
                dashboard.style.setProperty('--grid-gap-lg', '1.5rem');
            } else if (windowWidth < 1024) {
                dashboard.style.setProperty('--grid-gap', '1.25rem');
                dashboard.style.setProperty('--grid-gap-lg', '1.75rem');
            } else {
                dashboard.style.setProperty('--grid-gap', '1.5rem');
                dashboard.style.setProperty('--grid-gap-lg', '2rem');
            }
        }
        
        adjustGrid();
        window.addEventListener('resize', debounce(adjustGrid, 250));
    }

    // =============================================
    // INTERACTIVE GRID ENHANCEMENTS
    // =============================================
    
    function initializeInteractiveFeatures() {
        // Add ripple effects to interactive elements
        const interactiveElements = document.querySelectorAll('.stat-card, .btn-continue, .btn-browse');
        
        interactiveElements.forEach(element => {
            element.addEventListener('click', function(e) {
                const ripple = document.createElement('div');
                const rect = this.getBoundingClientRect();
                const size = Math.max(rect.width, rect.height);
                const x = e.clientX - rect.left - size / 2;
                const y = e.clientY - rect.top - size / 2;
                
                ripple.style.cssText = `
                    position: absolute;
                    border-radius: 50%;
                    background: rgba(255, 255, 255, 0.6);
                    width: ${size}px;
                    height: ${size}px;
                    left: ${x}px;
                    top: ${y}px;
                    transform: scale(0);
                    animation: ripple-effect 0.6s linear;
                    pointer-events: none;
                    z-index: 10;
                `;
                
                if (!this.style.position) this.style.position = 'relative';
                if (!this.style.overflow) this.style.overflow = 'hidden';
                
                this.appendChild(ripple);
                
                setTimeout(() => ripple.remove(), 600);
            });
        });

        // Add CSS for ripple animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes ripple-effect {
                to {
                    transform: scale(4);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(style);
    }

    // =============================================
    // STATISTICS COUNTER ANIMATION
    // =============================================
    
    function animateStatistics() {
        const statValues = document.querySelectorAll('.stat-value');
        
        statValues.forEach(stat => {
            const target = parseInt(stat.textContent) || 0;
            let current = 0;
            const increment = target / 60; // Animate over 60 frames
            const timer = setInterval(() => {
                current += increment;
                if (current >= target) {
                    stat.textContent = target;
                    clearInterval(timer);
                } else {
                    stat.textContent = Math.floor(current);
                }
            }, 16); // ~60fps
        });
    }

    // =============================================
    // GRID LAYOUT OPTIMIZATION
    // =============================================
    
    function optimizeGridLayout() {
        // Optimize grid performance using CSS containment
        const gridContainers = document.querySelectorAll('.stats-grid, .activity-grid, .dashboard-grid');
        
        gridContainers.forEach(container => {
            container.style.contain = 'layout style';
        });

        // Lazy load activity thumbnails
        const thumbnails = document.querySelectorAll('.activity-thumbnail');
        const imageObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    if (img.dataset.src) {
                        img.src = img.dataset.src;
                        img.classList.remove('lazy-loading');
                        imageObserver.unobserve(img);
                    }
                }
            });
        });

        thumbnails.forEach(img => {
            if (img.tagName === 'IMG' && img.src) {
                img.classList.add('lazy-loading');
                imageObserver.observe(img);
            }
        });
    }

    // =============================================
    // ACCESSIBILITY ENHANCEMENTS
    // =============================================
    
    function enhanceAccessibility() {
        // Add ARIA labels and roles
        const statsGrid = document.querySelector('.stats-grid');
        const activityGrid = document.querySelector('.activity-grid');
        
        if (statsGrid) {
            statsGrid.setAttribute('role', 'region');
            statsGrid.setAttribute('aria-label', 'User statistics');
        }
        
        if (activityGrid) {
            activityGrid.setAttribute('role', 'region');
            activityGrid.setAttribute('aria-label', 'Recent activity');
        }

        // Add keyboard navigation for grid items
        const focusableElements = document.querySelectorAll('.stat-card, .activity-item, .btn-continue, .btn-browse');
        
        focusableElements.forEach(element => {
            element.setAttribute('tabindex', '0');
        });

        // Add keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey || e.metaKey) {
                switch(e.key) {
                    case 'b':
                        e.preventDefault();
                        const browseBtn = document.querySelector('.btn-browse');
                        if (browseBtn) browseBtn.click();
                        break;
                    case 'h':
                        e.preventDefault();
                        window.location.href = '../index.php';
                        break;
                }
            }

            // Arrow key navigation for grid items
            if (['ArrowUp', 'ArrowDown', 'ArrowLeft', 'ArrowRight'].includes(e.key)) {
                const currentElement = document.activeElement;
                const gridItems = Array.from(document.querySelectorAll('.stat-card, .activity-item'));
                const currentIndex = gridItems.indexOf(currentElement);
                
                if (currentIndex !== -1) {
                    e.preventDefault();
                    let nextIndex = currentIndex;
                    
                    switch(e.key) {
                        case 'ArrowRight':
                        case 'ArrowDown':
                            nextIndex = Math.min(currentIndex + 1, gridItems.length - 1);
                            break;
                        case 'ArrowLeft':
                        case 'ArrowUp':
                            nextIndex = Math.max(currentIndex - 1, 0);
                            break;
                    }
                    
                    if (gridItems[nextIndex]) {
                        gridItems[nextIndex].focus();
                    }
                }
            }
        });
    }

    // =============================================
    // LOADING STATES MANAGEMENT
    // =============================================
    
    function handleLoadingStates() {
        const links = document.querySelectorAll('a[href]:not([href^="#"])');
        
        links.forEach(link => {
            link.addEventListener('click', function(e) {
                if (!this.href.includes('#')) {
                    this.classList.add('loading');
                    
                    const originalContent = this.innerHTML;
                    this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
                    
                    // Restore if navigation takes too long
                    setTimeout(() => {
                        this.classList.remove('loading');
                        this.innerHTML = originalContent;
                    }, 3000);
                }
            });
        });
    }

    // =============================================
    // PERFORMANCE MONITORING
    // =============================================
    
    function monitorPerformance() {
        // Monitor grid layout performance
        if ('performance' in window && 'measure' in performance) {
            performance.mark('dashboard-start');
            
            window.addEventListener('load', function() {
                performance.mark('dashboard-end');
                performance.measure('dashboard-load', 'dashboard-start', 'dashboard-end');
                
                const measure = performance.getEntriesByName('dashboard-load')[0];
                if (measure && measure.duration > 2000) {
                    console.warn('Dashboard loaded slowly:', measure.duration + 'ms');
                }
            });
        }

        // Monitor grid reflow performance
        let resizeTimer;
        window.addEventListener('resize', function() {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(() => {
                const start = performance.now();
                // Trigger reflow measurement
                document.querySelector('.dashboard-grid').offsetHeight;
                const duration = performance.now() - start;
                
                if (duration > 16) {
                    console.warn('Grid reflow took:', duration + 'ms');
                }
            }, 100);
        });
    }

    // =============================================
    // PROGRESSIVE ENHANCEMENT
    // =============================================
    
    function progressiveEnhancement() {
        // Check for CSS Grid support
        if (!CSS.supports('display', 'grid')) {
            // Fallback for older browsers
            const dashboard = document.querySelector('.dashboard-grid');
            dashboard.style.display = 'block';
            
            const sections = dashboard.querySelectorAll('> *');
            sections.forEach(section => {
                section.style.marginBottom = '2rem';
            });
            
            console.warn('CSS Grid not supported, using fallback layout');
        }

        // Check for backdrop-filter support
        if (!CSS.supports('backdrop-filter', 'blur(10px)')) {
            const glassElements = document.querySelectorAll('.stat-card, .activity-container');
            glassElements.forEach(element => {
                element.style.background = 'rgba(255, 255, 255, 0.95)';
            });
        }

        // Check for intersection observer support
        if (!('IntersectionObserver' in window)) {
            // Fallback: show all elements immediately
            const hiddenElements = document.querySelectorAll('[style*="opacity: 0"]');
            hiddenElements.forEach(element => {
                element.style.opacity = '1';
                element.style.transform = 'none';
            });
        }
    }

    // =============================================
    // THEME MANAGEMENT
    // =============================================
    
    function handleThemeChanges() {
        // Detect system theme changes
        const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
        
        function handleThemeChange(e) {
            document.body.classList.toggle('dark-theme', e.matches);
            
            // Update CSS custom properties for dark theme
            if (e.matches) {
                document.documentElement.style.setProperty('--bg-glass-strong', 'rgba(0, 0, 0, 0.8)');
            } else {
                document.documentElement.style.setProperty('--bg-glass-strong', 'rgba(255, 255, 255, 0.95)');
            }
        }
        
        mediaQuery.addListener(handleThemeChange);
        handleThemeChange(mediaQuery);
    }

    // =============================================
    // INITIALIZATION SEQUENCE
    // =============================================
    
    // Initialize all features
    try {
        progressiveEnhancement();
        initializeGridAnimations();
        handleResponsiveGrid();
        initializeInteractiveFeatures();
        optimizeGridLayout();
        enhanceAccessibility();
        handleLoadingStates();
        monitorPerformance();
        handleThemeChanges();
        
        // Delayed animations
        setTimeout(animateStatistics, 800);
        
        console.log('Dashboard grid system initialized successfully');
        
        // Dispatch ready event
        document.dispatchEvent(new CustomEvent('dashboardGridReady', {
            detail: {
                timestamp: Date.now(),
                features: {
                    gridLayout: true,
                    animations: true,
                    responsive: true,
                    accessibility: true,
                    performance: true
                }
            }
        }));
        
    } catch (error) {
        console.error('Error initializing dashboard:', error);
    }
});

// =============================================
// UTILITY FUNCTIONS
// =============================================

// Debounce function for performance optimization
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Throttle function for scroll/resize events
function throttle(func, limit) {
    let inThrottle;
    return function() {
        const args = arguments;
        const context = this;
        if (!inThrottle) {
            func.apply(context, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    }
}

// Check if element is in viewport
function isInViewport(element) {
    const rect = element.getBoundingClientRect();
    return (
        rect.top >= 0 &&
        rect.left >= 0 &&
        rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
        rect.right <= (window.innerWidth || document.documentElement.clientWidth)
    );
}

// Smooth scroll polyfill for older browsers
if (!Element.prototype.scrollIntoView) {
    Element.prototype.scrollIntoView = function() {
        const element = this;
        const rect = element.getBoundingClientRect();
        window.scrollTo({
            top: window.pageYOffset + rect.top,
            behavior: 'smooth'
        });
    };
}
</script>

<?php include '../includes/user-footer.php'; ?>