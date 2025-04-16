<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database configuration
$host = "localhost";
$user = "root";
$pass = "";
$db   = "cloud_storage";

// Constants
define('DEFAULT_MAX_STORAGE', 10485760); // 10MB
define('ADMIN_USERNAME', 'admin');
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'txt', 'zip']);
define('MAX_FILE_SIZE', 10485760); // 10MB per file

// Create connection
$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Security functions
if (!function_exists('sanitize')) {
    function sanitize($data) {
        return htmlspecialchars(strip_tags(trim($data)));
    }
}

if (!function_exists('sanitize_filename')) {
    function sanitize_filename($filename) {
        return preg_replace('/[^a-zA-Z0-9-_.]/', '', $filename);
    }
}

function is_allowed_file($filename) {
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    return in_array($ext, ALLOWED_EXTENSIONS);
}
?>