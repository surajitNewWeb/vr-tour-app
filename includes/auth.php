<?php
// Check if user is logged in
function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Check if user is admin
function is_admin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

// Simple login function that will definitely work
function login($email, $password) {
    global $conn;
    
    $email = mysqli_real_escape_string($conn, trim($email));
    
    // First check admin table
    $query = "SELECT * FROM admins WHERE email = '$email' LIMIT 1";
    $result = mysqli_query($conn, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        
        // For testing: If password is 'admin123' (plain text), accept it
        if ($password === 'admin123' || password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_name'] = $user['username'];
            $_SESSION['user_role'] = 'admin';
            return true;
        }
    }
    
    return false;
}

// Logout function
function logout() {
    $_SESSION = array();
    session_destroy();
}

// Require admin authentication
function require_admin() {
    if (!is_logged_in() || !is_admin()) {
        header('Location: login.php');
        exit;
    }
}
?>