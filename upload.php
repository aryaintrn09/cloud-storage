<?php
session_start();
require 'config.php';

// Cek apakah pengguna sudah login
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

// Tentukan folder tujuan penyimpanan
$user_folder = "uploads/$username";
if (!is_dir($user_folder)) {
    mkdir($user_folder, 0777, true);  // Membuat folder jika belum ada
}

// Proses file upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['file'])) {
    $file = $_FILES['file'];
    $file_name = $file['name'];
    $file_tmp = $file['tmp_name'];
    $file_size = $file['size'];
    $file_error = $file['error'];

    // Tentukan folder tujuan berdasarkan pilihan pengguna
    $folder = isset($_POST['folder']) && !empty($_POST['folder']) ? $_POST['folder'] : ''; 
    $upload_folder = $user_folder;  // Default folder adalah folder utama

    // Jika pengguna memilih folder, tentukan folder tujuan upload
    if (!empty($folder)) {
        $upload_folder .= '/' . $folder;
        if (!is_dir($upload_folder)) {
            mkdir($upload_folder, 0777, true);  // Membuat folder jika belum ada
        }
    }

    // Tentukan ekstensi file yang diizinkan
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'pdf', 'mp4', 'mp3', 'docx', 'xlsx', 'txt', 'zip', 'rar'];
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

    // Cek apakah ekstensi file valid
    if (in_array($file_ext, $allowed_extensions)) {
        // Cek apakah file tidak ada error
        if ($file_error === 0) {
            // Tentukan path file tujuan
            $file_dest = $upload_folder . '/' . $file_name;

            // Cek jika file dengan nama yang sama sudah ada di server
            if (file_exists($file_dest)) {
                // Jika file sudah ada, tambahkan timestamp untuk menghindari duplikasi
                $file_name = time() . '-' . $file_name;
                $file_dest = $upload_folder . '/' . $file_name;
            }

            // Pindahkan file ke direktori yang ditentukan
            if (move_uploaded_file($file_tmp, $file_dest)) {
                // Simpan informasi file ke database jika perlu
                $sql = "INSERT INTO files (filename, file_path, user_id) VALUES ('$file_name', '$file_dest', '$user_id')";
                if ($conn->query($sql) === TRUE) {
                    // Redirect ke dashboard setelah upload berhasil
                    header("Location: dashboard.php?upload_success=true");
                    exit;
                } else {
                    echo "Error: " . $conn->error;
                }
            } else {
                echo "Failed to upload the file.";
            }
        } else {
            echo "There was an error uploading the file.";
        }
    } else {
        echo "Invalid file type.";
    }
}
?>
