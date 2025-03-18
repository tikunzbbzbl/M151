<?php
/**
 * Eintrag bearbeiten mit Bildverwaltung
 */

// Einbinden der benötigten Dateien
require_once '../config/database.php';
require_once '../includes/session.php';
require_once '../includes/functions.php';
require_once '../includes/validation.php';

// Zugriffsschutz: Nur für angemeldete Benutzer
nur_angemeldet_zugriff();

// Eintrag-ID aus der URL abrufen
$eintrag_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Prüfen, ob der Benutzer der Eigentümer des Eintrags ist
if (!ist_eigentümer($eintrag_id, $pdo)) {
    // Keine Berechtigung, zum Dashboard umleiten
    umleiten_zu('dashboard.php?error=permission');
}

// Eintrag aus der Datenbank abrufen
$stmt = $pdo->prepare("
    SELECT id, titel, beschreibung 
    FROM eintraege 
    WHERE id = :id AND benutzer_id = :benutzer_id 
    LIMIT 1
");
$stmt->execute([
    'id' => $eintrag_id,
    'benutzer_id' => $_SESSION['user_id']
]);
$eintrag = $stmt->fetch();

// Wenn kein Eintrag gefunden wurde, zum Dashboard umleiten
if (!$eintrag) {
    umleiten_zu('dashboard.php?error=entry_not_found');
}

// Bestehende Bilder des Eintrags abrufen
$stmt = $pdo->prepare("
    SELECT id, dateiname, ist_thumbnail 
    FROM eintrag_bilder 
    WHERE eintrag_id = :eintrag_id 
    ORDER BY ist_thumbnail DESC, hochgeladen_am ASC
");
$stmt->execute(['eintrag_id' => $eintrag_id]);
$bilder = $stmt->fetchAll();

// Array für Fehlermeldungen
$fehler = [];
$success = false;
$bild_geloescht = false;

// Löschen eines Bildes verarbeiten
if (isset($_GET['delete_image']) && is_numeric($_GET['delete_image'])) {
    $bild_id = (int)$_GET['delete_image'];
    
    // Prüfen, ob das Bild zum Eintrag gehört und der Benutzer der Eigentümer ist
    $stmt = $pdo->prepare("
        SELECT eb.dateiname, eb.ist_thumbnail 
        FROM eintrag_bilder eb 
        JOIN eintraege e ON eb.eintrag_id = e.id 
        WHERE eb.id = :bild_id AND e.benutzer_id = :benutzer_id
    ");
    $stmt->execute([
        'bild_id' => $bild_id,
        'benutzer_id' => $_SESSION['user_id']
    ]);
    $zu_loeschendes_bild = $stmt->fetch();
    
    if ($zu_loeschendes_bild) {
        // War es ein Thumbnail? Falls ja, müssen wir ein neues Thumbnail setzen
        $war_thumbnail = $zu_loeschendes_bild['ist_thumbnail'] ? true : false;
        
        // Datei aus dem Dateisystem löschen
        $datei_pfad = 'uploads/' . $zu_loeschendes_bild['dateiname'];
        if (file_exists($datei_pfad)) {
            unlink($datei_pfad);
        }
        
        // Bild aus der Datenbank löschen
        $stmt = $pdo->prepare("DELETE FROM eintrag_bilder WHERE id = :id");
        $stmt->execute(['id' => $bild_id]);
        
        // Wenn das Thumbnail gelöscht wurde, setze ein neues Thumbnail (das erste Bild)
        if ($war_thumbnail) {
            $stmt = $pdo->prepare("
                SELECT id FROM eintrag_bilder 
                WHERE eintrag_id = :eintrag_id 
                ORDER BY hochgeladen_am ASC 
                LIMIT 1
            ");
            $stmt->execute(['eintrag_id' => $eintrag_id]);
            $neues_thumbnail_id = $stmt->fetchColumn();
            
            if ($neues_thumbnail_id) {
                $stmt = $pdo->prepare("
                    UPDATE eintrag_bilder 
                    SET ist_thumbnail = 1 
                    WHERE id = :id
                ");
                $stmt->execute(['id' => $neues_thumbnail_id]);
            }
        }
        
        $bild_geloescht = true;
        
        // Zurück zur Edit-Seite ohne Parameter umleiten
        umleiten_zu('edit_entry.php?id=' . $eintrag_id . '&image_deleted=1');
    }
}

// Thumbnail setzen verarbeiten
if (isset($_GET['set_thumbnail']) && is_numeric($_GET['set_thumbnail'])) {
    $bild_id = (int)$_GET['set_thumbnail'];
    
    // Prüfen, ob das Bild zum Eintrag gehört und der Benutzer der Eigentümer ist
    $stmt = $pdo->prepare("
        SELECT eb.id 
        FROM eintrag_bilder eb 
        JOIN eintraege e ON eb.eintrag_id = e.id 
        WHERE eb.id = :bild_id AND e.benutzer_id = :benutzer_id
    ");
    $stmt->execute([
        'bild_id' => $bild_id,
        'benutzer_id' => $_SESSION['user_id']
    ]);
    
    if ($stmt->fetchColumn()) {
        // Transaktion starten
        $pdo->beginTransaction();
        
        try {
            // Alle Thumbnails für diesen Eintrag zurücksetzen
            $stmt = $pdo->prepare("
                UPDATE eintrag_bilder 
                SET ist_thumbnail = 0 
                WHERE eintrag_id = :eintrag_id
            ");
            $stmt->execute(['eintrag_id' => $eintrag_id]);
            
            // Neues Thumbnail setzen
            $stmt = $pdo->prepare("
                UPDATE eintrag_bilder 
                SET ist_thumbnail = 1 
                WHERE id = :bild_id
            ");
            $stmt->execute(['bild_id' => $bild_id]);
            
            // Transaktion abschließen
            $pdo->commit();
            
            // Zurück zur Edit-Seite umleiten
            umleiten_zu('edit_entry.php?id=' . $eintrag_id . '&thumbnail_set=1');
        } catch (PDOException $e) {
            // Bei Fehler Transaktion zurückrollen
            $pdo->rollBack();
            $fehler['allgemein'] = "Fehler beim Setzen des Thumbnails.";
        }
    }
}

// Verarbeitung des Formulars bei POST-Anfrage
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validierungsregeln für das Eintrag-Formular
    $regeln = [
        'titel' => ['required' => true, 'min_length' => 3, 'max_length' => 100],
        'beschreibung' => ['required' => true, 'min_length' => 10, 'max_length' => 1000]
    ];
    
    // Serverseitige Validierung durchführen
    $fehler = validiere_formular($_POST, $regeln);
    
    // Wenn keine Fehler vorhanden sind, Eintrag aktualisieren
    if (empty($fehler)) {
        // Eingaben bereinigen
        $titel = bereinige_eingabe($_POST['titel']);
        $beschreibung = bereinige_eingabe($_POST['beschreibung']);
        
        // Starte eine Transaktion
        $pdo->beginTransaction();
        
        try {
            // Eintrag in der Datenbank aktualisieren
            $stmt = $pdo->prepare("
                UPDATE eintraege 
                SET titel = :titel, beschreibung = :beschreibung, aktualisiert_am = NOW() 
                WHERE id = :id AND benutzer_id = :benutzer_id
            ");
            
            // Parameter für die Anfrage
            $params = [
                'titel' => $titel,
                'beschreibung' => $beschreibung,
                'id' => $eintrag_id,
                'benutzer_id' => $_SESSION['user_id']
            ];
            
            // Eintrag aktualisieren
            $stmt->execute($params);
            
            // Neue Bilder verarbeiten, falls vorhanden
            if (isset($_FILES['bilder']) && !empty($_FILES['bilder']['name'][0])) {
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
                            // Ermittle, ob es ein Thumbnail werden soll
                            // Falls noch kein Thumbnail existiert, setze das erste Bild als Thumbnail
                            $stmt_check = $pdo->prepare("
                                SELECT COUNT(*) FROM eintrag_bilder 
                                WHERE eintrag_id = :eintrag_id AND ist_thumbnail = 1
                            ");
                            $stmt_check->execute(['eintrag_id' => $eintrag_id]);
                            $hat_thumbnail = $stmt_check->fetchColumn() > 0;
                            
                            $ist_thumbnail = !$hat_thumbnail ? 1 : 0;
                            
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
                        }
                    }
                }
            }
            
            // Commit der Transaktion
            $pdo->commit();
            
            // Erfolgreiche Aktualisierung
            $success = true;
            
            // Zum Dashboard weiterleiten
            umleiten_zu('dashboard.php?success=updated');
        } catch (PDOException $e) {
            // Rollback im Fehlerfall
            $pdo->rollBack();
            $fehler['allgemein'] = "Fehler beim Aktualisieren des Eintrags. Bitte versuchen Sie es später erneut.";
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
                <div class="card-header bg-primary text-white">
                    <h4>Eintrag bearbeiten</h4>
                </div>
                <div class="card-body">
                    
                    <?php if ($success): ?>
                        <!-- Erfolgsanzeige nach erfolgreicher Aktualisierung -->
                        <div class="alert alert-success">
                            Eintrag erfolgreich aktualisiert! Sie werden weitergeleitet...
                        </div>
                        <script>
                            setTimeout(function() {
                                window.location.href = 'dashboard.php?success=updated';
                            }, 2000);
                        </script>
                    <?php else: ?>
                        <!-- Anzeige von Meldungen -->
                        <?php if (isset($fehler['allgemein'])): ?>
                            <?php echo fehler_meldung($fehler['allgemein']); ?>
                        <?php endif; ?>
                        
                        <?php if (isset($_GET['image_deleted']) && $_GET['image_deleted'] == 1): ?>
                            <?php echo erfolgs_meldung("Bild erfolgreich gelöscht."); ?>
                        <?php endif; ?>
                        
                        <?php if (isset($_GET['thumbnail_set']) && $_GET['thumbnail_set'] == 1): ?>
                            <?php echo erfolgs_meldung("Thumbnail erfolgreich gesetzt."); ?>
                        <?php endif; ?>
                        
                        <!-- Aktuelle Bilder anzeigen -->
                        <?php if (count($bilder) > 0): ?>
                            <h5>Aktuelle Bilder</h5>
                            <div class="row mb-4">
                                <?php foreach ($bilder as $bild): ?>
                                    <div class="col-md-4 mb-3">
                                        <div class="card">
                                            <img src="uploads/<?php echo htmlspecialchars($bild['dateiname']); ?>" class="card-img-top" alt="Bild" style="height: 150px; object-fit: cover;">
                                            <div class="card-body p-2">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <?php if ($bild['ist_thumbnail']): ?>
                                                        <span class="badge bg-success">Thumbnail</span>
                                                    <?php else: ?>
                                                        <a href="edit_entry.php?id=<?php echo $eintrag_id; ?>&set_thumbnail=<?php echo $bild['id']; ?>" class="btn btn-outline-success btn-sm">Als Thumbnail setzen</a>
                                                    <?php endif; ?>
                                                    <a href="edit_entry.php?id=<?php echo $eintrag_id; ?>&delete_image=<?php echo $bild['id']; ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('Bild wirklich löschen?');">Löschen</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Eintrag-Bearbeitungs-Formular -->
                        <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'] . '?id=' . $eintrag_id); ?>" enctype="multipart/form-data" novalidate>
                            <!-- Formularfelder mit clientseitiger Validierung -->
                            <div class="form-group">
                                <label for="titel">Titel</label>
                                <input type="text" name="titel" id="titel" 
                                       class="form-control <?php echo isset($fehler['titel']) ? 'is-invalid' : ''; ?>" 
                                       value="<?php echo isset($_POST['titel']) ? htmlspecialchars($_POST['titel']) : htmlspecialchars($eintrag['titel']); ?>" 
                                       required minlength="3" maxlength="100">
                                <?php if (isset($fehler['titel'])): ?>
                                    <div class="invalid-feedback"><?php echo $fehler['titel']; ?></div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="form-group mt-3">
                                <label for="beschreibung">Beschreibung</label>
                                <textarea name="beschreibung" id="beschreibung" 
                                          class="form-control <?php echo isset($fehler['beschreibung']) ? 'is-invalid' : ''; ?>" 
                                          rows="5" required minlength="10" maxlength="1000"><?php echo isset($_POST['beschreibung']) ? htmlspecialchars($_POST['beschreibung']) : htmlspecialchars($eintrag['beschreibung']); ?></textarea>
                                <?php if (isset($fehler['beschreibung'])): ?>
                                    <div class="invalid-feedback"><?php echo $fehler['beschreibung']; ?></div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="form-group mt-3">
                                <label for="bilder">Weitere Bilder hochladen (optional)</label>
                                <input type="file" name="bilder[]" id="bilder" class="form-control-file" accept="image/jpeg, image/png, image/gif" multiple>
                                <small class="form-text text-muted">Sie können mehrere Bilder auswählen. Falls noch kein Thumbnail existiert, wird das erste Bild automatisch als Thumbnail verwendet. Max. 5MB pro Bild.</small>
                            </div>
                            
                            <div class="form-group mt-4">
                                <button type="submit" class="btn btn-primary">Eintrag aktualisieren</button>
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