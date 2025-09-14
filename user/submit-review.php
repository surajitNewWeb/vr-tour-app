<?php
// user/submit-review.php

// Start output buffering to prevent header errors
ob_start();

require_once '../includes/config.php';
require_once '../includes/user-auth.php';
require_once '../includes/database.php';

// Check if user is logged in before any output
if (!isUserLoggedIn()) {
    // Clear buffer and redirect
    ob_end_clean();
    header("Location: ../login.php");
    exit();
}

$user = getUserData();
$tourId = isset($_GET['tour_id']) ? intval($_GET['tour_id']) : 0;

// Get tour details
$tour = [];
if ($tourId) {
    $stmt = $pdo->prepare("SELECT id, title FROM tours WHERE id = ? AND published = 1");
    $stmt->execute([$tourId]);
    $tour = $stmt->fetch();
}

if (!$tour) {
    // Clear buffer and redirect
    ob_end_clean();
    header("Location: ../tours.php");
    exit();
}

// Check if user already reviewed this tour
$stmt = $pdo->prepare("SELECT id FROM reviews WHERE tour_id = ? AND user_id = ?");
$stmt->execute([$tourId, $user['id']]);
$existingReview = $stmt->fetch();

// Handle form submission
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$existingReview) {
    $rating = isset($_POST['rating']) ? intval($_POST['rating']) : 0;
    $comment = isset($_POST['comment']) ? trim($_POST['comment']) : '';
    
    // Validation
    if ($rating < 1 || $rating > 5) {
        $errors[] = "Please select a rating between 1 and 5 stars.";
    }
    
    if (empty($comment)) {
        $errors[] = "Please write a review comment.";
    } elseif (strlen($comment) < 10) {
        $errors[] = "Review comment must be at least 10 characters long.";
    }
    
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO reviews (tour_id, user_id, rating, comment, created_at) 
                VALUES (?, ?, ?, ?, NOW())
            ");
            $stmt->execute([$tourId, $user['id'], $rating, $comment]);
            
            $success = true;
            $_SESSION['success'] = "Thank you for your review! It will be visible after approval.";
            
            // Clear buffer and redirect
            ob_end_clean();
            header("Location: ../tours.php?id=" . $tourId);
            exit();
        } catch (PDOException $e) {
            $errors[] = "An error occurred while submitting your review. Please try again.";
        }
    }
}

// Now we can output content
require_once '../includes/user-header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Write a Review</h4>
                </div>
                <div class="card-body">
                    <h5 class="card-title"><?= htmlspecialchars($tour['title']) ?></h5>
                    
                    <?php if ($existingReview): ?>
                        <div class="alert alert-info">
                            <p>You have already submitted a review for this tour.</p>
                            <a href="../tours.php?id=<?= $tourId ?>" class="btn btn-primary">Back to Tour</a>
                        </div>
                    <?php else: ?>
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?= $error ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                        
                        <form method="post" action="">
                            <div class="mb-4">
                                <label class="form-label">Your Rating</label>
                                <div class="rating-input">
                                    <?php for ($i = 5; $i >= 1; $i--): ?>
                                        <input type="radio" id="star<?= $i ?>" name="rating" value="<?= $i ?>" class="d-none" required>
                                        <label for="star<?= $i ?>" class="star-label">★</label>
                                    <?php endfor; ?>
                                </div>
                                <div class="rating-labels">
                                    <small class="text-muted">Poor</small>
                                    <small class="text-muted">Excellent</small>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label for="comment" class="form-label">Your Review</label>
                                <textarea class="form-control" id="comment" name="comment" rows="5" placeholder="Share your experience with this tour..." required></textarea>
                                <div class="form-text">Minimum 10 characters</div>
                            </div>
                            
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="../tours.php?id=<?= $tourId ?>" class="btn btn-secondary me-md-2">Cancel</a>
                                <button type="submit" class="btn btn-primary">Submit Review</button>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.rating-input {
    display: flex;
    flex-direction: row-reverse;
    justify-content: flex-end;
    font-size: 2rem;
    line-height: 1;
}

.rating-input .star-label {
    color: #ddd;
    cursor: pointer;
    transition: color 0.2s;
    margin-right: 5px;
}

.rating-input input:checked ~ .star-label,
.rating-input .star-label:hover,
.rating-input .star-label:hover ~ .star-label {
    color: #ffc107;
}

.rating-input input:checked + .star-label {
    color: #ffc107;
}

.rating-labels {
    display: flex;
    justify-content: space-between;
    margin-top: 5px;
    width: 180px;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const stars = document.querySelectorAll('.star-label');
    
    stars.forEach(star => {
        star.addEventListener('click', function() {
            const id = this.htmlFor;
            document.getElementById(id).checked = true;
        });
    });
});
</script>

<?php 
// End output buffering and flush content
ob_end_flush();
require_once '../includes/user-footer.php'; 
?>