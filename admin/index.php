<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['is_admin'] != 1) {
  die("Access denied");
}

$users = $pdo->query("SELECT * FROM users")->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
  <title>Admin Panel</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-5">
  <h2>Admin Panel</h2>
  <a href="../dashboard.php" class="btn btn-secondary mb-3">Kembali ke Dashboard</a>

  <table class="table table-bordered">
    <thead>
      <tr>
        <th>Username</th>
        <th>Limit Storage (MB)</th>
        <th>Aksi</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($users as $user): ?>
      <tr>
        <td><?= htmlspecialchars($user['username']) ?></td>
        <td><?= $user['storage_limit'] / 1048576 ?></td>
        <td>
          <form method="POST" action="update_limit.php" class="d-flex">
            <input type="hidden" name="id" value="<?= $user['id'] ?>">
            <input type="number" name="limit" class="form-control" placeholder="MB" required>
            <button class="btn btn-primary ms-2">Update</button>
          </form>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</body>
</html>
