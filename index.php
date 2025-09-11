<?php
// index.php
require_once 'includes/config.php';
require_once 'includes/user-auth.php';
require_once 'includes/database.php';

// Get featured tours
$featured_tours = $pdo->query("
    SELECT t.*, 
           (SELECT COUNT(*) FROM scenes s WHERE s.tour_id = t.id) as scene_count,
           (SELECT AVG(rating) FROM reviews r WHERE r.tour_id = t.id AND r.approved = 1) as avg_rating
    FROM tours t 
    WHERE t.published = 1 AND t.featured = 1
    ORDER BY t.created_at DESC 
    LIMIT 6
")->fetchAll();

// Get recent tours
$recent_tours = $pdo->query("
    SELECT t.*, 
           (SELECT COUNT(*) FROM scenes s WHERE s.tour_id = t.id) as scene_count,
           (SELECT AVG(rating) FROM reviews r WHERE r.tour_id = t.id AND r.approved = 1) as avg_rating
    FROM tours t 
    WHERE t.published = 1
    ORDER BY t.created_at DESC 
    LIMIT 6
")->fetchAll();

// Get tour statistics
$stats = $pdo->query("
    SELECT 
        (SELECT COUNT(*) FROM tours WHERE published = 1) as total_tours,
        (SELECT COUNT(*) FROM scenes) as total_scenes,
        (SELECT COUNT(*) FROM users) as total_users
")->fetch();

$page_title = "Explore Immersive VR Tours";
$show_page_header = false;
include 'includes/user-header.php';
?>

<style>
    /* Homepage Styles */
    :root {
        --hero-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #667eea 100%);
        --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        --success-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        --warning-gradient: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        --dark-gradient: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
    }

    /* Hero Section */
    .hero {
        background: var(--hero-gradient);
        background-size: 400% 400%;
        animation: gradientShift 15s ease infinite;
        padding: 120px 0;
        position: relative;
        overflow: hidden;
        color: white;
    }

    .hero::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.1'%3E%3Cpath d='m36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E") repeat;
        opacity: 0.3;
    }

    @keyframes gradientShift {
        0% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
        100% { background-position: 0% 50%; }
    }

    .hero-content {
        position: relative;
        z-index: 2;
        animation: fadeInUp 1s ease-out;
    }

    .hero-visual {
        position: relative;
        z-index: 2;
        animation: fadeInRight 1s ease-out 0.3s both;
    }

    .hero-visual .fa-vr-cardboard {
        font-size: 8rem;
        opacity: 0.2;
        margin-bottom: 2rem;
    }

    .floating-elements {
        position: relative;
    }

    .floating-element {
        position: absolute;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
        width: 60px;
        height: 60px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        animation: float 3s ease-in-out infinite;
    }

    .element-1 {
        top: -30px;
        right: 20px;
        animation-delay: 0s;
    }

    .element-2 {
        bottom: 20px;
        left: -20px;
        animation-delay: 1s;
    }

    .element-3 {
        top: 50%;
        right: -40px;
        animation-delay: 2s;
    }

    @keyframes float {
        0%, 100% { transform: translateY(0px) rotate(0deg); }
        50% { transform: translateY(-20px) rotate(5deg); }
    }

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

    @keyframes fadeInRight {
        from {
            opacity: 0;
            transform: translateX(50px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    /* Button Styles */
    .btn {
        padding: 14px 32px;
        border-radius: 12px;
        font-weight: 600;
        font-size: 1rem;
        text-decoration: none;
        border: none;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        position: relative;
        overflow: hidden;
    }

    .btn-lg {
        padding: 16px 40px;
        font-size: 1.1rem;
    }

    .btn-primary {
        background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
        color: #667eea;
        box-shadow: 0 4px 15px rgba(255, 255, 255, 0.3);
    }

    .btn-primary:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(255, 255, 255, 0.4);
        color: #5a67d8;
    }

    .btn-outline-light {
        background: transparent;
        color: white;
        border: 2px solid rgba(255, 255, 255, 0.5);
        backdrop-filter: blur(10px);
    }

    .btn-outline-light:hover {
        background: white;
        color: #667eea;
        border-color: white;
        transform: translateY(-2px);
    }

    .btn-outline {
        background: transparent;
        color: #667eea;
        border: 2px solid #667eea;
    }

    .btn-outline:hover {
        background: #667eea;
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
    }

    .btn-light {
        background: white;
        color: #667eea;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .btn-light:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
    }

    /* Card Styles */
    .card {
        background: white;
        border-radius: 20px;
        border: none;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        transition: all 0.4s ease;
        overflow: hidden;
        position: relative;
    }

    .card-hover:hover {
        transform: translateY(-10px);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
    }

    .card-body {
        padding: 2rem;
        text-align: center;
    }

    .tour-card {
        transition: all 0.4s ease;
        position: relative;
        overflow: hidden;
    }

    .tour-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
        transition: left 0.5s ease;
        z-index: 1;
    }

    .tour-card:hover::before {
        left: 100%;
    }

    .tour-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 15px 35px rgba(102, 126, 234, 0.2);
    }

    .tour-image {
        height: 200px;
        object-fit: cover;
        transition: transform 0.4s ease;
    }

    .tour-card:hover .tour-image {
        transform: scale(1.05);
    }

    .tour-image-placeholder {
        height: 200px;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #6c757d;
        font-size: 2rem;
    }

    /* Badge Styles */
    .badge {
        padding: 6px 12px;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .badge-primary {
        background: var(--hero-gradient);
        color: white;
    }

    .badge-secondary {
        background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
        color: white;
    }

    .badge-outline {
        background: transparent;
        color: #6c757d;
        border: 1px solid #dee2e6;
    }

    /* Stats Section */
    .stats-section {
        padding: 80px 0;
        background: linear-gradient(135deg, #f8f9fc 0%, #ffffff 100%);
    }

    .stat-card {
        text-align: center;
        padding: 2.5rem 2rem;
        background: white;
        border-radius: 20px;
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
        transition: all 0.4s ease;
        position: relative;
        overflow: hidden;
        height: 100%;
    }

    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: var(--hero-gradient);
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 40px rgba(102, 126, 234, 0.15);
    }

    .stat-icon {
        width: 80px;
        height: 80px;
        margin: 0 auto 1.5rem;
        background: linear-gradient(135deg, #667eea20 0%, #764ba220 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
    }

    .stat-number {
        font-size: 3rem;
        font-weight: 800;
        background: var(--hero-gradient);
        -webkit-background-clip: text;
        background-clip: text;
        -webkit-text-fill-color: transparent;
        line-height: 1;
        margin-bottom: 0.5rem;
    }

    /* Features Section */
    .features-section {
        padding: 100px 0;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
        background: url("data:image/svg+xml,%3Csvg width='40' height='40' viewBox='0 0 40 40' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23ffffff' fill-opacity='0.05' fill-rule='evenodd'%3E%3Cpath d='M20 20c0 4.418-3.582 8-8 8s-8-3.582-8-8 3.582-8 8-8 8 3.582 8 8zm0-20c0 4.418-3.582 8-8 8s-8-3.582-8-8 3.582-8 8-8 8 3.582 8 8z'/%3E%3C/g%3E%3C/svg%3E") repeat;
    }

    .feature-card {
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 20px;
        padding: 2.5rem;
        text-align: center;
        height: 100%;
        transition: all 0.4s ease;
        position: relative;
        overflow: hidden;
    }

    .feature-card:hover {
        transform: translateY(-8px);
        background: rgba(255, 255, 255, 0.15);
    }

    .feature-icon {
        width: 80px;
        height: 80px;
        margin: 0 auto 2rem;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
    }

    /* How It Works Section */
    .how-it-works {
        padding: 100px 0;
        background: white;
    }

    .step-card {
        text-align: center;
        padding: 2rem;
        position: relative;
    }

    .step-number {
        width: 60px;
        height: 60px;
        background: var(--hero-gradient);
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        font-weight: 700;
        margin: 0 auto 1.5rem;
        position: relative;
        z-index: 2;
    }

    .step-card::after {
        content: '';
        position: absolute;
        top: 30px;
        left: 50%;
        width: 100%;
        height: 2px;
        background: linear-gradient(90deg, transparent 0%, #e9ecef 20%, #e9ecef 80%, transparent 100%);
        z-index: 1;
    }

    .step-card:last-child::after {
        display: none;
    }

    /* Testimonials Section */
    .testimonials-section {
        padding: 100px 0;
        background: linear-gradient(135deg, #f8f9fc 0%, #ffffff 100%);
    }

    .testimonial-card {
        background: white;
        border-radius: 20px;
        padding: 2.5rem;
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
        text-align: center;
        height: 100%;
        position: relative;
    }

    .testimonial-avatar {
        width: 80px;
        height: 80px;
        background: var(--hero-gradient);
        border-radius: 50%;
        margin: 0 auto 1.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 2rem;
        font-weight: 600;
    }

    .testimonial-stars {
        color: #fbbf24;
        font-size: 1.2rem;
        margin-bottom: 1.5rem;
    }

    /* CTA Section */
    .cta-section {
        background: var(--hero-gradient);
        background-size: 400% 400%;
        animation: gradientShift 15s ease infinite;
        color: white;
        padding: 100px 0;
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
        background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Ccircle cx='30' cy='30' r='4'/%3E%3Ccircle cx='10' cy='30' r='4'/%3E%3Ccircle cx='50' cy='30' r='4'/%3E%3Ccircle cx='30' cy='10' r='4'/%3E%3Ccircle cx='30' cy='50' r='4'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E") repeat;
    }

    /* Utility Classes */
    .container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 0 24px;
    }

    .row {
        display: flex;
        flex-wrap: wrap;
        margin: 0 -15px;
    }

    .col-md-4, .col-md-6, .col-lg-4, .col-lg-6 {
        padding: 0 15px;
        flex: 0 0 100%;
        max-width: 100%;
        margin-bottom: 2rem;
    }

    @media (min-width: 768px) {
        .col-md-4 {
            flex: 0 0 33.333333%;
            max-width: 33.333333%;
        }
        .col-md-6 {
            flex: 0 0 50%;
            max-width: 50%;
        }
    }

    @media (min-width: 992px) {
        .col-lg-4 {
            flex: 0 0 33.333333%;
            max-width: 33.333333%;
        }
        .col-lg-6 {
            flex: 0 0 50%;
            max-width: 50%;
        }
    }

    .d-flex {
        display: flex;
    }

    .align-items-center {
        align-items: center;
    }

    .justify-content-between {
        justify-content: space-between;
    }

    .justify-content-center {
        justify-content: center;
    }

    .text-center {
        text-align: center;
    }

    .flex-wrap {
        flex-wrap: wrap;
    }

    .gap-3 {
        gap: 1rem;
    }

    .mb-3 {
        margin-bottom: 1rem;
    }

    .mb-4 {
        margin-bottom: 1.5rem;
    }

    .mb-6 {
        margin-bottom: 2rem;
    }

    .mb-8 {
        margin-bottom: 3rem;
    }

    .mb-16 {
        margin-bottom: 6rem;
    }

    .me-1 {
        margin-right: 0.25rem;
    }

    .me-2 {
        margin-right: 0.5rem;
    }

    .ms-2 {
        margin-left: 0.5rem;
    }

    .py-16 {
        padding-top: 6rem;
        padding-bottom: 6rem;
    }

    .h-100 {
        height: 100%;
    }

    .opacity-20 {
        opacity: 0.2;
    }

    .opacity-90 {
        opacity: 0.9;
    }

    .display-4 {
        font-size: 3.5rem;
        font-weight: 300;
        line-height: 1.2;
    }

    .fw-bold {
        font-weight: 700;
    }

    .lead {
        font-size: 1.25rem;
        font-weight: 300;
        line-height: 1.6;
    }

    .h2, .h3, .h5 {
        font-family: 'Space Grotesk', sans-serif;
        font-weight: 600;
    }

    .h2 {
        font-size: 2rem;
    }

    .h3 {
        font-size: 1.75rem;
    }

    .h5 {
        font-size: 1.25rem;
    }

    .text-gray-600 {
        color: #6b7280;
    }

    .text-gray-400 {
        color: #9ca3af;
    }

    .text-warning {
        color: #fbbf24;
    }

    .text-success {
        color: #10b981;
    }

    .text-info {
        color: #3b82f6;
    }

    .text-primary {
        color: #667eea;
    }

    .text-sm {
        font-size: 0.875rem;
    }

    .text-3xl {
        font-size: 1.875rem;
    }

    .text-4xl {
        font-size: 2.25rem;
    }

    .text-8xl {
        font-size: 6rem;
    }

    .text-white {
        color: white;
    }

    .position-absolute {
        position: absolute;
    }

    .top-2 {
        top: 0.5rem;
    }

    .left-2 {
        left: 0.5rem;
    }

    .d-grid {
        display: grid;
    }

    .card-img-top {
        width: 100%;
        border-top-left-radius: 20px;
        border-top-right-radius: 20px;
    }

    .card-title {
        color: #1f2937;
        margin-bottom: 1rem;
    }

    .card-text {
        margin-bottom: 1.5rem;
        line-height: 1.6;
    }

    .card-footer {
        padding: 1.5rem 2rem 2rem;
        background: transparent;
        border: none;
    }

    .bg-primary {
        background: var(--hero-gradient);
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .hero {
            padding: 80px 0;
        }
        
        .display-4 {
            font-size: 2.5rem;
        }
        
        .hero-visual .fa-vr-cardboard {
            font-size: 4rem;
            margin-top: 2rem;
        }
        
        .text-8xl {
            font-size: 4rem;
        }
        
        .floating-element {
            width: 40px;
            height: 40px;
            font-size: 1rem;
        }
        
        .stat-number {
            font-size: 2rem;
        }
        
        .features-section,
        .how-it-works,
        .testimonials-section,
        .cta-section {
            padding: 60px 0;
        }
        
        .stats-section {
            padding: 60px 0;
        }
        
        .btn-lg {
            padding: 12px 24px;
            font-size: 1rem;
        }
        
        .container {
            padding: 0 16px;
        }

        .d-flex.gap-3 {
            flex-direction: column;
            align-items: center;
        }

        .d-flex.justify-content-between {
            flex-direction: column;
            align-items: flex-start;
            gap: 1rem;
        }

        .d-flex.justify-content-center.gap-3 {
            flex-direction: column;
            align-items: center;
        }
    }

    @media (max-width: 480px) {
        .display-4 {
            font-size: 2rem;
        }
        
        .lead {
            font-size: 1.1rem;
        }
        
        .stat-card {
            padding: 1.5rem;
        }
        
        .feature-card,
        .testimonial-card {
            padding: 1.5rem;
        }

        .hero {
            padding: 60px 0;
        }

        .py-16 {
            padding-top: 4rem;
            padding-bottom: 4rem;
        }

        .mb-16 {
            margin-bottom: 4rem;
        }

        .mb-8 {
            margin-bottom: 2rem;
        }
    }
</style>

<!-- Hero Section -->
<section class="hero">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <div class="hero-content">
                    <h1 class="display-4 fw-bold mb-4">Step Into Another World</h1>
                    <p class="lead mb-6">Experience breathtaking 360° virtual tours of museums, landmarks, and hidden gems from around the globe. Discover places you've never been, all from the comfort of your home.</p>
                    <div class="d-flex flex-wrap gap-3">
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
            </div>
            <div class="col-lg-6 text-center">
                <div class="hero-visual">
                    <i class="fas fa-vr-cardboard text-8xl text-white opacity-20"></i>
                    <div class="floating-elements">
                        <div class="floating-element element-1">
                            <i class="fas fa-mountain text-warning"></i>
                        </div>
                        <div class="floating-element element-2">
                            <i class="fas fa-landmark"></i>
                        </div>
                        <div class="floating-element element-3">
                            <i class="fas fa-water"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Stats Section -->
<section class="stats-section">
    <div class="container">
        <div class="row">
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-map-marked-alt" style="color: #667eea;"></i>
                    </div>
                    <div class="stat-number"><?php echo number_format($stats['total_tours']); ?></div>
                    <h5>Virtual Tours</h5>
                    <p class="text-gray-600">Immersive experiences waiting for you to explore</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-image" style="color: #48bb78;"></i>
                    </div>
                    <div class="stat-number"><?php echo number_format($stats['total_scenes']); ?></div>
                    <h5>360° Scenes</h5>
                    <p class="text-gray-600">High-quality panoramic views and interactive hotspots</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-users" style="color: #ed8936;"></i>
                    </div>
                    <div class="stat-number"><?php echo number_format($stats['total_users']); ?></div>
                    <h5>Community Members</h5>
                    <p class="text-gray-600">Active explorers discovering the world virtually</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="features-section">
    <div class="container">
        <div class="text-center mb-8">
            <h2 class="h2 mb-4">Why Choose Our VR Tours?</h2>
            <p class="lead opacity-90">Experience the future of travel with cutting-edge virtual reality technology</p>
        </div>
        <div class="row">
            <div class="col-md-4 mb-6">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-vr-cardboard"></i>
                    </div>
                    <h3 class="h5 mb-4">Immersive 360° Views</h3>
                    <p>Step inside stunning locations with full 360° panoramic photography. Look around, zoom in, and discover every detail as if you were really there.</p>
                </div>
            </div>
            <div class="col-md-4 mb-6">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-mouse-pointer"></i>
                    </div>
                    <h3 class="h5 mb-4">Interactive Hotspots</h3>
                    <p>Click on interactive elements to learn more, view additional content, and navigate seamlessly between different areas of each location.</p>
                </div>
            </div>
            <div class="col-md-4 mb-6">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-mobile-alt"></i>
                    </div>
                    <h3 class="h5 mb-4">Cross-Platform Access</h3>
                    <p>Enjoy tours on any device - desktop, tablet, or smartphone. VR headset compatible for the ultimate immersive experience.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- How It Works Section -->
<section class="how-it-works">
    <div class="container">
        <div class="text-center mb-8">
            <h2 class="h2 mb-4">How It Works</h2>
            <p class="lead text-gray-600">Get started with virtual tours in just three simple steps</p>
        </div>
        <div class="row">
            <div class="col-md-4">
                <div class="step-card">
                    <div class="step-number">1</div>
                    <h3 class="h5 mb-3">Choose Your Destination</h3>
                    <p class="text-gray-600">Browse our curated collection of virtual tours from museums, landmarks, and unique locations around the world.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="step-card">
                    <div class="step-number">2</div>
                    <h3 class="h5 mb-3">Start Exploring</h3>
                    <p class="text-gray-600">Click to enter the immersive 360° environment. Navigate through scenes and interact with hotspots to learn more.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="step-card">
                    <div class="step-number">3</div>
                    <h3 class="h5 mb-3">Share & Discover</h3>
                    <p class="text-gray-600">Rate your favorite tours, leave reviews, and discover new experiences recommended by our community.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Featured Tours Section -->
<section class="container mb-16">
    <div class="d-flex justify-content-between align-items-center mb-8">
        <h2 class="h3">Featured Experiences</h2>
        <a href="tours.php?filter=featured" class="btn btn-outline">
            View All <i class="fas fa-arrow-right ms-2"></i>
        </a>
    </div>
    
    <div class="row">
        <?php foreach ($featured_tours as $tour): ?>
            <div class="col-md-6 col-lg-4 mb-6">
                <div class="card tour-card h-100">
                    <?php if ($tour['thumbnail']): ?>
                        <img src="assets/images/uploads/<?php echo $tour['thumbnail']; ?>" 
                             class="card-img-top tour-image" 
                             alt="<?php echo htmlspecialchars($tour['title']); ?>">
                    <?php else: ?>
                        <div class="tour-image-placeholder">
                            <i class="fas fa-image text-3xl text-gray-400"></i>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($tour['featured']): ?>
                        <div class="position-absolute top-2 left-2">
                            <span class="badge badge-primary">
                                <i class="fas fa-star me-1"></i>Featured
                            </span>
                        </div>
                    <?php endif; ?>
                    
                    <div class="card-body">
                        <h3 class="h5 card-title"><?php echo htmlspecialchars($tour['title']); ?></h3>
                        <p class="card-text text-gray-600">
                            <?php echo htmlspecialchars(substr($tour['description'], 0, 120)); ?>
                            <?php if (strlen($tour['description']) > 120): ?>...<?php endif; ?>
                        </p>
                        
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="badge badge-secondary">
                                <i class="fas fa-image me-1"></i><?php echo $tour['scene_count']; ?> scenes
                            </span>
                            <span class="badge badge-outline"><?php echo htmlspecialchars($tour['category']); ?></span>
                        </div>
                        
                        <?php if ($tour['avg_rating']): ?>
                            <div class="d-flex align-items-center mb-3">
                                <div class="text-warning me-2">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star<?php echo $i <= round($tour['avg_rating']) ? '' : '-half-alt'; ?>"></i>
                                    <?php endfor; ?>
                                </div>
                                <span class="text-sm text-gray-600">(<?php echo number_format($tour['avg_rating'], 1); ?>)</span>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="card-footer">
                        <div class="d-grid">
                            <a href="vr/tour.php?id=<?php echo $tour['id']; ?>" class="btn btn-primary">
                                <i class="fas fa-play-circle me-2"></i>Start Tour
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- Recent Tours Section -->
<section class="container mb-16">
    <div class="d-flex justify-content-between align-items-center mb-8">
        <h2 class="h3">Recently Added</h2>
        <a href="tours.php?sort=newest" class="btn btn-outline">
            View All <i class="fas fa-arrow-right ms-2"></i>
        </a>
    </div>
    
    <div class="row">
        <?php foreach ($recent_tours as $tour): ?>
            <div class="col-md-6 col-lg-4 mb-6">
                <div class="card tour-card h-100">
                    <?php if ($tour['thumbnail']): ?>
                        <img src="assets/images/uploads/<?php echo $tour['thumbnail']; ?>" 
                             class="card-img-top tour-image" 
                             alt="<?php echo htmlspecialchars($tour['title']); ?>">
                    <?php else: ?>
                        <div class="tour-image-placeholder">
                            <i class="fas fa-image text-3xl text-gray-400"></i>
                        </div>
                    <?php endif; ?>
                    
                    <div class="card-body">
                        <h3 class="h5 card-title"><?php echo htmlspecialchars($tour['title']); ?></h3>
                        <p class="card-text text-gray-600">
                            <?php echo htmlspecialchars(substr($tour['description'], 0, 120)); ?>
                            <?php if (strlen($tour['description']) > 120): ?>...<?php endif; ?>
                        </p>
                        
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="badge badge-secondary">
                                <i class="fas fa-image me-1"></i><?php echo $tour['scene_count']; ?> scenes
                            </span>
                            <span class="badge badge-outline"><?php echo htmlspecialchars($tour['category']); ?></span>
                        </div>
                    </div>
                    
                    <div class="card-footer">
                        <div class="d-grid">
                            <a href="vr/tour.php?id=<?php echo $tour['id']; ?>" class="btn btn-outline">
                                <i class="fas fa-eye me-2"></i>Explore Tour
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- Testimonials Section -->
<section class="testimonials-section">
    <div class="container">
        <div class="text-center mb-8">
            <h2 class="h2 mb-4">What Our Users Say</h2>
            <p class="lead text-gray-600">Hear from travelers who've explored the world virtually</p>
        </div>
        <div class="row">
            <div class="col-md-4">
                <div class="testimonial-card">
                    <div class="testimonial-avatar">S</div>
                    <div class="testimonial-stars">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                    <p class="mb-4">"Absolutely amazing! I felt like I was actually walking through the Louvre. The level of detail and interactivity is incredible."</p>
                    <div>
                        <h5 class="h6 mb-1">Sarah Johnson</h5>
                        <p class="text-sm text-gray-600">Art Enthusiast</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="testimonial-card">
                    <div class="testimonial-avatar">M</div>
                    <div class="testimonial-stars">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                    <p class="mb-4">"Perfect for travel planning! I explored several destinations before booking my trip. Saved me so much time and helped me prioritize what to see."</p>
                    <div>
                        <h5 class="h6 mb-1">Michael Chen</h5>
                        <p class="text-sm text-gray-600">Travel Blogger</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="testimonial-card">
                    <div class="testimonial-avatar">E</div>
                    <div class="testimonial-stars">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                    <p class="mb-4">"As an educator, these virtual tours have revolutionized how I teach geography and history. My students are completely engaged!"</p>
                    <div>
                        <h5 class="h6 mb-1">Emily Rodriguez</h5>
                        <p class="text-sm text-gray-600">High School Teacher</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="cta-section">
    <div class="container text-center">
        <h2 class="h2 mb-4">Ready to Explore?</h2>
        <p class="lead mb-6 opacity-90">Join thousands of users discovering amazing virtual experiences every day.</p>
        <div class="d-flex justify-content-center gap-3">
            <?php if (isUserLoggedIn()): ?>
                <a href="tours.php" class="btn btn-light btn-lg">
                    <i class="fas fa-compass me-2"></i>Browse Tours
                </a>
            <?php else: ?>
                <a href="register.php" class="btn btn-light btn-lg">
                    <i class="fas fa-user-plus me-2"></i>Get Started Free
                </a>
                <a href="tours.php" class="btn btn-outline-light btn-lg">
                    <i class="fas fa-eye me-2"></i>Explore First
                </a>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php include 'includes/user-footer.php';