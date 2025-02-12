<?php
// functions.php

// Startet eine sichere Session (C8, C10)
function secure_session_start() {
    // Prüfen, ob bereits eine Session aktiv ist
    if (session_status() === PHP_SESSION_NONE) {
        $session_name = 'sec_session_id';
        $secure = false; // Auf true setzen, wenn HTTPS genutzt wird
        $httponly = true;
        
        // Session nur über Cookies erzwingen
        ini_set('session.use_only_cookies', 1);
        
        // Vorhandene Cookie-Parameter übernehmen
        $cookieParams = session_get_cookie_params();
        session_set_cookie_params([
            'lifetime' => $cookieParams["lifetime"],
            'path'     => $cookieParams["path"],
            'domain'   => $cookieParams["domain"],
            'secure'   => $secure,
            'httponly' => $httponly,
            'samesite' => 'Lax'
        ]);
        
        session_name($session_name);
        session_start();
        session_regenerate_id(true); // Verhindert Session Fixation
    }
}


// Prüft, ob der Benutzer angemeldet ist (C8)
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// Wrapper für htmlspecialchars zur Vermeidung von Script-Injection (C7)
function escape($html) {
    return htmlspecialchars($html, ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8");
}
?>
