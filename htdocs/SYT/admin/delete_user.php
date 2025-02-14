<?php
// admin/delete_user.php

// Binde die Datei ein, die die Datenbankverbindung herstellt, sodass wir SQL-Abfragen ausführen können.
require_once '../includes/db.php';

// Binde die Datei mit Hilfsfunktionen ein (zum Beispiel für sicheres Session-Handling und Escaping von Ausgaben).
require_once '../includes/functions.php';

// Starte eine sichere Session, um auf Session-Daten (wie den Login-Status) zugreifen zu können.
secure_session_start();

// Überprüfe, ob der Benutzer eingeloggt ist und ob er Admin-Rechte besitzt.
// Falls nicht, leite den Benutzer zur Login-Seite weiter und beende das Skript.
if (!is_logged_in() || empty($_SESSION['is_admin'])) {
    header("Location: ../login.php");
    exit;
}

// Überprüfe, ob der GET-Parameter "id" gesetzt ist und ob er numerisch ist.
// Dieser Parameter gibt die ID des Benutzers an, der gelöscht werden soll.
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Ungültige Anfrage.");
}

// Konvertiere den GET-Parameter in einen Integer und speichere ihn in $delete_id.
$delete_id = (int)$_GET['id'];

// Optional: Verhindere, dass sich der aktuell eingeloggte Admin selbst löscht.
// Wenn die zu löschende Benutzer-ID der ID des aktuell eingeloggten Benutzers entspricht, wird das Skript beendet.
if ($delete_id === $_SESSION['user_id']) {
    die("Du kannst dich nicht selbst löschen.");
}

// Prüfe, ob der Benutzer, der gelöscht werden soll, existiert.
// Bereite dazu eine SQL-Abfrage vor, die anhand der Benutzer-ID sucht.
$stmt = $pdo->prepare("SELECT id FROM users WHERE id = :id");
$stmt->execute([':id' => $delete_id]);
// Speichere das Ergebnis in der Variablen $user.
$user = $stmt->fetch();

// Wenn kein Benutzer gefunden wurde, beende das Skript mit einer Fehlermeldung.
if (!$user) {
    die("Benutzer nicht gefunden.");
}

// Führe das Löschen des Benutzers durch.
// Bereite eine SQL-Anweisung vor, die den Benutzer aus der Tabelle "users" entfernt.
$stmtDelete = $pdo->prepare("DELETE FROM users WHERE id = :id");
// Führe die SQL-Anweisung mit der zu löschenden Benutzer-ID aus.
$stmtDelete->execute([':id' => $delete_id]);

// Leite den Administrator nach dem Löschen zurück zur Verwaltungsseite für Benutzer.
header("Location: manage_users.php");
exit;
?>
