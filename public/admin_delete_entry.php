<?php
/**
 * Admin-Eintrag löschen
 * Nur Administratoren können alle Einträge löschen
 */

// Einbinden der benötigten Dateien
require_once '../config/database.php';
require_once '../includes/session.php';
require_once '../includes/functions.php';

// Zugriffsschutz: Nur für angemeldete Benutzer mit Admin-Rechten
nur_angemeldet_zugriff();
nur_admin_zugriff();

// Eintrag-ID aus der URL abrufen
$eintrag_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

try {
    // Starte eine Transaktion für sicheres Löschen
    $pdo->beginTransaction();
    
    // Alle Bilder dieses Eintrags abrufen und vom Dateisystem löschen
    $stmt = $pdo->prepare("
        SELECT dateiname FROM eintrag_bilder 
        WHERE eintrag_id = :eintrag_id
    ");
    $stmt->execute(['eintrag_id' => $eintrag_id]);
    $bilder = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Bilder vom Dateisystem löschen
    foreach ($bilder as $bild) {
        $datei_pfad = 'uploads/' . $bild;
        if (file_exists($datei_pfad) && $bild != 'placeholder.png') {
            unlink($datei_pfad);
        }
    }
    
    // Eintrag aus der Datenbank löschen
    // Die Bilder werden durch die Fremdschlüsselbeziehung (ON DELETE CASCADE) automatisch gelöscht
    $stmt = $pdo->prepare("DELETE FROM eintraege WHERE id = :id");
    $stmt->execute(['id' => $eintrag_id]);
    
    // Commit der Transaktion
    $pdo->commit();
    
    // Erfolgreiche Löschung, zum Admin-Dashboard umleiten
    umleiten_zu('admin_dashboard.php?success=entry_deleted');
} catch (PDOException $e) {
    // Bei Fehler Transaktion zurückrollen
    $pdo->rollBack();
    umleiten_zu('admin_dashboard.php?error=deletion_failed');
}
?>