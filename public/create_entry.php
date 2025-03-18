<?php
/**
 * Eintrag erstellen mit Bildupload-Funktion
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
    // Validierungsregeln für das Eintrag-Formular
    $regeln = [
        'titel' => ['required' => true, 'min_length' => 3, 'max_length' => 100],
        'beschreibung' => ['required' => true, 'min_length' => 10, 'max_length' => 1000]
    ];
    
    // Serverseitige Validierung durchführen
    $fehler = validiere_formular($_POST, $regeln);
    
    // Wenn keine Fehler vorhanden sind, Eintrag speichern
    if (empty($fehler)) {
        // Eingaben bereinigen
        $titel = bereinige_eingabe($_POST['titel']);
        $beschreibung = bereinige_eingabe($_POST['beschreibung']);
        
        // Starte eine Transaktion
        $pdo->beginTransaction();
        
        try {
            // Eintrag in die Datenbank einfügen
            $stmt = $pdo->prepare("
                INSERT INTO eintraege (benutzer_id, titel, beschreibung, erstellt_am) 
                VALUES (:benutzer_id, :titel, :beschreibung, NOW())
            ");
            
            // Parameter für die Anfrage
            $params = [
                'benutzer_id' => $_SESSION['user_id'],
                'titel' => $titel,
                'beschreibung' => $beschreibung
            ];
            
            // Eintrag speichern
            $stmt->execute($params);
            
            // ID des eingefügten Eintrags holen
            $eintrag_id = $pdo->lastInsertId();
            
            // Bilder verarbeiten, falls vorhanden
            if (isset($_FILES['bilder']) && !empty($_FILES['bilder']['name'][0])) {
                $erfolgreiche_uploads = 0;
                $erlaubte_typen = ['image/jpeg', 'image/png', 'image/gif'];
                
                // Durchlaufe alle hochgeladenen Dateien
                foreach ($_FILES['bilder']['tmp_name'] as $key => $tmp_name) {
                    // Überspringe, wenn kein Bild hochgeladen wurde
                    if ($_FILES['bilder']['error'][$key] !== UPLOAD_ERR_OK) {
                        continue;
                    }
                    
                    $datei_typ = $_FILES['bilder']['type'][$key];
                    $datei_groesse = $_FILES['bilder']['size'][$key];
                    
                    // Prüfe Dateityp und -größe
                    if (in_array($datei_typ, $erlaubte_typen) && $datei_groesse <= 5 * 1024 * 1024) {
                        // Generiere einen eindeutigen Dateinamen
                        $dateiendung = pathinfo($_FILES['bilder']['name'][$key], PATHINFO_EXTENSION);
                        $neuer_dateiname = 'eintrag_' . $eintrag_id . '_' . time() . '_' . $key . '.' . $dateiendung;
                        $ziel_pfad = 'uploads/' . $neuer_dateiname;
                        
                        // Verschiebe die Datei
                        if (move_uploaded_file($tmp_name, $ziel_pfad)) {
                            // Bestimme, ob es das erste Bild ist (dann ist es das Thumbnail)
                            $ist_thumbnail = ($erfolgreiche_uploads === 0) ? 1 : 0;
                            
                            // Speichere das Bild in der Datenbank
                            $stmt_bild = $pdo->prepare("
                                INSERT INTO eintrag_bilder (eintrag_id, dateiname, ist_thumbnail, hochgeladen_am) 
                                VALUES (:eintrag_id, :dateiname, :ist_thumbnail, NOW())
                            ");
                            
                            $stmt_bild->execute([
                                'eintrag_id' => $eintrag_id,
                                'dateiname' => $neuer_dateiname,
                                'ist_thumbnail' => $ist_thumbnail
                            ]);
                            
                            $erfolgreiche_uploads++;
                        }
                    }
                }
            }
            
            // Commit der Transaktion
            $pdo->commit();
            
            // Erfolgreiche Erstellung
            $success = true;
            
            // Zum Dashboard weiterleiten
            umleiten_zu('dashboard.php?success=created');
        } catch (PDOException $e) {
            // Rollback im Fehlerfall
            $pdo->rollBack();
            $fehler['allgemein'] = "Fehler beim Erstellen des Eintrags. Bitte versuchen Sie es später erneut.";
        }
    }
}

// Header einbinden
include_once '../includes/header.php';
?>

<div class="container mt-5">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h4>Neuen Eintrag erstellen</h4>
                </div>
                <div class="card-body">
                    
                    <?php if ($success): ?>
                        <!-- Erfolgsanzeige nach erfolgreicher Erstellung -->
                        <div class="alert alert-success">
                            Eintrag erfolgreich erstellt! Sie werden weitergeleitet...
                        </div>
                        <script>
                            setTimeout(function() {
                                window.location.href = 'dashboard.php?success=created';
                            }, 2000);
                        </script>
                    <?php else: ?>
                        <!-- Anzeige von allgemeinen Fehlern -->
                        <?php if (isset($fehler['allgemein'])): ?>
                            <?php echo fehler_meldung($fehler['allgemein']); ?>
                        <?php endif; ?>
                        
                        <!-- Eintrag-Erstellungs-Formular -->
                        <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" enctype="multipart/form-data" novalidate>
                            <!-- Formularfelder mit clientseitiger Validierung -->
                            <div class="form-group">
                                <label for="titel">Titel</label>
                                <input type="text" name="titel" id="titel" 
                                       class="form-control <?php echo isset($fehler['titel']) ? 'is-invalid' : ''; ?>" 
                                       value="<?php echo isset($_POST['titel']) ? htmlspecialchars($_POST['titel']) : ''; ?>" 
                                       required minlength="3" maxlength="100">
                                <?php if (isset($fehler['titel'])): ?>
                                    <div class="invalid-feedback"><?php echo $fehler['titel']; ?></div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="form-group mt-3">
                                <label for="beschreibung">Beschreibung</label>
                                <textarea name="beschreibung" id="beschreibung" 
                                          class="form-control <?php echo isset($fehler['beschreibung']) ? 'is-invalid' : ''; ?>" 
                                          rows="5" required minlength="10" maxlength="1000"><?php echo isset($_POST['beschreibung']) ? htmlspecialchars($_POST['beschreibung']) : ''; ?></textarea>
                                <?php if (isset($fehler['beschreibung'])): ?>
                                    <div class="invalid-feedback"><?php echo $fehler['beschreibung']; ?></div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="form-group mt-3">
                                <label for="bilder">Bilder hochladen (optional)</label>
                                <input type="file" name="bilder[]" id="bilder" class="form-control-file" accept="image/jpeg, image/png, image/gif" multiple>
                                <small class="form-text text-muted">Sie können mehrere Bilder auswählen. Das erste Bild wird automatisch als Thumbnail verwendet. Max. 5MB pro Bild.</small>
                            </div>
                            
                            <div class="form-group mt-4">
                                <button type="submit" class="btn btn-success">Eintrag erstellen</button>
                                <a href="dashboard.php" class="btn btn-secondary ml-2">Zurück zum Dashboard</a>
                            </div>
                        </form>
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