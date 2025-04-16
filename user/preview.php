<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['username'])) {
    header('Location: ../login.php');
    exit();
}

$username = $_SESSION['username'];
$file = isset($_GET['file']) ? basename($_GET['file']) : '';

if (empty($file)) {
    echo "No file specified.";
    exit();
}

$filePath = "../uploads/$username/$file";

if (!file_exists($filePath)) {
    echo "File does not exist.";
    exit();
}

$fileType = pathinfo($filePath, PATHINFO_EXTENSION);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/styles.css">
    <title>File Preview</title>
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <div class="container mt-5">
        <h2>Preview File: <?php echo htmlspecialchars($file); ?></h2>
        <div class="preview-container">
            <?php
            if (in_array($fileType, ['png', 'jpg', 'jpeg'])) {
                echo "<img src='$filePath' class='img-fluid' alt='Image Preview'>";
            } elseif ($fileType === 'pdf') {
                echo "<iframe src='$filePath' width='100%' height='600px'></iframe>";
            } elseif (in_array($fileType, ['mp3', 'wav'])) {
                echo "<audio controls>
                        <source src='$filePath' type='audio/$fileType'>
                        Your browser does not support the audio element.
                      </audio>";
            } else {
                echo "File type not supported for preview.";
            }
            ?>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>
    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/main.js"></script>
</body>
</html>