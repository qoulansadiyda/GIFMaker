<?php
session_start();

// Tentukan direktori tempat menyimpan file
$outputDir = 'temp_images'; // Ganti dengan direktori yang sesuai

// Pastikan $_SESSION['downloaded_files'] telah diinisialisasi
if (!isset($_SESSION['downloaded_files'])) {
    $_SESSION['downloaded_files'] = [];
}

// Ambil daftar file yang diunduh
$downloadedFiles = $_SESSION['downloaded_files'];

// Ambil semua file dalam direktori
$allFiles = scandir($outputDir);

// Hapus file yang tidak diunduh dari direktori
foreach ($allFiles as $file) {
    if ($file != '.' && $file != '..' && !in_array($file, $downloadedFiles)) {
        $filePath = $outputDir . DIRECTORY_SEPARATOR . $file;
        unlink($filePath);
    }
}

// Hapus informasi file yang tidak diunduh dari sesi
$_SESSION['downloaded_files'] = [];
?>
