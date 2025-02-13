<?php
// admin/dashboard.php
require_once '../includes/db.php';
require_once '../includes/functions.php';
secure_session_start();

// Nur Admins dürfen dieses Dashboard aufrufen
if (!is_logged_in() || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: ../login.php");
    exit;
}

// Statistiken abrufen
$stmtUsers = $pdo->query("SELECT COUNT(*) FROM users");
$totalUsers = $stmtUsers->fetchColumn();

$stmtKreationen = $pdo->query("SELECT COUNT(*) FROM kreationen");
$totalKreationen = $stmtKreationen->fetchColumn();

$stmtFiles = $pdo->query("SELECT COUNT(*) FROM kreation_files");
$totalFiles = $stmtFiles->fetchColumn();
?>
<?php include '../includes/header.php'; ?>
<div class="container mt-5">
    <h1 class="mb-4">Admin Dashboard</h1>
    <div class="row">
        <!-- Benutzer Statistik -->
        <div class="col-md-4">
            <div class="card text-white bg-primary mb-3">
                <div class="card-header">Benutzer</div>
                <div class="card-body">
                    <h5 class="card-title"><?php echo $totalUsers; ?></h5>
                    <p class="card-text">Gesamtzahl der registrierten Benutzer.</p>
                    <a href="manage_users.php" class="btn btn-light">Verwalten</a>
                </div>
            </div>
        </div>
        <!-- Kreationen Statistik -->
        <div class="col-md-4">
            <div class="card text-white bg-success mb-3">
                <div class="card-header">Posts</div>
                <div class="card-body">
                    <h5 class="card-title"><?php echo $totalKreationen; ?></h5>
                    <p class="card-text">Gesamtzahl der hochgeladenen Posts.</p>
                    <a href="manage_kreationen.php" class="btn btn-light">Verwalten</a>
                </div>
            </div>
        </div>
        <!-- Dateien Statistik -->
        <div class="col-md-4">
            <div class="card text-white bg-warning mb-3">
                <div class="card-header">Dateien</div>
                <div class="card-body">
                    <h5 class="card-title"><?php echo $totalFiles; ?></h5>
                    <p class="card-text">Gesamtzahl der hochgeladenen Dateien.</p>
                    <a href="manage_files.php" class="btn btn-light">Verwalten</a>
                </div>
            </div>
        </div>
    </div>
    <!-- Weitere Admin-Optionen können hier ergänzt werden -->
</div>
<?php include '../includes/footer.php'; ?>