<?php
// includes/user-header.php
if (!isset($page_title)) {
    $page_title = 'VR Tour Application';
}
$base_url="http://localhost/vr-tour-app/";
$current_user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - VR Tour Application</title>
    
    <!-- Preload critical resources -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <!-- Main CSS -->
    <link href="<?php echo $base_url; ?>assets/css/main.css" rel="stylesheet">
    
    <!-- Professional Header Styles -->
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --accent-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            --dark-gradient: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            
            --primary-color: #667eea;
            --secondary-color: #764ba2;
            --accent-color: #4facfe;
            --text-primary: #1a202c;
            --text-secondary: #4a5568;
            --text-muted: #718096;
            --border-color: #e2e8f0;
            --bg-glass: rgba(255, 255, 255, 0.95);
            --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.1);
            --shadow-md: 0 4px 6px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 10px 25px rgba(0, 0, 0, 0.15);
            --shadow-xl: 0 20px 40px rgba(0, 0, 0, 0.1);
            
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            --border-radius: 12px;
            --border-radius-sm: 8px;
            --border-radius-lg: 16px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', system-ui, sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            color: var(--text-primary);
            line-height: 1.6;
        }

        /* Skip Link */
        .skip-link {
            position: absolute;
            top: -40px;
            left: 6px;
            background: var(--primary-color);
            color: white;
            padding: 8px 16px;
            text-decoration: none;
            border-radius: var(--border-radius-sm);
            font-weight: 500;
            z-index: 1000;
            transition: var(--transition);
        }

        .skip-link:focus {
            top: 6px;
        }

        /* Navbar */
        .navbar {
            background: var(--bg-glass);
            backdrop-filter: blur(20px) saturate(180%);
            -webkit-backdrop-filter: blur(20px) saturate(180%);
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: var(--shadow-md);
            position: sticky;
            top: 0;
            z-index: 1000;
            padding: 0;
        }

        .navbar::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(90deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
            pointer-events: none;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 24px;
            position: relative;
        }

        .navbar .container {
            padding: 16px 24px;
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

        .w-100 {
            width: 100%;
        }

        .gap-4 {
            gap: 1.5rem;
        }

        .gap-2 {
            gap: 0.5rem;
        }

        .me-1 {
            margin-right: 0.25rem;
        }

        .me-2 {
            margin-right: 0.5rem;
        }

        .mt-4 {
            margin-top: 1rem;
        }

        .mb-6 {
            margin-bottom: 2rem;
        }

        /* Brand Logo */
        .navbar-brand {
            display: flex;
            align-items: center;
            text-decoration: none;
            font-family: 'Space Grotesk', sans-serif;
            font-weight: 700;
            font-size: 1.5rem;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            transition: var(--transition);
            position: relative;
        }

        .navbar-brand:hover {
            transform: translateY(-1px);
            filter: brightness(1.1);
        }

        .navbar-brand i {
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            font-size: 1.75rem;
            margin-right: 0.5rem;
        }

        /* Navigation Links */
        .nav-link {
            color: var(--text-primary);
            text-decoration: none;
            font-weight: 500;
            padding: 10px 16px;
            border-radius: var(--border-radius-sm);
            transition: var(--transition);
            display: flex;
            align-items: center;
            position: relative;
            overflow: hidden;
        }

        .nav-link::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: var(--primary-gradient);
            transition: var(--transition);
            z-index: -1;
        }

        .nav-link:hover {
            color: white;
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
        }

        .nav-link:hover::before {
            left: 0;
        }

        /* Buttons */
        .btn {
            padding: 12px 24px;
            border-radius: var(--border-radius);
            font-weight: 600;
            font-size: 0.95rem;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .btn-primary {
            background: var(--primary-gradient);
            color: white;
            box-shadow: var(--shadow-sm);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
            filter: brightness(1.1);
        }

        .btn-outline {
            background: transparent;
            color: var(--text-primary);
            border: 2px solid var(--border-color);
            backdrop-filter: blur(10px);
        }

        .btn-outline:hover {
            background: var(--primary-gradient);
            color: white;
            border-color: transparent;
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
        }

        /* Dropdown */
        .dropdown {
            position: relative;
        }

        .dropdown-toggle {
            background: var(--bg-glass);
            border: 2px solid var(--border-color);
            backdrop-filter: blur(10px);
        }

        .dropdown-toggle:hover {
            background: var(--primary-gradient);
            color: white;
            border-color: transparent;
        }

        .dropdown-menu {
            position: absolute;
            top: 100%;
            right: 0;
            background: white;
            border: none;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-xl);
            min-width: 220px;
            padding: 8px;
            margin-top: 8px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: var(--transition);
            backdrop-filter: blur(20px);
        }

        .dropdown:hover .dropdown-menu {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .dropdown-item {
            display: flex;
            align-items: center;
            padding: 12px 16px;
            color: var(--text-primary);
            text-decoration: none;
            border-radius: var(--border-radius-sm);
            transition: var(--transition);
            font-weight: 500;
        }

        .dropdown-item:hover {
            background: var(--primary-gradient);
            color: white;
            transform: translateX(4px);
        }

        .dropdown-item.text-danger {
            color: #e53e3e;
        }

        .dropdown-item.text-danger:hover {
            background: linear-gradient(135deg, #fc8181 0%, #e53e3e 100%);
            color: white;
        }

        .dropdown-divider {
            height: 1px;
            background: var(--border-color);
            margin: 8px 0;
            border: none;
        }

        /* Mobile Styles */
        .d-md-none {
            display: block;
        }

        .d-none {
            display: none;
        }

        .collapse {
            display: none;
        }

        .collapse.show {
            display: block;
        }

        #mobileMenu {
            background: white;
            border-radius: var(--border-radius);
            padding: 20px;
            margin-top: 16px;
            box-shadow: var(--shadow-lg);
            border: 1px solid var(--border-color);
        }

        .flex-column {
            flex-direction: column;
        }

        .w-100 {
            width: 100%;
        }

        /* Breadcrumb */
        .breadcrumb {
            display: flex;
            flex-wrap: wrap;
            padding: 16px 20px;
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-sm);
            margin: 0;
            list-style: none;
        }

        .breadcrumb-item {
            display: flex;
            align-items: center;
        }

        .breadcrumb-item + .breadcrumb-item::before {
            content: '/';
            color: var(--text-muted);
            margin: 0 8px;
        }

        .breadcrumb-item a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
        }

        .breadcrumb-item a:hover {
            color: var(--secondary-color);
        }

        .breadcrumb-item.active {
            color: var(--text-secondary);
        }

        /* Page Header */
        .page-header {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: var(--border-radius-lg);
            box-shadow: var(--shadow-lg);
            position: relative;
            overflow: hidden;
        }

        .page-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: var(--primary-gradient);
            opacity: 0.05;
        }

        .page-header h1 {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 3rem;
            font-weight: 700;
            margin: 0 0 16px 0;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            position: relative;
        }

        .page-header .lead {
            font-size: 1.25rem;
            color: var(--text-secondary);
            margin: 0;
            max-width: 600px;
            margin: 0 auto;
            position: relative;
        }

        /* Responsive Design */
        @media (min-width: 768px) {
            .d-md-flex {
                display: flex !important;
            }
            
            .d-md-none {
                display: none !important;
            }
            
            .d-none {
                display: block;
            }
        }

        @media (max-width: 767px) {
            .container {
                padding: 0 16px;
            }
            
            .navbar .container {
                padding: 12px 16px;
            }
            
            .navbar-brand {
                font-size: 1.25rem;
            }
            
            .page-header h1 {
                font-size: 2rem;
            }
            
            .page-header .lead {
                font-size: 1.1rem;
            }
            
            .page-header {
                padding: 40px 16px;
            }
            
            .btn {
                padding: 10px 20px;
                font-size: 0.9rem;
            }
        }

        @media (max-width: 480px) {
            .page-header h1 {
                font-size: 1.75rem;
            }
            
            .navbar-brand span {
                display: none;
            }
            
            .nav-link {
                padding: 12px 16px;
            }
        }

        /* Animation and Interactions */
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

        .page-header {
            animation: fadeInUp 0.6s ease-out;
        }

        .navbar {
            animation: fadeInUp 0.4s ease-out;
        }

        /* Focus States for Accessibility */
        .btn:focus,
        .nav-link:focus,
        .dropdown-toggle:focus {
            outline: 3px solid rgba(102, 126, 234, 0.3);
            outline-offset: 2px;
        }

        /* High Contrast Mode Support */
        @media (prefers-contrast: high) {
            :root {
                --primary-gradient: linear-gradient(135deg, #000 0%, #333 100%);
                --text-primary: #000;
                --border-color: #000;
            }
        }

        /* Reduced Motion Support */
        @media (prefers-reduced-motion: reduce) {
            * {
                transition: none !important;
                animation: none !important;
            }
        }

        /* Dark Mode Preparation */
        @media (prefers-color-scheme: dark) {
            :root {
                --text-primary: #f7fafc;
                --text-secondary: #e2e8f0;
                --text-muted: #a0aec0;
                --border-color: #2d3748;
                --bg-glass: rgba(26, 32, 44, 0.95);
            }
            
            body {
                background: linear-gradient(135deg, #1a202c 0%, #2d3748 100%);
            }
            
            .navbar {
                border-bottom-color: rgba(255, 255, 255, 0.1);
            }
            
            .page-header,
            .breadcrumb,
            #mobileMenu {
                background: #2d3748;
                color: var(--text-primary);
            }
            
            .dropdown-menu {
                background: #2d3748;
            }
        }
    </style>
    
    <!-- Favicon -->
    <link rel="apple-touch-icon" sizes="180x180" href="<?php echo $base_url; ?>assets/images/favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="<?php echo $base_url; ?>assets/images/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="<?php echo $base_url; ?>assets/images/favicon/favicon-16x16.png">
    <link rel="manifest" href="<?php echo $base_url; ?>assets/images/favicon/site.webmanifest">
    
    <!-- Structured Data -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "WebApplication",
        "name": "VR Tour Application",
        "description": "Immerse yourself in stunning 360° virtual tours of museums, landmarks, and exotic locations.",
        "applicationCategory": "MultimediaApplication",
        "operatingSystem": "Web Browser"
    }
    </script>
    
    <!-- PWA Meta Tags -->
    <meta name="theme-color" content="#6366f1">
    <meta name="description" content="Explore immersive virtual reality tours of museums, landmarks, and exotic locations.">
