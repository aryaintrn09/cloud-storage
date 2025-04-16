<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/file.php';

if (!is_logged_in()) {
    header("Location: login.php");
    exit();
}

if (isset($_GET['id'])) {
    $file_id = (int)$_GET['id'];
    $username = $_SESSION['username'];
    $is_admin = is_admin() && isset($_GET['admin']);
    
    if ($is_admin) {
        // Admin can delete any file
        $stmt = $conn->prepare("SELECT filepath FROM files WHERE id = ?");
        $stmt->bind_param("i", $file_id);
    } else {
        // Regular users can only delete their own files
        $stmt = $conn->prepare("SELECT filepath FROM files f 
                               JOIN users u ON f.user_id = u.id 
                               WHERE f.id = ? AND u.username = ?");
        $stmt->bind_param("is", $file_id, $username);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $file = $result->fetch_assoc();
        if (file_exists($file['filepath'])) {
            unlink($file['filepath']);
            $stmt = $conn->prepare("DELETE FROM files WHERE id = ?");
            $stmt->bind_param("i", $file_id);
            $stmt->execute();
            
            if ($is_admin) {
                header("Location: admin.php?deleted=1");
            } else {
                header("Location: index.php?delete=success");
            }
            exit();
        }
    }
}

if (is_admin()) {
    header("Location: admin.php");
} else {
    header("Location: index.php");
}
exit();