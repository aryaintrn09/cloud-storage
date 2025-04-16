<?php
require_once 'config.php';
require_once 'auth.php';

function upload_file($file, $username) {
    global $conn;
    
    // Validate file
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return "File upload error";
    }
    
    if (!is_allowed_file($file['name'])) {
        return "File type not allowed";
    }
    
    if ($file['size'] > MAX_FILE_SIZE) {
        return "File too large (max " . format_size(MAX_FILE_SIZE) . ")";
    }
    
    // Check storage space
    $storage_info = get_storage_info($username);
    if (($storage_info['used'] + $file['size']) > $storage_info['max']) {
        return "Not enough storage space";
    }
    
    // Prepare upload
    $user_folder = "uploads/user_" . preg_replace('/[^a-zA-Z0-9-_]/', '', $username);
    $filename = sanitize_filename(basename($file['name']));
    $target_path = $user_folder . "/" . $filename;
    
    // Handle duplicates
    $counter = 1;
    while (file_exists($target_path)) {
        $info = pathinfo($filename);
        $target_path = $user_folder . "/" . $info['filename'] . "_" . $counter . "." . $info['extension'];
        $counter++;
    }
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $target_path)) {
        $stmt = $conn->prepare("INSERT INTO files (user_id, filename, filepath, filesize) VALUES (?, ?, ?, ?)");
        $user_id = $_SESSION['user_id'];
        $stmt->bind_param("issi", $user_id, $filename, $target_path, $file['size']);
        return $stmt->execute() ? true : "Database error";
    }
    
    return "Failed to move uploaded file";
}

function delete_file($file_id, $username) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT filepath FROM files f 
                           JOIN users u ON f.user_id = u.id 
                           WHERE f.id = ? AND u.username = ?");
    $stmt->bind_param("is", $file_id, $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $file = $result->fetch_assoc();
        if (file_exists($file['filepath'])) {
            if (unlink($file['filepath'])) {
                $stmt = $conn->prepare("DELETE FROM files WHERE id = ?");
                $stmt->bind_param("i", $file_id);
                return $stmt->execute();
            }
        }
    }
    return false;
}

function rename_file($file_id, $username, $new_name) {
    global $conn;
    
    $new_name = sanitize_filename($new_name);
    if (!is_allowed_file($new_name)) {
        return "Invalid file extension";
    }
    
    // Get current file info
    $stmt = $conn->prepare("SELECT filepath, filename FROM files f 
                           JOIN users u ON f.user_id = u.id 
                           WHERE f.id = ? AND u.username = ?");
    $stmt->bind_param("is", $file_id, $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows !== 1) {
        return "File not found";
    }
    
    $file = $result->fetch_assoc();
    $old_path = $file['filepath'];
    $new_path = dirname($old_path) . "/" . $new_name;
    
    // Skip if name unchanged
    if ($old_path === $new_path) {
        return true;
    }
    
    // Check if new name exists
    if (file_exists($new_path)) {
        return "File with that name already exists";
    }
    
    // Rename file
    if (rename($old_path, $new_path)) {
        $stmt = $conn->prepare("UPDATE files SET filename = ?, filepath = ? WHERE id = ?");
        $stmt->bind_param("ssi", $new_name, $new_path, $file_id);
        return $stmt->execute() ? true : "Database update failed";
    }
    
    return "Failed to rename file";
}

function get_file_preview($filepath) {
    if (!file_exists($filepath)) {
        return '<div class="error">File not found</div>';
    }
    
    $mime = mime_content_type($filepath);
    $ext = strtolower(pathinfo($filepath, PATHINFO_EXTENSION));
    
    if (strpos($mime, 'image/') === 0) {
        return '<img src="'.htmlspecialchars($filepath).'" class="preview-img">';
    }
    elseif ($ext === 'pdf') {
        return '<embed src="'.htmlspecialchars($filepath).'" type="application/pdf" class="preview-pdf">';
    }
    elseif ($ext === 'txt' || strpos($mime, 'text/') === 0) {
        return '<pre class="preview-text">'.htmlspecialchars(file_get_contents($filepath)).'</pre>';
    }
    else {
        return '<div class="preview-generic">
                  <p>No preview available</p>
                  <a href="'.htmlspecialchars($filepath).'" download class="btn">Download</a>
                </div>';
    }
}

function get_user_files($username) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT * FROM files f 
                           JOIN users u ON f.user_id = u.id 
                           WHERE u.username = ?
                           ORDER BY upload_date DESC");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function get_all_files($filter_user = null) {
    global $conn;
    
    if ($filter_user) {
        $stmt = $conn->prepare("SELECT u.username, f.* FROM files f 
                               JOIN users u ON f.user_id = u.id 
                               WHERE u.username = ?
                               ORDER BY upload_date DESC");
        $stmt->bind_param("s", $filter_user);
    } else {
        $stmt = $conn->prepare("SELECT u.username, f.* FROM files f 
                               JOIN users u ON f.user_id = u.id 
                               ORDER BY u.username, upload_date DESC");
    }
    
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function get_storage_info($username) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT 
                            u.max_storage as max,
                            COALESCE(SUM(f.filesize), 0) as used
                           FROM users u
                           LEFT JOIN files f ON u.id = f.user_id
                           WHERE u.username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Return default values if no results
    if ($result->num_rows === 0) {
        return [
            'max' => DEFAULT_MAX_STORAGE,
            'used' => 0,
            'percentage' => 0
        ];
    }
    
    $storage = $result->fetch_assoc();
    $storage['percentage'] = $storage['max'] > 0 
        ? round(($storage['used'] / $storage['max']) * 100) 
        : 0;
    
    return $storage;
}

function get_all_users_storage() {
    global $conn;
    
    $stmt = $conn->prepare("SELECT 
                            u.id,
                            u.username,
                            u.is_admin,
                            u.max_storage,
                            COALESCE(SUM(f.filesize), 0) as used
                           FROM users u
                           LEFT JOIN files f ON u.id = f.user_id
                           GROUP BY u.id");
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function update_storage_limit($username, $gb) {
    global $conn;
    
    // Convert GB to bytes
    $bytes = $gb * 1024 * 1024 * 1024;
    $bytes = (int)$bytes;
    
    $stmt = $conn->prepare("UPDATE users SET max_storage = ? WHERE username = ?");
    $stmt->bind_param("is", $bytes, $username);
    return $stmt->execute();
}

function format_size($bytes) {
    if ($bytes == 0) {
        return '0 GB';
    }
    
    $gb = $bytes / (1024 * 1024 * 1024); // Convert to GB
    
    // Format angka: 
    // - 2 desimal jika kurang dari 10 GB
    // - 1 desimal jika kurang dari 100 GB
    // - 0 desimal jika lebih dari 100 GB
    if ($gb < 10) {
        return round($gb, 2) . ' GB';
    } elseif ($gb < 100) {
        return round($gb, 1) . ' GB';
    } else {
        return round($gb) . ' GB';
    }
}