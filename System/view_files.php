<?php
include 'db.php';

$result = $conn->query("SELECT file_extension, COUNT(*) AS count FROM files GROUP BY file_extension");

$filesByExtension = [];
while ($row = $result->fetch_assoc()) {
    $filesByExtension[$row['file_extension']] = $row['count'];
}

echo "<h1>Files by Extension</h1>";
foreach ($filesByExtension as $ext => $count) {
    echo "<h2>$ext</h2>";
    echo "<p>Number of files: $count</p>";

    $stmt = $conn->prepare("SELECT * FROM files WHERE file_extension = ?");
    $stmt->bind_param("s", $ext);
    $stmt->execute();
    $files = $stmt->get_result();

    echo "<ul>";
    while ($file = $files->fetch_assoc()) {
        echo "<li>" . $file['filename'] . " (Uploaded at: " . $file['upload_time'] . ")</li>";
    }
    echo "</ul>";

    $stmt->close();
}
?>
