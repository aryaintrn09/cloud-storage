<?php
require_once 'config.php';

function createUserFolder($username) {
    $safe_username = preg_replace('/[^a-zA-Z0-9-_]/', '', $username);
    $user_folder = "uploads/" . $safe_username;
    
    if (!file_exists($user_folder)) {
        mkdir($user_folder, 0755, true);
        file_put_contents($user_folder . "/index.html", "");
    }
    return $user_folder;
}

function register($username, $password) {
    global $conn;
    $username = sanitize($username);
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);
    
    try {
        $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        $stmt->bind_param("ss", $username, $hashed_password);
        
        if ($stmt->execute()) {
            createUserFolder($username);
            return true;
        }
    } catch (mysqli_sql_exception $e) {
        if ($e->getCode() == 1062) {
            return "Username sudah digunakan";
        }
    }
    return "Gagal registrasi";
}

function login($username, $password) {
    global $conn;
    $username = sanitize($username);
    $stmt = $conn->prepare("SELECT id, password, username FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        createUserFolder($user['username']);
        return true;
    }
    return false;
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function logout() {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
}
?>