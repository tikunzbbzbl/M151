<?php
// profil/delete_own_post.php

// Binde die Datei ein, die die Datenbankverbindung herstellt, sodass wir SQL-Abfragen ausführen können.
require_once '../includes/db.php';

// Binde die Datei mit gemeinsamen Hilfsfunktionen ein. Diese enthält Funktionen wie secure_session_start() und escape().
require_once '../includes/functions.php';

// Starte eine sichere Session, um auf Session-Daten (wie den Login-Status und Benutzer-ID) zugreifen zu können.
secure_session_start();

// Überprüfe, ob der Benutzer eingeloggt ist.
// Falls der Benutzer nicht eingeloggt ist, leite ihn zur Login-Seite weiter und beende das Skript.
if (!is_logged_in()) {
    header("Location: ../login.php");
    exit;
}

// Überprüfe, ob eine gültige Post-ID (GET-Parameter "id") übergeben wurde und ob sie numerisch ist.
// Diese ID steht für die Kreation (Post), die gelöscht werden soll.
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Ungültige Anfrage.");
}
// Konvertiere den GET-Parameter in einen Integer und speichere ihn in $post_id.
$post_id = (int)$_GET['id'];

// Überprüfe, ob der Post (Kreation) zum aktuell eingeloggten Benutzer gehört.
// Bereite dazu eine SQL-Abfrage vor, die in der Tabelle "kreationen" nach einem Datensatz sucht, 
// bei dem die ID des Posts und die Benutzer-ID (user_id) dem aktuell eingeloggten Benutzer entsprechen.
$stmt = $pdo->prepare("SELECT id FROM kreationen WHERE id = :id AND user_id = :uid");
$stmt->execute([':id' => $post_id, ':uid' => $_SESSION['user_id']]);
// Speichere das Ergebnis in $post.
$post = $stmt->fetch();

// Falls kein entsprechender Post gefunden wurde, beende das Skript mit einer Fehlermeldung.
if (!$post) {
    die("Kreation nicht gefunden oder keine Berechtigung.");
}

// Rufe alle Dateien ab, die zu dieser Kreation gehören, aus der Tabelle "kreation_files".
// Diese Abfrage sucht nach allen Dateieinträgen, bei denen die kreation_id gleich $post_id ist.
$stmtFiles = $pdo->prepare("SELECT file_name FROM kreation_files WHERE kreation_id = :kid");
$stmtFiles->execute([':kid' => $post_id]);
// Speichere alle abgefragten Dateieinträge in der Variablen $files.
$files = $stmtFiles->fetchAll();

// Durchlaufe alle abgefragten Dateien und lösche jede einzelne aus dem Dateisystem.
// Der Dateipfad wird erstellt, indem der Ordner "../uploads/" mit dem Dateinamen kombiniert wird.
foreach ($files as $file) {
    $filePath = '../uploads/' . $file['file_name'];
    // Überprüfe, ob die Datei existiert.
    if (file_exists($filePath)) {
        // Lösche die Datei vom Server.
        unlink($filePath);
    }
}

// Lösche alle Dateieinträge, die zu dieser Kreation gehören, aus der Tabelle "kreation_files".
// Dadurch wird die Zuordnung der Dateien zur Kreation in der Datenbank entfernt.
$stmtDelFiles = $pdo->prepare("DELETE FROM kreation_files WHERE kreation_id = :kid");
$stmtDelFiles->execute([':kid' => $post_id]);

// Lösche den Post (die Kreation) aus der Tabelle "kreationen".
// Die Abfrage löscht nur dann, wenn die Post-ID und die Benutzer-ID (user_id) zum aktuell eingeloggten Benutzer passen.
$stmtDelPost = $pdo->prepare("DELETE FROM kreationen WHERE id = :id AND user_id = :uid");
$stmtDelPost->execute([':id' => $post_id, ':uid' => $_SESSION['user_id']]);

// Nach dem Löschen leite den Benutzer zur Seite "profile_posts.php" weiter, 
// wo die restlichen Posts (Kreationen) des Benutzers angezeigt werden.
header("Location: profile_posts.php");
exit;
?>
