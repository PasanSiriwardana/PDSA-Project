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

$result = $conn->query("SELECT file_extension, COUNT(*) AS count FROM files GROUP BY file_extension");

$filesByExtension = [];
while ($row = $result->fetch_assoc()) {
    $filesByExtension[$row['file_extension']] = $row['count'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lectures Panel</title>
    <link rel="stylesheet" href="styles.css">
    <style>
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

        .upload-section {
            background: #fff;
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }
        .upload-section:hover {
            transform: scale(1.02);
        }
        form {
            display: flex;
            flex-direction: column;
        }
        input[type="file"] {
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
            transition: background 0.3s ease;
        }
        ul li:hover {
            background: #f8f9fa;
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
            <h1>Lectures Panel</h1>
        </header>

        <main>
            <section class="files-section">
                <h2>Files by Extension</h2>
                <div class="tabs">
                    <?php $tabIndex = 0; ?>
                    <?php foreach ($filesByExtension as $ext => $count): ?>
                        <div class="tab <?php if ($tabIndex == 0) echo 'active'; ?>" data-tab="tab-<?php echo $tabIndex; ?>">
                            <?php echo strtoupper($ext); ?>
                        </div>
                        <?php $tabIndex++; ?>
                    <?php endforeach; ?>
                </div>

                <?php $tabIndex = 0; ?>
                <?php foreach ($filesByExtension as $ext => $count): ?>
                    <div class="tab-content <?php if ($tabIndex == 0) echo 'active'; ?>" id="tab-<?php echo $tabIndex; ?>">
                        <p>Number of files: <?php echo $count; ?></p>

                        <?php
                        $stmt = $conn->prepare("SELECT * FROM files WHERE file_extension = ?");
                        $stmt->bind_param("s", $ext);
                        $stmt->execute();
                        $files = $stmt->get_result();

                        $fileArray = [];
                        while ($file = $files->fetch_assoc()) {
                            $fileArray[] = $file;
                        }
                        $stmt->close();

                        for ($i = 0; $i < count($fileArray) - 1; $i++) {
                            for ($j = 0; $j < count($fileArray) - $i - 1; $j++) {
                                if (strcasecmp($fileArray[$j]['filename'], $fileArray[$j + 1]['filename']) > 0) {
                                    $temp = $fileArray[$j];
                                    $fileArray[$j] = $fileArray[$j + 1];
                                    $fileArray[$j + 1] = $temp;
                                }
                            }
                        }
                        ?>
                        <ul>
                            <?php foreach ($fileArray as $file): ?>
                                <li>
                                    <a class="file-link" href="download.php?file=<?php echo urlencode($file['filename']); ?>">
                                        <?php echo $file['filename']; ?>
                                    </a>
                                    (Uploaded at: <?php echo $file['upload_time']; ?>)
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php $tabIndex++; ?>
                <?php endforeach; ?>
            </section>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var tabs = document.querySelectorAll('.tab');
            var contents = document.querySelectorAll('.tab-content');

            tabs.forEach(function(tab) {
                tab.addEventListener('click', function() {
                    var targetId = this.getAttribute('data-tab');

                    tabs.forEach(function(t) {
                        t.classList.remove('active');
                    });

                    contents.forEach(function(content) {
                        content.classList.remove('active');
                        content.classList.add('hidden');
                    });

                    document.getElementById(targetId).classList.remove('hidden');
                    document.getElementById(targetId).classList.add('active');
                    this.classList.add('active');
                });
            });
        });
    </script>
</body>
</html>
