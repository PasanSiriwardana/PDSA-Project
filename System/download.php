<?php
include 'db.php';

// Check if the 'file' parameter is set
if (isset($_GET['file'])) {
    $filename = basename($_GET['file']); // Sanitize filename
    $fileFound = false;

    // Define the base directory for uploads
    $baseDir = 'uploads/';

    // Search through all directories in the base directory
    foreach (glob($baseDir . '*', GLOB_ONLYDIR) as $dir) {
        $filepath = $dir . '/' . $filename;
        if (file_exists($filepath)) {
            $fileFound = true;
            break;
        }
    }

    if ($fileFound) {
        // Force download
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filepath));
        readfile($filepath);
        exit;
    } else {
        // If file not found
        header('HTTP/1.1 404 Not Found');
        echo 'File not found.';
    }
} else {
    // If no file parameter is set
    header('HTTP/1.1 400 Bad Request');
    echo 'No file specified.';
}
?>
