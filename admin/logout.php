<?php
// admin/logout.php

// Include config first
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/database.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Generate new CSRF token before logging out
generateCSRFToken();

// Check if user is logged in before trying to logout
if (isset($_SESSION['admin_id'])) {
    // Log the logout action (optional)
    error_log("Admin user logged out: " . $_SESSION['admin_username']);
    
    // Logout admin
    logoutAdmin();
    
    // Set logout message
    $_SESSION['message'] = "You have been successfully logged out.";
} else {
    // User wasn't logged in but accessed logout page
    $_SESSION['error'] = "You were not logged in.";
}

// Redirect to login page
header("Location: index.php");
exit();
?>