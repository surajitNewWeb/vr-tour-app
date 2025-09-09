<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/header.php';

// Get tour id from URL
$tour_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($tour_id <= 0) {
  die("<p>Invalid tour ID.</p>");
}

// Fetch tour info
$tour = $conn->query("SELECT * FROM tours WHERE id = $tour_id AND is_active = 1")->fetch_assoc();
if (!$tour) {
  die("<p>Tour not found.</p>");
}

// Fetch scenes for this tour
$scenes_result = $conn->query("SELECT * FROM scenes WHERE tour_id = $tour_id ORDER BY id ASC");
$scenes = [];
while ($row = $scenes_result->fetch_assoc()) {
  $scenes[] = $row;
}
?>
<link rel="stylesheet" href="assets/css/tour.css">

<!-- A-Frame VR Viewer -->
<a-scene embedded vr-mode-ui="enabled: true" loading-screen="enabled: true">
  <?php if (!empty($scenes)) : ?>
    <?php foreach ($scenes as $index => $scene) : ?>
      <a-sky 
        id="scene-<?= $scene['id'] ?>" 
        src="<?= htmlspecialchars($scene['panorama_url']) ?>" 
        rotation="0 -90 0"
        visible="<?= $index === 0 ? 'true' : 'false' ?>">
      </a-sky>

      <?php
      // Fetch hotspots for this scene
      $hotspots = $conn->query("SELECT * FROM hotspots WHERE scene_id = {$scene['id']}");
      while ($hotspot = $hotspots->fetch_assoc()) :
      ?>
        <a-entity
          class="hotspot"
          geometry="primitive: sphere; radius: 0.5"
          material="color: #00e6d1; opacity: 0.8"
          position="<?= $hotspot['position_x'] ?> <?= $hotspot['position_y'] ?> <?= $hotspot['position_z'] ?>"
          onclick="changeScene(<?= $hotspot['target_scene_id'] ?>)">
          <a-text value="<?= htmlspecialchars($hotspot['title']) ?>" 
                  align="center" 
                  color="#fff" 
                  position="0 0.7 0"
                  width="4">
          </a-text>
        </a-entity>
      <?php endwhile; ?>
    <?php endforeach; ?>
  <?php endif; ?>

  <!-- Camera / Controls -->
  <a-entity id="cameraRig">
    <a-camera look-controls="reverseMouseDrag: true">
      <a-cursor 
        id="cursor"
        fuse="true"
        fuse-timeout="1000"
        geometry="primitive: ring; radiusInner: 0.01; radiusOuter: 0.015"
        material="color: white; shader: flat"
        position="0 0 -1">
      </a-cursor>
    </a-camera>
  </a-entity>
</a-scene>

<!-- Tour Controls -->
<div class="tour-ui">
  <h2><?= htmlspecialchars($tour['title']) ?></h2>
  <p><?= htmlspecialchars($tour['description']) ?></p>
  <button onclick="toggleFullscreen()">🖥️ Fullscreen</button>
</div>

<script src="assets/js/tour.js"></script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
