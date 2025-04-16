<?php
session_start();
include '../includes/db.php';
include '../includes/header.php';

if (!isset($_SESSION['username'])) {
    header('Location: ../login.php');
    exit();
}

$username = $_SESSION['username'];
$userFolder = "../uploads/$username/";

if (!is_dir($userFolder)) {
    mkdir($userFolder, 0777, true);
}

$files = array_diff(scandir($userFolder), array('..', '.'));

?>

<div class="container mt-5">
    <h2>Welcome, <?php echo htmlspecialchars($username); ?></h2>
    <h4>Your Files</h4>
    <div class="row">
        <?php foreach ($files as $file): ?>
            <div class="col-md-4">
                <div class="card mb-4">
                    <img src="<?php echo $userFolder . $file; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($file); ?>">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($file); ?></h5>
                        <a href="preview.php?file=<?php echo urlencode($file); ?>" class="btn btn-primary">Preview</a>
                        <a href="rename.php?file=<?php echo urlencode($file); ?>" class="btn btn-warning">Rename</a>
                        <a href="delete.php?file=<?php echo urlencode($file); ?>" class="btn btn-danger">Delete</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <a href="upload.php" class="btn btn-success">Upload New File</a>
</div>

<?php include '../includes/footer.php'; ?>