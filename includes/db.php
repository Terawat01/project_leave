<?php
$servername = "127.0.0.1";
$username = "root";
$password = "";
$dbname = "leave";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Set charset to utf8
$conn->set_charset("utf8");

// Start session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>