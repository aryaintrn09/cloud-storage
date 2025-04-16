<?php
include 'includes/auth.php';

if (isset($_POST['register'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    if (register($username, $password)) {
        header("Location: login.php");
    } else {
        echo "Registrasi gagal!";
    }
}
?>
<form method="POST">
    <input type="text" name="username" placeholder="Username" required>
    <input type="password" name="password" placeholder="Password" required>
    <button type="submit" name="register">Register</button>
</form>
<a href="login.php">Login</a>