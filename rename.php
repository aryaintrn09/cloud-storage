<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/file.php';

if (!is_logged_in()) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['file_id']) && isset($_POST['new_filename'])) {
    $file_id = (int)$_POST['file_id'];
    $new_name = $_POST['new_filename'];
    $username = $_SESSION['username'];
    
    $result = rename_file($file_id, $username, $new_name);
    
    if ($result === true) {
        header("Location: index.php?rename=success");
    } else {
        header("Location: index.php?rename=error&message=" . urlencode($result));
    }
    exit();
}

header("Location: index.php");
exit();