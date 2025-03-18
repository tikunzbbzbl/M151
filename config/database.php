<?php
/**
 * Datenbankverbindungs-Konfiguration
 * Kompetenz C12: Meine Web-Applikation kommuniziert über einen Benutzer mit eingeschränkten Rechten mit der Datenbank.
 */

// Datenbankverbindungsinformationen
$db_host = 'localhost'; // Host-Adresse
$db_name = 'mein_projekt'; // Datenbankname
$db_user = 'projekt_user'; // Benutzername mit eingeschränkten Rechten (C12)
$db_pass = 'sicheres_passwort'; // Sicheres Passwort

// Verbindung zur Datenbank herstellen mit PDO
try {
    // Erstelle PDO-Verbindung
    $pdo = new PDO(
        "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4",
        $db_user,
        $db_pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // Fehler als Exceptions ausgeben
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Ergebnisse als assoziative Arrays zurückgeben
            PDO::ATTR_EMULATE_PREPARES => false // Echte Prepared Statements verwenden (C19: SQL-Injection verhindern)
        ]
    );
} catch (PDOException $e) {
    // Bei Fehlern Skript beenden und Fehlermeldung ausgeben (im Produktivbetrieb sollte keine detaillierte Fehlermeldung angezeigt werden)
    die('Verbindungsfehler: ' . $e->getMessage());
}
?>
