<?php
require 'includes/auth.php';

$username = $_SESSION['username'];
$dir = "uploads/$username/";
$file = basename($_GET['file']);
$path = $dir . $file;

$ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));

if (!file_exists($path)) {
  echo "<p class='text-danger text-center'>File tidak ditemukan.</p>";
  exit;
}

echo '<div class="text-center">';

if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
  // Zoomable image
  echo "
    <style>
      #zoomable-image {
        max-height: 500px;
        transition: transform 0.3s ease;
        cursor: zoom-in;
      }
      #zoomable-image.zoomed {
        transform: scale(2);
        cursor: zoom-out;
      }
    </style>
    <img id='zoomable-image' src='$path' class='img-fluid rounded shadow mb-2'>
    <script>
      const img = document.getElementById('zoomable-image');
      img.onclick = () => img.classList.toggle('zoomed');
    </script>
  ";
} elseif ($ext === 'pdf') {
  // PDF with fullscreen button
  echo "
    <button class='btn btn-sm btn-secondary mb-2' onclick='fullscreenPDF()'>📄 Fullscreen</button><br>
    <embed id='pdf-preview' src='$path' type='application/pdf' width='100%' height='500px' class='border rounded shadow'>
    <script>
      function fullscreenPDF() {
        const elem = document.getElementById('pdf-preview');
        if (elem.requestFullscreen) {
          elem.requestFullscreen();
        } else if (elem.webkitRequestFullscreen) {
          elem.webkitRequestFullscreen();
        } else if (elem.msRequestFullscreen) {
          elem.msRequestFullscreen();
        }
      }
    </script>
  ";
} elseif (in_array($ext, ['mp3', 'wav'])) {
  echo "<audio controls class='mt-3 w-75'>
          <source src='$path' type='audio/$ext'>
          Browser tidak mendukung audio.
        </audio>";
} elseif (in_array($ext, ['mp4', 'webm', 'ogg'])) {
  // Video preview
  echo "
    <video controls class='w-100 rounded shadow' style='max-height:500px'>
      <source src='$path' type='video/$ext'>
      Browser tidak mendukung video.
    </video>
  ";
} else {
  echo "<p class='text-muted'>Preview tidak tersedia untuk file ini.</p>";
}

echo '</div>';
