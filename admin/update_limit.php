<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['is_admin'] != 1) {
  die("Access denied");
}

$id = $_POST['id'];
$limit = $_POST['limit'] * 1048576; // dari MB ke Byte

$stmt = $pdo->prepare("UPDATE users SET storage_limit = ? WHERE id = ?");
$stmt->execute([$limit, $id]);

header("Location: index.php");
