<?php
require 'includes/auth.php';
require 'includes/db.php';

$username = $_SESSION['username'];
$dir = "uploads/$username";
$files = array_diff(scandir($dir), ['.', '..']);

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

// Cek status hampir penuh / penuh
$isFull = $usedBytes >= $limitBytes;
$isAlmostFull = !$isFull && $usedPercent >= 90;
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>File Saya</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include 'includes/navbar.php'; ?>

<div class="container mt-4">

  <h4 class="mb-4">File Saya</h4>

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

  <!-- FORM UPLOAD (DISABLE JIKA PENUH) -->
  <form action="upload.php" method="post" enctype="multipart/form-data" class="mb-4"
        <?= $isFull ? 'onsubmit="return false;"' : '' ?>>
    <div class="input-group">
      <input type="file" name="file" class="form-control" <?= $isFull ? 'disabled' : '' ?>>
      <button class="btn btn-primary" type="submit" <?= $isFull ? 'disabled' : '' ?>>Upload</button>
    </div>
  </form>

  <!-- TABEL FILE -->
  <table class="table table-bordered table-striped">
    <thead>
      <tr>
        <th>Nama File</th>
        <th>Ekstensi</th>
        <th>Aksi</th>
        <th>Preview</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($files as $file): ?>
        <?php $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION)); ?>
        <tr>
          <td><?= htmlspecialchars($file) ?></td>
          <td><?= strtoupper($ext) ?></td>
          <td>
            <button class="btn btn-warning btn-sm" onclick="showRenamePopup('<?= htmlspecialchars($file) ?>')">Rename</button>
            <form action="delete.php" method="post" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus file ini?')">
              <input type="hidden" name="file" value="<?= htmlspecialchars($file) ?>">
              <button class="btn btn-danger btn-sm">Delete</button>
            </form>
          </td>
          <td>
            <button class="btn btn-info btn-sm" onclick="previewFile('<?= rawurlencode($file) ?>')">Lihat</button>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <!-- MODAL RENAME -->
  <div class="modal fade" id="renamePopup" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <form action="rename.php" method="post">
          <div class="modal-header">
            <h5 class="modal-title">Rename File</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
          </div>
          <div class="modal-body">
            <input type="hidden" id="oldFile" name="old">
            <div class="mb-3">
              <label for="newFileName" class="form-label">Nama File Baru (dengan ekstensi)</label>
              <input type="text" id="newFileName" name="new" class="form-control" required>
            </div>
          </div>
          <div class="modal-footer">
            <button type="submit" class="btn btn-primary">Rename</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- MODAL PREVIEW -->
  <div class="modal fade" id="previewModal" tabindex="-1" aria-labelledby="previewLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="previewLabel">Preview File</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
        <div class="modal-body" id="previewContent">
          Memuat preview...
        </div>
      </div>
    </div>
  </div>

</div>

<!-- SCRIPTS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
function showRenamePopup(fileName) {
  document.getElementById('oldFile').value = fileName;
  document.getElementById('newFileName').value = fileName;
  const modal = new bootstrap.Modal(document.getElementById('renamePopup'));
  modal.show();
}

function previewFile(filename) {
  $('#previewContent').html("Memuat preview...");
  $.get("preview.php?file=" + filename, function(response) {
    $('#previewContent').html(response);
    const modal = new bootstrap.Modal(document.getElementById('previewModal'));
    modal.show();
  });
}
</script>

</body>
</html>
