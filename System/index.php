<?php
include 'db.php';

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['file'])) {
    $file = $_FILES['file'];
    $filename = $file['name'];
    $fileTmp = $file['tmp_name'];
    $fileExt = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

    // Create folder based on file extension
    $uploadDir = 'uploads/' . $fileExt;
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Move file to the appropriate folder
    $destination = $uploadDir . '/' . $filename;
    if (move_uploaded_file($fileTmp, $destination)) {
        // Save file info to the database
        $stmt = $conn->prepare("INSERT INTO files (filename, file_extension, upload_time) VALUES (?, ?, NOW())");
        $stmt->bind_param("ss", $filename, $fileExt);
        $stmt->execute();
        $stmt->close();
        $message = "File uploaded and sorted successfully!";
    } else {
        $message = "Failed to upload file.";
    }
}

// Handle file search
$searchQuery = '';
if (isset($_POST['search'])) {
    $searchQuery = $_POST['search'];
    $stmt = $conn->prepare("SELECT * FROM files WHERE filename LIKE ? ORDER BY filename");
    $searchParam = '%' . $searchQuery . '%';
    $stmt->bind_param("s", $searchParam);
    $stmt->execute();
    $searchResult = $stmt->get_result();
} else {
    $searchResult = $conn->query("SELECT * FROM files ORDER BY filename");
}

// Handle file rename
if (isset($_POST['rename'])) {
    $fileId = $_POST['file_id'];
    $newFilename = $_POST['new_filename'];

    // Fetch old file info
    $stmt = $conn->prepare("SELECT filename, file_extension FROM files WHERE id = ?");
    $stmt->bind_param("i", $fileId);
    $stmt->execute();
    $oldFile = $stmt->get_result()->fetch_assoc();

    // Rename file in directory
    $oldFilePath = 'uploads/' . $oldFile['file_extension'] . '/' . $oldFile['filename'];
    $newFilePath = 'uploads/' . $oldFile['file_extension'] . '/' . $newFilename;
    if (rename($oldFilePath, $newFilePath)) {
        // Update file info in the database
        $stmt = $conn->prepare("UPDATE files SET filename = ? WHERE id = ?");
        $stmt->bind_param("si", $newFilename, $fileId);
        $stmt->execute();
        $stmt->close();
        $message = "File renamed successfully!";
    } else {
        $message = "Failed to rename file.";
    }
}

// Handle file delete
if (isset($_POST['delete'])) {
    $fileId = $_POST['file_id'];

    // Fetch file info
    $stmt = $conn->prepare("SELECT filename, file_extension FROM files WHERE id = ?");
    $stmt->bind_param("i", $fileId);
    $stmt->execute();
    $file = $stmt->get_result()->fetch_assoc();

    // Delete file from directory
    $filePath = 'uploads/' . $file['file_extension'] . '/' . $file['filename'];
    if (unlink($filePath)) {
        // Delete file info from the database
        $stmt = $conn->prepare("DELETE FROM files WHERE id = ?");
        $stmt->bind_param("i", $fileId);
        $stmt->execute();
        $stmt->close();
        $message = "File deleted successfully!";
    } else {
        $message = "Failed to delete file.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File Sorting System</title>
    <link rel="stylesheet" href="styles.css">
    <style>
/* File List */
        ul {
            list-style-type: none;
            padding: 0;
            margin: 0;
        }

        header {
            background: #007bff;
            color: #fff;
            padding: 15px 0;
            text-align: center;
        }

        ul li {
            padding: 12px;
            border-bottom: 1px solid #ddd;
            display: flex; /* Use Flexbox for alignment */
            justify-content: space-between; /* Space between content and buttons */
            align-items: center; /* Vertically center items */
            transition: background 0.3s ease;
        }

        input[type="file"] {
            margin-bottom: 15px;
            padding: 8px;
            border-radius: 4px;
            border: 1px solid #ddd;
        }
        input[type="text"] {
            margin-bottom: 15px;
            padding: 8px;
            border-radius: 4px;
            border: 1px solid #ddd;
        }

        ul li:hover {
            background: #f8f9fa;
        }

        .file-actions {
            display: flex;
            gap: 5px; /* Space between buttons */
        }

        button {
            background: #007bff;
            color: #fff;
            border: none;
            padding: 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: background 0.3s ease, transform 0.2s ease;
        }

        button:hover {
            background: #0056b3;
        }

        .delete {
            background: #D11A2A;
        }

        .delete:hover {
            background: #a72e2e;
        }

        .rename {
            background: #007bff; /* Use the same color for rename */
        }

        .rename:hover {
            background: #0056b3;
        }

        .message {
            color: #d9534f;
            font-weight: bold;
            margin-top: 10px;
        }

    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>File Sorting System</h1>
        </header>

        <main>
            <!-- Upload Section -->
            <section class="upload-section">
                <h2>Upload File</h2>
                <form action="index.php" method="POST" enctype="multipart/form-data">
                    <input type="file" name="file" required>
                    <button type="submit">Upload</button>
                </form>
                <?php if (isset($message)) echo "<p class='message'>$message</p>"; ?>
            </section>

            <!-- Search Section -->
            <section class="files-section">
                <h2>Search Files</h2>
                <form action="index.php" method="POST">
                    <input type="text" name="search" value="<?php echo htmlspecialchars($searchQuery); ?>" placeholder="Search by filename">
                    <button type="submit">Search</button>
                </form>

                <?php if ($searchResult->num_rows > 0): ?>
                    <ul>
                        <?php while ($row = $searchResult->fetch_assoc()): ?>
                            <li class="file-item">
                                <span><?php echo htmlspecialchars($row['filename']); ?></span>
                                <form action="index.php" method="POST" style="display:inline;">
                                    <input type="hidden" name="file_id" value="<?php echo $row['id']; ?>">
                                    <button type="submit" class="delete" name="delete" onclick="return confirm('Are you sure you want to delete this file?');">Delete</button>
                                    <button type="button" class="rename" onclick="renameFile('<?php echo $row['id']; ?>', '<?php echo htmlspecialchars($row['filename']); ?>')">Rename</button>
                                </form>
                            </li>
                        <?php endwhile; ?>
                    </ul>
                <?php else: ?>
                    <p>No files found.</p>
                <?php endif; ?>
            </section>
        </main>
    </div>

    <script>
        function renameFile(fileId, oldFilename) {
            var newFilename = prompt("Enter new filename:", oldFilename);
            if (newFilename) {
                var form = document.createElement('form');
                form.method = 'POST';
                form.action = 'index.php';

                var fileIdInput = document.createElement('input');
                fileIdInput.type = 'hidden';
                fileIdInput.name = 'file_id';
                fileIdInput.value = fileId;
                form.appendChild(fileIdInput);

                var newFilenameInput = document.createElement('input');
                newFilenameInput.type = 'hidden';
                newFilenameInput.name = 'new_filename';
                newFilenameInput.value = newFilename;
                form.appendChild(newFilenameInput);

                var renameInput = document.createElement('input');
                renameInput.type = 'hidden';
                renameInput.name = 'rename';
                form.appendChild(renameInput);

                document.body.appendChild(form);
                form.submit();

                // Reload the page after renaming
                setTimeout(function() {
                    window.location.reload();
                }, 1000); // 1 second delay to ensure form submission
            }
        }
    </script>
</body>
</html>
