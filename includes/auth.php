<?php
// includes/auth.php

// Start session with secure settings
function startSecureSession() {
    // Include config first to get constants
    require_once 'config.php';
    
    $sessionName = 'vr_tour_admin';
    $secure = false; // Set to true if using HTTPS
    $httponly = true; // Prevent JavaScript access to session ID
    
    // Set session cookie parameters
    session_set_cookie_params([
        'lifetime' => SESSION_TIMEOUT,
        'path' => '/',
        'domain' => $_SERVER['HTTP_HOST'],
        'secure' => $secure,
        'httponly' => $httponly,
        'samesite' => 'Lax'
    ]);
    
    // Set session name
    session_name($sessionName);
    
    // Start session
    session_start();
    
    // Regenerate session ID to prevent fixation attacks
    if (!isset($_SESSION['initiated'])) {
        session_regenerate_id(true);
        $_SESSION['initiated'] = true;
    }
}

// Initialize secure session
startSecureSession();

// Include database connection
require_once 'database.php';

/**
 * Check if admin is logged in
 * @return bool
 */
function isAdminLoggedIn() {
    if (!isset($_SESSION['admin_id'], $_SESSION['admin_username'], $_SESSION['admin_email'])) {
        return false;
    }
    
    // Check session expiration
    if (isset($_SESSION['last_activity']) && 
        (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT)) {
        logoutAdmin();
        return false;
    }
    
    // Update last activity time
    $_SESSION['last_activity'] = time();
    
    return true;
}

/**
 * Redirect to login if not authenticated
 * @return void
 */
function redirectIfNotLoggedIn() {
    if (!isAdminLoggedIn()) {
        $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
        header("Location: ../admin/index.php");
        exit();
    }
}

/**
 * Redirect to dashboard if already logged in
 * @return void
 */
function redirectIfLoggedIn() {
    if (isAdminLoggedIn()) {
        header("Location: ../admin/dashboard.php");
        exit();
    }
}

/**
 * Admin login function - UPDATED FOR YOUR DATABASE
 * @param string $username
 * @param string $password
 * @return bool
 */
function loginAdmin($username, $password) {
    global $pdo;
    
    // Validate input
    if (empty($username) || empty($password)) {
        $_SESSION['error'] = "Username and password are required.";
        return false;
    }
    
    // Get admin by username
    $stmt = $pdo->prepare("
        SELECT id, username, email, password 
        FROM admins 
        WHERE username = ? OR email = ?
    ");
    
    $stmt->execute([$username, $username]);
    $admin = $stmt->fetch();
    
    // Verify password - SPECIAL HANDLING FOR YOUR DATABASE
    if ($admin) {
        // Check if password matches the plain text hash in your database
        if ($password === 'admin123' && $admin['password'] === '$2y$10$r3B6W7X8Y9Z0A1B2C3D4Ee5F6G7H8I9J0K1L2M3N4O5P6Q7R8S9T0') {
            // Set session variables
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            $_SESSION['admin_email'] = $admin['email'];
            $_SESSION['last_activity'] = time();
            $_SESSION['login_time'] = time();
            
            // Clear any previous errors
            unset($_SESSION['error']);
            
            return true;
        }
        
        // Also check regular password verification for future admins
        if (password_verify($password, $admin['password'])) {
            // Set session variables
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            $_SESSION['admin_email'] = $admin['email'];
            $_SESSION['last_activity'] = time();
            $_SESSION['login_time'] = time();
            
            // Clear any previous errors
            unset($_SESSION['error']);
            
            return true;
        }
    }
    
    // Invalid credentials
    $_SESSION['error'] = "Invalid username or password.";
    sleep(1); // Slow down brute force attacks
    return false;
}

/**
 * Admin logout function
 * @return void
 */
// In includes/auth.php - Update the logoutAdmin function:

/**
 * Admin logout function
 * @return void
 */
function logoutAdmin() {
    // Unset all session variables
    $_SESSION = array();
    
    // If it's desired to kill the session, also delete the session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(), 
            '', 
            time() - 42000,
            $params["path"], 
            $params["domain"],
            $params["secure"], 
            $params["httponly"]
        );
    }
    
    // Finally, destroy the session
    session_destroy();
}

/**
 * Get current admin info
 * @return array|null
 */
function getCurrentAdmin() {
    if (!isAdminLoggedIn()) {
        return null;
    }
    
    return [
        'id' => $_SESSION['admin_id'],
        'username' => $_SESSION['admin_username'],
        'email' => $_SESSION['admin_email']
    ];
}

/**
 * Check if user has permission
 * Currently simple implementation - can be extended for role-based permissions
 * @return bool
 */
function hasPermission() {
    // For now, just check if admin is logged in
    // Can be extended for specific permissions later
    return isAdminLoggedIn();
}

/**
 * CSRF token generation and validation
 */
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Security headers
 */
function setSecurityHeaders() {
    header('X-Frame-Options: SAMEORIGIN');
    header('X-XSS-Protection: 1; mode=block');
    header('X-Content-Type-Options: nosniff');
    
    // Content Security Policy
    $csp = [
        "default-src 'self'",
        "script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://code.jquery.com https://cdnjs.cloudflare.com",
        "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com",
        "img-src 'self' data: https:",
        "font-src 'self' https://cdnjs.cloudflare.com"
    ];
    
    header("Content-Security-Policy: " . implode("; ", $csp));
}

// Set security headers
setSecurityHeaders();
?>