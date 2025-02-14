<?php
// admin/delete_file.php

// Binde die Datei für die Datenbankverbindung ein, um auf die Datenbank zugreifen zu können.
require_once '../includes/db.php';

// Binde gemeinsame Funktionen ein, die unter anderem die Funktion zum sicheren Starten einer Session enthalten.
require_once '../includes/functions.php';

// Starte eine sichere Session. Dies ist wichtig für die Überprüfung, ob ein Benutzer eingeloggt ist.
secure_session_start();

// Überprüfe, ob der Benutzer eingeloggt ist und ob er Admin-Rechte besitzt.
// Falls nicht, leite den Benutzer zur Login-Seite weiter und beende das Skript.
if (!is_logged_in() || empty($_SESSION['is_admin'])) {
    header("Location: ../login.php");
    exit;
}

// Überprüfe, ob der GET-Parameter 'id' vorhanden und numerisch ist.
// Dieser Parameter gibt die ID der Datei an, die gelöscht werden soll.
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Ungültige Anfrage.");
}

// Konvertiere den 'id'-Parameter in einen Integer.
$file_id = (int)$_GET['id'];

// Bereite eine SQL-Abfrage vor, um den Dateinamen aus der Tabelle "kreation_files" anhand der Datei-ID abzurufen.
$stmt = $pdo->prepare("SELECT file_name FROM kreation_files WHERE id = :id");
$stmt->execute([':id' => $file_id]);
$file = $stmt->fetch();

// Falls kein Datensatz gefunden wurde, beende das Skript mit einer Fehlermeldung.
if (!$file) {
    die("Datei nicht gefunden.");
}

// Erstelle den vollständigen Pfad zur Datei im Upload-Ordner.
// Da diese Datei im Admin-Ordner liegt, verwenden wir "../uploads/" als Pfad zum Upload-Verzeichnis.
$filePath = '../uploads/' . $file['file_name'];

// Überprüfe, ob die Datei im Dateisystem existiert. Falls ja, lösche sie mit unlink().
if (file_exists($filePath)) {
    unlink($filePath);
}

// Bereite eine SQL-Anweisung vor, um den Datenbankeintrag der Datei aus der Tabelle "kreation_files" zu löschen.
$stmtDel = $pdo->prepare("DELETE FROM kreation_files WHERE id = :id");
$stmtDel->execute([':id' => $file_id]);

// Leite den Benutzer nach dem Löschen zur Verwaltungsseite für Dateien weiter.
header("Location: manage_files.php");
exit;
?>
