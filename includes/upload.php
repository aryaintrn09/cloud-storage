<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) exit("Unauthorized");
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

$target_dir = "../uploads/$username/";
if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);

$allowed = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'mp3', 'wav'];
$max_size = 10 * 1024 * 1024; // 10MB

$file = $_FILES['file'];
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$size = $file['size'];

if (!in_array($ext, $allowed)) {
    exit("Ekstensi tidak diperbolehkan.");
}

if ($size > $max_size) {
    exit("Ukuran file terlalu besar.");
}

// Cek storage terpakai
$stmt = $pdo->prepare("SELECT SUM(size) AS used FROM files WHERE user_id = ?");
$stmt->execute([$user_id]);
$used = $stmt->fetch()['used'] ?? 0;

$stmt = $pdo->prepare("SELECT storage_limit FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$limit = $stmt->fetch()['storage_limit'];

if ($used + $size > $limit) {
    exit("Storage limit terlampaui.");
}

$filename = basename($file['name']);
$target_file = $target_dir . $filename;
move_uploaded_file($file["tmp_name"], $target_file);

// Simpan ke database
$stmt = $pdo->prepare("INSERT INTO files (user_id, filename, filepath, size) VALUES (?, ?, ?, ?)");
$stmt->execute([$user_id, $filename, $target_file, $size]);

header("Location: ../views/dashboard.php");
exit();
