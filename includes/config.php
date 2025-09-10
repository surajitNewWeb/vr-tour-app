<?php
// includes/config.php

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'vr_tour_app');
define('DB_USER', 'root');
define('DB_PASS', '');

// Application settings
define('SITE_NAME', 'VR Tour Application');
define('ADMIN_EMAIL', 'admin@vrtour.com');

// Session settings
define('SESSION_TIMEOUT', 3600); // 1 hour

// File upload paths
define('PANORAMA_UPLOAD_PATH', '../assets/panoramas/uploads/');
define('THUMBNAIL_UPLOAD_PATH', '../assets/images/uploads/');
define('AVATAR_UPLOAD_PATH', '../assets/images/uploads/');

// Maximum file sizes (in bytes)
define('MAX_PANORAMA_SIZE', 10485760); // 10MB
define('MAX_THUMBNAIL_SIZE', 2097152); // 2MB
define('MAX_AVATAR_SIZE', 1048576); // 1MB

// Allowed file types
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif']);
define('ALLOWED_PANORAMA_TYPES', ['jpg', 'jpeg', 'png']);

// Debug mode (set to false in production)
define('DEBUG_MODE', true);

// Error reporting
if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Set default timezone
date_default_timezone_set('UTC');
?>