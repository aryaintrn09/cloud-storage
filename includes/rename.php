<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) exit("Unauthorized");
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $file_id = $_POST['file_id'];
    $new_name = trim($_POST['new_name']);

    if ($new_name === '') exit("Nama baru tidak boleh kosong");

    $stmt = $pdo->prepare("SELECT * FROM files WHERE id = ? AND user_id = ?");
    $stmt->execute([$file_id, $user_id]);
    $file = $stmt->fetch();

    if ($file) {
        $old_path = $file['filepath'];
        $ext = pathinfo($old_path, PATHINFO_EXTENSION);
        $new_file = "../uploads/$username/" . $new_name . "." . $ext;

        if (rename($old_path, $new_file)) {
            $pdo->prepare("UPDATE files SET filename = ?, filepath = ? WHERE id = ?")
                ->execute([$new_name . "." . $ext, $new_file, $file_id]);
        }
    }
}

header("Location: ../views/dashboard.php");
exit();
