<?php $host = 'localhost';
$user = 'root';
$pass = '';
$db = 'country_explorer';

// Connect to database
$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die('Connection failed');
}
?>