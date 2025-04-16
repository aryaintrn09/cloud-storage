<?php
include 'config.php';

// Upload File
function uploadFile($user_id, $file) {
    global $conn;
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($file["name"]);
    $filesize = $file["size"];

    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        $stmt = $conn->prepare("INSERT INTO files (user_id, filename, filepath, filesize) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("issi", $user_id, $file["name"], $target_file, $filesize);
        return $stmt->execute();
    }
    return false;
}

// Hapus File
function deleteFile($file_id, $user_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT filepath FROM files WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $file_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $file = $result->fetch_assoc();

    if ($file && unlink($file['filepath'])) {
        $stmt = $conn->prepare("DELETE FROM files WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $file_id, $user_id);
        return $stmt->execute();
    }
    return false;
}

// Ambil Daftar File User
function getUserFiles($user_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM files WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
?>