<?php
session_start();
if (!isset($_SESSION['user'])) {
  header("Location: auth/login.php");
  exit;
}
require 'config/db.php';

$user = $_SESSION['user'];
$user_id = $user['id'];
$username = $user['username'];

$stmt = $pdo->prepare("SELECT * FROM files WHERE user_id = ?");
$stmt->execute([$user_id]);
$files = $stmt->fetchAll();

$stmt = $pdo->prepare("SELECT SUM(size) AS total FROM files WHERE user_id = ?");
$stmt->execute([$user_id]);
$totalUsed = $stmt->fetch()['total'] ?? 0;
$limit = $user['storage_limit'];
$percent = ($totalUsed / $limit) * 100;
?>

<!DOCTYPE html>
<html>
<head>
  <title>Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-5">
  <h2>Hai, <?= htmlspecialchars($username) ?></h2>
  <a href="auth/logout.php" class="btn btn-danger mb-3">Logout</a>

  <p>Storage: <?= round($totalUsed / 1048576, 2) ?> MB / <?= round($limit / 1048576) ?> MB</p>
  <div class="progress mb-3">
    <div class="progress-bar" role="progressbar" style="width: <?= $percent ?>%;"><?= round($percent) ?>%</div>
  </div>

  <form action="upload.php" method="POST" enctype="multipart/form-data">
    <input type="file" name="file" class="form-control mb-2" required>
    <button type="submit" class="btn btn-primary">Upload</button>
  </form>

  <h4 class="mt-4">File Anda</h4>
  <ul class="list-group">
  <?php foreach ($files as $file): ?>
    <li class="list-group-item d-flex justify-content-between align-items-center">
      <?= htmlspecialchars($file['filename']) ?> (<?= round($file['size'] / 1024, 2) ?> KB)
      <div>
        <form method="POST" action="rename.php" class="d-inline">
          <input type="hidden" name="id" value="<?= $file['id'] ?>">
          <input name="newname" placeholder="Rename" class="form-control d-inline" style="width:auto;" required>
          <button class="btn btn-warning btn-sm" type="submit">Rename</button>
        </form>
        <form method="POST" action="delete.php" class="d-inline">
          <input type="hidden" name="id" value="<?= $file['id'] ?>">
          <button class="btn btn-danger btn-sm" type="submit">Delete</button>
        </form>
      </div>
    </li>
  <?php endforeach; ?>
  </ul>
</body>
</html>
