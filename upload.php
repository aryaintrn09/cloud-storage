<?php
require 'includes/auth.php';
require 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['file'])) {
  $username = $_SESSION['username'];
  $dir = "uploads/$username";

  // Buat folder user jika belum ada
  if (!is_dir($dir)) {
    mkdir($dir, 0777, true);
  }

  $file = $_FILES['file'];
  $fileName = basename($file['name']);
  $fileTmp = $file['tmp_name'];
  $fileSize = $file['size'];
  $fileError = $file['error'];

  // Validasi apakah ada error pada upload
  if ($fileError !== UPLOAD_ERR_OK) {
    die("Terjadi kesalahan saat mengunggah file.");
  }

  // Validasi ukuran file (maksimal 10MB)
  if ($fileSize > 10 * 1024 * 1024) {
    die("Ukuran file terlalu besar. Maksimal 10MB.");
  }

  // Validasi ekstensi file
  $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'mp3'];
  $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
  if (!in_array($fileExt, $allowedExtensions)) {
    die("Ekstensi file tidak didukung.");
  }

  // Tentukan path penyimpanan file
  $filePath = $dir . '/' . $fileName;

  // Cek apakah file sudah ada
  if (file_exists($filePath)) {
    die("File sudah ada di server.");
  }

  // Pindahkan file ke folder yang sesuai
  if (move_uploaded_file($fileTmp, $filePath)) {
    echo json_encode(['status' => 'success', 'message' => 'File berhasil diunggah!']);
  } else {
    echo json_encode(['status' => 'error', 'message' => 'Gagal mengunggah file.']);
  }
} else {
  echo json_encode(['status' => 'error', 'message' => 'Tidak ada file yang dipilih.']);
}
?>
