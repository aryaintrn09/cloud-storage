<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/file.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];
$error = '';
if (isset($_POST['upload'])) {
    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $result = uploadFile($username, $_FILES['file']);
        if ($result === true) {
            header("Location: index.php?upload_success=1");
            exit();
        } else {
            $error = $result;
        }
    } else {
        $error = "Silakan pilih file yang valid!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Upload File - Cloud Storage</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Upload File</h1>
        <?php if ($error): ?>
            <div class="alert error"><?= $error ?></div>
        <?php endif; ?>
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label>Pilih File (Max 10MB):</label>
                <input type="file" name="file" required>
            </div>
            <div class="form-actions">
                <button type="submit" name="upload" class="btn">Upload</button>
                <a href="index.php" class="btn secondary">Kembali</a>
            </div>
        </form>
    </div>
</body>
</html>