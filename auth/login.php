<?php
session_start();
require '../config/db.php';

$error = "";

// Login
if (isset($_POST['login'])) {
  $username = htmlspecialchars($_POST['username']);
  $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
  $stmt->execute([$username]);
  $user = $stmt->fetch();

  if ($user && password_verify($_POST['password'], $user['password'])) {
    $_SESSION['user'] = $user;
    header("Location: ../dashboard.php");
    exit;
  } else {
    $error = "Username atau password salah.";
  }
}

// Register (proses di modal)
if (isset($_POST['register'])) {
  $username = htmlspecialchars($_POST['reg_username']);
  $password = password_hash($_POST['reg_password'], PASSWORD_BCRYPT);

  $check = $pdo->prepare("SELECT id FROM users WHERE username = ?");
  $check->execute([$username]);
  if ($check->fetch()) {
    $error = "Username sudah digunakan.";
  } else {
    $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
    if ($stmt->execute([$username, $password])) {
      mkdir("../uploads/$username", 0777, true);
      header("Location: login.php");
      exit;
    } else {
      $error = "Gagal register.";
    }
  }
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Login - Cloud Storage</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="d-flex align-items-center justify-content-center vh-100">
  <div class="card shadow p-4" style="width: 400px;">
    <h3 class="text-center mb-4">Login</h3>

    <?php if ($error && isset($_POST['login'])): ?>
      <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST">
      <input class="form-control mb-3" name="username" placeholder="Username" required>
      <input class="form-control mb-3" type="password" name="password" placeholder="Password" required>
      <button class="btn btn-success w-100 mb-2" type="submit" name="login">Login</button>
    </form>

    <p class="text-center">
      Belum punya akun? <a href="#" data-bs-toggle="modal" data-bs-target="#registerModal">Register</a>
    </p>
  </div>

  <!-- REGISTER MODAL -->
  <div class="modal fade" id="registerModal" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <form method="POST">
          <div class="modal-header">
            <h5 class="modal-title">Register</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <?php if ($error && isset($_POST['register'])): ?>
              <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>
            <input class="form-control mb-3" name="reg_username" placeholder="Username" required>
            <input class="form-control mb-3" type="password" name="reg_password" placeholder="Password" required>
          </div>
          <div class="modal-footer">
            <button type="submit" name="register" class="btn btn-primary">Register</button>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

  <?php if (isset($_POST['register'])): ?>
  <script>
    var modal = new bootstrap.Modal(document.getElementById('registerModal'));
    modal.show();
  </script>
  <?php endif; ?>
</body>
</html>
