<?php
header("Content-Type: application/json");
require_once __DIR__ . "/../../includes/db.php";

$sql = "SELECT id, title, short_description, thumbnail_url, category, average_rating, view_count 
        FROM tours WHERE is_active = 1";
$result = $conn->query($sql);

$tours = [];
while ($row = $result->fetch_assoc()) {
    $tours[] = $row;
}

echo json_encode($tours);
?>
