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

  function previewFile(filename) {
    const ext = filename.split('.').pop().toLowerCase();
    const url = `uploads/<?= $_SESSION['username'] ?>/${filename}`;
    let content = "";
  
    if (["png", "jpg", "jpeg", "gif"].includes(ext)) {
      content = `<img src="${url}" class="img-fluid">`;
    } else if (["pdf"].includes(ext)) {
      content = `<embed src="${url}" type="application/pdf" width="100%" height="600px">`;
    } else if (["mp3", "wav"].includes(ext)) {
      content = `<audio controls src="${url}" class="w-100"></audio>`;
    } else {
      content = `<p class="text-muted">Preview tidak tersedia untuk file ini.</p>`;
    }
  
    document.getElementById("previewContent").innerHTML = content;
    new bootstrap.Modal(document.getElementById('previewModal')).show();
  }
  
  
  
  loadFiles();
  previewFile(filename);
  