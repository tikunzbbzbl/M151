<?php
/**
 * Allgemeine Hilfsfunktionen für das Projekt
 */

/**
 * Funktion zum Anzeigen von Erfolgsmeldungen
 * 
 * @param string $message Die anzuzeigende Meldung
 * @return string HTML für die Erfolgsmeldung
 */
function erfolgs_meldung($message) {
    return '<div class="alert alert-success">' . $message . '</div>';
}

/**
 * Funktion zum Anzeigen von Fehlermeldungen
 * 
 * @param string $message Die anzuzeigende Meldung
 * @return string HTML für die Fehlermeldung
 */
function fehler_meldung($message) {
    return '<div class="alert alert-danger">' . $message . '</div>';
}

/**
 * Funktion zum Anzeigen von Warnmeldungen
 * 
 * @param string $message Die anzuzeigende Meldung
 * @return string HTML für die Warnmeldung
 */
function warn_meldung($message) {
    return '<div class="alert alert-warning">' . $message . '</div>';
}

/**
 * Funktion zum Umleiten zu einer anderen Seite
 * 
 * @param string $url Die Ziel-URL
 */
function umleiten_zu($url) {
    header("Location: $url");
    exit;
}

/**
 * Funktion zum Erstellen eines HTML-Eingabefelds mit Validierung
 * 
 * @param string $name Name des Feldes
 * @param string $label Beschriftung des Feldes
 * @param string $typ Feldtyp (text, email, password, etc.)
 * @param array $attribute Zusätzliche Attribute (z.B. required, placeholder)
 * @param array $fehler Array mit Fehlermeldungen
 * @return string HTML für das Eingabefeld
 */
function eingabefeld_erstellen($name, $label, $typ = 'text', $attribute = [], $fehler = []) {
    $id = $name;
    $value = isset($_POST[$name]) ? $_POST[$name] : '';
    $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    
    $html = '<div class="form-group">';
    $html .= "<label for=\"$id\">$label</label>";
    
    $attr_str = '';
    foreach ($attribute as $key => $val) {
        $attr_str .= " $key=\"$val\"";
    }
    
    $fehler_klasse = isset($fehler[$name]) ? ' is-invalid' : '';
    $html .= "<input type=\"$typ\" name=\"$name\" id=\"$id\" value=\"$value\" class=\"form-control$fehler_klasse\"$attr_str>";
    
    if (isset($fehler[$name])) {
        $html .= "<div class=\"invalid-feedback\">{$fehler[$name]}</div>";
    }
    
    $html .= '</div>';
    
    return $html;
}
?>