</head>
<body>
    <!-- Skip to main content -->
    <a href="#main-content" class="skip-link">Skip to main content</a>
    
    <!-- Navigation -->
    <nav class="navbar">
        <div class="container">
            <div class="d-flex align-items-center justify-content-between w-100">
                <!-- Logo -->
                <a class="navbar-brand" href="<?php echo $base_url; ?>index.php">
                    <i class="fas fa-vr-cardboard me-2"></i>
                    <span>VR Tours</span>
                </a>

                <!-- Desktop Navigation -->
                <div class="d-none d-md-flex align-items-center gap-4">
                    <a class="nav-link" href="<?php echo $base_url; ?>tours.php">
                        <i class="fas fa-compass me-1"></i>Explore
                    </a>
                    <a class="nav-link" href="<?php echo $base_url; ?>categories.php">
                        <i class="fas fa-tag me-1"></i>Categories
                    </a>
                    
                    <?php if (isUserLoggedIn()): ?>
                    <div class="dropdown">
                        <button class="btn btn-outline dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle me-1"></i>
                            <?php echo htmlspecialchars($current_user['username']); ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="<?php echo $base_url; ?>user/dashboard.php">
                                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                            </a></li>
                            <li><a class="dropdown-item" href="<?php echo $base_url; ?>user/favorites.php">
                                <i class="fas fa-heart me-2"></i>Favorites
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?php echo $base_url; ?>user/profile.php">
                                <i class="fas fa-user me-2"></i>Profile
                            </a></li>
                            <li><a class="dropdown-item" href="<?php echo $base_url; ?>user/settings.php">
                                <i class="fas fa-cog me-2"></i>Settings
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="<?php echo $base_url; ?>logout.php">
                                <i class="fas fa-sign-out-alt me-2"></i>Logout
                            </a></li>
                        </ul>
                    </div>
                    <?php else: ?>
                    <div class="d-flex gap-2">
                        <a href="<?php echo $base_url; ?>login.php" class="btn btn-outline">Login</a>
                        <a href="<?php echo $base_url; ?>register.php" class="btn btn-primary">Sign Up</a>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Mobile menu button -->
                <button class="d-md-none btn btn-outline" type="button" onclick="toggleMobileMenu()">
                    <i class="fas fa-bars"></i>
                </button>
            </div>

            <!-- Mobile Navigation -->
            <div class="collapse d-md-none mt-4" id="mobileMenu">
                <div class="d-flex flex-column gap-2">
                    <a class="nav-link" href="<?php echo $base_url; ?>tours.php">
                        <i class="fas fa-compass me-2"></i>Explore Tours
                    </a>
                    <a class="nav-link" href="<?php echo $base_url; ?>categories.php">
                        <i class="fas fa-tag me-2"></i>Categories
                    </a>
                    
                    <?php if (isUserLoggedIn()): ?>
                    <hr>
                    <a class="nav-link" href="<?php echo $base_url; ?>user/dashboard.php">
                        <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                    </a>
                    <a class="nav-link" href="<?php echo $base_url; ?>user/favorites.php">
                        <i class="fas fa-heart me-2"></i>Favorites
                    </a>
                    <a class="nav-link" href="<?php echo $base_url; ?>user/profile.php">
                        <i class="fas fa-user me-2"></i>Profile
                    </a>
                    <a class="nav-link" href="<?php echo $base_url; ?>user/settings.php">
                        <i class="fas fa-cog me-2"></i>Settings
                    </a>
                    <a class="nav-link text-danger" href="<?php echo $base_url; ?>logout.php">
                        <i class="fas fa-sign-out-alt me-2"></i>Logout
                    </a>
                    <?php else: ?>
                    <hr>
                    <a href="<?php echo $base_url; ?>login.php" class="btn btn-outline w-100">Login</a>
                    <a href="<?php echo $base_url; ?>register.php" class="btn btn-primary w-100">Sign Up</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main id="main-content" class="container">
        <!-- Breadcrumb -->
        <?php if (isset($breadcrumbs)): ?>
        <nav aria-label="breadcrumb" class="mb-6">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?php echo $base_url; ?>index.php">Home</a></li>
                <?php foreach ($breadcrumbs as $crumb): ?>
                    <?php if (isset($crumb['url'])): ?>
                        <li class="breadcrumb-item"><a href="<?php echo $crumb['url']; ?>"><?php echo $crumb['title']; ?></a></li>
                    <?php else: ?>
                        <li class="breadcrumb-item active"><?php echo $crumb['title']; ?></li>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ol>
        </nav>
        <?php endif; ?>

        <!-- Page Header -->
        <?php if (isset($show_page_header) && $show_page_header): ?>
        <div class="page-header mb-6">
            <h1><?php echo $page_title; ?></h1>
            <?php if (isset($page_subtitle)): ?>
                <p class="lead"><?php echo $page_subtitle; ?></p>
            <?php endif; ?>
        </div>
        <?php endif; ?>

    <script>
        // Mobile menu toggle functionality
        function toggleMobileMenu() {
            const mobileMenu = document.getElementById('mobileMenu');
            if (mobileMenu.classList.contains('show')) {
                mobileMenu.classList.remove('show');
            } else {
                mobileMenu.classList.add('show');
            }
        }

        // Close mobile menu when clicking outside
        document.addEventListener('click', function(event) {
            const mobileMenu = document.getElementById('mobileMenu');
            const mobileButton = event.target.closest('.d-md-none');
            
            if (!mobileButton && !mobileMenu.contains(event.target)) {
                mobileMenu.classList.remove('show');
            }
        });

        // Smooth scroll for skip link
        document.querySelector('.skip-link').addEventListener('click', function(e) {
            e.preventDefault();
            document.getElementById('main-content').scrollIntoView({
                behavior: 'smooth'
            });
        });
    </script>