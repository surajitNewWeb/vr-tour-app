<?php
// logout.php
require_once 'includes/config.php';
require_once 'includes/user-auth.php';
require_once 'includes/database.php';

// Use the UserAuth class to logout
global $userAuth;
$userAuth->logout();

// Redirect to homepage
header("Location: index.php");
exit();