<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

require_once '../includes/db.php';

$username = $_SESSION['username'];
$user_id = $_SESSION['user_id'];

// Cek storage terpakai
$stmt = $pdo->prepare("SELECT SUM(size) AS used FROM files WHERE user_id = ?");
$stmt->execute([$user_id]);
$row = $stmt->fetch();
$used_space = $row['used'] ?? 0;

$stmt = $pdo->prepare("SELECT storage_limit FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$limit = $stmt->fetch()['storage_limit'] ?? 104857600;

$files = $pdo->prepare("SELECT * FROM files WHERE user_id = ? ORDER BY uploaded_at DESC");
$files->execute([$user_id]);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Dashboard - <?= htmlspecialchars($username) ?></title>
    <link rel="stylesheet" href="../assets/style.css">
    <script src="../assets/script.js" defer></script>
</head>
<body>
    <h2>Welcome, <?= htmlspecialchars($username) ?>!</h2>

    <form id="uploadForm" enctype="multipart/form-data" method="POST" action="../includes/upload.php">
        <input type="file" name="file" required>
        <button type="submit">Upload</button>
    </form>

    <progress id="progressBar" value="0" max="100" style="width: 300px;"></progress>
    <p>Storage: <?= round($used_space / 1024 / 1024, 2) ?>MB / <?= round($limit / 1024 / 1024) ?>MB</p>

    <h3>Files</h3>
    <ul>
        <?php while ($file = $files->fetch()): ?>
            <li>
                <?= htmlspecialchars($file['filename']) ?>
                (<a href="../uploads/<?= $username ?>/<?= urlencode($file['filename']) ?>" target="_blank">View</a>)
                <form action="../includes/delete.php" method="POST" style="display:inline">
                    <input type="hidden" name="file_id" value="<?= $file['id'] ?>">
                    <button type="submit">Delete</button>
                </form>
                <form action="../includes/rename.php" method="POST" style="display:inline">
                    <input type="hidden" name="file_id" value="<?= $file['id'] ?>">
                    <input type="text" name="new_name" placeholder="Rename">
                    <button type="submit">Rename</button>
                </form>
            </li>
        <?php endwhile; ?>
    </ul>

    <br><a href="logout.php">Logout</a>
</body>
</html>
