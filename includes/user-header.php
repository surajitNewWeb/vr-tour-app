<?php
// includes/user-header.php
if (!isset($page_title)) {
    $page_title = "VR Tour Application";
}

if (!isset($body_class)) {
    $body_class = "";
}

if (isUserLoggedIn()) {
    $body_class .= " user-logged-in";
} else {
    $body_class .= " user-guest";
}

$user_data = getUserData();
?>
<!DOCTYPE html>
<html lang="en" class="h-100">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description"
        content="Experience immersive virtual reality tours of museums, landmarks, and cultural sites from around the world.">
    <meta name="keywords" content="VR, virtual reality, 360 tours, museums, landmarks, cultural heritage">
    <meta name="author" content="VR Tour Application">
    <meta name="robots" content="index, follow">

    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="<?php echo htmlspecialchars($page_title); ?> - VR Tour Application">
    <meta property="og:description"
        content="Experience immersive virtual reality tours of museums, landmarks, and cultural sites from around the world.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo BASE_URL . ltrim($_SERVER['REQUEST_URI'], '/'); ?>">
    <meta property="og:image" content="<?php echo BASE_URL; ?>assets/images/og-image.jpg">

    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo htmlspecialchars($page_title); ?> - VR Tour Application">
    <meta name="twitter:description"
        content="Experience immersive virtual reality tours of museums, landmarks, and cultural sites from around the world.">
    <meta name="twitter:image" content="<?php echo BASE_URL; ?>assets/images/og-image.jpg">

    <!-- Favicon -->
    <link rel="apple-touch-icon" sizes="180x180"
        href="<?php echo BASE_URL; ?>assets/images/favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32"
        href="<?php echo BASE_URL; ?>assets/images/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16"
        href="<?php echo BASE_URL; ?>assets/images/favicon/favicon-16x16.png">

    <title>
        <?php echo htmlspecialchars($page_title); ?> - VR Tour Application
    </title>

    <!-- Preload critical resources -->
    <link rel="preload" href="<?php echo BASE_URL; ?>assets/css/main.css" as="style">
    <link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" as="style"
        crossorigin="anonymous">

    <!-- CSS -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
        integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- A-Frame -->
    <script src="https://aframe.io/releases/1.4.0/aframe.min.js"></script>

    <!-- Preconnect to external domains -->
    <link rel="preconnect" href="https://cdnjs.cloudflare.com">
    <link rel="preconnect" href="https://aframe.io">

    <!-- JSON-LD Structured Data -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "WebApplication",
      "name": "VR Tour Application",
      "url": "<?php echo BASE_URL; ?>",
      "description": "Experience immersive virtual reality tours of museums, landmarks, and cultural sites from around the world.",
      "applicationCategory": "MultimediaApplication",
      "operatingSystem": "Web Browser",
      "permissions": "microphone, vr",
      "screenshot": "<?php echo BASE_URL; ?>assets/images/og-image.jpg"
    }
    </script>

    <style>
        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --secondary: #ec4899;
            --accent: #06b6d4;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --info: #3b82f6;
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-300: #d1d5db;
            --gray-400: #9ca3af;
            --gray-500: #6b7280;
            --gray-600: #4b5563;
            --gray-700: #374151;
            --gray-800: #1f2937;
            --gray-900: #111827;
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
            --radius-sm: 0.125rem;
            --radius: 0.375rem;
            --radius-md: 0.5rem;
            --radius-lg: 0.75rem;
            --radius-xl: 1rem;
            --transition: all 0.15s ease-in-out;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        /* Header Styles */
        .navbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(99, 102, 241, 0.1);
            padding: 0;
            z-index: 1000;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: var(--shadow);
        }

        .navbar.scrolled {
            background: rgba(255, 255, 255, 0.98);
            box-shadow: var(--shadow-md);
            border-bottom: 1px solid rgba(99, 102, 241, 0.2);
        }

        .navbar-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            max-width: 1400px;
            margin: 0 auto;
            padding: 1rem 1.5rem;
            position: relative;
        }

        .navbar-brand {
            display: flex;
            align-items: center;
            text-decoration: none;
            font-weight: 800;
            font-size: 1.5rem;
            color: var(--primary);
            transition: var(--transition);
        }

        .navbar-brand i {
            margin-right: 0.75rem;
            font-size: 2rem;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .navbar-menu {
            display: flex;
            align-items: center;
            gap: 2rem;
            flex: 1;
            justify-content: center;
        }

        .navbar-start {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .navbar-end {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .nav-link {
            color: var(--gray-700);
            text-decoration: none;
            padding: 0.75rem 1.25rem;
            border-radius: var(--radius-lg);
            transition: var(--transition);
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .nav-link:hover,
        .nav-link.active {
            color: var(--primary);
            background: rgba(99, 102, 241, 0.1);
        }

        .navbar-item.has-dropdown {
            position: relative;
        }

        .navbar-dropdown {
            position: absolute;
            top: calc(100% + 1rem);
            right: 0;
            width: 220px;
            background: white;
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-lg);
            padding: 0.75rem;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 1001;
            border: 1px solid var(--gray-200);
        }

        .navbar-item.has-dropdown:hover .navbar-dropdown {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .dropdown-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.875rem 1rem;
            color: var(--gray-700);
            text-decoration: none;
            transition: var(--transition);
            border-radius: var(--radius-md);
            font-weight: 500;
            margin-bottom: 0.25rem;
        }

        .dropdown-item:hover {
            background: var(--gray-100);
            color: var(--primary);
        }

        .dropdown-divider {
            height: 1px;
            background: var(--gray-200);
            margin: 0.5rem 0;
        }

        .user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border: 2px solid white;
            box-shadow: var(--shadow-md);
        }

        .user-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .user-avatar i {
            font-size: 1.25rem;
            color: white;
        }

        .navbar-burger {
            display: none;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            width: 32px;
            height: 32px;
            background: none;
            border: none;
            cursor: pointer;
            padding: 4px;
            border-radius: var(--radius-md);
            transition: var(--transition);
        }

        .navbar-burger span {
            width: 20px;
            height: 2px;
            background: var(--gray-700);
            margin: 2px 0;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            transform-origin: center;
        }

        .navbar-burger.active span:nth-child(1) {
            transform: translateY(6px) rotate(45deg);
            background: var(--primary);
        }

        .navbar-burger.active span:nth-child(2) {
            opacity: 0;
            transform: scale(0);
        }

        .navbar-burger.active span:nth-child(3) {
            transform: translateY(-6px) rotate(-45deg);
            background: var(--primary);
        }

        /* Button Styles */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: var(--radius-lg);
            font-weight: 600;
            text-decoration: none;
            transition: var(--transition);
            cursor: pointer;
            font-size: 0.875rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            box-shadow: var(--shadow-md);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .btn-outline {
            background: transparent;
            color: var(--primary);
            border: 2px solid var(--primary);
        }

        .btn-outline:hover {
            background: var(--primary);
            color: white;
        }

        .buttons {
            display: flex;
            gap: 0.75rem;
            align-items: center;
        }

        /* Main Content */
        .main-content {
            margin-top: 84px;
            min-height: calc(100vh - 200px);
        }

        /* Footer Styles */
        .footer {
            background: linear-gradient(135deg, var(--gray-900), var(--gray-800));
            color: white;
            padding: 4rem 0 2rem;
            position: relative;
        }

        .footer-content {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr;
            gap: 3rem;
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 1.5rem;
        }

        .footer-brand {
            display: flex;
            align-items: center;
            font-weight: 800;
            font-size: 1.75rem;
            margin-bottom: 1.5rem;
            color: white;
        }

        .footer-brand i {
            margin-right: 0.75rem;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .footer-description {
            color: var(--gray-400);
            margin-bottom: 2rem;
            line-height: 1.7;
            font-size: 1.05rem;
        }

        .footer-social {
            display: flex;
            gap: 1rem;
        }

        .social-link {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 44px;
            height: 44px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: var(--radius-lg);
            color: white;
            text-decoration: none;
            transition: var(--transition);
        }

        .social-link:hover {
            background: var(--primary);
        }

        .footer-section h3 {
            color: white;
            margin-bottom: 1.5rem;
            font-size: 1.25rem;
            font-weight: 700;
        }

        .footer-links {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .footer-links li {
            margin-bottom: 0.875rem;
        }

        .footer-links a {
            color: var(--gray-400);
            text-decoration: none;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .footer-links a:hover {
            color: white;
        }

        .footer-newsletter {
            display: flex;
            gap: 0.5rem;
            margin-top: 1rem;
        }

        .footer-newsletter input {
            width: 100%;
            padding: 0.875rem 1rem;
            border: 1px solid var(--gray-600);
            border-radius: var(--radius-lg);
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }

        .footer-newsletter input:focus {
            outline: none;
            border-color: var(--primary);
        }

        .footer-newsletter button {
            padding: 0.875rem 1.25rem;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            border: none;
            border-radius: var(--radius-lg);
            cursor: pointer;
            transition: var(--transition);
            font-weight: 600;
        }

        .footer-bottom {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1400px;
            margin: 3rem auto 0;
            padding: 2rem 1.5rem 0;
            border-top: 1px solid var(--gray-700);
        }

        .footer-copyright {
            color: var(--gray-500);
        }

        /* Toast Container */
        .toast-container {
            position: fixed;
            top: 100px;
            right: 20px;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .toast {
            padding: 1.25rem 1.5rem;
            border-radius: var(--radius-xl);
            background: white;
            box-shadow: var(--shadow-lg);
            display: flex;
            align-items: center;
            gap: 1rem;
            animation: toastIn 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            max-width: 380px;
            border-left: 4px solid;
        }

        .toast.toast-success {
            border-left-color: var(--success);
        }

        .toast.toast-error {
            border-left-color: var(--danger);
        }

        .toast.toast-warning {
            border-left-color: var(--warning);
        }

        .toast.toast-info {
            border-left-color: var(--info);
        }

        .toast-icon {
            font-size: 1.375rem;
        }

        .toast-message {
            flex: 1;
            color: var(--gray-700);
            font-weight: 500;
        }

        .toast-close {
            background: none;
            border: none;
            color: var(--gray-500);
            cursor: pointer;
            padding: 0.375rem;
            border-radius: var(--radius);
            transition: var(--transition);
        }

        @keyframes toastIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        /* Back to Top Button */
        .back-to-top {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            border: none;
            box-shadow: var(--shadow-lg);
            cursor: pointer;
            z-index: 999;
            opacity: 0;
            visibility: hidden;
            transform: translateY(20px);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .back-to-top.visible {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        /* Loading Spinner */
        .loading-spinner {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        }

        .spinner-icon {
            font-size: 3rem;
            color: var(--primary);
            margin-bottom: 1.5rem;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            from {
                transform: rotate(0deg);
            }
            to {
                transform: rotate(360deg);
            }
        }

        /* Responsive Design */
        @media (max-width: 1200px) {
            .navbar-content {
                padding: 1rem;
            }

            .footer-content {
                max-width: 1000px;
                grid-template-columns: 1fr 1fr 1fr;
                gap: 2rem;
            }

            .footer-content .footer-section:first-child {
                grid-column: 1 / -1;
            }
        }

        @media (max-width: 1024px) {
            .navbar-menu {
                gap: 1rem;
            }

            .navbar-start {
                gap: 1rem;
            }

            .footer-content {
                grid-template-columns: repeat(2, 1fr);
                gap: 2rem;
            }
        }

        @media (max-width: 768px) {
            .navbar-content {
                padding: 0.75rem 1rem;
            }

            .navbar-menu {
                position: fixed;
                top: 74px;
                left: 0;
                width: 100%;
                background: rgba(255, 255, 255, 0.98);
                backdrop-filter: blur(20px);
                flex-direction: column;
                padding: 2rem 1.5rem;
                box-shadow: var(--shadow-lg);
                transform: translateY(-100%);
                opacity: 0;
                visibility: hidden;
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            }

            .navbar-menu.active {
                transform: translateY(0);
                opacity: 1;
                visibility: visible;
            }

            .navbar-start {
                flex-direction: column;
                width: 100%;
                align-items: stretch;
                gap: 0.5rem;
                margin-bottom: 1.5rem;
            }

            .navbar-end {
                flex-direction: column;
                width: 100%;
                align-items: stretch;
                gap: 1rem;
            }

            .nav-link {
                justify-content: flex-start;
                padding: 1rem;
                border-radius: var(--radius-lg);
                font-size: 1rem;
            }

            .navbar-burger {
                display: flex;
            }

            .navbar-dropdown {
                position: static;
                width: 100%;
                box-shadow: none;
                border: 1px solid var(--gray-200);
                margin-top: 0.5rem;
                transform: none;
                opacity: 1;
                visibility: visible;
            }

            .main-content {
                margin-top: 74px;
            }

            .footer-content {
                grid-template-columns: 1fr;
                gap: 2rem;
                padding: 0 1rem;
            }

            .footer-bottom {
                flex-direction: column;
                gap: 1.5rem;
                text-align: center;
                padding: 2rem 1rem 0;
            }

            .footer-newsletter {
                flex-direction: column;
                gap: 0.75rem;
            }

            .toast-container {
                left: 1rem;
                right: 1rem;
                align-items: center;
                top: 90px;
            }

            .toast {
                max-width: 100%;
                margin: 0 auto;
            }

            .back-to-top {
                bottom: 1.5rem;
                right: 1.5rem;
                width: 48px;
                height: 48px;
            }
        }

        @media (max-width: 480px) {
            .navbar-brand {
                font-size: 1.25rem;
            }

            .navbar-brand i {
                font-size: 1.5rem;
                margin-right: 0.5rem;
            }

            .navbar-content {
                padding: 0.5rem 1rem;
            }

            .footer-content {
                padding: 0 0.75rem;
            }

            .footer-bottom {
                padding: 2rem 0.75rem 0;
            }

            .main-content {
                margin-top: 68px;
            }

            .btn {
                padding: 0.625rem 1.25rem;
                font-size: 0.8125rem;
            }

            .toast {
                padding: 1rem;
                font-size: 0.875rem;
            }
        }
    </style>
</head>

<body class="<?php echo trim($body_class); ?>">
    <!-- Loading Spinner -->
    <div id="loading-spinner" class="loading-spinner" style="display: none;">
        <div class="spinner-content">
            <i class="fas fa-spinner spinner-icon"></i>
            <p>Loading VR Experience...</p>
        </div>
    </div>

    <!-- Toast Container -->
    <div id="toast-container" class="toast-container"></div>

    <!-- Back to Top Button -->
    <button id="back-to-top" class="back-to-top" aria-label="Back to top">
        <i class="fas fa-arrow-up"></i>
    </button>

    <!-- Navigation -->
    <nav class="navbar" id="navbar" role="navigation" aria-label="Main navigation">
        <div class="navbar-content">
            <a href="<?php echo BASE_URL; ?>" class="navbar-brand" aria-label="VR Tours - Home">
                <i class="fas fa-vr-cardboard" aria-hidden="true"></i>
                <span>VR Tours</span>
            </a>

            <div class="navbar-menu" id="navbar-menu">
                <div class="navbar-start">
                    <a href="<?php echo BASE_URL; ?>index.php"
                        class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>"
                        aria-current="<?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'page' : 'false'; ?>">
                        <i class="fas fa-home" aria-hidden="true"></i>
                        <span>Home</span>
                    </a>
                    <a href="<?php echo BASE_URL; ?>tours.php"
                        class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'tours.php' ? 'active' : ''; ?>"
                        aria-current="<?php echo basename($_SERVER['PHP_SELF']) == 'tours.php' ? 'page' : 'false'; ?>">
                        <i class="fas fa-compass" aria-hidden="true"></i>
                        <span>Browse Tours</span>
                    </a>
                    <a href="<?php echo BASE_URL; ?>about.php"
                        class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'about.php' ? 'active' : ''; ?>"
                        aria-current="<?php echo basename($_SERVER['PHP_SELF']) == 'about.php' ? 'page' : 'false'; ?>">
                        <i class="fas fa-address-card" aria-hidden="true"></i>
                        <span>About</span>
                    </a>
                    <a href="<?php echo BASE_URL; ?>contact.php"
                        class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'contact.php' ? 'active' : ''; ?>"
                        aria-current="<?php echo basename($_SERVER['PHP_SELF']) == 'contact.php' ? 'page' : 'false'; ?>">
                        <i class="fas fa-address-book" aria-hidden="true"></i>
                        <span>Contact</span>
                    </a>
                </div>

                <div class="navbar-end">
                    <?php if (isUserLoggedIn()): 
                        $user_data = getUserData();
                    ?>
                        <div class="navbar-item has-dropdown">
                            <a class="nav-link" tabindex="0" role="button" aria-haspopup="true" aria-expanded="false">
                                <span class="user-avatar">
                                    <?php if (!empty($user_data['avatar'])): ?>
                                        <img src="<?php echo BASE_URL; ?>assets/images/uploads/<?php echo $user_data['avatar']; ?>"
                                            alt="<?php echo htmlspecialchars($user_data['username']); ?>" loading="lazy">
                                    <?php else: ?>
                                        <i class="fas fa-user-circle" aria-hidden="true"></i>
                                    <?php endif; ?>
                                </span>
                                <span><?php echo htmlspecialchars($user_data['username']); ?></span>
                            </a>
                            <div class="navbar-dropdown" role="menu">
                                <a href="<?php echo BASE_URL; ?>user/dashboard.php" class="dropdown-item" role="menuitem">
                                    <i class="fas fa-tachometer-alt" aria-hidden="true"></i> Dashboard
                                </a>
                                <a href="<?php echo BASE_URL; ?>user/profile.php" class="dropdown-item" role="menuitem">
                                    <i class="fas fa-user" aria-hidden="true"></i> Profile
                                </a>
                                <a href="<?php echo BASE_URL; ?>user/favorites.php" class="dropdown-item" role="menuitem">
                                    <i class="fas fa-heart" aria-hidden="true"></i> Favorites
                                </a>
                                <hr class="dropdown-divider" role="separator">
                                <a href="<?php echo BASE_URL; ?>logout.php" class="dropdown-item" role="menuitem">
                                    <i class="fas fa-sign-out-alt" aria-hidden="true"></i> Logout
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="navbar-item">
                            <div class="buttons">
                                <a href="<?php echo BASE_URL; ?>register.php" class="btn btn-outline">
                                    <i class="fas fa-user-plus" aria-hidden="true"></i>
                                    <span>Sign Up</span>
                                </a>
                                <a href="<?php echo BASE_URL; ?>login.php" class="btn btn-primary">
                                    <i class="fas fa-sign-in-alt" aria-hidden="true"></i>
                                    <span>Login</span>
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <button class="navbar-burger" id="navbar-burger" aria-label="Toggle navigation menu" aria-expanded="false">
                <span aria-hidden="true"></span>
                <span aria-hidden="true"></span>
                <span aria-hidden="true"></span>
            </button>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="main-content" role="main">