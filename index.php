<?php
// index.php
require_once 'includes/config.php';
require_once 'includes/user-auth.php';
require_once 'includes/database.php';

// Get featured tours using the correct Database class methods
$stmt = $pdo->prepare("
    SELECT t.*, 
           (SELECT COUNT(*) FROM scenes s WHERE s.tour_id = t.id) as scene_count,
           (SELECT AVG(rating) FROM reviews r WHERE r.tour_id = t.id AND r.approved = 1) as avg_rating
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
           (SELECT AVG(rating) FROM reviews r WHERE r.tour_id = t.id AND r.approved = 1) as avg_rating
    FROM tours t 
    WHERE t.published = 1
    ORDER BY t.created_at DESC 
    LIMIT 6
");
$stmt->execute();
$recent_tours = $stmt->fetchAll();

// Get tour statistics
$stmt = $pdo->prepare("
    SELECT 
        (SELECT COUNT(*) FROM tours WHERE published = 1) as total_tours,
        (SELECT COUNT(*) FROM scenes) as total_scenes,
        (SELECT COUNT(*) FROM users) as total_users,
        (SELECT COUNT(*) FROM favorites) as total_favorites
");
$stmt->execute();
$stats = $stmt->fetch();

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
    --card-shadow: 0 20px 40px rgba(0,0,0,0.1);
    --card-shadow-hover: 0 30px 60px rgba(0,0,0,0.15);
    --glow-primary: 0 0 30px rgba(99, 102, 241, 0.3);
    --glow-secondary: 0 0 30px rgba(236, 72, 153, 0.3);
}

/* Hero Section */
.hero {
    min-height: 100vh;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    position: relative;
    display: flex;
    align-items: center;
    overflow: hidden;
}

.hero::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: 
        radial-gradient(circle at 20% 80%, rgba(120, 119, 198, 0.3) 0%, transparent 50%),
        radial-gradient(circle at 80% 20%, rgba(255, 119, 198, 0.3) 0%, transparent 50%);
    animation: heroGlow 8s ease-in-out infinite alternate;
}

