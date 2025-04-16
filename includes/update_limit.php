<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    die("Unauthorized");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'];
    $new_limit = (int) $_POST['new_limit'] * 1024 * 1024; // Convert to bytes

    $stmt = $pdo->prepare("UPDATE users SET storage_limit = ? WHERE id = ?");
    $stmt->execute([$new_limit, $user_id]);
}

header("Location: ../views/admin.php");
exit();
