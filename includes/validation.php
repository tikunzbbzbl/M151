<?php
/**
 * Validierungsfunktionen
 * Kompetenz C6: In meinem Projekt werden alle Benutzereingaben serverseitig validiert.
 * Kompetenz C7: Script-Injection wird in meinem Projekt konsequent verhindert.
 * Kompetenz C19: In meinem Projekt wird SQL-Injection konsequent verhindert.
 */

/**
 * Funktion zur sicheren Bereinigung von Eingabedaten
 * Kompetenz C7: Script-Injection verhindern
 * 
 * @param string $daten Die zu bereinigenden Daten
 * @return string Bereinigte Daten
 */
function bereinige_eingabe($daten) {
    $daten = trim($daten); // Leerzeichen am Anfang und Ende entfernen
    $daten = stripslashes($daten); // Backslashes entfernen
    $daten = htmlspecialchars($daten, ENT_QUOTES, 'UTF-8'); // HTML-Zeichen umwandeln
    return $daten;
}

/**
 * Funktion zur Validierung einer E-Mail-Adresse
 * Kompetenz C6: Serverseitige Validierung
 * 
 * @param string $email Die zu prüfende E-Mail-Adresse
 * @return bool True wenn gültig, sonst False
 */
function ist_gueltige_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Funktion zur Validierung eines Passworts
 * Kompetenz C6: Serverseitige Validierung
 * 
 * @param string $passwort Das zu prüfende Passwort
 * @return bool True wenn gültig, sonst False
 */
function ist_gueltiges_passwort($passwort) {
    // Passwort muss mindestens 8 Zeichen lang sein und Buchstaben und Zahlen enthalten
    return strlen($passwort) >= 8 && 
           preg_match('/[A-Za-z]/', $passwort) && 
           preg_match('/[0-9]/', $passwort);
}

/**
 * Funktion zum sicheren Hashing von Passwörtern
 * Kompetenz C11: Passwörter sicher hashen und salzen
 * 
 * @param string $passwort Das zu hashende Passwort
 * @return string Gehashtes Passwort
 */
function passwort_hash_erstellen($passwort) {
    // PASSWORD_DEFAULT verwendet den aktuell stärksten Algorithmus (derzeit bcrypt)
    // Ein zufälliger Salt wird automatisch generiert und im Hash-String gespeichert
    return password_hash($passwort, PASSWORD_DEFAULT);
}

/**
 * Funktion zur Überprüfung eines Passworts gegen den gespeicherten Hash
 * Kompetenz C11: Passwörter sicher hashen und salzen
 * 
 * @param string $passwort Das eingegebene Passwort
 * @param string $hash Der gespeicherte Hash
 * @return bool True wenn das Passwort stimmt, sonst False
 */
function passwort_verifizieren($passwort, $hash) {
    return password_verify($passwort, $hash);
}

/**
 * Funktion zur Validierung von Formular-Eingaben
 * Kompetenz C6: Serverseitige Validierung
 * 
 * @param array $daten Assoziatives Array mit Formulardaten
 * @param array $regeln Validierungsregeln
 * @return array Array mit Fehlermeldungen (leer, wenn keine Fehler)
 */
function validiere_formular($daten, $regeln) {
    $fehler = [];
    
    foreach ($regeln as $feld => $regel) {
        // Prüfen ob das Feld existiert
        if (!isset($daten[$feld])) {
            $fehler[$feld] = "Das Feld $feld fehlt.";
            continue;
        }
        
        // Bereinigen der Eingabe
        $wert = bereinige_eingabe($daten[$feld]);
        
        // Pflichtfeld prüfen
        if (isset($regel['required']) && $regel['required'] && empty($wert)) {
            $fehler[$feld] = "Das Feld $feld ist erforderlich.";
            continue;
        }
        
        // Minimale Länge prüfen
        if (isset($regel['min_length']) && strlen($wert) < $regel['min_length']) {
            $fehler[$feld] = "Das Feld $feld muss mindestens {$regel['min_length']} Zeichen lang sein.";
            continue;
        }
        
        // Maximale Länge prüfen
        if (isset($regel['max_length']) && strlen($wert) > $regel['max_length']) {
            $fehler[$feld] = "Das Feld $feld darf höchstens {$regel['max_length']} Zeichen lang sein.";
            continue;
        }
        
        // E-Mail validieren
        if (isset($regel['email']) && $regel['email'] && !ist_gueltige_email($wert)) {
            $fehler[$feld] = "Bitte geben Sie eine gültige E-Mail-Adresse ein.";
            continue;
        }
        
        // Passwort validieren
        if (isset($regel['passwort']) && $regel['passwort'] && !ist_gueltiges_passwort($wert)) {
            $fehler[$feld] = "Das Passwort muss mindestens 8 Zeichen lang sein und Buchstaben sowie Zahlen enthalten.";
            continue;
        }
    }
    
    return $fehler;
}
?>
