<?php
session_start();
require 'config.php';

// Cek apakah pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    echo "You must be logged in to rename files.";
    exit;
}

$user_id = $_SESSION['user_id'];

// Cek apakah parameter 'oldName' dan 'newName' ada
if (isset($_POST['oldName']) && isset($_POST['newName'])) {
    $old_name = $_POST['oldName'];
    $new_name = $_POST['newName'];
    $user_folder = "uploads/" . $_SESSION['username'];

    // Tentukan path lengkap ke file yang akan diganti namanya
    $old_file_path = $user_folder . '/' . $old_name;
    $new_file_path = $user_folder . '/' . $new_name;

    // Cek apakah file ada
    if (file_exists($old_file_path)) {
        // Cek apakah file dengan nama baru sudah ada
        if (!file_exists($new_file_path)) {
            if (rename($old_file_path, $new_file_path)) {
                echo "File renamed successfully.";
            } else {
                echo "Failed to rename the file.";
            }
        } else {
            echo "A file with the new name already exists.";
        }
    } else {
        echo "File does not exist.";
    }
} else {
    echo "Invalid request.";
}
?>
