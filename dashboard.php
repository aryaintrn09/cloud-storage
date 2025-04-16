<?php
require 'includes/auth.php';
require 'includes/db.php';

$username = $_SESSION['username'];
$dir = "uploads/$username";

// Hitung total size yang digunakan (dalam bytes)
function getFolderSize($dir) {
  $size = 0;
  foreach (scandir($dir) as $file) {
    if ($file != "." && $file != "..") {
      $path = $dir . "/" . $file;
      $size += is_file($path) ? filesize($path) : getFolderSize($path);
    }
  }
  return $size;
}

// Ambil limit penyimpanan dari database
$userQuery = $pdo->prepare("SELECT storage_limit FROM users WHERE username = ?");
$userQuery->execute([$username]);
$user = $userQuery->fetch();

$limitMB = $user['storage_limit'] ?? 100; // default 100MB
$limitBytes = $limitMB * 1024 * 1024;
$usedBytes = getFolderSize($dir);
$freeBytes = max($limitBytes - $usedBytes, 0);
$usedPercent = round(($usedBytes / $limitBytes) * 100, 2);

// Format fungsi
function formatSize($bytes) {
  if ($bytes >= 1073741824) return round($bytes / 1073741824, 2) . ' GB';
  elseif ($bytes >= 1048576) return round($bytes / 1048576, 2) . ' MB';
  elseif ($bytes >= 1024) return round($bytes / 1024, 2) . ' KB';
  else return $bytes . ' B';
}

// Ambil daftar file yang ada di folder user
$files = array_diff(scandir($dir), array('.', '..'));

// Cek status hampir penuh / penuh
$isFull = $usedBytes >= $limitBytes;
$isAlmostFull = !$isFull && $usedPercent >= 90;
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    /* Drag and Drop Styles */
    .dropzone {
      border: 2px dashed #007bff;
      padding: 30px;
      text-align: center;
      cursor: pointer;
      position: relative;
    }
    .dropzone.dragover {
      background-color: #e9ecef;
    }
    .upload-btn {
      background-color: #007bff;
      color: white;
      padding: 10px 20px;
      border: none;
      cursor: pointer;
      font-size: 16px;
      width: 100%;
    }
    .file-info {
      margin-top: 10px;
    }
  </style>
</head>
<body>
<?php include 'includes/navbar.php'; ?>

