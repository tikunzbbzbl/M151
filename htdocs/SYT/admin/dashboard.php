<?php
// admin/dashboard.php

// Binde die Datei mit der Datenbankverbindung ein, damit wir auf die Datenbank zugreifen können
require_once '../includes/db.php';

// Binde gemeinsame Funktionen ein (zum Beispiel für das sichere Starten der Session)
require_once '../includes/functions.php';

// Starte eine sichere Session (wichtig für Login-Überprüfungen und Zugriffsschutz)
secure_session_start();

// Überprüfe, ob der Benutzer eingeloggt ist und ob er Admin-Rechte besitzt.
// Falls nicht, wird der Benutzer zur Login-Seite umgeleitet und das Skript beendet.
if (!is_logged_in() || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: ../login.php");
    exit;
}

// ------------------------------
// Statistiken aus der Datenbank abrufen
// ------------------------------

// Ermitteln der Gesamtzahl der registrierten Benutzer
$stmtUsers = $pdo->query("SELECT COUNT(*) FROM users");
// fetchColumn() gibt den Wert der ersten Spalte der ersten Zeile zurück (hier: die Anzahl der Benutzer)
$totalUsers = $stmtUsers->fetchColumn();

// Ermitteln der Gesamtzahl der hochgeladenen Posts (Kreationen)
$stmtKreationen = $pdo->query("SELECT COUNT(*) FROM kreationen");
$totalKreationen = $stmtKreationen->fetchColumn();

// Ermitteln der Gesamtzahl der hochgeladenen Dateien
$stmtFiles = $pdo->query("SELECT COUNT(*) FROM kreation_files");
$totalFiles = $stmtFiles->fetchColumn();
?>
<!-- Binde den Header ein, der das HTML-Grundgerüst (Kopfbereich, Navigation, CSS, etc.) enthält -->
<?php include '../includes/header.php'; ?>

<div class="container mt-5">
    <h1 class="mb-4">Admin Dashboard</h1>
    <div class="row">
        <!-- Benutzer Statistik -->
        <div class="col-md-4">
            <div class="card text-white bg-primary mb-3">
                <!-- Kopfzeile der Karte -->
                <div class="card-header">Benutzer</div>
                <div class="card-body">
                    <!-- Zeige die Anzahl der registrierten Benutzer an -->
                    <h5 class="card-title"><?php echo $totalUsers; ?></h5>
                    <p class="card-text">Gesamtzahl der registrierten Benutzer.</p>
                    <!-- Link zur Verwaltung der Benutzer -->
                    <a href="manage_users.php" class="btn btn-light">Verwalten</a>
                </div>
            </div>
        </div>
        <!-- Posts Statistik -->
        <div class="col-md-4">
            <div class="card text-white bg-success mb-3">
                <div class="card-header">Posts</div>
                <div class="card-body">
                    <!-- Zeige die Anzahl der hochgeladenen Posts (Kreationen) an -->
                    <h5 class="card-title"><?php echo $totalKreationen; ?></h5>
                    <p class="card-text">Gesamtzahl der hochgeladenen Posts.</p>
                    <!-- Link zur Verwaltung der Posts -->
                    <a href="manage_posts.php" class="btn btn-light">Verwalten</a>
                </div>
            </div>
        </div>
        <!-- Dateien Statistik -->
        <div class="col-md-4">
            <div class="card text-white bg-warning mb-3">
                <div class="card-header">Dateien</div>
                <div class="card-body">
                    <!-- Zeige die Anzahl der hochgeladenen Dateien an -->
                    <h5 class="card-title"><?php echo $totalFiles; ?></h5>
                    <p class="card-text">Gesamtzahl der hochgeladenen Dateien.</p>
                    <!-- Link zur Verwaltung der Dateien -->
                    <a href="manage_files.php" class="btn btn-light">Verwalten</a>
                </div>
            </div>
        </div>
    </div>
    <!-- Hier können weitere Admin-Optionen ergänzt werden -->
</div>

<!-- Binde den Footer ein, der das Ende des HTML-Dokuments darstellt -->
<?php include '../includes/footer.php'; ?>
