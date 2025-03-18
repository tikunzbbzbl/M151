<?php
/**
 * Dashboard-Seite (geschützter Bereich für angemeldete Benutzer)
 */

// Einbinden der benötigten Dateien
require_once '../config/database.php';
require_once '../includes/session.php';
require_once '../includes/functions.php';

// Zugriffsschutz: Nur für angemeldete Benutzer
nur_angemeldet_zugriff();

// Header einbinden
include_once '../includes/header.php';

// Einträge des Benutzers aus der Datenbank abrufen
$stmt = $pdo->prepare("
    SELECT e.id, e.titel, e.beschreibung, e.erstellt_am, e.aktualisiert_am 
    FROM eintraege e
    WHERE e.benutzer_id = :benutzer_id 
    ORDER BY e.erstellt_am DESC
");
$stmt->execute(['benutzer_id' => $_SESSION['user_id']]);
$eintraege = $stmt->fetchAll();

// Aktuelles Profilbild abrufen
$stmt = $pdo->prepare("SELECT profilbild FROM benutzer WHERE id = :id");
$stmt->execute(['id' => $_SESSION['user_id']]);
$profilbild = $stmt->fetchColumn();

// Standard-Profilbild verwenden falls keines gesetzt ist
$profilbild_url = 'uploads/placeholder.png';
if ($profilbild && file_exists('uploads/' . $profilbild)) {
    $profilbild_url = 'uploads/' . $profilbild;
}
?>

<div class="container mt-5">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <img src="<?php echo htmlspecialchars($profilbild_url); ?>" alt="Profilbild" class="rounded-circle me-3" style="width: 50px; height: 50px; object-fit: cover;">
                        <h4 class="mb-0">Dashboard - Willkommen, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</h4>
                    </div>
                    <div>
                        <a href="profile_edit.php" class="btn btn-info btn-sm">Profilbild ändern</a>
                        <a href="change_password.php" class="btn btn-warning btn-sm">Passwort ändern</a>
                        <a href="logout.php" class="btn btn-danger btn-sm">Abmelden</a>
                    </div>
                </div>
                <div class="card-body">
                    
                    <!-- Erfolgsmeldung nach Erstellen/Ändern/Löschen eines Eintrags -->
                    <?php if (isset($_GET['success']) && $_GET['success'] == 'created'): ?>
                        <?php echo erfolgs_meldung("Eintrag erfolgreich erstellt!"); ?>
                    <?php elseif (isset($_GET['success']) && $_GET['success'] == 'updated'): ?>
                        <?php echo erfolgs_meldung("Eintrag erfolgreich aktualisiert!"); ?>
                    <?php elseif (isset($_GET['success']) && $_GET['success'] == 'deleted'): ?>
                        <?php echo erfolgs_meldung("Eintrag erfolgreich gelöscht!"); ?>
                    <?php elseif (isset($_GET['success']) && $_GET['success'] == 'password_changed'): ?>
                        <?php echo erfolgs_meldung("Passwort erfolgreich geändert!"); ?>
                    <?php endif; ?>
                    
                    <!-- Fehleranzeige -->
                    <?php if (isset($_GET['error']) && $_GET['error'] == 'permission'): ?>
                        <?php echo fehler_meldung("Sie haben keine Berechtigung, diesen Eintrag zu bearbeiten oder zu löschen."); ?>
                    <?php endif; ?>
                    
                    <h5>Ihre Einträge</h5>
                    
                    <!-- Button zum Erstellen eines neuen Eintrags -->
                    <div class="mb-3">
                        <a href="create_entry.php" class="btn btn-success">Neuen Eintrag erstellen</a>
                    </div>
                    
                    <!-- Anzeige der Einträge in Karten -->
                    <?php if (count($eintraege) > 0): ?>
                        <div class="row">
                            <?php foreach ($eintraege as $eintrag): ?>
                                <?php
                                // Thumbnail für den Eintrag abrufen
                                $stmt = $pdo->prepare("
                                    SELECT dateiname 
                                    FROM eintrag_bilder 
                                    WHERE eintrag_id = :eintrag_id AND ist_thumbnail = 1 
                                    LIMIT 1
                                ");
                                $stmt->execute(['eintrag_id' => $eintrag['id']]);
                                $thumbnail = $stmt->fetchColumn();
                                
// Anzahl der Bilder für diesen Eintrag
$stmt = $pdo->prepare("SELECT COUNT(*) FROM eintrag_bilder WHERE eintrag_id = :eintrag_id");
$stmt->execute(['eintrag_id' => $eintrag['id']]);
$anzahl_bilder = $stmt->fetchColumn();
?>

<div class="col-md-6 col-lg-4 mb-4">
    <div class="card h-100">
        <?php if ($thumbnail && file_exists('uploads/' . $thumbnail)): ?>
            <img src="uploads/<?php echo htmlspecialchars($thumbnail); ?>" class="card-img-top" alt="Thumbnail" style="height: 200px; object-fit: cover;">
        <?php endif; ?>
        <div class="card-body">
            <h5 class="card-title"><?php echo htmlspecialchars($eintrag['titel']); ?></h5>
            <p class="card-text">
                <?php 
                // Beschreibung kürzen, wenn sie zu lang ist
                $beschreibung = htmlspecialchars($eintrag['beschreibung']);
                if (strlen($beschreibung) > 150) {
                    echo substr($beschreibung, 0, 150) . '...';
                } else {
                    echo $beschreibung;
                }
                ?>
            </p>
            <?php if ($anzahl_bilder > 0): ?>
                <p class="text-muted small"><i class="fa fa-image"></i> <?php echo $anzahl_bilder; ?> Bild<?php echo $anzahl_bilder != 1 ? 'er' : ''; ?></p>
            <?php endif; ?>
            <p class="text-muted small">Erstellt am: <?php echo date('d.m.Y H:i', strtotime($eintrag['erstellt_am'])); ?></p>
        </div>
        <div class="card-footer">
            <a href="edit_entry.php?id=<?php echo $eintrag['id']; ?>" class="btn btn-primary btn-sm">Bearbeiten</a>
            <a href="delete_entry.php?id=<?php echo $eintrag['id']; ?>" 
               class="btn btn-danger btn-sm" 
               onclick="return confirm('Sind Sie sicher, dass Sie diesen Eintrag löschen möchten?');">Löschen</a>
        </div>
    </div>
</div>
<?php endforeach; ?>
</div>
<?php else: ?>
<div class="alert alert-info">
Sie haben noch keine Einträge erstellt. Klicken Sie auf "Neuen Eintrag erstellen", um loszulegen!
</div>
<?php endif; ?>
</div>
</div>
</div>
</div>
</div>

<?php
// Footer einbinden
include_once '../includes/footer.php';
?>