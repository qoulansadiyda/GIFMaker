<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fps = $_POST["fps_video"];
    
    // Create a temporary directory to store video frames
    $tempDir = "temp_video_frames";
    if (!is_dir($tempDir)) {
        mkdir($tempDir);
    } else {
        // Clear existing files in temp_video_frames directory
        $filesInTempDir = glob("$tempDir/*");
        foreach ($filesInTempDir as $fileInTempDir) {
            unlink($fileInTempDir);
        }
    }

    // Process the uploaded video
    $videoName = basename($_FILES["video"]["name"]);
    $videoPath = "$tempDir/$videoName";
    move_uploaded_file($_FILES["video"]["tmp_name"], $videoPath);

    // Use ffmpeg to extract frames from the video
    $framesDir = "temp_video_frames/frames";
    mkdir($framesDir);
    $ffmpegCommand = "ffmpeg -i $videoPath -vf fps=$fps $framesDir/%04d.jpg";
    exec($ffmpegCommand);

    // Use ffmpeg to create GIF from extracted frames
    $outputGif = "output_video.gif";
    $framesPath = "$framesDir/%04d.jpg";
    $ffmpegGifCommand = "ffmpeg -framerate $fps -i $framesPath -vf 'scale=trunc(iw/2)*2:trunc(ih/2)*2' $outputGif";
    exec($ffmpegGifCommand);

    // Display the converted GIF
    echo "<h2>Converted GIF:</h2>";
    $randomParam = "?rand=" . uniqid(); // Add random parameter to URL
    echo "<img src='$outputGif$randomParam' alt='Converted GIF' style='max-width: 100%; height: auto;'>";

    // Display download link
    echo "<br>";
    $downloadLink = "download.php?file=$outputGif";
    echo "<a href='$downloadLink' download><button>Download GIF</button></a>";

    // Store information in session to track downloaded files
    $_SESSION['downloaded_files'][] = $outputGif;

    // Remove temporary files after conversion
    unlink($videoPath);
    $filesInFramesDir = glob("$framesDir/*");
    foreach ($filesInFramesDir as $fileInFramesDir) {
        unlink($fileInFramesDir);
    }
    rmdir($framesDir);
}
?>
