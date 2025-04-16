<?php
session_start();
require 'config/db.php';

if (!isset($_SESSION['user'])) {
  die("Unauthorized");
}

$user_id = $_SESSION['user']['id'];
$id = $_POST['id'];
$newname = htmlspecialchars($_POST['newname']);

$stmt = $pdo->prepare("SELECT * FROM files WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $user_id]);
$file = $stmt->fetch();

if ($file) {
  $ext = pathinfo($file['filename'], PATHINFO_EXTENSION);
  $new_filename = $newname . '.' . $ext;
  $new_path = dirname($file['filepath']) . '/' . $new_filename;

  if (rename($file['filepath'], $new_path)) {
    $pdo->prepare("UPDATE files SET filename = ?, filepath = ? WHERE id = ?")
        ->execute([$new_filename, $new_path, $id]);
  }
}

header("Location: dashboard.php");
