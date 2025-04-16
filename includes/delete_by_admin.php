<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    die("Unauthorized");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $file_id = $_POST['file_id'];

    $stmt = $pdo->prepare("SELECT * FROM files WHERE id = ?");
    $stmt->execute([$file_id]);
    $file = $stmt->fetch();

    if ($file) {
        unlink($file['filepath']);
        $pdo->prepare("DELETE FROM files WHERE id = ?")->execute([$file_id]);
    }
}

header("Location: ../views/admin.php");
exit();
