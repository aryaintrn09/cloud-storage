<?php
session_start();
require 'config.php';

// Cek apakah pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    echo "You must be logged in to download files.";
    exit;
}

$user_id = $_SESSION['user_id'];

// Cek apakah parameter 'filename' ada
if (isset($_GET['filename'])) {
    $filename = $_GET['filename'];
    $user_folder = "uploads/" . $_SESSION['username'];

    // Tentukan path lengkap ke file yang akan di-download
    $file_path = $user_folder . '/' . $filename;

    // Cek apakah file ada
    if (file_exists($file_path)) {
        // Set header untuk memaksa browser mengunduh file
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($file_path) . '"');
        header('Content-Length: ' . filesize($file_path));
        readfile($file_path);  // Membaca file dan mengirimnya ke browser
        exit;
    } else {
        echo "File does not exist.";
    }
} else {
    echo "No file specified for download.";
}
?>
