<?php
// C12: Verbindung zur MySQL-Datenbank mit eingeschränkten Rechten (kein root-Account in Produktion verwenden)
$host = "localhost";   
$dbname = "mein_projekt";  
$username = "root";  // Erstelle einen speziellen Benutzer mit begrenzten Rechten
$password = "root";  

// Verbindung herstellen
try {
    $db = new mysqli($host, $username, $password, $dbname);
    if ($db->connect_error) {
        throw new Exception("Fehler bei der Datenbankverbindung: " . $db->connect_error);
    }
} catch (Exception $e) {
    die("Fehler: " . $e->getMessage());
}
?>