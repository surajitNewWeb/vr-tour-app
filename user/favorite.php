<?php
// user/favorite.php
require_once '../includes/config.php';
require_once '../includes/user-auth.php';
require_once '../includes/database.php';

redirectIfUserNotLoggedIn();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tour_id = intval($_POST['tour_id']);
    $action = $_POST['action'] ?? 'toggle';
    
    // Check if tour exists and is published
    $tour_stmt = $pdo->prepare("SELECT id FROM tours WHERE id = ? AND published = 1");
    $tour_stmt->execute([$tour_id]);
    
    if (!$tour_stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Tour not found']);
        exit();
    }
    
    // Check if already favorited
    $fav_stmt = $pdo->prepare("SELECT id FROM favorites WHERE user_id = ? AND tour_id = ?");
    $fav_stmt->execute([$_SESSION['user_id'], $tour_id]);
    $existing_fav = $fav_stmt->fetch();
    
    if ($action === 'add' || ($action === 'toggle' && !$existing_fav)) {
        // Add to favorites
        $insert_stmt = $pdo->prepare("
            INSERT INTO favorites (user_id, tour_id, created_at) 
            VALUES (?, ?, NOW())
        ");
        
        if ($insert_stmt->execute([$_SESSION['user_id'], $tour_id])) {
            echo json_encode(['success' => true, 'status' => 'added']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error adding to favorites']);
        }
        
    } elseif ($action === 'remove' || ($action === 'toggle' && $existing_fav)) {
        // Remove from favorites
        $delete_stmt = $pdo->prepare("DELETE FROM favorites WHERE user_id = ? AND tour_id = ?");
        
        if ($delete_stmt->execute([$_SESSION['user_id'], $tour_id])) {
            echo json_encode(['success' => true, 'status' => 'removed']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error removing from favorites']);
        }
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>