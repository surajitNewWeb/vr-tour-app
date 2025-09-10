<?php
// minimal loader page - frontend VR will fetch /api/tour.php?id=...
$id = intval($_GET['id'] ?? 0);
if (!$id) { echo "Tour id required"; exit; }
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><title>VR Tour</title></head>
<body>
  <div id="app">Loading tour...</div>
  <script>
  (async ()=>{
    const id = <?= $id ?>;
    const res = await fetch('/api/tour.php?id=' + id);
    const data = await res.json();
    document.getElementById('app').innerText = JSON.stringify(data, null, 2);
    // Your A-Frame/Three.js frontend will parse this JSON and render panoramas & hotspots.
  })();
  </script>
</body></html>
