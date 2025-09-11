<?php
// vr/tour.php
require_once '../includes/config.php';
require_once '../includes/user-auth.php';
require_once '../includes/database.php';

$tour_id = intval($_GET['id'] ?? 0);
$user_id = $_SESSION['user_id'] ?? null;

// Validate tour
$tour_stmt = $pdo->prepare("
    SELECT t.*, u.username as creator_name
    FROM tours t
    LEFT JOIN users u ON t.created_by = u.id
    WHERE t.id = ? AND t.published = 1
");
$tour_stmt->execute([$tour_id]);
$tour = $tour_stmt->fetch();

if (!$tour) {
    http_response_code(404);
    die("Tour not found or unpublished.");
}

// Fetch first scene
$scene_stmt = $pdo->prepare("SELECT * FROM scenes WHERE tour_id = ? ORDER BY id ASC LIMIT 1");
$scene_stmt->execute([$tour_id]);
$scene = $scene_stmt->fetch();

if (!$scene) {
    http_response_code(404);
    die("No scenes available for this tour.");
}

// Check if tour is in user's favorites
$is_favorite = false;
if ($user_id) {
    $fav_stmt = $pdo->prepare("SELECT 1 FROM favorites WHERE user_id = ? AND tour_id = ?");
    $fav_stmt->execute([$user_id, $tour_id]);
    $is_favorite = (bool)$fav_stmt->fetch();
}

// Verify panorama file
$panoramaPath = "../assets/panoramas/" . $scene['panorama'];
if (!file_exists(__DIR__ . "/../assets/panoramas/" . $scene['panorama'])) {
    die("Panorama not found: " . htmlspecialchars($scene['panorama']));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($tour['title']) ?> | VR Tour</title>
  <link rel="stylesheet" href="../assets/css/vr.css">
  <script src="https://aframe.io/releases/1.4.0/aframe.min.js"></script>
  <script src="../assets/js/vr.js" defer></script>
</head>
<body>
  <div id="topbar">
    <h2><?= htmlspecialchars($tour['title']) ?></h2>
    <?php if ($user_id): ?>
      <button id="favoriteBtn" class="<?= $is_favorite ? 'active' : '' ?>">
        <?= $is_favorite ? '★ Favorited' : '☆ Favorite' ?>
      </button>
    <?php endif; ?>
  </div>

  <div id="loading">Loading VR Tour...</div>

  <a-scene id="vrScene" embedded vr-mode-ui="enabled: true">
    <a-sky id="panorama" src="<?= $panoramaPath ?>"></a-sky>

    <a-entity id="cameraRig">
      <a-camera id="camera" look-controls wasd-controls>
        <a-cursor id="cursor" color="#FFD700" fuse="true" fuse-timeout="1200"></a-cursor>
      </a-camera>
    </a-entity>

    <a-entity id="hotspots"></a-entity>
  </a-scene>

  <script>
    const TOUR_ID = <?= (int)$tour_id ?>;
    const USER_ID = <?= $user_id ? (int)$user_id : 'null' ?>;
    let currentSceneId = <?= (int)$scene['id'] ?>;
  </script>
</body>
</html>
