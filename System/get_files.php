<?php
include 'db.php';

$folderId = $_GET['folder_id'];

$stmt = $conn->prepare("SELECT * FROM files WHERE folder_id = ?");
$stmt->bind_param("i", $folderId);
$stmt->execute();
$result = $stmt->get_result();
$files = [];

while ($row = $result->fetch_assoc()) {
    $files[] = $row;
}

echo json_encode($files);
?>
