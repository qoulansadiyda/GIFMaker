<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fps = $_POST["fps"];
    
    // Create a temporary directory to store images
    $tempDir = "temp_images";
    if (!is_dir($tempDir)) {
        mkdir($tempDir);
    } else {
        // Clear existing files in temp_images directory
        $filesInTempDir = glob("$tempDir/*");
        foreach ($filesInTempDir as $fileInTempDir) {
            unlink($fileInTempDir);
        }
    }

    // Process each uploaded image
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
    echo "<h2>Converted GIF:</h2>";
    $randomParam = "?rand=" . uniqid(); // Add random parameter to URL
    echo "<img src='$outputGif$randomParam' alt='Converted GIF' style='max-width: 50%; height: 50%;'>";

    // Display download link
    echo "<br>";
    $downloadLink = "download.php?file=$outputGif";
    echo "<a href='$downloadLink' download><button>Download GIF</button></a>";

    // Store information in session to track downloaded files
    $_SESSION['downloaded_files'][] = $outputGif;

    // Remove temporary files after conversion
    $filesInTempDir = glob("$tempDir/*");
    foreach ($filesInTempDir as $fileInTempDir) {
        unlink($fileInTempDir);
    }
}
?>
