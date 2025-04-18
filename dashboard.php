<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Ambil nama pengguna berdasarkan user_id
$sql = "SELECT username FROM users WHERE id='$user_id'";
$result = $conn->query($sql);
$user = $result->fetch_assoc();
$username = $user['username'];

// Buat folder berdasarkan nama pengguna jika belum ada
$user_folder = "uploads/$username";
if (!is_dir($user_folder)) {
    mkdir($user_folder, 0777, true);  // Membuat folder jika belum ada
}

$files = array_diff(scandir($user_folder), array('.', '..'));

// Check for successful file upload through query string
//  
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cloud Storage</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet"> <!-- Font Awesome -->
    <style>
        .file-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        .btn-group {
            display: flex;
            gap: 10px;
        }
        .container {
            margin-top: 20px;
        }
        /* Dark Mode Custom Styles */
        body.dark-mode {
            background-color: #121212;
            color: white;
        }
        .navbar-dark-mode {
            background-color: #333;
        }
        .btn-dark-mode {
            background-color: #6c757d;
            color: white;
        }
        .btn-dark-mode:hover {
            background-color: #5a6268;
        }
    </style>
</head>
<body id="body" class="light-mode">

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark navbar-dark-mode">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Cloud Storage</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="#">Welcome, <?= $_SESSION['username']; ?>!</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout</a>
                    </li>
                    <li class="nav-item">
                        <button class="btn btn-dark-mode" id="toggleModeBtn">
                            <i id="modeIcon" class="fas fa-moon"></i> <!-- Icon moon by default -->
                        </button>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">
        <h2>Your Files</h2>
        
        <!-- Upload Form -->
        <form action="upload.php" method="post" enctype="multipart/form-data">
            <div class="mb-3">
                <input type="file" name="file" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Upload File</button>
        </form>

        <hr>

        <h3>Files</h3>
        <div class="list-group">
            <?php foreach ($files as $file): ?>
                <div class="list-group-item file-item">
                    <span><?= $file ?></span>
                    <div class="btn-group">
                        <button class="btn btn-warning btn-sm" onclick="showRenameModal('<?= $file ?>')">Rename</button>
                        <button class="btn btn-danger btn-sm" onclick="showDeleteModal('<?= $file ?>')">Delete</button>
                        <button class="btn btn-info btn-sm" onclick="previewFile('<?= $file ?>')">Preview</button>
                        <button class="btn btn-success btn-sm" onclick="downloadFile('<?= $file ?>')">Download</button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Modal for Rename -->
    <div class="modal fade" id="renameModal" tabindex="-1" aria-labelledby="renameModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="renameModalLabel">Rename File</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="renameForm">
                        <div class="mb-3">
                            <label for="newName" class="form-label">New File Name</label>
                            <input type="text" class="form-control" id="newName" required>
                        </div>
                        <input type="hidden" id="oldFileName">
                        <button type="submit" class="btn btn-primary">Rename</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for Delete Confirmation -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Delete File</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this file?</p>
                    <input type="hidden" id="deleteFileName">
                    <button type="button" class="btn btn-danger" onclick="deleteFile()">Delete</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for Preview -->
    <div class="modal fade" id="previewModal" tabindex="-1" aria-labelledby="previewModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="previewModalLabel">File Preview</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="previewContent">
                    <!-- Content will be injected here dynamically -->
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Notification for AJAX actions -->
    <div class="toast-container position-fixed top-0 end-0 p-3">
        <div id="toast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header">
                <strong class="me-auto" id="toastTitle"></strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body" id="toastMessage"></div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Show Toast after upload success
        <?php if ($upload_success): ?>
            showToast('File Uploaded', 'Your file has been successfully uploaded!');
        <?php endif; ?>

        // Function to toggle dark mode
        function toggleDarkMode() {
            let body = document.getElementById('body');
            let navbar = document.querySelector('.navbar');
            let button = document.getElementById('toggleModeBtn');
            let icon = document.getElementById('modeIcon');
            
            // Toggle between light and dark mode
            if (body.classList.contains('light-mode')) {
                body.classList.remove('light-mode');
                body.classList.add('dark-mode');
                navbar.classList.add('navbar-dark-mode');
                icon.classList.remove('fa-moon');
                icon.classList.add('fa-sun');
                button.classList.add('btn-dark-mode');
                localStorage.setItem('theme', 'dark');
            } else {
                body.classList.remove('dark-mode');
                body.classList.add('light-mode');
                navbar.classList.remove('navbar-dark-mode');
                icon.classList.remove('fa-sun');
                icon.classList.add('fa-moon');
                button.classList.remove('btn-dark-mode');
                localStorage.setItem('theme', 'light');
            }
        }

        // Check local storage for theme preference
        window.onload = function() {
            let savedTheme = localStorage.getItem('theme');
            if (savedTheme === 'dark') {
                toggleDarkMode();
            }
        };

        // Event listener for dark mode toggle button
        document.getElementById('toggleModeBtn').addEventListener('click', toggleDarkMode);

        // Function to show the Rename Modal and set the current filename
        function showRenameModal(filename) {
            $('#oldFileName').val(filename);
            $('#newName').val(filename); // Set the current file name in the input field
            $('#renameModal').modal('show');
        }

        // Function to show the Delete Modal
        function showDeleteModal(filename) {
            $('#deleteFileName').val(filename);
            $('#deleteModal').modal('show');
        }

        // Function to handle Delete file action using AJAX
        function deleteFile() {
            const filename = $('#deleteFileName').val();
            $.ajax({
                url: 'delete.php',
                method: 'POST',
                data: { filename: filename },
                success: function(response) {
                    showToast('File Deleted', response);
                    location.reload();
                }
            });
            $('#deleteModal').modal('hide');
        }

        // Function to handle Rename file action using AJAX
        function renameFile() {
            const newName = $('#newName').val();
            const oldName = $('#oldFileName').val();
            $.ajax({
                url: 'rename.php',
                method: 'POST',
                data: { oldName: oldName, newName: newName },
                success: function(response) {
                    showToast('File Renamed', response);
                    location.reload();
                }
            });
            $('#renameModal').modal('hide');
        }

        // Function to display Toast notification
        function showToast(title, message) {
            $('#toastTitle').text(title);
            $('#toastMessage').text(message);
            var toast = new bootstrap.Toast($('#toast')[0], {
                delay: 5000 // Set the delay (in milliseconds) for the toast to disappear (5 seconds)
            });
            toast.show(); // Show the toast
        }

        // Function to handle file preview
        function previewFile(filename) {
            const fileExtension = filename.split('.').pop().toLowerCase();
            const previewUrl = 'uploads/<?= $username ?>/' + filename;
            let content = '';

            if (['jpg', 'jpeg', 'png'].includes(fileExtension)) {
                content = `<img src="${previewUrl}" class="img-fluid" alt="File Preview">`;
            } else if (fileExtension === 'pdf') {
                content = `<embed src="${previewUrl}" type="application/pdf" width="100%" height="500px">`;
            } else if (fileExtension === 'mp4') {
                content = `<video controls width="100%">
                            <source src="${previewUrl}" type="video/mp4">
                            Your browser does not support the video tag.
                        </video>`;
            } else if (fileExtension === 'mp3') {
                content = `<audio controls>
                            <source src="${previewUrl}" type="audio/mp3">
                            Your browser does not support the audio element.
                        </audio>`;
            } else {
                content = `<p>Preview not available for this file type.</p>`;
            }

            $('#previewContent').html(content);
            var myModal = new bootstrap.Modal(document.getElementById('previewModal'), {
                keyboard: false
            });
            myModal.show();
        }

        // Function to handle file download
        function downloadFile(filename) {
            const fileUrl = 'download.php?filename=' + filename;
            window.location.href = fileUrl; // Trigger file download
        }

        // Handle form submission for rename
        $('#renameForm').on('submit', function(e) {
            e.preventDefault();
            renameFile();
        });
    </script>
</body>
</html>
