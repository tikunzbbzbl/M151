<?php
/**
 * Abmeldeseite
 * Kompetenz C9: Eine angemeldete Person kann sich wieder abmelden. Die Session wird dabei korrekt beendet.
 */

// Einbinden der Session-Datei
require_once '../includes/session.php';
require_once '../includes/functions.php';

// Benutzer muss angemeldet sein
nur_angemeldet_zugriff();

// Session-Variablen löschen
session_unset();

// Zerstören der Session-Datei auf dem Server
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    // Cookie löschen durch Setzen eines abgelaufenen Zeitstempels
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Session zerstören
session_destroy();

// Zur Startseite umleiten
umleiten_zu('index.php');
?>