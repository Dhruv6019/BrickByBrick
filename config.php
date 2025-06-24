<?php
$servername = "localhost";
$username = "";
$password = "";
$dbname = "";

// Set timezone for MySQL connection
date_default_timezone_set('');

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Set timezone for MySQL session
$conn->query("");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
