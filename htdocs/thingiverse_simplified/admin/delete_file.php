<?php
// admin/delete_file.php
require_once '../includes/db.php';
require_once '../includes/functions.php';
secure_session_start();

if (!is_logged_in() || empty($_SESSION['is_admin'])) {
    header("Location: ../login.php");
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Ungültige Anfrage.");
}

$file_id = (int)$_GET['id'];

// Datei aus der Datenbank abrufen
$stmt = $pdo->prepare("SELECT file_name FROM kreation_files WHERE id = :id");
$stmt->execute([':id' => $file_id]);
$file = $stmt->fetch();

if (!$file) {
    die("Datei nicht gefunden.");
}

// Datei im Dateisystem löschen
$filePath = '../uploads/' . $file['file_name'];
if (file_exists($filePath)) {
    unlink($filePath);
}

// Datenbankeintrag löschen
$stmtDel = $pdo->prepare("DELETE FROM kreation_files WHERE id = :id");
$stmtDel->execute([':id' => $file_id]);

header("Location: manage_files.php");
exit;
?>
