<?php
require 'includes/auth.php';

$dir = "uploads/" . $_SESSION['username'] . "/";
$file = basename($_POST['file']);
$path = $dir . $file;

if (file_exists($path)) {
  unlink($path);
}

header("Location: files.php");
exit();
