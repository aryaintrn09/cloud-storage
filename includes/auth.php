<?php
require_once 'config.php';

function create_user_folder($username) {
    $safe_username = preg_replace('/[^a-zA-Z0-9-_]/', '', $username);
    $folder_path = "uploads/user_" . $safe_username;
    
    if (!file_exists($folder_path)) {
        mkdir($folder_path, 0755, true);
        file_put_contents($folder_path . "/index.html", "");
    }
    return $folder_path;
}

function register_user($username, $password, $is_admin = false) {
    global $conn;
    
    $username = sanitize($username);
    $hashed_pw = password_hash($password, PASSWORD_BCRYPT);
    $max_storage = $is_admin ? PHP_INT_MAX : DEFAULT_MAX_STORAGE;

    try {
        $stmt = $conn->prepare("INSERT INTO users (username, password, is_admin, max_storage) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssii", $username, $hashed_pw, $is_admin, $max_storage);
        
        if ($stmt->execute()) {
            create_user_folder($username);
            return true;
        }
    } catch (mysqli_sql_exception $e) {
        if ($e->getCode() == 1062) {
            return "Username already exists";
        }
        return "Registration failed";
    }
    return false;
}

function login_user($username, $password) {
    global $conn;
    
    $username = sanitize($username);
    $stmt = $conn->prepare("SELECT id, username, password, is_admin, max_storage FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['is_admin'] = $user['is_admin'];
            $_SESSION['max_storage'] = $user['max_storage'];
            create_user_folder($user['username']);
            return true;
        }
    }
    return false;
}

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function is_admin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'];
}

function logout_user() {
    $_SESSION = [];
    session_destroy();
    header("Location: login.php");
    exit();
}

function get_current_user_data() {
    if (!isset($_SESSION['user_id'])) {
        return null;
    }
    
    return [
        'id' => $_SESSION['user_id'],
        'username' => $_SESSION['username'],
        'is_admin' => $_SESSION['is_admin'],
        'max_storage' => $_SESSION['max_storage'] ?? DEFAULT_MAX_STORAGE
    ];
}
?>