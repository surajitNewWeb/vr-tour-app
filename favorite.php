<?php
require_once __DIR__ . '/includes/auth.php';
requireLogin();

$tour_id = intval($_POST['tour_id']);
$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("INSERT INTO vr_favorites (user_id, tour_id) VALUES (?, ?)");
$stmt->bind_param("ii", $user_id, $tour_id);
$stmt->execute();

header("Location: favorites.php");
exit;
