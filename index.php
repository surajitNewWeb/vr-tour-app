<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

$pageTitle = 'Home';
$featuredTours = getAllTours(3, true);

ob_start();
?>
<!-- Hero Section -->
<section class="hero">
    <div class="hero-content">
        <h1>Explore Worlds Without Limits</h1>
        <p>Experience museums, landmarks, and exotic locations through immersive virtual reality tours from the comfort of your home.</p>
        <div class="cta-buttons">
            <a href="<?php echo BASE_URL; ?>tours/" class="btn btn-primary">Explore Tours</a>
            <?php if (isLoggedIn()): ?>
                <a href="<?php echo BASE_URL; ?>admin/tours.php" class="btn btn-secondary">Create Tour</a>
            <?php else: ?>
                <a href="<?php echo BASE_URL; ?>auth/register.php" class="btn btn-secondary">Get Started</a>
            <?php endif; ?>
        </div>
    </div>
    <div class="hero-visual">
        <div class="vr-headset">
            <i class="fas fa-vr-cardboard"></i>
        </div>
    </div>
</section>

<!-- Featured Tours -->
<section class="featured-tours">
    <h2>Featured Virtual Tours</h2>
    <div class="tours-grid">
        <?php if (!empty($featuredTours)): ?>
            <?php foreach ($featuredTours as $tour): ?>
                <div class="tour-card">
                    <div class="tour-image" style="background-image: url('<?php echo BASE_URL . ($tour['thumbnail_url'] ?: 'assets/images/default-tour.jpg'); ?>')"></div>
                    <div class="tour-content">
                        <h3><?php echo htmlspecialchars($tour['title']); ?></h3>
                        <p><?php echo htmlspecialchars($tour['short_description'] ?: substr($tour['description'], 0, 100) . '...'); ?></p>
                        <div class="tour-meta">
                            <span>By <?php echo htmlspecialchars($tour['author_name']); ?></span>
                            <span><?php echo date('M j, Y', strtotime($tour['created_at'])); ?></span>
                        </div>
                        <a href="<?php echo BASE_URL; ?>tours/view.php?id=<?php echo $tour['id']; ?>" class="btn btn-primary btn-sm">View Tour</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="no-tours">No featured tours available. Check back later!</p>
        <?php endif; ?>
    </div>
    <div class="section-cta">
        <a href="<?php echo BASE_URL; ?>tours/" class="btn btn-secondary">View All Tours</a>
    </div>
</section>

<!-- How it Works -->
<section class="how-it-works">
    <h2>How It Works</h2>
    <div class="steps">
        <div class="step">
            <div class="step-icon">1</div>
            <h3>Choose a Tour</h3>
            <p>Browse our collection of virtual experiences from around the world.</p>
        </div>
        <div class="step">
            <div class="step-icon">2</div>
            <h3>Enter VR Mode</h3>
            <p>Use any VR headset or explore directly from your browser.</p>
        </div>
        <div class="step">
            <div class="step-icon">3</div>
            <h3>Explore & Interact</h3>
            <p>Navigate through spaces and interact with informational hotspots.</p>
        </div>
    </div>
</section>
<?php
$content = ob_get_clean();
include 'includes/header.php';
echo $content;
include 'includes/footer.php';
?>