<div class="container mt-4">
  <h4 class="mb-4">Dashboard</h4>

  <!-- STORAGE PROGRESS BAR -->
  <div class="mb-4">
    <label class="form-label fw-bold">Penggunaan Penyimpanan</label>
    <div class="progress mb-1" style="height: 25px;">
      <div class="progress-bar <?= $isFull ? 'bg-danger' : ($isAlmostFull ? 'bg-warning' : 'bg-info') ?>"
           role="progressbar"
           style="width: <?= min($usedPercent, 100) ?>%;"
           aria-valuenow="<?= $usedPercent ?>" aria-valuemin="0" aria-valuemax="100">
        <?= $usedPercent ?>%
      </div>
    </div>
    <small class="text-muted">
      Terpakai: <strong><?= formatSize($usedBytes) ?></strong> dari <strong><?= $limitMB ?> MB</strong> |
      Sisa: <strong><?= formatSize($freeBytes) ?></strong>
    </small>

    <?php if ($isAlmostFull): ?>
      <div class="alert alert-warning mt-2 mb-0 p-2">⚠️ Penyimpanan hampir penuh!</div>
    <?php endif; ?>
    <?php if ($isFull): ?>
      <div class="alert alert-danger mt-2 mb-0 p-2">❌ Penyimpanan penuh. Anda tidak bisa mengunggah file baru.</div>
    <?php endif; ?>
  </div>

  <!-- FORM UPLOAD -->
  <div class="mb-4">
    <form action="upload.php" method="post" enctype="multipart/form-data" id="uploadForm" <?= $isFull ? 'onsubmit="return false;"' : '' ?>>
      <div class="mb-3">
        <!-- Tombol Klik untuk Upload -->
        <input type="file" id="fileInput" name="file" class="form-control d-none" <?= $isFull ? 'disabled' : '' ?>>
        <button type="button" class="upload-btn" onclick="document.getElementById('fileInput').click();">
          Klik untuk Unggah File
        </button>
      </div>
      
      <!-- Tombol Upload -->
      <button type="submit" id="submitBtn" class="btn btn-primary" <?= $isFull ? 'disabled' : '' ?>>
        Upload
      </button>
      
      <!-- File Info -->
      <div id="fileInfo" class="file-info"></div>
    </form>
  </div>

  <!-- DRAG AND DROP ZONE -->
  <div class="dropzone mb-4" id="dropzone" <?= $isFull ? 'disabled' : '' ?>>
    <p>Drag & Drop file atau klik tombol di atas untuk memilih file</p>
  </div>

  <!-- DAFTAR FILE YANG SUDAH DITAMBAHKAN -->
  <h5>Daftar File:</h5>
  <ul class="list-group">
    <?php foreach ($files as $file): ?>
      <li class="list-group-item"><?= $file ?></li>
    <?php endforeach; ?>
  </ul>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
  let selectedFile = null;

  // Drag & Drop Upload
  const dropzone = document.getElementById('dropzone');
  
  dropzone.addEventListener('dragover', function(e) {
    e.preventDefault();
    dropzone.classList.add('dragover');
  });

  dropzone.addEventListener('dragleave', function() {
    dropzone.classList.remove('dragover');
  });

  dropzone.addEventListener('drop', function(e) {
    e.preventDefault();
    dropzone.classList.remove('dragover');
    
    selectedFile = e.dataTransfer.files[0];
    showFileInfo(selectedFile);
  });

  // Handle file input selection
  const fileInput = document.getElementById('fileInput');
  fileInput.addEventListener('change', function() {
    selectedFile = fileInput.files[0];
    showFileInfo(selectedFile);
  });

  // Show file information before upload
  function showFileInfo(file) {
    if (file) {
      const fileInfoDiv = document.getElementById('fileInfo');
      fileInfoDiv.innerHTML = `
        <p><strong>Nama File:</strong> ${file.name}</p>
        <p><strong>Ukuran:</strong> ${formatSize(file.size)}</p>
        <p><strong>Ekstensi:</strong> ${file.name.split('.').pop()}</p>
      `;
    }
  }

  // Format size in human readable format
  function formatSize(bytes) {
    if (bytes >= 1073741824) return (bytes / 1073741824).toFixed(2) + ' GB';
    if (bytes >= 1048576) return (bytes / 1048576).toFixed(2) + ' MB';
    if (bytes >= 1024) return (bytes / 1024).toFixed(2) + ' KB';
    return bytes + ' B';
  }

  // Form validation before submitting
  $('#uploadForm').submit(function(event) {
    event.preventDefault();
    
    if (!selectedFile) {
      alert('Silakan pilih file terlebih dahulu.');
      return;
    }

    // Validate file size (e.g., 10MB max)
    const maxSize = 10 * 1024 * 1024; // 10MB
    if (selectedFile.size > maxSize) {
      alert('Ukuran file terlalu besar! Maksimum 10MB.');
      return;
    }

    // Validate file extension
    const allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'mp3'];
    const fileExtension = selectedFile.name.split('.').pop().toLowerCase();
    if (!allowedExtensions.includes(fileExtension)) {
      alert('Ekstensi file tidak didukung! Harap pilih file dengan ekstensi jpg, jpeg, png, gif, pdf, atau mp3.');
      return;
    }

    // If file is valid, submit the form
    const formData = new FormData();
    formData.append('file', selectedFile);

    $.ajax({
      url: 'upload.php',
      type: 'POST',
      data: formData,
      processData: false,
      contentType: false,
      success: function(response) {
        alert('File berhasil diunggah!');
        location.reload(); // Refresh halaman setelah upload
      },
      error: function() {
        alert('Terjadi kesalahan saat mengunggah file.');
      }
    });
  });
</script>

</body>
</html>
