<?php
/**
 * Eintrag löschen
 * Kompetenz C7: Script-Injection wird in meinem Projekt konsequent verhindert.
 * Kompetenz C18: Diese Datensätze können ausschließlich von der Person gelöscht werden, welche diese Datensätze erstellt hat.
 * Kompetenz C19: In meinem Projekt wird SQL-Injection konsequent verhindert.
 */

// Einbinden der benötigten Dateien
require_once '../config/database.php';
require_once '../includes/session.php';
require_once '../includes/functions.php';

// Zugriffsschutz: Nur für angemeldete Benutzer
nur_angemeldet_zugriff();

// Eintrag-ID aus der URL abrufen und als Integer casten (um SQL-Injection zu verhindern, C19)
$eintrag_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Prüfen, ob der Benutzer der Eigentümer des Eintrags ist (C18)
if (!ist_eigentümer($eintrag_id, $pdo)) {
    // Keine Berechtigung, zum Dashboard umleiten
    umleiten_zu('dashboard.php?error=permission');
}

// Eintrag aus der Datenbank löschen
$stmt = $pdo->prepare("
    DELETE FROM eintraege 
    WHERE id = :id AND benutzer_id = :benutzer_id
");

// Parameter für die Anfrage
$params = [
    'id' => $eintrag_id,
    'benutzer_id' => $_SESSION['user_id'] // Sicherstellen, dass nur der Eigentümer den Eintrag löschen kann (C18)
];

try {
    // Eintrag löschen
    $stmt->execute($params);
    
    // Erfolgreiche Löschung, zum Dashboard umleiten
    umleiten_zu('dashboard.php?success=deleted');
} catch (PDOException $e) {
    // Fehler bei der Löschung, zum Dashboard umleiten
    umleiten_zu('dashboard.php?error=deletion_failed');
}
?>