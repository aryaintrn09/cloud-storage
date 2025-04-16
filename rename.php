<?php
require 'includes/auth.php';

$username = $_SESSION['username'];
$dir = "uploads/$username/";

$old = basename($_POST['old']);
$new = basename($_POST['new']);

$oldPath = $dir . $old;
$newPath = $dir . $new;

// Cek: tidak boleh sama dan harus nama valid
if ($old === $new || !preg_match('/^[a-zA-Z0-9_\-\.]+$/', $new)) {
  header("Location: files.php");
  exit();
}

// Cek: file lama ada, baru belum ada
if (file_exists($oldPath) && !file_exists($newPath)) {
  rename($oldPath, $newPath);
}

header("Location: files.php");
exit();
