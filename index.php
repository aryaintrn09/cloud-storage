<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/file.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];
$files = getUserFiles($username);
$storage_used = 0;
foreach ($files as $file) {
    $storage_used += $file['filesize'];
}
$storage_percentage = count($files) > 0 ? round(($storage_used / (10 * 1024 * 1024)) * 100) : 0;
?>
<!DOCTYPE html>
<html>
<head>
    <title>My Files - Cloud Storage</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Selamat datang, <?= htmlspecialchars($username) ?>!</h1>
            <nav>
                <a href="upload.php" class="btn">Upload File</a>
                <a href="logout.php" class="btn danger">Logout</a>
            </nav>
        </header>
        
        <div class="storage-info">
            <p>Penyimpanan digunakan: <?= formatFileSize($storage_used) ?> dari 10 MB</p>
            <div class="storage-bar">
                <div class="storage-used" style="width: <?= $storage_percentage ?>%"></div>
            </div>
        </div>
        
        <?php if (isset($_GET['upload_success'])): ?>
            <div class="alert success">File berhasil diupload!</div>
        <?php endif; ?>
        
        <?php if (isset($_GET['delete_success'])): ?>
            <div class="alert success">File berhasil dihapus!</div>
        <?php endif; ?>
        
        <?php if (empty($files)): ?>
            <p>Anda belum memiliki file.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Nama File</th>
                        <th>Ukuran</th>
                        <th>Tanggal Upload</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($files as $file): ?>
                    <tr>
                        <td><?= htmlspecialchars($file['filename']) ?></td>
                        <td><?= formatFileSize($file['filesize']) ?></td>
                        <td><?= date('d M Y H:i', strtotime($file['upload_date'])) ?></td>
                        <td class="actions">
                            <a href="<?= htmlspecialchars($file['filepath']) ?>" download class="btn">Download</a>
                            <a href="delete.php?id=<?= $file['id'] ?>" class="btn danger" onclick="return confirm('Yakin ingin menghapus file ini?')">Hapus</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>