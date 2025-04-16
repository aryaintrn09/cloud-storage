<?php
session_start();
include 'includes/file.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
}

$files = getUserFiles($_SESSION['user_id']);
?>
<h1>File Saya</h1>
<a href="upload.php">Upload File</a>
<table border="1">
    <tr>
        <th>Nama File</th>
        <th>Ukuran (KB)</th>
        <th>Aksi</th>
    </tr>
    <?php foreach ($files as $file): ?>
    <tr>
        <td><?= $file['filename'] ?></td>
        <td><?= round($file['filesize'] / 1024, 2) ?> KB</td>
        <td>
            <a href="<?= $file['filepath'] ?>" download>Download</a>
            <a href="delete.php?id=<?= $file['id'] ?>">Hapus</a>
        </td>
    </tr>
    <?php endforeach; ?>
</table>