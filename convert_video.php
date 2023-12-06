<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fps = $_POST["fps_video"];

    // Create a temporary directory to store video frames
    $tempDir = "temp_frames";
    if (!is_dir($tempDir)) {
        mkdir($tempDir);
    } else {
        // Clear existing files in temp_frames directory
        $filesInTempDir = glob("$tempDir/*");
        foreach ($filesInTempDir as $fileInTempDir) {
            unlink($fileInTempDir);
        }
    }

    // Process the uploaded video
    $videoPath = $_FILES["video"]["tmp_name"];
    $outputGif = "output_" . time() . ".gif";

    // Remove all previous output files in the directory
    $previousOutputFiles = glob("output_*.gif");
    foreach ($previousOutputFiles as $previousOutputFile) {
        unlink($previousOutputFile);
    }

    // Use ffmpeg to create GIF from video
    $ffmpegCommand = "ffmpeg -i '$videoPath' -vf 'fps=$fps,scale=trunc(iw/2)*2:trunc(ih/2)*2' '$tempDir/frame%03d.jpg'";
    exec($ffmpegCommand);

    // Use ffmpeg to create GIF
    $ffmpegCommand = "ffmpeg -framerate $fps -i '$tempDir/frame%03d.jpg' -vf 'scale=trunc(iw/2)*2:trunc(ih/2)*2' '$outputGif'";
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
    rmdir($tempDir);
}
?>
