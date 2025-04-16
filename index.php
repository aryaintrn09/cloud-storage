<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/file.php';

if (!is_logged_in()) {
    header("Location: login.php");
    exit();
}

$user = get_current_user_data();
$username = $user['username'];

// Handle file upload
$upload_msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $upload_msg = upload_file($_FILES['file'], $username);
    if ($upload_msg === true) {
        header("Location: index.php?upload=success");
        exit();
    }
}

// Get user files and storage info
$files = get_user_files($username);
$storage = get_storage_info($username);
$usage_percent = $storage['percentage'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Cloud Storage</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header class="header">
            <h1>Cloud Storage</h1>
            <div class="user-menu">
                <span>Welcome, <?= htmlspecialchars($username) ?></span>
                <?php if (is_admin()): ?>
                    <a href="admin.php" class="btn admin-btn">Admin Panel</a>
                <?php endif; ?>
                <a href="logout.php" class="btn logout-btn">Logout</a>
            </div>
        </header>

        <main class="content">
            <section class="storage-section">
                <h2>Storage Overview</h2>
                <div class="storage-info">
                    <p>Used: <?= format_size($storage['used']) ?> of <?= format_size($storage['max']) ?></p>
                    <div class="storage-bar">
                        <div class="storage-used" style="width: <?= $usage_percent ?>%"></div>
                    </div>
                    <p><?= $usage_percent ?>% used</p>
                </div>
            </section>

            <section class="upload-section">
                <h2>Upload File</h2>
                <?php if (isset($_GET['upload'])): ?>
                    <div class="alert success">File uploaded successfully!</div>
                <?php elseif (!empty($upload_msg)): ?>
                    <div class="alert error"><?= $upload_msg ?></div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data" class="upload-form">
                    <div class="form-group">
                        <input type="file" name="file" id="file-input" required>
                        <label for="file-input" class="file-label">Choose File (Max <?= format_size(MAX_FILE_SIZE) ?>)</label>
                    </div>
                    <button type="submit" class="btn upload-btn">Upload</button>
                </form>
            </section>

            <section class="files-section">
                <h2>Your Files</h2>
                <?php if (empty($files)): ?>
                    <p>No files uploaded yet.</p>
                <?php else: ?>
                    <table class="files-table">
                        <thead>
                            <tr>
                                <th>Filename</th>
                                <th>Size</th>
                                <th>Uploaded</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($files as $file): ?>
                            <tr>
                                <td><?= htmlspecialchars($file['filename']) ?></td>
                                <td><?= format_size($file['filesize']) ?></td>
                                <td><?= date('M d, Y H:i', strtotime($file['upload_date'])) ?></td>
                                <td class="actions">
                                    <button onclick="showPreview('<?= htmlspecialchars($file['filepath']) ?>')" 
                                            class="btn preview-btn">Preview</button>
                                    <button onclick="showRenameModal(<?= $file['id'] ?>, '<?= htmlspecialchars($file['filename']) ?>')" 
                                            class="btn rename-btn">Rename</button>
                                    <a href="<?= htmlspecialchars($file['filepath']) ?>" 
                                       download 
                                       class="btn download-btn">Download</a>
                                    <a href="delete.php?id=<?= $file['id'] ?>" 
                                       onclick="return confirm('Delete this file?')" 
                                       class="btn delete-btn">Delete</a>
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

    <!-- Rename Modal -->
    <div id="rename-modal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2>Rename File</h2>
            <form id="rename-form" method="POST" action="rename.php">
                <input type="hidden" name="file_id" id="rename-file-id">
                <div class="form-group">
                    <label for="new-filename">New Filename:</label>
                    <input type="text" id="new-filename" name="new_filename" required>
                </div>
                <button type="submit" class="btn">Save</button>
            </form>
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

    function showRenameModal(fileId, currentName) {
        document.getElementById('rename-file-id').value = fileId;
        document.getElementById('new-filename').value = currentName;
        document.getElementById('rename-modal').style.display = 'block';
    }

    function closeModal() {
        document.getElementById('preview-modal').style.display = 'none';
        document.getElementById('rename-modal').style.display = 'none';
    }

    // Close modal when clicking outside
    window.onclick = function(event) {
        const modals = ['preview-modal', 'rename-modal'];
        modals.forEach(modalId => {
            const modal = document.getElementById(modalId);
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        });
    }
    </script>
</body>
</html>