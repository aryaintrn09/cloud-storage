<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cloud Storage - Main Page</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .hero-section {
            height: 100vh;
            background: linear-gradient(to right, #6a11cb, #2575fc);
            color: white;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .hero-content {
            text-align: center;
        }

        .btn-custom {
            background-color: #2575fc;
            color: white;
            border: none;
        }

        .btn-custom:hover {
            background-color: #6a11cb;
        }

        .about-section {
            padding: 50px 0;
            background-color: #f8f9fa;
        }

        .about-section h3 {
            font-size: 2rem;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

    <!-- Hero Section -->
    <div class="hero-section">
        <div class="hero-content">
            <h1 class="display-4">Welcome to Cloud Storage</h1>
            <p class="lead">Store, manage, and share your files securely.</p>
            <div class="mt-4">
                <?php if (!isset($_SESSION['user_id'])): ?>
                    <a href="login.php" class="btn btn-custom btn-lg">Login</a>
                    <a href="register.php" class="btn btn-outline-light btn-lg ms-3">Register</a>
                <?php else: ?>
                    <a href="dashboard.php" class="btn btn-custom btn-lg">Go to Dashboard</a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- About Section -->
    <div class="about-section text-center">
        <div class="container">
            <h3>What is Cloud Storage?</h3>
            <p>Cloud Storage allows you to store and access your files from anywhere, anytime. It is an essential tool for managing your documents, photos, videos, and other media securely.</p>
        </div>
    </div>

    <!-- Footer Section -->
    <footer class="bg-dark text-white text-center py-4">
        <p>&copy; 2025 Cloud Storage. All rights reserved.</p>
    </footer>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
