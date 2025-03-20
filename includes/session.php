<?php
/**
 * Session-Handling
 * Kompetenz C8: In meinem Projekt wird das Session-Handling korrekt eingesetzt.
 * Kompetenz C10: In meinem Projekt wird Session-Fixation und Session-Hijacking erschwert.
 */

// Session-Konfiguration
ini_set('session.cookie_httponly', 1); // XSS-Angriffe durch JavaScript-Zugriff auf Cookies verhindern
ini_set('session.use_only_cookies', 1); // Nur Cookies für Session-IDs verwenden
ini_set('session.cookie_secure', 1); // Cookies nur über HTTPS übertragen (für Produktivumgebung)

// Session starten
session_start();

// Session-Fixation verhindern (C10)
if (!isset($_SESSION['initialized'])) {
    // Neue Session-ID generieren beim ersten Aufruf
    session_regenerate_id(true);
    $_SESSION['initialized'] = true;
    $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT']; // Browser-Info speichern
    $_SESSION['last_activity'] = time(); // Zeitstempel der Aktivität
}

// Session-Hijacking erschweren durch Überprüfung des User-Agents (C10)
if (isset($_SESSION['user_agent']) && $_SESSION['user_agent'] !== $_SERVER['HTTP_USER_AGENT']) {
    // Bei verändertem User-Agent Session zerstören
    session_unset();
    session_destroy();
    header('Location: /index.php');
    exit;
}

// Session-Timeout prüfen (30 Minuten)
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
    // Session nach 30 Minuten Inaktivität automatisch beenden
    session_unset();
    session_destroy();
    header('Location: /index.php?timeout=1');
    exit;
}

// Letzten Aktivitätszeitstempel aktualisieren
$_SESSION['last_activity'] = time();

/**
 * Funktion zur Prüfung, ob ein Benutzer angemeldet ist
 * Kompetenz C8: Zugriffsschutz für angemeldete Benutzer
 * 
 * @return bool True, wenn Benutzer angemeldet ist, sonst False
 */
function ist_angemeldet() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Funktion zur Prüfung, ob ein Benutzer Admin-Rechte hat
 * 
 * @return bool True, wenn Benutzer Administrator ist, sonst False
 */
function ist_admin() {
    return ist_angemeldet() && isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;
}

/**
 * Funktion, die den Zugriff auf geschützte Seiten nur für angemeldete Benutzer erlaubt
 * Kompetenz C8: Eine angemeldete Person hat Zugriff auf weitere Funktionen
 */
function nur_angemeldet_zugriff() {
    if (!ist_angemeldet()) {
        // Umleitung zur Anmeldeseite, wenn nicht angemeldet
        header('Location: /login.php?error=login_required');
        exit;
    }
}

/**
 * Funktion, die den Zugriff auf Admin-Seiten nur für Administratoren erlaubt
 */
function nur_admin_zugriff() {
    if (!ist_admin()) {
        // Umleitung zum Dashboard, wenn keine Admin-Rechte
        header('Location: /dashboard.php?error=admin_required');
        exit;
    }
}

/**
 * Funktion zur Prüfung, ob der aktuelle Benutzer Eigentümer eines Datensatzes ist
 * Kompetenz C17, C18: Nur Eigentümer können Datensätze ändern/löschen
 * 
 * @param int $eintrag_id ID des zu prüfenden Eintrags
 * @param PDO $pdo Datenbankverbindung
 * @return bool True wenn der aktuelle Benutzer Eigentümer ist, sonst False
 */
function ist_eigentümer($eintrag_id, $pdo) {
    if (!ist_angemeldet()) {
        return false;
    }
    
    $stmt = $pdo->prepare("SELECT benutzer_id FROM eintraege WHERE id = :id LIMIT 1");
    $stmt->execute(['id' => $eintrag_id]);
    $eintrag = $stmt->fetch();
    
    if ($eintrag && $eintrag['benutzer_id'] == $_SESSION['user_id']) {
        return true;
    }
    
    return false;
}
?>