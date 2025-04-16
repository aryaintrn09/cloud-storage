<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user_id']) || !$_SESSION['is_admin']) {
    die("Unauthorized access.");
}

// Ambil semua user
$users = $pdo->query("SELECT id, username, storage_limit FROM users ORDER BY username ASC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin Panel</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <h2>Admin Panel</h2>
    <a href="dashboard.php">Kembali ke Dashboard</a> | <a href="logout.php">Logout</a>

    <h3>Daftar Pengguna</h3>
    <table border="1" cellpadding="8">
        <thead>
            <tr>
                <th>Username</th>
                <th>Storage Limit (MB)</th>
                <th>Total File</th>
                <th>Total Ukuran</th>
                <th>Ganti Limit</th>
                <th>File Upload</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): 
                $stmt = $pdo->prepare("SELECT COUNT(*) AS total, SUM(size) AS used FROM files WHERE user_id = ?");
                $stmt->execute([$user['id']]);
                $data = $stmt->fetch();
                $total_file = $data['total'] ?? 0;
                $used = round(($data['used'] ?? 0) / 1024 / 1024, 2);
            ?>
            <tr>
                <td><?= htmlspecialchars($user['username']) ?></td>
                <td><?= round($user['storage_limit'] / 1024 / 1024) ?> MB</td>
                <td><?= $total_file ?></td>
                <td><?= $used ?> MB</td>
                <td>
                    <form action="../includes/update_limit.php" method="POST">
                        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                        <input type="number" name="new_limit" min="1" required> MB
                        <button type="submit">Update</button>
                    </form>
                </td>
                <td><a href="user_files.php?user_id=<?= $user['id'] ?>">Lihat File</a></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
