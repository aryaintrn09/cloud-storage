<?php
session_start();
require 'config.php';

// Cek apakah pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    echo "You must be logged in to delete files.";
    exit;
}

$user_id = $_SESSION['user_id'];

// Cek apakah parameter 'filename' ada
if (isset($_POST['filename'])) {
    $filename = $_POST['filename'];
    $user_folder = "uploads/" . $_SESSION['username'];

    // Tentukan path lengkap ke file yang akan dihapus
    $file_path = $user_folder . '/' . $filename;

    // Cek apakah file ada
    if (file_exists($file_path)) {
        if (unlink($file_path)) {
            echo "File deleted successfully.";
        } else {
            echo "Failed to delete the file.";
        }
    } else {
        echo "File does not exist.";
    }
} else {
    echo "No file specified for deletion.";
}
?>
