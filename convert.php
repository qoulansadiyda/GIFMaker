<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle file upload
    $uploadDir = 'uploads/';
    $uploadedFiles = [];

    if ($_FILES['images']) {
        $files = $_FILES['images'];
        $uploadedFiles = handleImageUpload($files, $uploadDir);
    } elseif ($_FILES['video']) {
        $file = $_FILES['video'];
        $uploadedFiles = handleVideoUpload($file, $uploadDir);
    }

    // Handle conversion using ffmpeg
    $outputGif = '';
    if (!empty($uploadedFiles)) {
        $outputGif = convertToGif($uploadedFiles, $uploadDir);
    }

    // Display result and provide download link
    echo '<h4>Preview GIF:</h4>';
    if (!empty($outputGif)) {
        echo '<img src="' . $outputGif . '" alt="Converted GIF" class="img-fluid">';
        echo '<a href="' . $outputGif . '" download class="btn btn-primary mt-3">Download GIF</a>';
    } else {
        echo '<p class="text-danger">Conversion failed. Please check your files and try again.</p>';
    }
}

function handleImageUpload($files, $uploadDir) {
    $uploadedFiles = [];
    foreach ($files['tmp_name'] as $key => $tmp_name) {
        $file_name = $files['name'][$key];
        $file_size = $files['size'][$key];
        $file_tmp = $files['tmp_name'][$key];
        $file_type = $files['type'][$key];

        $target_file = $uploadDir . basename($file_name);

        move_uploaded_file($file_tmp, $target_file);
        $uploadedFiles[] = $target_file;
    }
    return $uploadedFiles;
}

function handleVideoUpload($file, $uploadDir) {
    $uploadedFile = '';
    $file_name = $file['name'];
    $file_size = $file['size'];
    $file_tmp = $file['tmp_name'];
    $file_type = $file['type'];

    $target_file = $uploadDir . basename($file_name);

    move_uploaded_file($file_tmp, $target_file);
    $uploadedFile = $target_file;

    return $uploadedFile;
}

function convertToGif($files, $uploadDir) {
    // Customize ffmpeg command based on your requirements
    $outputGif = $uploadDir . 'output.gif';

    // Generate a list of input files for ffmpeg
    $inputFiles = '';
    foreach ($files as $file) {
        $inputFiles .= "-i $file ";
    }

    // Example ffmpeg command to convert images to GIF
    // Adjust the settings as needed for your specific use case
    $ffmpegCommand = "ffmpeg $inputFiles -filter_complex \"[0:v] palettegen\" -y palette.png && ffmpeg $inputFiles -i palette.png -lavfi \"[0:v][1:v] paletteuse\" -y $outputGif";

    exec($ffmpegCommand);

    // Remove temporary palette file
    unlink($uploadDir . 'palette.png');

    return $outputGif;
}

?>
