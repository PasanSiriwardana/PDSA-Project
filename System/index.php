<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['file'])) {
    $file = $_FILES['file'];
    $filename = $file['name'];
    $fileTmp = $file['tmp_name'];
    $fileExt = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

    $uploadDir = 'uploads/' . $fileExt;
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $destination = $uploadDir . '/' . $filename;
    if (move_uploaded_file($fileTmp, $destination)) {
        $stmt = $conn->prepare("INSERT INTO files (filename, file_extension, upload_time) VALUES (?, ?, NOW())");
        $stmt->bind_param("ss", $filename, $fileExt);
        $stmt->execute();
        $stmt->close();
        $message = "File uploaded and sorted successfully!";
    } else {
        $message = "Failed to upload file.";
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete'])) {
    $fileId = $_POST['file_id'];
    $stmt = $conn->prepare("SELECT filename, file_extension FROM files WHERE id = ?");
    $stmt->bind_param("i", $fileId);
    $stmt->execute();
    $stmt->bind_result($filename, $fileExt);
    $stmt->fetch();
    $stmt->close();

    $filePath = 'uploads/' . $fileExt . '/' . $filename;
    if (file_exists($filePath)) {
        unlink($filePath);
    }

    $stmt = $conn->prepare("DELETE FROM files WHERE id = ?");
    $stmt->bind_param("i", $fileId);
    $stmt->execute();
    $stmt->close();

    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['rename'])) {
    $fileId = $_POST['file_id'];
    $newFilename = $_POST['new_filename'];
    $stmt = $conn->prepare("SELECT filename, file_extension FROM files WHERE id = ?");
    $stmt->bind_param("i", $fileId);
    $stmt->execute();
    $stmt->bind_result($oldFilename, $fileExt);
    $stmt->fetch();
    $stmt->close();

    $oldFilePath = 'uploads/' . $fileExt . '/' . $oldFilename;
    $newFilePath = 'uploads/' . $fileExt . '/' . $newFilename;
    if (file_exists($oldFilePath)) {
        rename($oldFilePath, $newFilePath);
    }

    $stmt = $conn->prepare("UPDATE files SET filename = ? WHERE id = ?");
    $stmt->bind_param("si", $newFilename, $fileId);
    $stmt->execute();
    $stmt->close();

    header("Location: index.php");
    exit();
}

$result = $conn->query("SELECT id, filename, file_extension FROM files ORDER BY upload_time DESC");

$files = [];
while ($row = $result->fetch_assoc()) {
    $files[] = $row;
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
        /* General Styles */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f0f2f5;
            color: #333;
            margin: 0;
            padding: 0;
        }
        header {
            background: #007bff;
            color: #fff;
            padding: 15px 0;
            text-align: center;
        }
        h1 {
            margin: 0;
            font-size: 2em;
        }
        .container {
            width: 90%;
            max-width: 1200px;
            margin: auto;
        }

        .upload-section, .search-section {
            background: #fff;
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }
        .upload-section:hover, .search-section:hover {
            transform: scale(1.02);
        }
        form {
            display: flex;
            flex-direction: column;
        }
        input[type="file"], input[type="text"] {
            margin-bottom: 15px;
            padding: 8px;
            border-radius: 4px;
            border: 1px solid #ddd;
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
            background: #ff0000;
            color: #fff;
            border: none;
            padding: 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: background 0.3s ease, transform 0.2s ease;
        }
        .delete:hover {
            background: #cd0000;
        }
        .rename {
            background: #6100ff;
            color: #fff;
            border: none;
            padding: 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: background 0.3s ease, transform 0.2s ease;
        }
        .rename:hover {
            background: #4502b2;
        }
        .message {
            color: #d9534f;
            font-weight: bold;
            margin-top: 10px;
        }

        .tabs {
            display: flex;
            cursor: pointer;
            margin-bottom: 20px;
            overflow: hidden;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .tab {
            flex: 1;
            padding: 12px;
            text-align: center;
            background: #f8f9fa;
            border: 1px solid #ddd;
            border-bottom: none;
            transition: background 0.3s ease, color 0.3s ease;
            font-weight: 600;
            cursor: pointer;
            position: relative;
        }
        .tab.active {
            background: #007bff;
            color: #fff;
            border-bottom: 2px solid #007bff;
        }
        .tab:hover {
            background: #e2e6ea;
        }
        .tab-content {
            display: none;
            padding: 20px;
            background: #fff;
            border: 1px solid #ddd;
            border-top: none;
            border-radius: 0 0 8px 8px;
            transition: opacity 0.3s ease;
        }
        .tab-content.active {
            display: block;
            opacity: 1;
        }
        .tab-content.hidden {
            opacity: 0;
        }

        ul {
            list-style-type: none;
            padding: 0;
            margin: 0;
        }
        ul li {
            padding: 12px;
            border-bottom: 1px solid #ddd;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: background 0.3s ease;
        }
        ul li:hover {
            background: #f8f9fa;
        }
        .file-actions {
            display: flex;
            gap: 5px;
        }

        a.file-link {
            color: #007bff;
            text-decoration: none;
        }
        a.file-link:hover {
            text-decoration: underline;
        }
        </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>File Sorting System</h1>
        </header>

        <main>
            <section class="upload-section">
                <h2>Upload File</h2>
                <form action="index.php" method="POST" enctype="multipart/form-data">
                    <input type="file" name="file" required>
                    <button type="submit">Upload</button>
                </form>
                <?php if (isset($message)) echo "<p class='message'>$message</p>"; ?>
            </section>

            <section class="search-section">
                <h2>Manage Files</h2>
                <ul>
                    <?php foreach ($files as $file): ?>
                        <li>
                            <span><?php echo htmlspecialchars($file['filename']); ?></span>
                            <div class="file-actions">
                                <form action="index.php" method="POST" style="display:inline;">
                                    <input type="hidden" name="file_id" value="<?php echo $file['id']; ?>">
                                    <button type="submit" name="delete" class="delete">Delete</button>
                                </form>
                                <button type="button" class="rename" onclick="renameFile('<?php echo $file['id']; ?>', '<?php echo htmlspecialchars($file['filename']); ?>')">Rename</button>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </section>
        </main>
    </div>

    <script>
        function renameFile(fileId, oldFilename) {
            const newFilename = prompt("Enter new filename:", oldFilename);
            if (newFilename && newFilename !== oldFilename) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'index.php';

                const fileIdInput = document.createElement('input');
                fileIdInput.type = 'hidden';
                fileIdInput.name = 'file_id';
                fileIdInput.value = fileId;

                const newFilenameInput = document.createElement('input');
                newFilenameInput.type = 'hidden';
                newFilenameInput.name = 'new_filename';
                newFilenameInput.value = newFilename;

                const renameInput = document.createElement('input');
                renameInput.type = 'hidden';
                renameInput.name = 'rename';

                form.appendChild(fileIdInput);
                form.appendChild(newFilenameInput);
                form.appendChild(renameInput);

                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>
</html>
