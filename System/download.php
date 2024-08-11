<?php
include 'db.php';

if (isset($_GET['file'])) {
    $filename = basename($_GET['file']);
    $fileFound = false;

    $baseDir = 'uploads/';

    foreach (glob($baseDir . '*', GLOB_ONLYDIR) as $dir) {
        $filepath = $dir . '/' . $filename;
        if (file_exists($filepath)) {
            $fileFound = true;
            break;
        }
    }

    if ($fileFound) {
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
        header('HTTP/1.1 404 Not Found');
        echo 'File not found.';
    }
} else {
    header('HTTP/1.1 400 Bad Request');
    echo 'No file specified.';
}
?>
