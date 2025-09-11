<?php
// includes/user-auth.php

// Start session with secure settings
function startUserSession() {
    // Include config first to get constants
    require_once 'config.php';
    
    $sessionName = 'vr_tour_user';
    $secure = false; // Set to true if using HTTPS
    $httponly = true;
    
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
    if (!isset($_SESSION['user_initiated'])) {
        session_regenerate_id(true);
        $_SESSION['user_initiated'] = true;
    }
}

// Initialize secure session
startUserSession();

// Include database connection
require_once 'database.php';

/**
 * Check if user is logged in
 * @return bool
 */
function isUserLoggedIn() {
    if (!isset($_SESSION['user_id'], $_SESSION['user_username'], $_SESSION['user_email'])) {
        return false;
    }
    
    // Check session expiration
    if (isset($_SESSION['user_last_activity']) && 
        (time() - $_SESSION['user_last_activity'] > SESSION_TIMEOUT)) {
        logoutUser();
        return false;
    }
    
    // Update last activity time
    $_SESSION['user_last_activity'] = time();
    
    return true;
}

/**
 * Redirect to login if not authenticated
 * @return void
 */
function redirectIfUserNotLoggedIn() {
    if (!isUserLoggedIn()) {
        $_SESSION['user_redirect_url'] = $_SERVER['REQUEST_URI'];
        header("Location: login.php");
        exit();
    }
}

/**
 * Redirect to dashboard if already logged in
 * @return void
 */
function redirectIfUserLoggedIn() {
    if (isUserLoggedIn()) {
        header("Location: user/dashboard.php");
        exit();
    }
}

/**
 * User login function
 * @param string $username
 * @param string $password
 * @return bool
 */
function loginUser($username, $password) {
    global $pdo;
    
    // Validate input
    if (empty($username) || empty($password)) {
        $_SESSION['user_error'] = "Username and password are required.";
        return false;
    }
    
    // Get user by username or email
    $stmt = $pdo->prepare("
        SELECT id, username, email, password 
        FROM users 
        WHERE username = ? OR email = ?
    ");
    
    $stmt->execute([$username, $username]);
    $user = $stmt->fetch();
    
    // Verify password
    if ($user && password_verify($password, $user['password'])) {
        // Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_username'] = $user['username'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_last_activity'] = time();
        $_SESSION['user_login_time'] = time();
        
        // Clear any previous errors
        unset($_SESSION['user_error']);
        
        return true;
    }
    
    // Invalid credentials
    $_SESSION['user_error'] = "Invalid username or password.";
    sleep(1); // Slow down brute force attacks
    return false;
}

/**
 * User registration function
 * @param string $username
 * @param string $email
 * @param string $password
 * @return bool
 */
function registerUser($username, $email, $password) {
    global $pdo;
    
    // Validate input
    if (empty($username) || empty($email) || empty($password)) {
        $_SESSION['user_error'] = "All fields are required.";
        return false;
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['user_error'] = "Invalid email format.";
        return false;
    }
    
    if (strlen($password) < 6) {
        $_SESSION['user_error'] = "Password must be at least 6 characters.";
        return false;
    }
    
    // Check if username or email already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $email]);
    
    if ($stmt->fetch()) {
        $_SESSION['user_error'] = "Username or email already exists.";
        return false;
    }
    
    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert new user
    $stmt = $pdo->prepare("
        INSERT INTO users (username, email, password, created_at) 
        VALUES (?, ?, ?, NOW())
    ");
    
    if ($stmt->execute([$username, $email, $hashedPassword])) {
        // Auto-login after registration
        return loginUser($username, $password);
    }
    
    $_SESSION['user_error'] = "Error creating account. Please try again.";
    return false;
}

/**
 * User logout function
 * @return void
 */
function logoutUser() {
    // Unset all session variables
    unset($_SESSION['user_id']);
    unset($_SESSION['user_username']);
    unset($_SESSION['user_email']);
    unset($_SESSION['user_last_activity']);
    unset($_SESSION['user_login_time']);
    
    // Destroy session cookie
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
    
    // Destroy session
    session_destroy();
}

/**
 * Get current user info
 * @return array|null
 */
function getCurrentUser() {
    if (!isUserLoggedIn()) {
        return null;
    }
    
    return [
        'id' => $_SESSION['user_id'],
        'username' => $_SESSION['user_username'],
        'email' => $_SESSION['user_email']
    ];
}