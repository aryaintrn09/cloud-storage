<?php
require 'db.php';
require 'auth.php';

$file_id = $_POST['id'];
$username = $_SESSION['username'];

$stmt = $conn->prepare("SELECT f.filepath FROM files f 
    JOIN users u ON f.user_id = u.id WHERE f.id = ? AND u.username = ?");
$stmt->bind_param("is", $file_id, $username);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    unlink($row['filepath']);
    $conn->query("DELETE FROM files WHERE id = $file_id");
    echo "File berhasil dihapus.";
} else {
    echo "File tidak ditemukan atau tidak memiliki akses.";
}
?>
