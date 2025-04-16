<?php
session_start();
include_once 'includes/db.php';
include_once 'includes/header.php';
?>

<div class="container mt-5">
    <h1>Welcome to Cloud Storage</h1>
    <p>Your secure and easy-to-use cloud storage solution.</p>
    <div class="mt-4">
        <a href="login.php" class="btn btn-primary">Login</a>
        <a href="register.php" class="btn btn-secondary">Register</a>
    </div>
</div>

<?php
include_once 'includes/footer.php';
?>