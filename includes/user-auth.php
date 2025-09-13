<?php
// includes/user-auth.php

// Include the database connection
require_once 'database.php';

class UserAuth {
    private $db;
    
    public function __construct() {
        global $pdo;
        $this->db = $pdo;
        $this->initSession();
    }
    
    private function initSession() {
        if (session_status() == PHP_SESSION_NONE) {
            session_set_cookie_params([
                'lifetime' => SESSION_TIMEOUT,
                'path' => '/',
                'domain' => $_SERVER['HTTP_HOST'],
                'secure' => isset($_SERVER['HTTPS']),
                'httponly' => true,
                'samesite' => 'Strict'
            ]);
            session_start();
        }
        
        // Regenerate session ID periodically for security
        if (!isset($_SESSION['last_regeneration'])) {
            session_regenerate_id(true);
            $_SESSION['last_regeneration'] = time();
        } elseif (time() - $_SESSION['last_regeneration'] > 1800) { // 30 minutes
            session_regenerate_id(true);
            $_SESSION['last_regeneration'] = time();
        }
    }
    
    public function login($email, $password) {
        try {
            $stmt = $this->db->prepare("SELECT id, username, email, password, avatar FROM users WHERE email = :email");
            $stmt->execute([':email' => $email]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                // Password is correct, set up session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_username'] = $user['username'];
                $_SESSION['user_avatar'] = $user['avatar'];
                $_SESSION['user_logged_in'] = true;
                $_SESSION['last_activity'] = time();
                
                // Update last login
                $stmt = $this->db->prepare("UPDATE users SET last_login = NOW() WHERE id = :id");
                $stmt->execute([':id' => $user['id']]);
                
                return true;
            }
            
            return false;
        } catch (PDOException $e) {
            error_log("Login error: " . $e->getMessage());
            return false;
        }
    }
    
    public function register($username, $email, $password) {
        try {
            // Check if user already exists
            $stmt = $this->db->prepare("SELECT id FROM users WHERE email = :email OR username = :username");
            $stmt->execute([':email' => $email, ':username' => $username]);
            
            if ($stmt->fetch()) {
                return false; // User already exists
            }
            
            // Hash password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert new user
            $stmt = $this->db->prepare("INSERT INTO users (username, email, password, created_at) VALUES (:username, :email, :password, NOW())");
            $stmt->execute([
                ':username' => $username,
                ':email' => $email,
                ':password' => $hashedPassword
            ]);
            
            if ($stmt->rowCount() > 0) {
                return $this->login($email, $password);
            }
            
            return false;
        } catch (PDOException $e) {
            error_log("Registration error: " . $e->getMessage());
            return false;
        }
    }
    
    public function isLoggedIn() {
        if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true) {
            // Check session expiration
            if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT)) {
                $this->logout();
                return false;
            }
            
            // Update last activity time
            $_SESSION['last_activity'] = time();
            return true;
        }
        return false;
    }
    
    public function logout() {
        // Unset all session variables
        $_SESSION = array();
        
        // Delete session cookie
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        // Destroy the session
        session_destroy();
        
        return true;
    }
    
    public function getUserData() {
        if ($this->isLoggedIn()) {
            return [
                'id' => $_SESSION['user_id'],
                'username' => $_SESSION['user_username'],
                'email' => $_SESSION['user_email'],
                'avatar' => $_SESSION['user_avatar']
            ];
        }
        return null;
    }
}

// Initialize user authentication
$userAuth = new UserAuth();

// Helper functions
function isUserLoggedIn() {
    global $userAuth;
    return $userAuth->isLoggedIn();
}

function getUserData() {
    global $userAuth;
    return $userAuth->getUserData();
}

function time_ago($datetime, $full = false) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);
    
    // Calculate weeks manually instead of using dynamic property
    $weeks = floor($diff->d / 7);
    $days = $diff->d - ($weeks * 7);
    
    $string = array(
        'y' => 'year',
        'm' => 'month',
        'w' => 'week',
        'd' => 'day',
        'h' => 'hour',
        'i' => 'minute',
        's' => 'second',
    );
    
    // Build the values array manually
    $values = [
        'y' => $diff->y,
        'm' => $diff->m,
        'w' => $weeks,
        'd' => $days,
        'h' => $diff->h,
        'i' => $diff->i,
        's' => $diff->s,
    ];
    
    $result = array();
    foreach ($string as $k => $v) {
        if ($values[$k]) {
            $result[] = $values[$k] . ' ' . $v . ($values[$k] > 1 ? 's' : '');
        }
    }
    
    if (!$full) {
        $result = array_slice($result, 0, 1);
    }
    
    return $result ? implode(', ', $result) . ' ago' : 'just now';
}
?>