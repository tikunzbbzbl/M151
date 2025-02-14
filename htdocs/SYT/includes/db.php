<?php
// db.php
// Diese Datei stellt die Verbindung zur MySQL-Datenbank mittels PDO her.
// Es wird ein Benutzer mit eingeschränkten Rechten verwendet, um die Sicherheit zu erhöhen.

// Datenbank-Verbindungsdaten definieren:
$host   = 'localhost';                // Der Datenbankserver (hier: localhost, da MySQL lokal läuft)
$dbname = 'thingiverse_simplified';     // Der Name der Datenbank, mit der verbunden werden soll
$user   = 'user1';                      // Der Datenbankbenutzer mit eingeschränkten Rechten
$pass   = 'pass';                       // Das Passwort des Datenbankbenutzers

// DSN (Data Source Name) erstellt eine Zeichenkette, die alle nötigen Informationen für die PDO-Verbindung enthält.
// Hier wird der MySQL-Treiber, der Host, der Datenbankname und der verwendete Zeichensatz (utf8mb4) angegeben.
$dsn    = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";

try {
    // Versuche, eine neue PDO-Verbindung zu erstellen.
    // Die Optionen setzen den Fehlerbehandlungsmodus auf Exception und den Standard-Fetch-Modus auf assoziative Arrays.
    $pdo = new PDO($dsn, $user, $pass, [
       PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Fehler werden als Exceptions geworfen
       PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC        // Ergebnisse werden als assoziative Arrays zurückgegeben
    ]);
} catch (PDOException $e) {
    // Falls die Verbindung fehlschlägt, wird hier eine Fehlermeldung ausgegeben und das Skript beendet.
    die("Datenbankverbindung fehlgeschlagen: " . $e->getMessage());
}
?>
