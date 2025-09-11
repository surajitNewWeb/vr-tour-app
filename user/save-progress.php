<?php
require_once '../includes/config.php';
require_once '../includes/user-auth.php';
require_once '../includes/database.php';

if (!isUserLoggedIn()) {
    header('HTTP/1.1 401 Unauthorized');
    exit(json_encode(['success' => false, 'message' => 'Please login to save progress']));
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('HTTP/1.1 405 Method Not Allowed');
    exit(json_encode(['success' => false, 'message' => 'Method not allowed']));
}

$tour_id = intval($_POST['tour_id'] ?? 0);
$scene_id = intval($_POST['scene_id'] ?? 0);

// Validate tour and scene exist
$tour_stmt = $pdo->prepare("SELECT id FROM tours WHERE id = ? AND published = 1");
$tour_stmt->execute([$tour_id]);
$tour = $tour_stmt->fetch();

$scene_stmt = $pdo->prepare("SELECT id FROM scenes WHERE id = ? AND tour_id = ?");
$scene_stmt->execute([$scene_id, $tour_id]);
$scene = $scene_stmt->fetch();

if (!$tour || !$scene) {
    header('HTTP/1.1 404 Not Found');
    exit(json_encode(['success' => false, 'message' => 'Tour or scene not found']));
}

// Check if progress already exists
$check_stmt = $pdo->prepare("SELECT id FROM progress WHERE user_id = ? AND tour_id = ?");
$check_stmt->execute([$_SESSION['user_id'], $tour_id]);
$existing = $check_stmt->fetch();

if ($existing) {
    // Update existing progress
    $update_stmt = $pdo->prepare("
        UPDATE progress 
        SET last_scene_id = ?, updated_at = NOW() 
        WHERE user_id = ? AND tour_id = ?
    ");
    $update_stmt->execute([$scene_id, $_SESSION['user_id'], $tour_id]);
} else {
    // Create new progress
    $insert_stmt = $pdo->prepare("
        INSERT INTO progress (user_id, tour_id, last_scene_id, updated_at) 
        VALUES (?, ?, ?, NOW())
    ");
    $insert_stmt->execute([$_SESSION['user_id'], $tour_id, $scene_id]);
}

echo json_encode(['success' => true]);
?>