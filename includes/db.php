<?php
$host = 'localhost';
$dbname = 'cloud_storage';
$username = 'root';
$password = '';

// Create connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set character set to utf8
$conn->set_charset("utf8");

// Function to escape user input to prevent SQL injection
function escapeInput($data) {
    global $conn;
    return $conn->real_escape_string($data);
}

// Function to validate file size and type
function validateFile($file) {
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf', 'mp3', 'wav'];
    $maxFileSize = 5 * 1024 * 1024; // 5MB

    $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
    if (!in_array(strtolower($fileExtension), $allowedExtensions)) {
        return "Invalid file type.";
    }
    if ($file['size'] > $maxFileSize) {
        return "File size exceeds the limit.";
    }
    return true;
}
?>