<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "smart_scholar_db";

// Create connection
$conn = new mysqli("localhost", "root", "root", "smart_scholar_db");
// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
?>
