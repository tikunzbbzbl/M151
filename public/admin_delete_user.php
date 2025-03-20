<?php
/**
 * Admin-Benutzer löschen
 * Nur Administratoren haben Zugriff auf diese Seite
 */

// Einbinden der benötigten Dateien
require_once '../config/database.php';
require_once '../includes/session.php';
require_once '../includes/functions.php';

// Zugriffsschutz: Nur für angemeldete Benutzer mit Admin-Rechten
nur_angemeldet_zugriff();
nur_admin_zugriff();

// Benutzer-ID aus der URL abrufen
$benutzer_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Verhindere, dass der Admin sich selbst löscht
if ($benutzer_id == $_SESSION['user_id']) {
    umleiten_zu('admin_dashboard.php?error=cant_delete_self');
}

// Benutzerinformationen abrufen (für Profilbild)
$stmt = $pdo->prepare("SELECT profilbild FROM benutzer WHERE id = :id LIMIT 1");
$stmt->execute(['id' => $benutzer_id]);
$benutzer = $stmt->fetch();

if ($benutzer) {
    try {
        // Starte eine Transaktion für sicheres Löschen
        $pdo->beginTransaction();
        
        // Alle Bilder dieses Benutzers abrufen und vom Dateisystem löschen
        $stmt = $pdo->prepare("
            SELECT eb.dateiname 
            FROM eintrag_bilder eb
            JOIN eintraege e ON eb.eintrag_id = e.id
            WHERE e.benutzer_id = :benutzer_id
        ");
        $stmt->execute(['benutzer_id' => $benutzer_id]);
        $bilder = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        // Bilder vom Dateisystem löschen
        foreach ($bilder as $bild) {
            $datei_pfad = 'uploads/' . $bild;
            if (file_exists($datei_pfad) && $bild != 'placeholder.png') {
                unlink($datei_pfad);
            }
        }
        
        // Profilbild löschen, falls vorhanden
        if ($benutzer['profilbild'] && file_exists('uploads/' . $benutzer['profilbild']) && $benutzer['profilbild'] != 'placeholder.png') {
            unlink('uploads/' . $benutzer['profilbild']);
        }
        
        // Alle Einträge und deren Bilder werden durch die Fremdschlüsselbeziehung (ON DELETE CASCADE) automatisch gelöscht
        
        // Benutzer aus der Datenbank löschen
        $stmt = $pdo->prepare("DELETE FROM benutzer WHERE id = :id");
        $stmt->execute(['id' => $benutzer_id]);
        
        // Commit der Transaktion
        $pdo->commit();
        
        // Erfolgreiche Löschung, zum Admin-Dashboard umleiten
        umleiten_zu('admin_dashboard.php?success=user_deleted');
    } catch (PDOException $e) {
        // Bei Fehler Transaktion zurückrollen
        $pdo->rollBack();
        umleiten_zu('admin_dashboard.php?error=deletion_failed');
    }
} else {
    // Benutzer existiert nicht
    umleiten_zu('admin_dashboard.php?error=user_not_found');
}
?>