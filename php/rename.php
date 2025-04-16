<?php
require 'db.php';
require 'auth.php';

$id = $_POST['id'];
$newName = basename($_POST['new_name']);
$username = $_SESSION['username'];

$stmt = $conn->prepare("SELECT f.filepath, f.filename, f.user_id FROM files f 
    JOIN users u ON f.user_id = u.id WHERE f.id = ? AND u.username = ?");
$stmt->bind_param("is", $id, $username);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();

if ($row) {
    $oldPath = $row['filepath'];
    $ext = pathinfo($oldPath, PATHINFO_EXTENSION);
    $newPath = dirname($oldPath) . "/" . $newName;

    // Tambahkan ekstensi kalau hilang
    if (!str_ends_with($newPath, ".$ext")) {
        $newPath .= ".$ext";
    }

    if (rename($oldPath, $newPath)) {
        $stmt = $conn->prepare("UPDATE files SET filename = ?, filepath = ? WHERE id = ?");
        $stmt->bind_param("ssi", $newName, $newPath, $id);
        $stmt->execute();
        echo "File berhasil diubah namanya.";
    } else {
        echo "Gagal mengganti nama file.";
    }
} else {
    echo "File tidak ditemukan.";
}
?>
