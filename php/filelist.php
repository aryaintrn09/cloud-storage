<?php
require 'db.php';
require 'auth.php';

header('Content-Type: application/json');

$username = $_SESSION['username'];

$stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    echo json_encode([]);
    exit;
}

$user_id = $user['id'];

$stmt = $conn->prepare("SELECT id, filename, size, uploaded_at FROM files WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$files = [];
while ($row = $result->fetch_assoc()) {
    $row['size'] = round($row['size'] / 1024, 2); // KB
    $files[] = $row;
}

echo json_encode($files);
