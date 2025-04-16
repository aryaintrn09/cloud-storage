<?php
require 'db.php';

$username = $_POST['username'];
$password = password_hash($_POST['password'], PASSWORD_DEFAULT);

$stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
$stmt->bind_param("ss", $username, $password);

if ($stmt->execute()) {
    mkdir("../uploads/$username"); // folder user
    header("Location: ../index.html?register=success");
} else {
    echo "Username sudah digunakan.";
}
?>
