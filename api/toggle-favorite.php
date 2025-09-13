<?php
require_once '../includes/config.php';
require_once '../includes/user-auth.php';
require_once '../includes/database.php';

header('Content-Type: application/json');

if (!isUserLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];
$data = json_decode(file_get_contents('php://input'), true);
$tour_id = intval($data['tour_id']);
$action = $data['action'];

if ($action === 'add') {
    $database->query("INSERT INTO favorites (user_id, tour_id, created_at) VALUES (:user_id, :tour_id, NOW())");
    $database->bind(':user_id', $user_id);
    $database->bind(':tour_id', $tour_id);
    $success = $database->execute();
} else {
    $database->query("DELETE FROM favorites WHERE user_id = :user_id AND tour_id = :tour_id");
    $database->bind(':user_id', $user_id);
    $database->bind(':tour_id', $tour_id);
    $success = $database->execute();
}

echo json_encode(['success' => $success]);
?>