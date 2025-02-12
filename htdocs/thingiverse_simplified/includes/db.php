<?php
// db.php
// Verbindung zur Datenbank mittels PDO – es wird ein Benutzer mit eingeschränkten Rechten genutzt.
$host   = 'localhost';
$dbname = 'thingiverse_simplified';
$user   = 'user1'; // bitte entsprechend anpassen
$pass   = 'pass';     // bitte entsprechend anpassen
$dsn    = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $user, $pass, [
       PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
       PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    die("Datenbankverbindung fehlgeschlagen: " . $e->getMessage());
}
?>
