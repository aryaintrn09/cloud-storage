<?php
session_start();
require 'config/db.php';

if (!isset($_SESSION['user'])) {
  die("Unauthorized");
}

$user_id = $_SESSION['user']['id'];
$file_id = $_POST['id'] ?? 0;

$stmt = $pdo->prepare("SELECT * FROM files WHERE id = ? AND user_id = ?");
$stmt->execute([$file_id, $user_id]);
$file = $stmt->fetch();

if ($file) {
  if (file_exists($file['filepath'])) {
    unlink($file['filepath']);
  }

  $pdo->prepare("DELETE FROM files WHERE id = ?")->execute([$file_id]);
}

header("Location: dashboard.php");
