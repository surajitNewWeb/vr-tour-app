<?php
require_once '../includes/config.php';
require_once '../includes/user-auth.php';
require_once '../includes/database.php';

header('Content-Type: application/json');

if (!isUserLoggedIn()) {
    echo json_encode(['is_favorite' => false]);
    exit;
}

$user_id = $_SESSION['user_id'];
$data = json_decode(file_get_contents('php://input'), true);
$tour_id = intval($data['tour_id']);

$database->query("SELECT id FROM favorites WHERE user_id = :user_id AND tour_id = :tour_id");
$database->bind(':user_id', $user_id);
$database->bind(':tour_id', $tour_id);
$favorite = $database->single();

echo json_encode(['is_favorite' => !empty($favorite)]);
?>