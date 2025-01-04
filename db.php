<?php
// Database connection
$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'rest_db';

$conn = new mysqli($host, $user, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

?>
