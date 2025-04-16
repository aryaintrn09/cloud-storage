<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['username'])) {
    header('Location: ../login.php');
    exit();
}

$username = $_SESSION['username'];
$targetDir = "../uploads/$username/";
if (!is_dir($targetDir)) {
    mkdir($targetDir, 0755, true);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fileName = basename($_FILES['file']['name']);
    $targetFilePath = $targetDir . $fileName;
    $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);
    $fileSize = $_FILES['file']['size'];

    $allowedTypes = ['jpg', 'png', 'pdf', 'mp3', 'wav'];
    $maxFileSize = 5 * 1024 * 1024; // 5MB

    if (in_array($fileType, $allowedTypes) && $fileSize <= $maxFileSize) {
        if (move_uploaded_file($_FILES['file']['tmp_name'], $targetFilePath)) {
            echo json_encode(['status' => 'success', 'message' => 'File uploaded successfully.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'File upload failed.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid file type or size exceeded.']);
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/styles.css">
    <title>Upload File</title>
</head>
<body>
    <div class="container">
        <h2>Upload File</h2>
        <form id="uploadForm" enctype="multipart/form-data">
            <div class="form-group">
                <label for="file">Choose file</label>
                <input type="file" class="form-control" name="file" id="file" required>
            </div>
            <button type="submit" class="btn btn-primary">Upload</button>
            <div id="progressBar" class="progress mt-3" style="display:none;">
                <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%;" id="progress"></div>
            </div>
            <div id="message" class="mt-3"></div>
        </form>
    </div>

    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/ajax.js"></script>
    <script>
        document.getElementById('uploadForm').onsubmit = function(event) {
            event.preventDefault();
            var formData = new FormData(this);
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'upload.php', true);
            xhr.upload.onprogress = function(e) {
                if (e.lengthComputable) {
                    var percentComplete = (e.loaded / e.total) * 100;
                    document.getElementById('progress').style.width = percentComplete + '%';
                    document.getElementById('progressBar').style.display = 'block';
                }
            };
            xhr.onload = function() {
                document.getElementById('message').innerHTML = this.responseText;
                document.getElementById('progressBar').style.display = 'none';
            };
            xhr.send(formData);
        };
    </script>
</body>
</html>