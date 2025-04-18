<?php
session_start();
session_unset();  // Menghapus semua variabel session
session_destroy();  // Menghancurkan sesi
header("Location: index.php");
exit;
?>
