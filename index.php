<?php
require_once __DIR__ . '/includes/db.php';

// Fetch featured tours from DB
$tours = $conn->query("SELECT * FROM vr_tours LIMIT 6");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>VR World Tours</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <!-- TailwindCSS -->
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- Leaflet CSS & JS -->
  <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
  <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
  <!-- Fonts & Icons -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
  <style>
    body { font-family: 'Poppins', sans-serif; }
    #map { height: 400px; border-radius: 1rem; }
  </style>
</head>
<body class="bg-gray-50 text-gray-800">

  <!-- 🌍 Hero Section -->
  <section class="relative bg-gradient-to-r from-indigo-600 to-purple-700 text-white py-20">
    <div class="max-w-6xl mx-auto px-6 text-center">
      <h1 class="text-5xl font-bold mb-4">Explore the World in Virtual Reality</h1>
      <p class="text-lg mb-6">Walk through iconic landmarks and museums without leaving your home.</p>
      <a href="#tours" class="bg-white text-indigo-600 px-6 py-3 rounded-full font-semibold shadow hover:bg-gray-100 transition">
        Start Exploring
      </a>
    </div>
  </section>

  <!-- 🏛️ Featured Tours -->
  <section id="tours" class="max-w-6xl mx-auto px-6 py-16">
    <h2 class="text-3xl font-bold text-center mb-10">Featured VR Tours</h2>
    <div class="grid md:grid-cols-3 sm:grid-cols-2 gap-8">
      <?php while ($tour = $tours->fetch_assoc()): ?>
        <div class="bg-white rounded-2xl shadow hover:shadow-lg transition overflow-hidden">
          <img src="<?= $tour['thumbnail'] ?>" alt="<?= $tour['name'] ?>" class="h-48 w-full object-cover">
          <div class="p-6">
            <h3 class="text-xl font-semibold mb-2"><?= $tour['name'] ?></h3>
            <p class="text-sm text-gray-600 mb-4"><?= substr($tour['description'], 0, 100) ?>...</p>
            <a href="tour.php?id=<?= $tour['id'] ?>" 
               class="bg-indigo-600 text-white px-4 py-2 rounded-full text-sm font-medium hover:bg-indigo-700 transition">
              View Tour
            </a>
          </div>
        </div>
      <?php endwhile; ?>
    </div>
  </section>

  <!-- 🗺️ Interactive Map -->
  <section class="max-w-6xl mx-auto px-6 pb-16">
    <h2 class="text-3xl font-bold text-center mb-6">Explore on the Map</h2>
    <div id="map"></div>
  </section>

  <!-- ⚡ Footer -->
  <footer class="bg-gray-900 text-gray-300 py-8">
    <div class="max-w-6xl mx-auto px-6 flex flex-col md:flex-row justify-between items-center">
      <p>&copy; <?= date('Y') ?> VR World Tours. All rights reserved.</p>
      <div class="space-x-4 mt-4 md:mt-0">
        <a href="#" class="hover:text-white">Privacy</a>
        <a href="#" class="hover:text-white">Terms</a>
        <a href="#" class="hover:text-white">Contact</a>
      </div>
    </div>
  </footer>

  <!-- Map Script -->
  <script>
    var map = L.map('map').setView([20, 0], 2); // world view

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    <?php
    $result = $conn->query("SELECT id, name, latitude, longitude FROM vr_tours");
    while ($row = $result->fetch_assoc()):
    ?>
      L.marker([<?= $row['latitude'] ?>, <?= $row['longitude'] ?>])
        .addTo(map)
        .bindPopup("<b><?= $row['name'] ?></b><br><a href='tour.php?id=<?= $row['id'] ?>' class='text-indigo-600'>View Tour</a>");
    <?php endwhile; ?>
  </script>
</body>
</html>
