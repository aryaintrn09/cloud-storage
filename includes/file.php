<?php
require_once 'config.php';

function uploadFile($username, $file) {
    global $conn;
    
    $user_folder = "uploads/" . preg_replace('/[^a-zA-Z0-9-_]/', '', $username);
    $filename = sanitize_filename(basename($file["name"]));
    $target_file = $user_folder . "/" . $filename;
    $filesize = $file["size"];
    
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'txt', 'zip'];
    $file_extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    
    if (!in_array($file_extension, $allowed_extensions)) {
        return "Ekstensi file tidak diizinkan";
    }
    
    if ($filesize > 10 * 1024 * 1024) {
        return "Ukuran file terlalu besar (max 10MB)";
    }
    
    if (file_exists($target_file)) {
        return "File dengan nama yang sama sudah ada";
    }
    
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        $stmt = $conn->prepare("INSERT INTO files (user_id, filename, filepath, filesize) 
                               VALUES ((SELECT id FROM users WHERE username = ?), ?, ?, ?)");
        $stmt->bind_param("sssi", $username, $filename, $target_file, $filesize);
        return $stmt->execute() ? true : "Gagal menyimpan ke database";
    }
    return "Gagal mengupload file";
}

function deleteFile($file_id, $username) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT filepath FROM files f 
                           JOIN users u ON f.user_id = u.id 
                           WHERE f.id = ? AND u.username = ?");
    $stmt->bind_param("is", $file_id, $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $file = $result->fetch_assoc();

    if ($file && file_exists($file['filepath'])) {
        if (unlink($file['filepath'])) {
            $stmt = $conn->prepare("DELETE FROM files WHERE id = ?");
            $stmt->bind_param("i", $file_id);
            return $stmt->execute();
        }
    }
    return false;
}

function getUserFiles($username) {
    global $conn;
    $stmt = $conn->prepare("SELECT f.* FROM files f 
                           JOIN users u ON f.user_id = u.id 
                           WHERE u.username = ? 
                           ORDER BY upload_date DESC");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function formatFileSize($bytes) {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    }
    return $bytes . ' bytes';
}
?>