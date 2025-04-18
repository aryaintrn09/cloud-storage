<?php
$servername = "localhost";
$username = "root";
$password = "";  // Ganti dengan password Anda jika ada
$dbname = "cloud_storage";

// Buat koneksi
$conn = new mysqli($servername, $username, $password, $dbname);

// Periksa koneksi
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
