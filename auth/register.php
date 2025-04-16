<?php
require '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = htmlspecialchars($_POST['username']);
  $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

  $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
  if ($stmt->execute([$username, $password])) {
    mkdir("../uploads/$username", 0777, true);
    header("Location: login.php");
    exit;
  } else {
    echo "Username sudah digunakan.";
  }
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Register</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container">
  <h2 class="mt-5">Register</h2>
  <form method="POST">
    <input class="form-control mb-2" name="username" placeholder="Username" required>
    <input class="form-control mb-2" type="password" name="password" placeholder="Password" required>
    <button class="btn btn-primary" type="submit">Register</button>
  </form>
</body>
</html>
