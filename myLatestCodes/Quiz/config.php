<?php
// Database Configuration
$servername = "localhost";
$username = "root";
$password = "";
$database = "quiz_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die(json_encode(["error" => "Connection failed: " . $conn->connect_error]));
}

// Set charset
$conn->set_charset("utf8");

?>
