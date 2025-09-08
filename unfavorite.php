<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isLoggedIn()) {
    $user_id = $_SESSION['user_id'];
    $tour_id = intval($_POST['tour_id']);

    $stmt = $conn->prepare("DELETE FROM favorites WHERE user_id=? AND tour_id=?");
    $stmt->bind_param("ii", $user_id, $tour_id);
    $stmt->execute();

    header("Location: tour.php?id=" . $tour_id);
    exit;
}
header("Location: index.php");
exit;
