<?php
require 'includes/auth.php';

if ($_SESSION['role'] !== 'admin') {
  die("Akses ditolak.");
}

$user = basename($_GET['username']);
$dir = "uploads/$user";

if (!is_dir($dir)) {
  echo "Folder tidak ditemukan.";
  exit();
}

$files = array_diff(scandir($dir), ['.', '..']);
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>File <?= htmlspecialchars($user) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include 'includes/navbar.php'; ?>

<div class="container mt-4">
  <h4>File milik: <?= htmlspecialchars($user) ?></h4>
  <ul class="list-group">
    <?php foreach ($files as $file): ?>
      <li class="list-group-item"><?= htmlspecialchars($file) ?></li>
    <?php endforeach; ?>
  </ul>
</div>
</body>
</html>
