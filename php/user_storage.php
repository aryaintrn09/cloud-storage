<?php
require 'db.php';
require 'auth.php';

header('Content-Type: application/json');

$username = $_SESSION['username'];

$stmt = $conn->prepare("SELECT id, storage_limit FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$user_id = $user['id'];

$stmt = $conn->prepare("SELECT SUM(size) AS total FROM files WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();
$total = $data['total'] ?? 0;

echo json_encode([
  "used" => (int)$total,
  "limit" => (int)$user['storage_limit']
]);
