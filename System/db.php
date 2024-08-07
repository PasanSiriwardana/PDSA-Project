<?php
$servername = "localhost";
$username = "root";
$password = "";
$port = "3307"; // Port for MySQL

// Create connection
$conn = new mysqli($servername, $username, $password, "file_sorting_db", $port);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
