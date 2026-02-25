<?php
// Display country image from database

include 'conn.php';

// Get country ID from URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    die('Invalid ID');
}

// Get image from database
$sql = "SELECT flag_image FROM countries WHERE id = $id";
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($result);

if (!$row || !$row['flag_image']) {
    die('Image not found');
}

// Show the image
header('Content-Type: image/png');
echo $row['flag_image'];

mysqli_close($conn);
?>
