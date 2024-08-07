<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $folderName = $_POST['folder_name'];

    $stmt = $conn->prepare("INSERT INTO folders (name) VALUES (?)");
    $stmt->bind_param("s", $folderName);
    $stmt->execute();

    echo json_encode(['status' => 'success']);
}
?>
