<?php require 'php/auth.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">

  <h3>Selamat datang, <?= htmlspecialchars($_SESSION['username']) ?>!</h3>

  <!-- Upload form -->
  <form id="uploadForm" enctype="multipart/form-data">
    <div class="mb-3">
      <input type="file" name="file" class="form-control" required>
    </div>
    <button type="submit" class="btn btn-primary">Upload</button>
  </form>

  <!-- Progress bar -->
  <div class="progress mt-3" style="height: 20px;">
    <div id="progressBar" class="progress-bar" role="progressbar" style="width: 0%;">0%</div>
  </div>

  <!-- Feedback -->
  <div id="feedback" class="mt-3"></div>

  <!-- File List -->
  <h5 class="mt-4">File Kamu:</h5>
  <table class="table table-striped">
    <thead>
      <tr><th>Nama File</th><th>Ukuran</th><th>Tanggal</th><th>Aksi</th></tr>
    </thead>
    <tbody id="fileList"></tbody>
  </table>

  <script>
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
        document.getElementById("feedback").innerHTML = `<div class="alert alert-info">${xhr.responseText}</div>`;
        document.getElementById("progressBar").style.width = "0%";
        document.getElementById("progressBar").textContent = "0%";
        loadFiles();
      };

      xhr.send(formData);
    });

    function loadFiles() {
      fetch("php/filelist.php")
        .then(res => res.json())
        .then(data => {
          const tbody = document.getElementById("fileList");
          tbody.innerHTML = "";
          data.forEach(file => {
            tbody.innerHTML += `
              <tr>
                <td>${file.filename}</td>
                <td>${file.size} KB</td>
                <td>${file.uploaded_at}</td>
                <td>
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
          document.getElementById("feedback").innerHTML = `<div class="alert alert-warning">${msg}</div>`;
          loadFiles();
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
          document.getElementById("feedback").innerHTML = `<div class="alert alert-info">${msg}</div>`;
          loadFiles();
        });
      }
    }

    // Panggil loadFiles saat halaman selesai dimuat
    window.onload = loadFiles;
  </script>
</body>
</html>
