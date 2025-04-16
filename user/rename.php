<?php
session_start();
include('../includes/db.php');

if (!isset($_SESSION['username'])) {
    header('Location: ../login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $oldName = $_POST['oldName'];
    $newName = $_POST['newName'];
    $username = $_SESSION['username'];
    $userDir = '../uploads/' . $username . '/';

    // Validate new file name
    if (empty($newName) || preg_match('/[\/\\:*?"<>|]/', $newName)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid file name.']);
        exit();
    }

    // Rename the file
    if (file_exists($userDir . $oldName)) {
        if (rename($userDir . $oldName, $userDir . $newName)) {
            echo json_encode(['status' => 'success', 'message' => 'File renamed successfully.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error renaming file.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'File does not exist.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
?>