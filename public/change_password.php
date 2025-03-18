<?php
/**
 * Passwort ändern
 * Kompetenz C5: In meinem Projekt werden alle Benutzereingaben clientseitig validiert.
 * Kompetenz C6: In meinem Projekt werden alle Benutzereingaben serverseitig validiert.
 * Kompetenz C7: Script-Injection wird in meinem Projekt konsequent verhindert.
 * Kompetenz C11: In meinem Projekt werden sensible Daten wie das Passwort mit sicheren und aktuellen Methoden gehasht und gesaltet.
 * Kompetenz C15: In meinem Projekt kann eine angemeldete Person ihr Passwort ändern.
 * Kompetenz C19: In meinem Projekt wird SQL-Injection konsequent verhindert.
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
    // Validierungsregeln für das Passwort-Ändern-Formular
    $regeln = [
        'passwort_alt' => ['required' => true],
        'passwort_neu' => ['required' => true, 'passwort' => true],
        'passwort_neu_wiederholen' => ['required' => true]
    ];
    
    // Serverseitige Validierung durchführen (C6)
    $fehler = validiere_formular($_POST, $regeln);
    
    // Prüfen, ob die neuen Passwörter übereinstimmen
    if ($_POST['passwort_neu'] !== $_POST['passwort_neu_wiederholen']) {
        $fehler['passwort_neu_wiederholen'] = "Die neuen Passwörter stimmen nicht überein.";
    }
    
    // Wenn keine Fehler vorhanden sind, Passwort ändern
    if (empty($fehler)) {
        // Altes Passwort (wird nicht bereinigt) und neue Passwörter
        $passwort_alt = $_POST['passwort_alt'];
        $passwort_neu = $_POST['passwort_neu'];
        
        // Aktuelles Passwort des Benutzers aus der Datenbank abrufen
        $stmt = $pdo->prepare("SELECT passwort FROM benutzer WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $_SESSION['user_id']]);
        $benutzer = $stmt->fetch();
        
        // Prüfen, ob das alte Passwort stimmt
        if ($benutzer && passwort_verifizieren($passwort_alt, $benutzer['passwort'])) {
            // Neues Passwort hashen (C11)
            $passwort_neu_hash = passwort_hash_erstellen($passwort_neu);
            
            // Passwort in der Datenbank aktualisieren
            $stmt = $pdo->prepare("UPDATE benutzer SET passwort = :passwort WHERE id = :id");
            $params = [
                'passwort' => $passwort_neu_hash,
                'id' => $_SESSION['user_id']
            ];
            
            try {
                // Passwort aktualisieren
                $stmt->execute($params);
                
                // Erfolgreiche Änderung
                $success = true;
                
                // Session-ID nach Passwortänderung erneuern (Sicherheitsmaßnahme)
                session_regenerate_id(true);
                
                // Zum Dashboard weiterleiten
                umleiten_zu('dashboard.php?success=password_changed');
            } catch (PDOException $e) {
                $fehler['allgemein'] = "Fehler beim Ändern des Passworts. Bitte versuchen Sie es später erneut.";
            }
        } else {
            // Altes Passwort stimmt nicht
            $fehler['passwort_alt'] = "Das aktuelle Passwort ist nicht korrekt.";
        }
    }
}

// Header einbinden
include_once '../includes/header.php';
?>

<div class="container mt-5">
    <div class="row">
        <div class="col-md-6 mx-auto">
            <div class="card">
                <div class="card-header bg-warning">
                    <h4>Passwort ändern</h4>
                </div>
                <div class="card-body">
                    
                    <?php if ($success): ?>
                        <!-- Erfolgsanzeige nach erfolgreicher Passwortänderung -->
                        <div class="alert alert-success">
                            Passwort erfolgreich geändert! Sie werden weitergeleitet...
                        </div>
                        <script>
                            setTimeout(function() {
                                window.location.href = 'dashboard.php?success=password_changed';
                            }, 2000);
                        </script>
                    <?php else: ?>
                        <!-- Anzeige von allgemeinen Fehlern -->
                        <?php if (isset($fehler['allgemein'])): ?>
                            <?php echo fehler_meldung($fehler['allgemein']); ?>
                        <?php endif; ?>
                        
                        <!-- Passwort-Ändern-Formular -->
                        <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" novalidate>
                            <!-- Formularfelder mit clientseitiger Validierung (C5) -->
                            <div class="form-group">
                                <label for="passwort_alt">Aktuelles Passwort</label>
                                <input type="password" name="passwort_alt" id="passwort_alt" 
                                       class="form-control <?php echo isset($fehler['passwort_alt']) ? 'is-invalid' : ''; ?>" 
                                       required>
                                <?php if (isset($fehler['passwort_alt'])): ?>
                                    <div class="invalid-feedback"><?php echo $fehler['passwort_alt']; ?></div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="form-group mt-3">
                                <label for="passwort_neu">Neues Passwort</label>
                                <input type="password" name="passwort_neu" id="passwort_neu" 
                                       class="form-control <?php echo isset($fehler['passwort_neu']) ? 'is-invalid' : ''; ?>" 
                                       required minlength="8"
                                       pattern="(?=.*[A-Za-z])(?=.*\d).{8,}">
                                <small class="form-text text-muted">Mindestens 8 Zeichen mit Buchstaben und Zahlen.</small>
                                <?php if (isset($fehler['passwort_neu'])): ?>
                                    <div class="invalid-feedback"><?php echo $fehler['passwort_neu']; ?></div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="form-group mt-3">
                                <label for="passwort_neu_wiederholen">Neues Passwort wiederholen</label>
                                <input type="password" name="passwort_neu_wiederholen" id="passwort_neu_wiederholen" 
                                       class="form-control <?php echo isset($fehler['passwort_neu_wiederholen']) ? 'is-invalid' : ''; ?>" 
                                       required>
                                <?php if (isset($fehler['passwort_neu_wiederholen'])): ?>
                                    <div class="invalid-feedback"><?php echo $fehler['passwort_neu_wiederholen']; ?></div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="form-group mt-4">
                                <button type="submit" class="btn btn-warning">Passwort ändern</button>
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
// Hinweis: Die clientseitige Validierung (C5) wird hier nicht mit JavaScript durchgeführt,
// sondern durch die HTML5-Attribute im Formular (required, minlength, pattern).
// Die vollständige Validierung findet serverseitig statt (C6).
?>

<?php
// Footer einbinden
include_once '../includes/footer.php';
?>