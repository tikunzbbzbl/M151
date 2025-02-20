<?php
// admin/delete_post.php

// Binde die Datei für die Datenbankverbindung ein.
// Dadurch können wir später SQL-Abfragen ausführen.
require_once '../includes/db.php';

// Binde die Datei mit Hilfsfunktionen ein (zum Beispiel sichere Session-Funktion und Escape-Funktion).
require_once '../includes/functions.php';

// Starte eine sichere Session, um den Login-Status und andere Session-Daten zu nutzen.
secure_session_start();

// Überprüfe, ob der Benutzer eingeloggt ist und ob er Admin-Rechte besitzt.
// Falls nicht, wird der Benutzer zur Login-Seite weitergeleitet und das Skript beendet.
if (!is_logged_in() || empty($_SESSION['is_admin'])) {
    header("Location: ../login.php");
    exit;
}

// Überprüfe, ob der GET-Parameter "id" gesetzt und numerisch ist.
// Dieser Parameter gibt die ID des zu löschenden Posts (Kreation) an.
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Ungültige Anfrage.");
}
// Konvertiere den Parameter in einen Integer und speichere ihn in $post_id.
$post_id = (int)$_GET['id'];

// Rufe alle Dateien ab, die zu diesem Post gehören.
// Hier wird in der Tabelle "kreation_files" nach Einträgen gesucht, bei denen kreation_id gleich $post_id ist.
$stmtFiles = $pdo->prepare("SELECT file_name FROM kreation_files WHERE kreation_id = :kid");
$stmtFiles->execute([':kid' => $post_id]);
// Alle gefundenen Dateinamen werden in $files gespeichert.
$files = $stmtFiles->fetchAll();

// Durchlaufe alle Dateien und lösche sie aus dem Dateisystem:
// Erstelle für jede Datei den vollständigen Pfad, indem "../uploads/" und der Dateiname zusammengefügt werden.
foreach ($files as $file) {
    $filePath = '../uploads/' . $file['file_name'];
    // Überprüfe, ob die Datei existiert.
    if (file_exists($filePath)) {
        // Lösche die Datei vom Server.
        unlink($filePath);
    }
}

// Lösche alle zu diesem Post gehörenden Einträge in der Tabelle "kreation_files".
// Dadurch wird die Zuordnung der Dateien zur Kreation aus der Datenbank entfernt.
$stmtDelFiles = $pdo->prepare("DELETE FROM kreation_files WHERE kreation_id = :kid");
$stmtDelFiles->execute([':kid' => $post_id]);

// Lösche den Post (die Kreation) selbst aus der Tabelle "kreationen".
// Hier wird der Eintrag entfernt, bei dem die ID gleich $post_id ist.
$stmtDelPost = $pdo->prepare("DELETE FROM kreationen WHERE id = :id");
$stmtDelPost->execute([':id' => $post_id]);

// Nach dem Löschen leite den Benutzer zur Verwaltungsseite für Kreationen weiter.
header("Location: manage_kreationen.php");
exit;
?>
