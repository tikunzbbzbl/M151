<?php
/**
 * Großansicht eines einzelnen Bildes
 */

// Einbinden der benötigten Dateien
require_once '../includes/session.php';
require_once '../includes/functions.php';
require_once '../config/database.php';

// Bild-ID aus der URL abrufen
$bild_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Bild und zugehörigen Eintrag aus der Datenbank abrufen
$stmt = $pdo->prepare("
    SELECT b.id, b.dateiname, b.eintrag_id, 
           e.titel as eintrag_titel
    FROM eintrag_bilder b
    JOIN eintraege e ON b.eintrag_id = e.id
    WHERE b.id = :id 
    LIMIT 1
");
$stmt->execute(['id' => $bild_id]);
$bild = $stmt->fetch();

// Wenn kein Bild gefunden wurde, zurück zur Startseite
if (!$bild) {
    umleiten_zu('index.php?error=image_not_found');
}

// Header einbinden
include_once '../includes/header.php';
?>

<div class="container mt-5">
    <div class="row">
        <div class="col-md-10 mx-auto">
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4>Bildansicht</h4>
                        <a href="view_entry.php?id=<?php echo $bild['eintrag_id']; ?>" class="btn btn-light btn-sm">Zurück zum Eintrag</a>
                    </div>
                </div>
                <div class="card-body text-center">
                    <img src="uploads/<?php echo htmlspecialchars($bild['dateiname']); ?>" 
                         class="img-fluid" 
                         alt="Bild in Vollansicht" 
                         style="max-height: 80vh;">
                    
                    <div class="mt-3">
                        <p>Aus dem Eintrag: <strong><?php echo htmlspecialchars($bild['eintrag_titel']); ?></strong></p>
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