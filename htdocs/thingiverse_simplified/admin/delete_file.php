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

// Dateidaten abrufen
$stmt = $pdo->prepare("SELECT file_name FROM kreation_files WHERE id = :id");
$stmt->execute([':id' => $file_id]);
$file = $stmt->fetch();

if (!$file) {
    die("Datei nicht gefunden.");
}

// Datei aus dem Dateisystem löschen (Pfad anpassen, falls uploads/ in einem anderen Ordner liegt)
$file_path = '../uploads/' . $file['file_name'];
if (file_exists($file_path)) {
    unlink($file_path);
}

// Eintrag aus der Datenbank löschen
$stmt = $pdo->prepare("DELETE FROM kreation_files WHERE id = :id");
$stmt->execute([':id' => $file_id]);

header("Location: manage_files.php");
exit;
?>