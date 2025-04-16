<?php
session_start();
include 'includes/file.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
}

if (isset($_GET['id'])) {
    if (deleteFile($_GET['id'], $_SESSION['user_id'])) {
        header("Location: index.php");
    } else {
        echo "Gagal menghapus file!";
    }
}
?>