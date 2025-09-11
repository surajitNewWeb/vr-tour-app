<?php
// logout.php
require_once 'includes/config.php';
require_once 'includes/user-auth.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in before trying to logout
if (isset($_SESSION['user_id'])) {
    logoutUser();
    $_SESSION['user_message'] = "You have been successfully logged out.";
} else {
    $_SESSION['user_error'] = "You were not logged in.";
}

// Redirect to home page
header("Location:index.php");
exit();