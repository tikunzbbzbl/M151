<?php
// functions.php

// ------------------------------
// Funktion: secure_session_start()
// ------------------------------
// Diese Funktion startet eine sichere Session. Sie stellt sicher, dass
// Session-Cookies nur über Cookies übertragen werden und dass die Session-ID
// bei jedem Start neu generiert wird, um Session-Fixation zu verhindern.
// (Sie entspricht den Anforderungen C8 und C10 aus den Projektvorgaben.)
function secure_session_start() {
    // Überprüfe, ob noch keine Session aktiv ist.
    // session_status() gibt den aktuellen Status der Session zurück.
    if (session_status() === PHP_SESSION_NONE) {
        // Definiere einen benutzerdefinierten Session-Namen.
        $session_name = 'sec_session_id';
        
        // Variable $secure: Setze dies auf true, wenn HTTPS verwendet wird.
        $secure = false;
        
        // Variable $httponly: Wenn true, wird das Session-Cookie nur über HTTP(S) zugänglich sein,
        // nicht über JavaScript. Dies erhöht die Sicherheit.
        $httponly = true;
        
        // Erzwinge, dass die Session nur über Cookies verwendet wird.
        ini_set('session.use_only_cookies', 1);
        
        // Hole die aktuellen Cookie-Parameter (wie Lebensdauer, Pfad, Domain) der aktuellen Session.
        $cookieParams = session_get_cookie_params();
        
        // Setze die Cookie-Parameter für die Session.
        // Hier werden die bestehenden Parameter übernommen und zusätzlich
        // 'secure', 'httponly' und 'samesite' (Lax) definiert.
        session_set_cookie_params([
            'lifetime' => $cookieParams["lifetime"],
            'path'     => $cookieParams["path"],
            'domain'   => $cookieParams["domain"],
            'secure'   => $secure,
            'httponly' => $httponly,
            'samesite' => 'Lax'
        ]);
        
        // Setze den Session-Namen, der für das Session-Cookie verwendet wird.
        session_name($session_name);
        
        // Starte die Session.
        session_start();
        
        // Regeneriere die Session-ID, um Session-Fixation zu verhindern.
        session_regenerate_id(true);
    }
}


// ------------------------------
// Funktion: is_logged_in()
// ------------------------------
// Diese Funktion prüft, ob ein Benutzer eingeloggt ist, indem sie nach der Session-Variable 'user_id' sucht.
// Entspricht den Anforderungen für den geschützten Zugriff (C8).
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// ------------------------------
// Funktion: escape()
// ------------------------------
// Diese Funktion ist ein Wrapper für htmlspecialchars(), um zu verhindern,
// dass schädlicher HTML- oder JavaScript-Code (Script-Injection) ausgegeben wird.
// Sie wandelt Sonderzeichen in HTML-Entities um.
// ENT_QUOTES: Wandelt sowohl doppelte als auch einfache Anführungszeichen um.
// ENT_SUBSTITUTE: Ersetzt ungültige Zeichen, wenn die Codierung fehlschlägt.
// "UTF-8": Verwendet den UTF-8-Zeichensatz.
function escape($html) {
    return htmlspecialchars($html, ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8");
}
?>
