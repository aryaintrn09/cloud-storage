<?php
require_once '../includes/db.php';

$msg = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT);

    // Cek apakah user sudah ada
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->rowCount() > 0) {
        $msg = "Username sudah digunakan!";
    } else {
        $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        $stmt->execute([$username, $password]);

        // Buat folder user
        mkdir("../uploads/$username", 0777, true);
        header("Location: login.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Register</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <h2>Register</h2>
    <form method="POST">
        <input type="text" name="username" required placeholder="Username">
        <input type="password" name="password" required placeholder="Password">
        <button type="submit">Register</button>
    </form>
    <p><?= htmlspecialchars($msg) ?></p>
    <a href="login.php">Sudah punya akun?</a>
</body>
</html>
