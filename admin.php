<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/file.php';

if (!is_logged_in() || !is_admin()) {
    header("Location: index.php");
    exit();
}

$current_user = get_current_user_data();
$filter_user = $_GET['user'] ?? null;

// Handle storage limit update
// Di bagian atas admin.php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_limit'])) {
    $username = $_POST['username'];
    $gb = (float)$_POST['max_storage'];
    update_storage_limit($username, $gb);
    header("Location: admin.php?updated=1");
    exit();
}

// Handle file deletion
if (isset($_GET['delete'])) {   
    $file_id = (int)$_GET['delete'];
    $stmt = $conn->prepare("SELECT filepath FROM files WHERE id = ?");
    $stmt->bind_param("i", $file_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $file = $result->fetch_assoc();
        if (file_exists($file['filepath'])) {
            unlink($file['filepath']);
            $stmt = $conn->prepare("DELETE FROM files WHERE id = ?");
            $stmt->bind_param("i", $file_id);
            $stmt->execute();
            header("Location: admin.php?deleted=1");
            exit();
        }
    }
}

// Get all data
$users = get_all_users_storage();
$files = get_all_files($filter_user);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header class="header">
            <h1>Admin Dashboard</h1>
            <div class="user-menu">
                <span>Logged in as: <?= htmlspecialchars($current_user['username']) ?></span>
                <a href="index.php" class="btn">User View</a>
                <a href="logout.php" class="btn logout-btn">Logout</a>
            </div>
        </header>

        <main class="content">
            <?php if (isset($_GET['deleted'])): ?>
                <div class="alert success">File deleted successfully</div>
            <?php endif; ?>

            <section class="users-section">
                <h2>User Management</h2>
                <table class="users-table">
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Storage Used</th>
                            <th>Storage Limit</th>
                            <th>Usage</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): 
                            $usage = $user['max_storage'] > 0 
                                ? round(($user['used'] / $user['max_storage']) * 100) 
                                : 0;
                        ?>
                        <tr>
                            <td>
                                <?= htmlspecialchars($user['username']) ?>
                                <?php if ($user['is_admin']): ?>
                                    <span class="admin-badge">Admin</span>
                                <?php endif; ?>
                            </td>
                            <td><?= format_size($user['used']) ?></td>
                            <td>
                                <form method="POST" class="storage-form">
                                    <input type="hidden" name="username" value="<?= htmlspecialchars($user['username']) ?>">
                                    <input type="number" name="max_storage" 
                                        value="<?= round($user['max_storage'] / (1024 * 1024 * 1024)) ?>" 
                                        min="1" step="0.1"> GB
                                    <button type="submit" name="update_limit" class="btn small">Update</button>
                                </form>
                            </td>
                            <td>
                                <div class="storage-bar">
                                    <div class="storage-used" style="width: <?= $usage ?>%"></div>
                                </div>
                                <?= $usage ?>%
                            </td>
                            <td>
                                <a href="?user=<?= urlencode($user['username']) ?>" class="btn small">View Files</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </section>

            <section class="files-section">
                <h2>
                    <?= $filter_user ? "Files for: " . htmlspecialchars($filter_user) : "All Files" ?>
                    <?php if ($filter_user): ?>
                        <a href="admin.php" class="btn small">Show All</a>
                    <?php endif; ?>
                </h2>
                
                <?php if (empty($files)): ?>
                    <p>No files found.</p>
                <?php else: ?>
                    <table class="files-table">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Filename</th>
                                <th>Size</th>
                                <th>Uploaded</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($files as $file): ?>
                            <tr>
                                <td><?= htmlspecialchars($file['username']) ?></td>
                                <td><?= htmlspecialchars($file['filename']) ?></td>
                                <td><?= format_size($file['filesize']) ?></td>
                                <td><?= date('M d, Y H:i', strtotime($file['upload_date'])) ?></td>
                                <td class="actions">
                                    <button onclick="showPreview('<?= htmlspecialchars($file['filepath']) ?>')" 
                                            class="btn small preview-btn">Preview</button>
                                    <a href="<?= htmlspecialchars($file['filepath']) ?>" 
                                       download 
                                       class="btn small download-btn">Download</a>
                                    <a href="admin.php?delete=<?= $file['id'] ?>" 
                                       onclick="return confirm('Delete this file?')" 
                                       class="btn small delete-btn">Delete</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </section>
        </main>
    </div>

    <!-- Preview Modal -->
    <div id="preview-modal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <div id="preview-content"></div>
        </div>
    </div>

    <script>
    function showPreview(filepath) {
        fetch('preview.php?file=' + encodeURIComponent(filepath))
            .then(response => response.text())
            .then(html => {
                document.getElementById('preview-content').innerHTML = html;
                document.getElementById('preview-modal').style.display = 'block';
            });
    }

    function closeModal() {
        document.getElementById('preview-modal').style.display = 'none';
    }

    // Close modal when clicking outside
    window.onclick = function(event) {
        if (event.target === document.getElementById('preview-modal')) {
            closeModal();
        }
    }
    </script>
</body>
</html>