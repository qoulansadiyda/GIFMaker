<?php
session_start();

// Download the converted GIF
$file = $_GET['file'];

if (file_exists($file)) {
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename=' . basename($file));
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($file));
    readfile($file);
    
    // Remove the file from the list of downloaded files
    if (($key = array_search($file, $_SESSION['downloaded_files'])) !== false) {
        unset($_SESSION['downloaded_files'][$key]);
    }
    
    // Delete the file from the server
    unlink($file);

    // Delete the temporary images directory
    $tempDir = 'temp_images';
    if (is_dir($tempDir)) {
        $filesInTempDir = glob("$tempDir/*");
        foreach ($filesInTempDir as $fileInTempDir) {
            unlink($fileInTempDir);
        }
        rmdir($tempDir);
    }
} else {
    echo 'File not found.';
}
?>
