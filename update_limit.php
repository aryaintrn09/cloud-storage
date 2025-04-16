<?php
require 'includes/auth.php';
require 'includes/db.php';

if ($_SESSION['role'] !== 'admin') {
  die("Akses ditolak.");
}

if (isset($_POST['user_id'], $_POST['limit'])) {
  $userId = intval($_POST['user_id']);
  $limit = intval($_POST['limit']);

  $stmt = $conn->prepare("UPDATE users SET storage_limit = ? WHERE id = ?");
  $stmt->bind_param("ii", $limit, $userId);
  $stmt->execute();
}

header("Location: admin.php");
exit();
