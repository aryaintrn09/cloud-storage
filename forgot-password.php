<?php
session_start();
require 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $security_answer = $_POST['security_answer'];
    $new_password = $_POST['new_password'];

    // Cek apakah username ada di database
    $sql = "SELECT * FROM users WHERE username='$username'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Cek apakah jawaban keamanan sesuai
        if ($security_answer === $user['security_answer']) {
            // Update password pengguna
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $sql = "UPDATE users SET password='$hashed_password' WHERE username='$username'";

            if ($conn->query($sql) === TRUE) {
                // Redirect ke halaman login setelah reset password berhasil
                header("Location: login.php");
                exit();
            } else {
                $error = "Error resetting password.";
            }
        } else {
            $error = "Incorrect security answer!";
        }
    } else {
        $error = "No such user found!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

        <!-- Navbar -->
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Cloud Storage</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item d-flex align-items-center ms-3">
                        <div class="form-check form-switch text-white">
                            <input class="form-check-input" type="checkbox" id="darkModeToggle">
                            <label class="form-check-label" for="darkModeToggle">
                                <span id="modeIcon">🌞</span> <!-- Sun emoji for light mode -->
                            </label>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <h2>Reset Password</h2>
        <?php if (isset($error)) { echo "<div class='alert alert-danger'>$error</div>"; } ?>
        <form action="forgot-password.php" method="post">
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" id="username" name="username" required>
            </div>
            <div class="mb-3">
                <label for="security_answer" class="form-label">Security Answer</label>
                <input type="text" class="form-control" id="security_answer" name="security_answer" required>
            </div>
            <div class="mb-3">
                <label for="new_password" class="form-label">New Password</label>
                <input type="password" class="form-control" id="new_password" name="new_password" required>
            </div>
            <button type="submit" class="btn btn-primary">Reset Password</button>
            <p class="mt-3">Remember Your Old Password? <a href="login.php">Login here</a></p>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Optional: Add some JavaScript for form validation or other interactivity    // Load dark mode preference from localStorage
    document.addEventListener('DOMContentLoaded', function () {
        const darkModeToggle = document.getElementById('darkModeToggle');
        const modeIcon = document.getElementById('modeIcon');
        const isDark = localStorage.getItem('darkMode') === 'true';

        // Set the initial dark mode state based on localStorage
        if (isDark) {
            document.body.classList.add('dark-mode');
            darkModeToggle.checked = true;
            modeIcon.textContent = '🌙'; // Moon emoji for dark mode
        } else {
            modeIcon.textContent = '🌞'; // Sun emoji for light mode
        }

        // Toggle dark mode and save the preference to localStorage
        darkModeToggle.addEventListener('change', function () {
            document.body.classList.toggle('dark-mode');
            localStorage.setItem('darkMode', this.checked);

            // Change the emoji based on the new state
            if (this.checked) {
                modeIcon.textContent = '🌙'; // Moon emoji for dark mode
            } else {
                modeIcon.textContent = '🌞'; // Sun emoji for light mode
            }
        });
    });
    </script>
    <style>
        body.dark-mode {
            background-color: #121212;
            color: #ffffff;
        }

        .dark-mode .navbar {
            background-color: #1f1f1f !important;
        }

        .dark-mode .list-group-item {
            background-color: #2c2c2c;
            color: #ffffff;
            border-color: #444;
        }

        .dark-mode .modal-content {
            background-color: #1e1e1e;
            color: #ffffff;
        }

        .dark-mode .form-control {
            background-color: #2c2c2c;
            color: #ffffff;
            border-color: #444;
        }

        .dark-mode .btn-close {
            filter: invert(1);
        }

        .dark-mode .toast {
            background-color: #2c2c2c;
            color: #fff;
        }
    </style>

</body>
</html>
