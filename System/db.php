<?php
$servername = "localhost";
$username = "root";
$password = "";
$port = "3307";

$conn = new mysqli($servername, $username, $password, "file_sorting_db", $port);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
