<?php
// admin/delete_post.php
require_once '../includes/db.php';
require_once '../includes/functions.php';
secure_session_start();

// Nur Admins dürfen diesen Bereich nutzen
if (!is_logged_in() || empty($_SESSION['is_admin'])) {
    header("Location: ../login.php");
    exit;
}

// Prüfe, ob eine gültige ID übergeben wurde
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Ungültige Anfrage.");
}
$post_id = (int)$_GET['id'];

// Alle zu diesem Post gehörenden Dateien abrufen
$stmtFiles = $pdo->prepare("SELECT file_name FROM kreation_files WHERE kreation_id = :kid");
$stmtFiles->execute([':kid' => $post_id]);
$files = $stmtFiles->fetchAll();

// Jede Datei aus dem Dateisystem löschen
foreach ($files as $file) {
    $filePath = '../uploads/' . $file['file_name'];
    if (file_exists($filePath)) {
        unlink($filePath);
    }
}

// Lösche die Dateieinträge in der Datenbank
$stmtDelFiles = $pdo->prepare("DELETE FROM kreation_files WHERE kreation_id = :kid");
$stmtDelFiles->execute([':kid' => $post_id]);

// Lösche den Post (Kreation)
$stmtDelPost = $pdo->prepare("DELETE FROM kreationen WHERE id = :id");
$stmtDelPost->execute([':id' => $post_id]);

header("Location: manage_kreationen.php");
exit;
?>
