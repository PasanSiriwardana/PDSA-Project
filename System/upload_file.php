<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $folderId = $_POST['folder_id'];
    $fileName = $_POST['file_name'];
    $fileContent = $_POST['file_content'];

    $stmt = $conn->prepare("INSERT INTO files (folder_id, name, content) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $folderId, $fileName, $fileContent);
    $stmt->execute();

    echo json_encode(['status' => 'success']);
}
?>
