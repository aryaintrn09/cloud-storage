<?php
require 'includes/auth.php';
require 'includes/db.php';

if ($_SESSION['role'] !== 'admin') exit();

$user_id = intval($_GET['user']);
$files = $conn->query("SELECT * FROM files WHERE user_id = $user_id");
?>

<!DOCTYPE html>
<html>
<head>
  <title>File User</title>
  <link rel="stylesheet" href="assets/bootstrap/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
  <h4>File User ID: <?= $user_id ?></h4>
  <table class="table table-bordered">
    <thead>
      <tr>
        <th>Nama File</th>
        <th>Ukuran</th>
        <th>Preview</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($row = $files->fetch_assoc()): ?>
        <tr>
          <td><?= $row['filename'] ?></td>
          <td><?= round($row['filesize'] / 1024, 2) ?> KB</td>
          <td>
            <form method="post" action="preview.php">
              <input type="hidden" name="path" value="<?= $row['filepath'] ?>">
              <button class="btn btn-info btn-sm">Preview</button>
            </form>
          </td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
  <a href="admin.php" class="btn btn-secondary">← Kembali</a>
</div>
</body>
</html>
