<?php
session_start();
include 'includes/file.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
}

if (isset($_POST['upload'])) {
    if (uploadFile($_SESSION['user_id'], $_FILES['file'])) {
        header("Location: index.php");
    } else {
        echo "Upload gagal!";
    }
}
?>
<form method="POST" enctype="multipart/form-data">
    <input type="file" name="file" required>
    <button type="submit" name="upload">Upload</button>
</form>
<a href="index.php">Kembali</a>