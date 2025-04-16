<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/file.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

if (isset($_GET['id'])) {
    $file_id = $_GET['id'];
    $username = $_SESSION['username'];
    
    if (deleteFile($file_id, $username)) {
        header("Location: index.php?delete_success=1");
    } else {
        header("Location: index.php?delete_error=1");
    }
    exit();
}

header("Location: index.php");
?>