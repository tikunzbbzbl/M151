<?php
// logout.php – Beendet die Session (C9)

// Binde die Datei mit den Hilfsfunktionen ein, um auf secure_session_start() zugreifen zu können.
require_once 'includes/functions.php';

// Starte eine sichere Session, damit wir sicherstellen, dass eine Session aktiv ist, bevor wir sie beenden.
secure_session_start();

// Lösche alle Session-Daten, indem das $_SESSION-Array geleert wird.
// Damit werden alle gespeicherten Session-Variablen entfernt.
$_SESSION = [];

// Falls die Session-Cookies verwendet werden, lösche auch das Session-Cookie vom Browser.
if (ini_get("session.use_cookies")) {
    // Hole die aktuellen Parameter des Session-Cookies (wie Pfad, Domain, Secure-Flag, etc.).
    $params = session_get_cookie_params();
    
    // Setze ein Cookie mit demselben Namen wie das Session-Cookie, aber mit einem Ablaufdatum in der Vergangenheit.
    // Dadurch wird das Cookie beim Browser gelöscht.
    setcookie(session_name(), '', time() - 42000,
        $params["path"], 
        $params["domain"], 
        $params["secure"], 
        $params["httponly"]
    );
}

// Beende die Session und lösche alle Session-Daten auf dem Server.
session_destroy();

// Leite den Benutzer zur Startseite weiter.
header("Location: index.php");
exit;
?>
