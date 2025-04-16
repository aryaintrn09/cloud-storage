<?php
session_start();
include('../includes/db.php');

if (!isset($_SESSION['username'])) {
    header('Location: ../login.php');
    exit();
}

$username = $_SESSION['username'];
$file_id = $_POST['file_id'] ?? null;

if ($file_id) {
    // Prepare statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT file_name FROM files WHERE id = ? AND user_name = ?");
    $stmt->bind_param("is", $file_id, $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $file = $result->fetch_assoc();
        $file_path = '../uploads/' . $username . '/' . $file['file_name'];

        // Delete file from server
        if (file_exists($file_path)) {
            unlink($file_path);
        }

        // Delete file record from database
        $stmt = $conn->prepare("DELETE FROM files WHERE id = ? AND user_name = ?");
        $stmt->bind_param("is", $file_id, $username);
        $stmt->execute();

        echo json_encode(['status' => 'success', 'message' => 'File deleted successfully.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'File not found or you do not have permission to delete it.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'No file specified.']);
}

$stmt->close();
$conn->close();
?>