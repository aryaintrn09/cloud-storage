<?php require 'php/auth.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Dashboard - Cloud Storage</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    .progress-bar {
      transition: width 0.4s ease;
    }
  </style>
</head>
<body class="p-4">

  <div class="container">
    <h3>Halo, <?= htmlspecialchars($_SESSION['username']) ?>!</h3>

    <!-- Upload Form -->
    <form id="uploadForm" enctype="multipart/form-data" class="mt-4">
      <div class="mb-3">
        <input type="file" name="file" class="form-control" required>
      </div>
      <button type="submit" class="btn btn-primary">Upload</button>
    </form>

    <!-- Upload Progress -->
    <div class="progress mt-3" style="height: 20px;">
      <div id="progressBar" class="progress-bar" role="progressbar" style="width: 0%;">0%</div>
    </div>

    <!-- Feedback -->
    <div id="feedback" class="mt-3"></div>

    <!-- Storage Usage -->
    <div class="mt-4">
      <strong>Penggunaan Penyimpanan:</strong>
      <div class="progress" style="height: 20px;">
        <div id="storageBar" class="progress-bar bg-success" role="progressbar" style="width: 0%;">0%</div>
      </div>
    </div>

    <!-- File List -->
    <h5 class="mt-4">File Kamu:</h5>
    <div class="table-responsive">
      <table class="table table-bordered table-striped">
        <thead>
          <tr><th>Nama File</th><th>Ukuran</th><th>Tanggal</th><th>Aksi</th></tr>
        </thead>
        <tbody id="fileList"></tbody>
      </table>
    </div>
  </div>

  <!-- Modal Preview -->
  <div class="modal fade" id="previewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content p-3">
        <h5>Preview</h5>
        <div id="previewContent" class="text-center"></div>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <!-- Script -->
  <script>
    function showFeedback(msg, type = "info") {
      const colors = {
        info: "primary",
        success: "success",
        warning: "warning",
        danger: "danger"
      };
      const color = colors[type] || "info";

      document.getElementById("feedback").innerHTML = `
        <div class="alert alert-${color} alert-dismissible fade show" role="alert">
          ${msg}
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      `;
    }

    document.getElementById("uploadForm").addEventListener("submit", function (e) {
      e.preventDefault();
      const formData = new FormData(this);
      const xhr = new XMLHttpRequest();

      xhr.open("POST", "php/upload.php", true);

      xhr.upload.onprogress = function (e) {
        if (e.lengthComputable) {
          let percent = Math.round((e.loaded / e.total) * 100);
          let bar = document.getElementById("progressBar");
          bar.style.width = percent + "%";
          bar.textContent = percent + "%";
        }
      };

      xhr.onload = function () {
        showFeedback(xhr.responseText, xhr.status === 200 ? "success" : "danger");
        document.getElementById("progressBar").style.width = "0%";
        document.getElementById("progressBar").textContent = "0%";
        loadFiles();
        updateStorage();
      };

      xhr.send(formData);
    });

    function icon(ext) {
      ext = ext.toLowerCase();
      if (["png", "jpg", "jpeg"].includes(ext)) return "🖼️";
      if (["pdf"].includes(ext)) return "📄";
      if (["mp3", "wav"].includes(ext)) return "🎵";
      return "📁";
    }

    function loadFiles() {
      fetch("php/filelist.php")
        .then(res => res.json())
        .then(data => {
          const tbody = document.getElementById("fileList");
          tbody.innerHTML = "";
          data.forEach(file => {
            const ext = file.filename.split('.').pop();
            tbody.innerHTML += `
              <tr>
                <td>${icon(ext)} ${file.filename}</td>
                <td>${(file.size / 1024).toFixed(1)} KB</td>
                <td>${file.uploaded_at}</td>
                <td>
                  <button class="btn btn-info btn-sm" onclick="previewFile('${file.filename}')">Preview</button>
                  <button class="btn btn-danger btn-sm" onclick="deleteFile(${file.id})">Delete</button>
                  <button class="btn btn-secondary btn-sm" onclick="renameFile(${file.id}, '${file.filename}')">Rename</button>
                </td>
              </tr>
            `;
          });
        });
    }

    function deleteFile(id) {
      if (confirm("Yakin ingin menghapus file ini?")) {
        fetch("php/delete.php", {
          method: "POST",
          headers: {"Content-Type": "application/x-www-form-urlencoded"},
          body: "id=" + id
        })
        .then(res => res.text())
        .then(msg => {
          showFeedback(msg, "warning");
          loadFiles();
          updateStorage();
        });
      }
    }

    function renameFile(id, currentName) {
      const newName = prompt("Masukkan nama baru:", currentName);
      if (newName && newName !== currentName) {
        fetch("php/rename.php", {
          method: "POST",
          headers: {"Content-Type": "application/x-www-form-urlencoded"},
          body: `id=${id}&new_name=${encodeURIComponent(newName)}`
        })
        .then(res => res.text())
        .then(msg => {
          showFeedback(msg, "info");
          loadFiles();
        });
      }
    }

    function previewFile(filename) {
      const ext = filename.split('.').pop().toLowerCase();
      const url = `uploads/<?= $_SESSION['username'] ?>/${filename}`;
      let content = "";

      if (["png", "jpg", "jpeg", "gif"].includes(ext)) {
        content = `<img src="${url}" class="img-fluid">`;
      } else if (ext === "pdf") {
        content = `<embed src="${url}" type="application/pdf" width="100%" height="600px">`;
      } else if (["mp3", "wav"].includes(ext)) {
        content = `<audio controls src="${url}" class="w-100"></audio>`;
      } else {
        content = `<p class="text-muted">Preview tidak tersedia untuk file ini.</p>`;
      }

      document.getElementById("previewContent").innerHTML = content;
      new bootstrap.Modal(document.getElementById('previewModal')).show();
    }

    function updateStorage() {
      fetch("php/user_storage.php")
        .then(res => res.json())
        .then(data => {
          const usedMB = (data.used / (1024 * 1024)).toFixed(2);
          const limitMB = (data.limit / (1024 * 1024)).toFixed(2);
          const percent = Math.round((data.used / data.limit) * 100);
          const bar = document.getElementById("storageBar");
          bar.style.width = percent + "%";
          bar.textContent = `${usedMB} MB / ${limitMB} MB`;
          bar.classList.toggle("bg-danger", percent >= 90);
        });
    }

    window.onload = function () {
      loadFiles();
      updateStorage();
    };
  </script>
</body>
</html>
