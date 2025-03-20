<?php
/**
 * Detailansicht eines Eintrags
 * Diese Seite zeigt alle Details eines Eintrags und seine Bilder an
 */

// Einbinden der benötigten Dateien
require_once '../includes/session.php';
require_once '../includes/functions.php';
require_once '../config/database.php';

// Eintrag-ID aus der URL abrufen
$eintrag_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Eintrag aus der Datenbank abrufen
$stmt = $pdo->prepare("
    SELECT e.id, e.titel, e.beschreibung, e.erstellt_am, e.aktualisiert_am,
           b.id as benutzer_id, b.vorname, b.nachname, b.profilbild
    FROM eintraege e
    JOIN benutzer b ON e.benutzer_id = b.id
    WHERE e.id = :id 
    LIMIT 1
");
$stmt->execute(['id' => $eintrag_id]);
$eintrag = $stmt->fetch();

// Wenn kein Eintrag gefunden wurde, zur Startseite umleiten
if (!$eintrag) {
    umleiten_zu('index.php?error=entry_not_found');
}

// Alle Bilder des Eintrags abrufen
$stmt = $pdo->prepare("
    SELECT id, dateiname, ist_thumbnail, hochgeladen_am 
    FROM eintrag_bilder 
    WHERE eintrag_id = :eintrag_id 
    ORDER BY ist_thumbnail DESC, hochgeladen_am ASC
");
$stmt->execute(['eintrag_id' => $eintrag_id]);
$bilder = $stmt->fetchAll();

// Header einbinden
include_once '../includes/header.php';

// Profilbild des Erstellers anzeigen oder Default verwenden
$profilbild_pfad = 'uploads/placeholder.png'; // Default path
if ($eintrag['profilbild'] && file_exists('uploads/' . $eintrag['profilbild'])) {
    $profilbild_pfad = 'uploads/' . $eintrag['profilbild'];
}
?>

<div class="container mt-5">
    <div class="row">
        <div class="col-md-10 mx-auto">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0 ms-2">
                                                <?php 
                                                    $titel = htmlspecialchars($eintrag['titel']);
                                                    if (strlen($titel) > 40) {  // Kürzt Titel auf 40 Zeichen
                                                        echo substr($titel, 0, 37) . '...';
                                                    } else {
                                                        echo $titel;
                                                    }
                                                ?>
                                                </h4>
                        <a href="index.php" class="btn btn-light btn-sm">Zurück zur Übersicht</a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Informationen zum Ersteller -->
                    <div class="mb-4 d-flex align-items-center">
                        <img src="<?php echo htmlspecialchars($profilbild_pfad); ?>" alt="Profilbild" class="rounded-circle mr-3" style="width: 50px; height: 50px; object-fit: cover;">
                        <div class="ms-3">
                            <p class="mb-0">Erstellt von: <strong><?php echo htmlspecialchars($eintrag['vorname'] . ' ' . $eintrag['nachname']); ?></strong></p>
                            <p class="mb-0 text-muted small">
                                am <?php echo date('d.m.Y H:i', strtotime($eintrag['erstellt_am'])); ?> Uhr
                                <?php if ($eintrag['aktualisiert_am']): ?>
                                    (aktualisiert am <?php echo date('d.m.Y H:i', strtotime($eintrag['aktualisiert_am'])); ?> Uhr)
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                    
                    <!-- Beschreibung des Eintrags -->
                    <div class="mb-4">
                        <h5>Beschreibung</h5>
                        <div class="p-3 bg-light rounded">
                            <?php echo nl2br(htmlspecialchars($eintrag['beschreibung'])); ?>
                        </div>
                    </div>
                    
                    <!-- Bilder des Eintrags -->
                    <?php if (count($bilder) > 0): ?>
                        <h5>Bilder</h5>
                        <div class="row">
                            <?php foreach ($bilder as $bild): ?>
                                <div class="col-md-4 mb-3">
                                    <div class="card">
                                        <a href="view_image.php?id=<?php echo $bild['id']; ?>" title="Bild in voller Größe anzeigen">
                                            <img src="uploads/<?php echo htmlspecialchars($bild['dateiname']); ?>" 
                                                class="card-img-top" 
                                                alt="Bild" 
                                                style="height: 200px; object-fit: cover;">
                                        </a>
                                        <?php if ($bild['ist_thumbnail']): ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            Dieser Eintrag enthält keine Bilder.
                        </div>
                    <?php endif; ?>
                    
                    <!-- Aktionsbuttons -->
                    <div class="mt-4">
                        <?php if (ist_angemeldet() && $_SESSION['user_id'] == $eintrag['benutzer_id']): ?>
                            <a href="edit_entry.php?id=<?php echo $eintrag['id']; ?>" class="btn btn-primary">Bearbeiten</a>
                            <a href="delete_entry.php?id=<?php echo $eintrag['id']; ?>" class="btn btn-danger" 
                               onclick="return confirm('Sind Sie sicher, dass Sie diesen Eintrag löschen möchten?');">Löschen</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Footer einbinden
include_once '../includes/footer.php';
?>