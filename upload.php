<?php
session_start();
require 'config/db.php';

if (!isset($_SESSION['user'])) {
  die("Unauthorized");
}

$user = $_SESSION['user'];
$username = $user['username'];
$user_id = $user['id'];

$allowed_ext = ['jpg', 'png', 'pdf', 'docx', 'txt'];
$max_size = 5 * 1024 * 1024; // 5 MB

if ($_FILES['file']) {
  $file = $_FILES['file'];
  $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

  // Validasi ekstensi
  if (!in_array($ext, $allowed_ext)) {
    die("Ekstensi file tidak diizinkan.");
  }

  // Validasi ukuran
  if ($file['size'] > $max_size) {
    die("Ukuran file terlalu besar (maks. 5 MB).");
  }

  // Cek apakah melebihi limit storage
  $stmt = $pdo->prepare("SELECT SUM(size) as total FROM files WHERE user_id = ?");
  $stmt->execute([$user_id]);
  $total_used = $stmt->fetch()['total'] ?? 0;

  if ($total_used + $file['size'] > $user['storage_limit']) {
    die("Storage Anda penuh. Tidak bisa upload file.");
  }

  // Simpan file
  $upload_dir = "uploads/$username/";
  if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
  }

  $target_file = $upload_dir . basename($file['name']);
  move_uploaded_file($file['tmp_name'], $target_file);

  // Simpan ke database
  $stmt = $pdo->prepare("INSERT INTO files (user_id, filename, filepath, size) VALUES (?, ?, ?, ?)");
  $stmt->execute([$user_id, $file['name'], $target_file, $file['size']]);

  header("Location: dashboard.php");
}
