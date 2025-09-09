<?php
// admin/index.php
session_start();
require_once __DIR__ . '/../app/controllers/admincontroller.php';
require_once __DIR__ . '/../config/database.php';

$admin = new AdminController($pdo);

// Fetch counts for dashboard
$total_users = count($admin->getUsers());
$total_tours = count($admin->getAllTours());
$total_media = count($admin->getAllMedia());

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <h1>VR Tour Admin Dashboard</h1>
    <nav>
        <a href="index.php">Dashboard</a> |
        <a href="tours.php">Tours</a> |
        <a href="scenes.php">Scenes</a> |
        <a href="hotspots.php">Hotspots</a> |
        <a href="media.php">Media</a> |
        <a href="users.php">Users</a> |
        <a href="../public/login.php?logout=1">Logout</a>
    </nav>

    <hr>

    <div class="dashboard-cards">
        <div class="card">
            <h2><?php echo $total_users; ?></h2>
            <p>Total Users</p>
        </div>
        <div class="card">
            <h2><?php echo $total_tours; ?></h2>
            <p>Total Tours</p>
        </div>
        <div class="card">
            <h2><?php echo $total_media; ?></h2>
            <p>Total Media Files</p>
        </div>
    </div>
</body>
</html>
