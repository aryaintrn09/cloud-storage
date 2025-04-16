<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) exit("Unauthorized");
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $file_id = $_POST['file_id'];

    $stmt = $pdo->prepare("SELECT * FROM files WHERE id = ? AND user_id = ?");
    $stmt->execute([$file_id, $user_id]);
    $file = $stmt->fetch();

    if ($file) {
        unlink($file['filepath']);
        $pdo->prepare("DELETE FROM files WHERE id = ?")->execute([$file_id]);
    }
}

header("Location: ../views/dashboard.php");
exit();
