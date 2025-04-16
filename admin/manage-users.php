<?php
session_start();
include('../includes/db.php');
include('../includes/header.php');

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: index.php');
    exit();
}

$query = "SELECT * FROM users";
$result = mysqli_query($conn, $query);

if (isset($_POST['delete_user'])) {
    $user_id = $_POST['user_id'];
    $delete_query = "DELETE FROM users WHERE id = ?";
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();
    header("Location: manage-users.php");
}

if (isset($_POST['set_limit'])) {
    $user_id = $_POST['user_id'];
    $storage_limit = $_POST['storage_limit'];
    $update_query = "UPDATE users SET storage_limit = ? WHERE id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("ii", $storage_limit, $user_id);
    $stmt->execute();
    $stmt->close();
    header("Location: manage-users.php");
}
?>

<div class="container">
    <h2 class="mt-4">Manage Users</h2>
    <table class="table table-bordered mt-3">
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Email</th>
                <th>Storage Limit (MB)</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($user = mysqli_fetch_assoc($result)) : ?>
                <tr>
                    <td><?php echo $user['id']; ?></td>
                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td>
                        <form method="POST" class="d-inline">
                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                            <input type="number" name="storage_limit" value="<?php echo $user['storage_limit']; ?>" required>
                            <button type="submit" name="set_limit" class="btn btn-primary btn-sm">Set Limit</button>
                        </form>
                    </td>
                    <td>
                        <form method="POST" class="d-inline">
                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                            <button type="submit" name="delete_user" class="btn btn-danger btn-sm">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php include('../includes/footer.php'); ?>