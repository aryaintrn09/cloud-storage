<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/file.php';

if (!is_logged_in()) {
    die("Access denied");
}

if (isset($_GET['file'])) {
    $filepath = $_GET['file'];
    $username = $_SESSION['username'];
    
    // Verify file belongs to user or admin is viewing
    $allowed = false;
    
    if (is_admin()) {
        $allowed = true;
    } else {
        $user_folder = "uploads/user_" . preg_replace('/[^a-zA-Z0-9-_]/', '', $username);
        $allowed = strpos(realpath($filepath), realpath($user_folder)) === 0;
    }

    if ($allowed && file_exists($filepath)) {
        echo get_file_preview($filepath);
    } else {
        echo '<div class="alert error">Access denied</div>';
    }
} else {
    echo '<div class="alert error">File not specified</div>';
}
?>