<?php
// user/favorite-status.php
require_once '../includes/config.php';
require_once '../includes/user-auth.php';
require_once '../includes/database.php';

if (!isUserLoggedIn()) {
    header('Content-Type: application/json');
    echo json_encode(['isFavorited' => false]);
    exit();
}

$user = getUserData();
$tourId = isset($_GET['tour_id']) ? intval($_GET['tour_id']) : 0;

// Check if tour is favorited
$stmt = $pdo->prepare("SELECT id FROM favorites WHERE user_id = ? AND tour_id = ?");
$stmt->execute([$user['id'], $tourId]);
$isFavorited = $stmt->fetch() ? true : false;

header('Content-Type: application/json');
echo json_encode(['isFavorited' => $isFavorited]);
?>