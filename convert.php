<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fps = $_POST["fps"];
    
    // Bikin direktori sementara untuk menyimpan image
    $tempDir = "temp_images";
    if (!is_dir($tempDir)) {
        mkdir($tempDir);
    } else {
        // Hapus semua file yang ada di temp_images
        $filesInTempDir = glob("$tempDir/*");
        foreach ($filesInTempDir as $fileInTempDir) {
            unlink($fileInTempDir);
        }
    }

    // Proses setiap image yang diinput
    $imagePaths = [];
    foreach ($_FILES["images"]["error"] as $key => $error) {
        if ($error == UPLOAD_ERR_OK) {
            $tmp_name = $_FILES["images"]["tmp_name"][$key];
            $name = basename($_FILES["images"]["name"][$key]);
            $targetPath = "$tempDir/$name";
            move_uploaded_file($tmp_name, $targetPath);
            $imagePaths[] = $targetPath;
        }
    }

    // Use ffmpeg to create GIF
    $outputGif = "output_" . time() . ".gif"; // Change file name by adding timestamp

    // Remove all previous output files in the directory
    $previousOutputFiles = glob("output_*.gif");
    foreach ($previousOutputFiles as $previousOutputFile) {
        unlink($previousOutputFile);
    }

    $imageList = implode("|", $imagePaths);
    $ffmpegCommand = "ffmpeg -framerate $fps -i 'temp_images/%*.jpg' -vf 'scale=trunc(iw/2)*2:trunc(ih/2)*2' $outputGif";
    exec($ffmpegCommand);

    // Display the converted GIF
    echo "<!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Converted GIF</title>
        <link rel='stylesheet' href='styles.css'>
    </head>
    <body>
        <div class='result-container'>
            <h2>Converted GIF Preview:</h2>
            <img class='result-image' src='$outputGif' alt='Converted GIF' style='max-width: 50%; height: 50%;'>
            <br>
            <a href='download.php?file=$outputGif' download class='download-button'>Download GIF</a>
        </div>
    </body>
    </html>";


    // Store information in session to track downloaded files
    $_SESSION['downloaded_files'][] = $outputGif;

    // Remove temporary files after conversion
    $filesInTempDir = glob("$tempDir/*");
    foreach ($filesInTempDir as $fileInTempDir) {
        unlink($fileInTempDir);
    }
}
?>
