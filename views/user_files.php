<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    die("Unauthorized");
}

$user_id = $_GET['user_id'] ?? 0;

$stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) die("User tidak ditemukan.");

$files = $pdo->prepare("SELECT * FROM files WHERE user_id = ?");
$files->execute([$user_id]);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>File: <?= htmlspecialchars($user['username']) ?></title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <h2>File milik <?= htmlspecialchars($user['username']) ?></h2>
    <a href="admin.php">Kembali ke Admin Panel</a>

    <ul>
        <?php while ($file = $files->fetch()): ?>
            <li>
                <?= htmlspecialchars($file['filename']) ?> 
                (<a href="<?= $file['filepath'] ?>" target="_blank">Lihat</a>)
                <form action="../includes/delete_by_admin.php" method="POST" style="display:inline">
                    <input type="hidden" name="file_id" value="<?= $file['id'] ?>">
                    <button type="submit">Hapus</button>
                </form>
            </li>
        <?php endwhile; ?>
    </ul>
</body>
</html>
