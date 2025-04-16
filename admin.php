<?php
require 'includes/auth.php';
require 'includes/db.php';

// Pastikan user adalah admin
if ($_SESSION['role'] !== 'admin') {
  echo "Akses ditolak.";
  exit();
}

// Ambil semua user
$result = $conn->query("SELECT id, username, storage_limit FROM users");
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Panel Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include 'includes/navbar.php'; ?>

<div class="container mt-4">
  <h3>Panel Admin</h3>
  <table class="table table-bordered">
    <thead>
      <tr>
        <th>Username</th>
        <th>Storage Limit (MB)</th>
        <th>Aksi</th>
        <th>File</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
          <td><?= htmlspecialchars($row['username']) ?></td>
          <td>
            <form action="update_limit.php" method="post" class="d-flex gap-2">
              <input type="hidden" name="user_id" value="<?= $row['id'] ?>">
              <input type="number" name="limit" value="<?= $row['storage_limit'] ?>" min="1" class="form-control" style="width: 100px;">
              <button class="btn btn-sm btn-primary">Update</button>
            </form>
          </td>
          <td>
            <a class="btn btn-sm btn-secondary" href="admin_view.php?username=<?= urlencode($row['username']) ?>">Lihat File</a>
          </td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>
</body>
</html>
