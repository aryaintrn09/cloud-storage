<?php
require 'db.php';
require 'auth.php';

$username = $_SESSION['username'];

// Validasi ekstensi
$allowed_ext = ['jpg', 'jpeg', 'png', 'pdf', 'mp3', 'wav'];
$max_size = 10 * 1024 * 1024; // 10 MB
$stmt = $conn->prepare("SELECT SUM(size) as total FROM files WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$total = $stmt->get_result()->fetch_assoc()['total'] ?? 0;

$stmt = $conn->prepare("SELECT storage_limit FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$limit = $stmt->get_result()->fetch_assoc()['storage_limit'];

if ($total + $file_size > $limit) {
    echo "Gagal: Melebihi batas penyimpanan kamu.";
    exit;
}

if (!isset($_FILES['file'])) {
    echo "Tidak ada file diunggah.";
    exit;
}

$file = $_FILES['file'];
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

if (!in_array($ext, $allowed_ext)) {
    echo "Ekstensi file tidak diperbolehkan.";
    exit;
}

if ($file['size'] > $max_size) {
    echo "Ukuran file terlalu besar (maksimal 10MB).";
    exit;
}

// Dapatkan user_id
$stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$user_id = $user['id'];

// Simpan file
$upload_dir = "../uploads/$username/";
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

$filename = basename($file['name']);
$target = $upload_dir . $filename;

if (move_uploaded_file($file['tmp_name'], $target)) {
    // Masukkan ke database
    $size = $file['size'];
    $stmt = $conn->prepare("INSERT INTO files (user_id, filename, filepath, size) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("issi", $user_id, $filename, $target, $size);
    $stmt->execute();

    echo "File berhasil diupload.";
} else {
    echo "Gagal menyimpan file.";
}
?>
