<?php
require_once __DIR__ . '/includes/db.php';

$scene_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch scene details
$scene = $conn->query("SELECT * FROM vr_scenes WHERE id = $scene_id")->fetch_assoc();
if (!$scene) {
    die("Scene not found.");
}

// Fetch hotspots
$hotspots = $conn->query("SELECT * FROM vr_hotspots WHERE scene_id = $scene_id");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?= $scene['title'] ?> - VR Tour</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <!-- A-Frame -->
  <script src="https://aframe.io/releases/1.4.2/aframe.min.js"></script>
  <!-- Tailwind for UI overlay -->
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    body, html { margin: 0; height: 100%; overflow: hidden; }
    .ui-overlay { position: absolute; top: 10px; left: 10px; z-index: 999; }
  </style>
</head>
<body>

  <!-- UI Overlay -->
  <div class="ui-overlay">
    <a href="tour.php?id=<?= $scene['tour_id'] ?>" 
       class="bg-white text-gray-800 px-4 py-2 rounded shadow hover:bg-gray-100 transition">
      ← Back to Tour
    </a>
  </div>

  <!-- 🎥 VR Scene -->
  <a-scene>
    <!-- Background panorama -->
    <a-sky src="<?= $scene['panorama_url'] ?>" rotation="0 -90 0"></a-sky>

    <!-- 🎧 Audio Guide -->
    <?php if (!empty($scene['audio_url'])): ?>
      <a-sound src="<?= $scene['audio_url'] ?>" autoplay="true" loop="false" position="0 1.6 -1"></a-sound>
    <?php endif; ?>

    <!-- 🔥 Hotspots -->
    <?php while ($hotspot = $hotspots->fetch_assoc()): ?>
      <a-entity
        geometry="primitive: circle; radius: 0.3"
        material="color: red; opacity: 0.8"
        position="<?= $hotspot['pos_x'] ?> <?= $hotspot['pos_y'] ?> <?= $hotspot['pos_z'] ?>"
        class="clickable"
        event-set__mouseenter="scale: 1.2 1.2 1.2"
        event-set__mouseleave="scale: 1 1 1"
        <?php if ($hotspot['link_scene_id']): ?>
          onclick="window.location.href='scene.php?id=<?= $hotspot['link_scene_id'] ?>'"
        <?php else: ?>
          onclick="alert('<?= addslashes($hotspot['description']) ?>')"
        <?php endif; ?>
      >
      </a-entity>
    <?php endwhile; ?>

    <!-- Camera + Cursor -->
    <a-entity camera look-controls>
      <a-entity cursor="fuse: true; fuseTimeout: 1000"
                position="0 0 -1"
                geometry="primitive: ring; radiusInner: 0.02; radiusOuter: 0.03"
                material="color: white; shader: flat">
      </a-entity>
    </a-entity>
  </a-scene>

</body>
</html>
