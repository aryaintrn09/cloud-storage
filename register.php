<?php
session_start();
if (isset($_SESSION['user_id'])) {
  header("Location: dashboard.php");
  exit();
}

require 'includes/db.php';

$error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $username = trim($_POST['username']);
  $email = trim($_POST['email']);
  $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

  // Validasi
  if (empty($username) || empty($email) || empty($_POST['password'])) {
    $error = "Semua field harus diisi.";
  } else {
    $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
      $error = "Email sudah terdaftar.";
    } else {
      $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
      $stmt->bind_param("sss", $username, $email, $password);
      $stmt->execute();

      // Buat folder untuk user
      if (!file_exists("uploads/$username")) {
        mkdir("uploads/$username", 0777, true);
      }

      header("Location: index.php");
      exit();
    }
  }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Register - Cloud Storage</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container">
  <div class="row justify-content-center align-items-center" style="height:100vh;">
    <div class="col-md-5">
      <div class="card shadow-lg">
        <div class="card-body">
          <h3 class="text-center mb-4">Register</h3>
          <?php if ($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
          <?php endif; ?>
          <form method="post">
            <div class="mb-3">
              <label>Username</label>
              <input type="text" name="username" class="form-control" required>
            </div>
            <div class="mb-3">
              <label>Email</label>
              <input type="email" name="email" class="form-control" required>
            </div>
            <div class="mb-3">
              <label>Password</label>
              <input type="password" name="password" class="form-control" required>
            </div>
            <button class="btn btn-success w-100">Register</button>
          </form>
          <p class="text-center mt-3">
            Sudah punya akun? <a href="index.php">Login di sini</a>
          </p>
        </div>
      </div>
    </div>
  </div>
</div>
</body>
</html>
