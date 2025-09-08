<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

$tour_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch tour details
$tour = $conn->query("SELECT * FROM vr_tours WHERE id = $tour_id")->fetch_assoc();
if (!$tour) {
    die("Tour not found.");
}

// Fetch all scenes
$scenes = $conn->query("SELECT * FROM vr_scenes WHERE tour_id = $tour_id");

// ✅ Check if user already favorited
$isFavorite = false;
if (isLoggedIn()) {
    $user_id = $_SESSION['user_id'];
    $checkFav = $conn->query("SELECT id FROM favorites WHERE user_id=$user_id AND tour_id=$tour_id");
    if ($checkFav && $checkFav->num_rows > 0) {
        $isFavorite = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?= $tour['name'] ?> - VR Tour</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
  <style>
    body { font-family: 'Poppins', sans-serif; }
  </style>
</head>
<body class="bg-gray-50 text-gray-800">

  <!-- 🌍 Hero Section -->
  <section class="relative bg-gradient-to-r from-purple-600 to-pink-600 text-white py-20">
    <div class="max-w-5xl mx-auto px-6 text-center">
      <h1 class="text-4xl font-bold mb-4"><?= $tour['name'] ?></h1>
      <p class="text-lg max-w-2xl mx-auto"><?= $tour['description'] ?></p>

      <!-- ⭐ Favorite Button -->
      <div class="mt-6">
        <?php if (isLoggedIn()): ?>
          <?php if ($isFavorite): ?>
            <!-- Remove Favorite -->
            <form method="POST" action="unfavorite.php" class="inline-block">
              <input type="hidden" name="tour_id" value="<?= $tour['id'] ?>">
              <button type="submit" class="bg-gray-600 text-white px-6 py-2 rounded-full text-lg font-medium hover:bg-gray-700 transition">
                ❌ Remove from Favorites
              </button>
            </form>
          <?php else: ?>
            <!-- Add Favorite -->
            <form method="POST" action="favorite.php" class="inline-block">
              <input type="hidden" name="tour_id" value="<?= $tour['id'] ?>">
              <button type="submit" class="bg-yellow-500 text-white px-6 py-2 rounded-full text-lg font-medium hover:bg-yellow-600 transition">
                ⭐ Add to Favorites
              </button>
            </form>
          <?php endif; ?>
        <?php else: ?>
          <p class="mt-4 text-sm">👉 <a href="login.php" class="underline hover:text-gray-200">Login</a> to save this tour to favorites.</p>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <!-- 🎥 Scenes Section -->
  <section class="max-w-6xl mx-auto px-6 py-16">
    <h2 class="text-3xl font-bold text-center mb-10">Available Scenes</h2>
    <div class="grid md:grid-cols-3 sm:grid-cols-2 gap-8">
      <?php while ($scene = $scenes->fetch_assoc()): ?>
        <div class="bg-white rounded-2xl shadow hover:shadow-lg transition overflow-hidden">
          <img src="<?= $scene['panorama_url'] ?>" alt="<?= $scene['title'] ?>" class="h-48 w-full object-cover">
          <div class="p-6">
            <h3 class="text-xl font-semibold mb-2"><?= $scene['title'] ?></h3>
            <?php if ($scene['audio_url']): ?>
              <audio controls class="w-full mb-4">
                <source src="<?= $scene['audio_url'] ?>" type="audio/mpeg">
                Your browser does not support the audio element.
              </audio>
            <?php endif; ?>
            <a href="scene.php?id=<?= $scene['id'] ?>" 
               class="bg-pink-600 text-white px-4 py-2 rounded-full text-sm font-medium hover:bg-pink-700 transition">
              View in VR
            </a>
          </div>
        </div>
      <?php endwhile; ?>
    </div>
  </section>

  <!-- ⚡ Footer -->
  <footer class="bg-gray-900 text-gray-300 py-8">
    <div class="max-w-6xl mx-auto px-6 flex flex-col md:flex-row justify-between items-center">
      <p>&copy; <?= date('Y') ?> VR World Tours. All rights reserved.</p>
      <div class="space-x-4 mt-4 md:mt-0">
        <a href="index.php" class="hover:text-white">Home</a>
        <a href="favorites.php" class="hover:text-white">My Favorites</a>
        <a href="#" class="hover:text-white">Contact</a>
      </div>
    </div>
  </footer>

</body>
</html>