@keyframes heroGlow {
    0% { opacity: 0.5; }
    100% { opacity: 1; }
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
    font-size: 4rem;
    font-weight: 800;
    line-height: 1.1;
    margin-bottom: 2rem;
    background: linear-gradient(135deg, #ffffff, #f0f0f0);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    animation: titleFloat 6s ease-in-out infinite alternate;
}

@keyframes titleFloat {
    0% { transform: translateY(0px); }
    100% { transform: translateY(-10px); }
}

.hero-description {
    font-size: 1.25rem;
    line-height: 1.8;
    margin-bottom: 3rem;
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
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
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

/* VR Headset Visualization */
.hero-visual {
    display: flex;
    justify-content: center;
    align-items: center;
    position: relative;
}

.vr-headset {
    width: 300px;
    height: 180px;
    position: relative;
    animation: float 6s ease-in-out infinite;
}

@keyframes float {
    0%, 100% { transform: translateY(0px) rotateY(0deg); }
    50% { transform: translateY(-20px) rotateY(5deg); }
}

.vr-frame {
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #2d3748, #4a5568);
    border-radius: 20px;
    position: relative;
    box-shadow: 0 20px 40px rgba(0,0,0,0.3);
}

.vr-frame::before {
    content: '';
    position: absolute;
    top: 10px;
    left: 10px;
    right: 10px;
    bottom: 10px;
    background: linear-gradient(135deg, #667eea, #764ba2);
    border-radius: 15px;
    opacity: 0.8;
}

.vr-lens {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: radial-gradient(circle, #00d4ff, #0099cc);
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    box-shadow: inset 0 5px 20px rgba(0,0,0,0.3), 0 0 20px rgba(0, 212, 255, 0.5);
    animation: lensGlow 3s ease-in-out infinite alternate;
}

@keyframes lensGlow {
    0% { box-shadow: inset 0 5px 20px rgba(0,0,0,0.3), 0 0 20px rgba(0, 212, 255, 0.5); }
    100% { box-shadow: inset 0 5px 20px rgba(0,0,0,0.3), 0 0 30px rgba(0, 212, 255, 0.8); }
}

.vr-lens-left {
    left: 40px;
}

.vr-lens-right {
    right: 40px;
}

.floating-elements {
    position: absolute;
    width: 100%;
    height: 100%;
    pointer-events: none;
}

.floating-element {
    position: absolute;
    width: 60px;
    height: 60px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    color: white;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    animation: floatElements 8s ease-in-out infinite;
}

.element-1 {
    top: 20%;
    left: 10%;
    animation-delay: 0s;
}

.element-2 {
    top: 60%;
    right: 10%;
    animation-delay: 2s;
}

.element-3 {
    bottom: 20%;
    left: 20%;
    animation-delay: 4s;
}

@keyframes floatElements {
    0%, 100% { transform: translateY(0px) scale(1); }
    33% { transform: translateY(-20px) scale(1.1); }
    66% { transform: translateY(10px) scale(0.9); }
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
.recent-section {
    padding: 8rem 0;
    position: relative;
}

.featured-section {
    background: linear-gradient(135deg, #ffffff, #f8fafc);
}

.recent-section {
    background: linear-gradient(135deg, #f8fafc, #e2e8f0);
}

.section-header {
    text-align: center;
    margin-bottom: 4rem;
    max-width: 600px;
    margin-left: auto;
    margin-right: auto;
}

.section-header h2 {
    font-size: 3rem;
    font-weight: 800;
    margin-bottom: 1rem;
    background: linear-gradient(135deg, var(--gray-900), var(--gray-700));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.section-header p {
    font-size: 1.25rem;
    color: var(--gray-600);
    line-height: 1.8;
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
    0% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
    100% { transform: translateX(100%) translateY(100%) rotate(45deg); }
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
    0% { opacity: 0.5; }
    100% { opacity: 1; }
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
    
    .tours-grid {
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 2rem;
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
    
    .vr-headset {
        width: 250px;
        height: 150px;
    }
    
    .vr-lens {
        width: 60px;
        height: 60px;
    }
    
    .vr-lens-left {
        left: 30px;
    }
    
    .vr-lens-right {
        right: 30px;
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
    
    .floating-element {
        width: 40px;
        height: 40px;
        font-size: 16px;
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

<!-- Hero Section -->
<section class="hero">
    <div class="container">
        <div class="hero-content">
            <div class="hero-text">
                <h1 class="hero-title">Step Into Another World</h1>
                <p class="hero-description">Experience breathtaking 360° virtual tours of museums, landmarks, and hidden gems from around the globe.</p>
                <div class="hero-actions">
                    <a href="tours.php" class="btn btn-primary btn-lg">
                        <i class="fas fa-compass me-2"></i>Explore Tours
                    </a>
                    <?php if (!isUserLoggedIn()): ?>
                        <a href="register.php" class="btn btn-outline-light btn-lg">
                            <i class="fas fa-user-plus me-2"></i>Get Started
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="hero-visual">
                <div class="vr-headset">
                    <div class="vr-lens vr-lens-left"></div>
                    <div class="vr-lens vr-lens-right"></div>
                    <div class="vr-frame"></div>
                </div>
                <div class="floating-elements">
                    <div class="floating-element element-1">
                        <i class="fas fa-mountain"></i>
                    </div>
                    <div class="floating-element element-2">
                        <i class="fas fa-landmark"></i>
                    </div>
                    <div class="floating-element element-3">
                        <i class="fas fa-umbrella-beach"></i>
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
                <div class="stat-number" id="stat-tours"><?= $stats['total_tours'] ?></div>
                <div class="stat-label">VR Tours</div>
            </div>
            <div class="stat-item">
                <div class="stat-number" id="stat-scenes"><?= $stats['total_scenes'] ?></div>
                <div class="stat-label">360° Scenes</div>
            </div>
            <div class="stat-item">
                <div class="stat-number" id="stat-users"><?= $stats['total_users'] ?></div>
                <div class="stat-label">Explorers</div>
            </div>
            <div class="stat-item">
                <div class="stat-number" id="stat-favorites"><?= $stats['total_favorites'] ?></div>
                <div class="stat-label">Favorites</div>
            </div>
        </div>
    </div>
</section>

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
                                <img src="assets/images/uploads/<?= htmlspecialchars($tour['thumbnail']) ?>" alt="<?= htmlspecialchars($tour['title']) ?>" loading="lazy">
                            <?php else: ?>
                                <div class="tour-image-placeholder">
                                    <i class="fas fa-image"></i>
                                </div>
                            <?php endif; ?>
                            <div class="tour-badges">
                                <span class="badge badge-primary">Featured</span>
                                <span class="badge badge-secondary"><?= $tour['scene_count'] ?> scenes</span>
                            </div>
                            <div class="tour-overlay">
                                <a href="vr/tour.php?id=<?= $tour['id'] ?>" class="btn btn-primary">
                                    <i class="fas fa-play"></i> Start Tour
                                </a>
                            </div>
                        </div>
                        <div class="tour-content">
                            <h3 class="tour-title"><?= htmlspecialchars($tour['title']) ?></h3>
                            <p class="tour-description"><?= htmlspecialchars(substr($tour['description'], 0, 100)) ?>...</p>
                            <div class="tour-meta">
                                <div class="tour-rating">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star <?= $i <= round($tour['avg_rating']) ? 'active' : '' ?>"></i>
                                    <?php endfor; ?>
                                    <span>(<?= $tour['avg_rating'] ? round($tour['avg_rating'], 1) : 'No ratings' ?>)</span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <div class="section-footer">
            <a href="tours.php" class="btn btn-outline-primary">
                <i class="fas fa-eye me-2"></i>View All Tours
            </a>
        </div>
    </div>
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
                                <img src="assets/images/uploads/<?= htmlspecialchars($tour['thumbnail']) ?>" alt="<?= htmlspecialchars($tour['title']) ?>" loading="lazy">
                            <?php else: ?>
                                <div class="tour-image-placeholder">
                                    <i class="fas fa-image"></i>
                                </div>
                            <?php endif; ?>
                            <div class="tour-badges">
                                <span class="badge badge-secondary"><?= $tour['scene_count'] ?> scenes</span>
                                <span class="badge badge-success">New</span>
                            </div>
                            <div class="tour-overlay">
                                <a href="vr/tour.php?id=<?= $tour['id'] ?>" class="btn btn-primary">
                                    <i class="fas fa-play"></i> Start Tour
                                </a>
                            </div>
                        </div>
                        <div class="tour-content">
                            <h3 class="tour-title"><?= htmlspecialchars($tour['title']) ?></h3>
                            <p class="tour-description"><?= htmlspecialchars(substr($tour['description'], 0, 100)) ?>...</p>
                            <div class="tour-meta">
                                <span class="tour-date">
                                    <i class="fas fa-clock"></i>
                                    <?= time_ago($tour['created_at']) ?>
                                </span>
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
                <p>Complete panoramic views with seamless navigation between scenes</p>
            </div>
            <div class="feature-item">
                <div class="feature-icon">
                    <i class="fas fa-vr-cardboard"></i>
                </div>
                <h3>VR Ready</h3>
                <p>Fully compatible with VR headsets for an immersive experience</p>
            </div>
            <div class="feature-item">
                <div class="feature-icon">
                    <i class="fas fa-mobile-alt"></i>
                </div>
                <h3>Mobile Friendly</h3>
                <p>Optimized for all devices - desktop, tablet, and mobile</p>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="cta-section">
    <div class="container">
        <div class="cta-content">
            <h2>Ready to Explore?</h2>
            <p>Join thousands of users experiencing the world through virtual reality</p>
            <div class="cta-actions">
                <?php if (!isUserLoggedIn()): ?>
                    <a href="register.php" class="btn btn-primary btn-lg">Create Account</a>
                    <a href="tours.php" class="btn btn-outline-light btn-lg">Browse Tours</a>
                <?php else: ?>
                    <a href="tours.php" class="btn btn-primary btn-lg">Explore Tours</a>
                    <a href="vr/tour.php?id=2" class="btn btn-outline-light btn-lg">Try Demo</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
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
    document.querySelectorAll('.stats-section, .featured-section, .recent-section, .features-section, .tours-grid').forEach(el => {
        observer.observe(el);
    });

    // Initialize tour cards with stagger effect
    document.querySelectorAll('.tour-card').forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(50px)';
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
        card.addEventListener('mouseenter', function() {
            this.style.zIndex = '10';
        });

        card.addEventListener('mouseleave', function() {
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

    // Enhanced floating elements animation
    document.querySelectorAll('.floating-element').forEach((element, index) => {
        element.style.animationDelay = `${index * 2}s`;
        element.style.animationDuration = `${8 + index}s`;
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
        btn.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px) scale(1.02)';
        });

        btn.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
        });

        btn.addEventListener('mousedown', function() {
            this.style.transform = 'translateY(1px) scale(0.98)';
        });

        btn.addEventListener('mouseup', function() {
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
    </style>
`;

document.head.insertAdjacentHTML('beforeend', additionalStyles);
</script>

<?php include 'includes/user-footer.php'; ?>