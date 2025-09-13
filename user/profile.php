<?php
// user/profile.php
require_once '../includes/config.php';
require_once '../includes/user-auth.php';
require_once '../includes/database.php';

if (!isUserLoggedIn()) {
    header('Location: ../login.php');
    exit;
}

$user_data = getUserData();
$errors = [];
$success = false;

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    
    // Validation and update logic
}

$page_title = "Profile Settings";
include '../includes/user-header.php';
?>

<div class="container">
    <h1>Profile Settings</h1>
    
    <div class="row">
        <div class="col-md-8">
            <!-- Profile form with validation -->
        </div>
        <div class="col-md-4">
            <!-- Avatar upload section -->
        </div>
    </div>
</div>

<?php include '../includes/user-footer.php'; ?>