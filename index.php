<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/header.php';
?>

<!-- 🌌 Hero Section -->
<section class="hero">
  <div class="hero-overlay">
    <div class="hero-content">
      <h1>Explore the World in <span>Virtual Reality</span></h1>
      <p>Step inside museums, landmarks, and nature tours — all from your browser.</p>
      <a href="tours.php" class="btn-primary">Start Exploring</a>
    </div>
  </div>
</section>

<!-- 🌟 Featured Tours -->
<section class="featured">
  <div class="container">
    <h2>Featured VR Tours</h2>
    <div class="tour-grid">
      <?php
      $tours = $conn->query("SELECT * FROM tours WHERE is_featured = 1 AND is_active = 1 LIMIT 6");
      if ($tours && $tours->num_rows > 0) {
        while ($tour = $tours->fetch_assoc()) {
          echo "
          <div class='tour-card'>
            <img src='{$tour['thumbnail_url']}' alt='{$tour['title']}'>
            <div class='tour-info'>
              <h3>{$tour['title']}</h3>
              <p>{$tour['short_description']}</p>
              <a href='tour.php?id={$tour['id']}' class='btn-secondary'>View Tour</a>
            </div>
          </div>";
        }
      } else {
        echo "<p>No featured tours available.</p>";
      }
      ?>
    </div>
  </div>
</section>

<!-- 📌 Categories -->
<section class="categories">
  <div class="container">
    <h2>Browse by Category</h2>
    <div class="category-grid">
      <a href="tours.php?category=museum" class="category-card">🏛️ Museums</a>
      <a href="tours.php?category=landmark" class="category-card">🌍 Landmarks</a>
      <a href="tours.php?category=nature" class="category-card">🌲 Nature</a>
      <a href="tours.php?category=historical" class="category-card">📜 Historical</a>
      <a href="tours.php?category=educational" class="category-card">📘 Educational</a>
      <a href="tours.php?category=entertainment" class="category-card">🎭 Entertainment</a>
    </div>
  </div>
</section>

<!-- 🙋 Call to Action -->
<section class="cta">
  <div class="container">
    <h2>Want to create your own VR Tour?</h2>
    <p>Join our community of creators and showcase your immersive experiences.</p>
    <a href="register.php" class="btn-primary">Get Started</a>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
