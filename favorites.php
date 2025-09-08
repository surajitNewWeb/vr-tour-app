<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch user favorites
$sql = "SELECT vr_tours.* FROM favorites 
        JOIN vr_tours ON favorites.tour_id = vr_tours.id 
        WHERE favorites.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My Favorites - VR Tours</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
  <style>
    body { font-family: 'Poppins', sans-serif; }
  </style>
</head>
<body class="bg-gray-50 text-gray-800">

  <!-- 🌟 Hero -->
  <section class="bg-gradient-to-r from-pink-600 to-purple-600 text-white py-16">
    <div class="max-w-5xl mx-auto text-center px-6">
      <h1 class="text-4xl font-bold mb-4">⭐ My Favorite Tours</h1>
      <p class="text-lg">Here are the tours you’ve saved. Explore them anytime!</p>
    </div>
  </section>

  <!-- 💖 Favorites Grid -->
  <section class="max-w-6xl mx-auto px-6 py-16">
    <?php if ($result->num_rows > 0): ?>
      <div class="grid md:grid-cols-3 sm:grid-cols-2 gap-8">
        <?php while ($tour = $result->fetch_assoc()): ?>
          <div class="bg-white rounded-2xl shadow hover:shadow-lg transition overflow-hidden">
            <img src="<?= $tour['cover_image'] ?>" alt="<?= $tour['name'] ?>" class="h-48 w-full object-cover">
            <div class="p-6">
              <h3 class="text-xl font-semibold mb-2"><?= $tour['name'] ?></h3>
              <p class="text-sm text-gray-600 mb-4"><?= substr($tour['description'], 0, 100) ?>...</p>

              <!-- View Tour -->
              <a href="tour.php?id=<?= $tour['id'] ?>" 
                 class="bg-pink-600 text-white px-4 py-2 rounded-full text-sm font-medium hover:bg-pink-700 transition">
                View Tour
              </a>

              <!-- Remove Favorite -->
              <form method="POST" action="unfavorite.php" class="inline-block ml-2">
                <input type="hidden" name="tour_id" value="<?= $tour['id'] ?>">
                <button type="submit" class="bg-gray-600 text-white px-4 py-2 rounded-full text-sm font-medium hover:bg-gray-700 transition">
                  ❌ Remove
                </button>
              </form>
            </div>
          </div>
        <?php endwhile; ?>
      </div>
    <?php else: ?>
      <div class="text-center py-20">
        <p class="text-gray-600 text-lg">You haven’t added any favorites yet.</p>
        <a href="index.php" class="mt-4 inline-block bg-pink-600 text-white px-6 py-2 rounded-full hover:bg-pink-700 transition">
          Browse Tours
        </a>
      </div>
    <?php endif; ?>
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
