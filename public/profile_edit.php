<?php
/**
 * Profil bearbeiten - Profilbild hochladen
 */

// Einbinden der benötigten Dateien
require_once '../config/database.php';
require_once '../includes/session.php';
require_once '../includes/functions.php';
require_once '../includes/validation.php';

// Zugriffsschutz: Nur für angemeldete Benutzer
nur_angemeldet_zugriff();

// Array für Fehlermeldungen
$fehler = [];
$success = false;

// Verarbeitung des Formulars bei POST-Anfrage
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Prüfen, ob ein Bild hochgeladen wurde
    if (isset($_FILES['profilbild']) && $_FILES['profilbild']['error'] === UPLOAD_ERR_OK) {
        // Erlaubte Bildformate
        $erlaubte_typen = ['image/jpeg', 'image/png', 'image/gif'];
        $datei_typ = $_FILES['profilbild']['type'];
        
        if (in_array($datei_typ, $erlaubte_typen)) {
            // Begrenzung der Dateigröße (z.B. auf 2MB)
            if ($_FILES['profilbild']['size'] <= 2 * 1024 * 1024) {
                // Generiere einen eindeutigen Dateinamen
                $dateiendung = pathinfo($_FILES['profilbild']['name'], PATHINFO_EXTENSION);
                $neuer_dateiname = 'profil_' . $_SESSION['user_id'] . '_' . time() . '.' . $dateiendung;
                $ziel_pfad = 'uploads/' . $neuer_dateiname;
                
                // Versuche, die Datei zu verschieben
                if (move_uploaded_file($_FILES['profilbild']['tmp_name'], $ziel_pfad)) {
                    // Lösche altes Profilbild, falls vorhanden
                    $stmt = $pdo->prepare("SELECT profilbild FROM benutzer WHERE id = :id");
                    $stmt->execute(['id' => $_SESSION['user_id']]);
                    $altes_bild = $stmt->fetchColumn();
                    
                    if ($altes_bild && file_exists('uploads/' . $altes_bild) && $altes_bild != 'placeholder.png') {
                        unlink('uploads/' . $altes_bild);
                    }
                    
                    // Aktualisiere die Datenbank mit dem neuen Profilbild
                    $stmt = $pdo->prepare("UPDATE benutzer SET profilbild = :profilbild WHERE id = :id");
                    $stmt->execute([
                        'profilbild' => $neuer_dateiname,
                        'id' => $_SESSION['user_id']
                    ]);
                    
                    // Erfolgreiche Aktualisierung
                    $success = true;
                } else {
                    $fehler['allgemein'] = "Fehler beim Hochladen des Bildes.";
                }
            } else {
                $fehler['allgemein'] = "Die Datei ist zu groß. Maximale Größe: 2MB.";
            }
        } else {
            $fehler['allgemein'] = "Ungültiges Dateiformat. Erlaubt sind JPEG, PNG und GIF.";
        }
    } else if ($_FILES['profilbild']['error'] !== UPLOAD_ERR_NO_FILE) {
        // Ein anderer Fehler als "keine Datei ausgewählt" ist aufgetreten
        $fehler['allgemein'] = "Fehler beim Hochladen: Code " . $_FILES['profilbild']['error'];
    }
}

// Aktuelles Profilbild des Benutzers abrufen
$stmt = $pdo->prepare("SELECT profilbild FROM benutzer WHERE id = :id");
$stmt->execute(['id' => $_SESSION['user_id']]);
$profilbild = $stmt->fetchColumn();

// Header einbinden
include_once '../includes/header.php';
?>

<div class="container mt-5">
    <div class="row">
        <div class="col-md-6 mx-auto">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4>Profilbild bearbeiten</h4>
                </div>
                <div class="card-body">
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success">
                            Profilbild erfolgreich aktualisiert!
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($fehler['allgemein'])): ?>
                        <?php echo fehler_meldung($fehler['allgemein']); ?>
                    <?php endif; ?>
                    
                    <!-- Aktuelles Profilbild anzeigen -->
                    <div class="text-center mb-4">
                        <h5>Aktuelles Profilbild</h5>
                        <?php if ($profilbild && file_exists('uploads/' . $profilbild)): ?>
                            <img src="uploads/<?php echo htmlspecialchars($profilbild); ?>" alt="Profilbild" class="img-thumbnail" style="max-width: 200px; max-height: 200px;">
                        <?php else: ?>
                            <img src="uploads/placeholder.png" alt="Standard-Profilbild" class="img-thumbnail" style="max-width: 200px; max-height: 200px;">
                            <p class="text-muted mt-2">Standardbild (Sie haben noch kein eigenes Profilbild hochgeladen)</p>
                        <?php endif; ?>
                    </div>
                    
                    <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="profilbild">Neues Profilbild hochladen</label>
                            <input type="file" name="profilbild" id="profilbild" class="form-control-file" accept="image/jpeg, image/png, image/gif" required>
                            <small class="form-text text-muted">Maximale Dateigröße: 2MB. Erlaubte Formate: JPEG, PNG, GIF.</small>
                        </div>
                        
                        <div class="form-group mt-4">
                            <button type="submit" class="btn btn-primary">Hochladen</button>
                            <a href="dashboard.php" class="btn btn-secondary ml-2">Zurück zum Dashboard</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Footer einbinden
include_once '../includes/footer.php';
?>