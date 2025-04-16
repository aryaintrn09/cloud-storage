<?php
session_start();
include('../includes/db.php');
include('../includes/header.php');

// Check if the user is logged in and is an admin
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// Fetch users from the database
$query = "SELECT * FROM users";
$result = mysqli_query($conn, $query);
?>

<div class="container mt-5">
    <h2>Admin Dashboard</h2>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Username</th>
                <th>Email</th>
                <th>Storage Limit</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($user = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td><?php echo htmlspecialchars($user['storage_limit']); ?> MB</td>
                    <td>
                        <a href="manage-users.php?user_id=<?php echo $user['id']; ?>" class="btn btn-warning">Manage</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php include('../includes/footer.php'); ?>