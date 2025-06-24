<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "newreal";

// Set timezone for MySQL connection
date_default_timezone_set('Asia/Kolkata');

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Set timezone for MySQL session
$conn->query("SET time_zone = '+05:30'");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>