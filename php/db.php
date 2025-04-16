<?php
$host = "localhost";      // biasanya 'localhost'
$user = "root";           // username database kamu
$pass = "";               // password database kamu
$db   = "cloud_storage";  // nama database

$conn = new mysqli($host, $user, $pass, $db);

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
?>
