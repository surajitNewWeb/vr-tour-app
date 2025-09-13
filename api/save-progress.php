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
$scene_id = intval($data['scene_id']);

// Check if progress exists
$database->query("SELECT id FROM progress WHERE user_id = :user_id AND tour_id = :tour_id");
$database->bind(':user_id', $user_id);
$database->bind(':tour_id', $tour_id);
$progress = $database->single();

if ($progress) {
    // Update existing progress
    $database->query("UPDATE progress SET last_scene_id = :scene_id, updated_at = NOW() WHERE id = :id");
    $database->bind(':scene_id', $scene_id);
    $database->bind(':id', $progress['id']);
} else {
    // Create new progress
    $database->query("INSERT INTO progress (user_id, tour_id, last_scene_id, updated_at) VALUES (:user_id, :tour_id, :scene_id, NOW())");
    $database->bind(':user_id', $user_id);
    $database->bind(':tour_id', $tour_id);
    $database->bind(':scene_id', $scene_id);
}

$success = $database->execute();
echo json_encode(['success' => $success]);
?>