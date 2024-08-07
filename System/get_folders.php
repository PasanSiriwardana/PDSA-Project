<?php
include 'db.php';

$result = $conn->query("SELECT * FROM folders");
$folders = [];

while ($row = $result->fetch_assoc()) {
    $folders[] = $row;
}

echo json_encode($folders);
?>
