<?php
// vr/scene.php
require_once '../includes/config.php';
require_once '../includes/user-auth.php';
require_once '../includes/database.php';

header('Content-Type: application/json');

$scene_id = intval($_GET['id'] ?? 0);
$user_id = $_SESSION['user_id'] ?? null;

$scene_stmt = $pdo->prepare("SELECT * FROM scenes WHERE id = ?");
$scene_stmt->execute([$scene_id]);
$scene = $scene_stmt->fetch();

if (!$scene) {
    echo json_encode(['error' => 'Scene not found']);
    exit;
}

// Fetch hotspots
$hotspot_stmt = $pdo->prepare("
    SELECT h.*, s.name as target_scene_name
    FROM hotspots h
    LEFT JOIN scenes s ON h.target_scene_id = s.id
    WHERE h.scene_id = ?
");
$hotspot_stmt->execute([$scene_id]);
$hotspots = $hotspot_stmt->fetchAll();

// Save progress if user logged in
if ($user_id) {
    $progress_stmt = $pdo->prepare("
        INSERT INTO progress (user_id, tour_id, last_scene_id, completed)
        VALUES (?, ?, ?, 0)
        ON DUPLICATE KEY UPDATE last_scene_id = VALUES(last_scene_id), updated_at = NOW()
    ");
    $progress_stmt->execute([$user_id, $scene['tour_id'], $scene['id']]);
}

echo json_encode([
    'scene' => $scene,
    'hotspots' => $hotspots
]);
