<?php
/**
 * Admin-Benutzer bearbeiten
 * Nur Administratoren haben Zugriff auf diese Seite
 */

// Einbinden der benötigten Dateien
require_once '../config/database.php';
require_once '../includes/session.php';
require_once '../includes/functions.php';
require_once '../includes/validation.php';

// Zugriffsschutz: Nur für angemeldete Benutzer mit Admin-Rechten
nur_angemeldet_zugriff();
nur_admin_zugriff();

// Benutzer-ID aus der URL abrufen
$benutzer_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Array für Fehlermeldungen
$fehler = [];
$success = false;

// Benutzer aus der Datenbank abrufen
$stmt = $pdo->prepare("
    SELECT id, vorname, nachname, email, profilbild, is_admin 
    FROM benutzer 
    WHERE id = :id 
    LIMIT 1
");
$stmt->execute(['id' => $benutzer_id]);
$benutzer = $stmt->fetch();

// Wenn kein Benutzer gefunden wurde, zum Admin-Dashboard umleiten
if (!$benutzer) {
    umleiten_zu('admin_dashboard.php?error=user_not_found');
}

// Verarbeitung des Formulars bei POST-Anfrage
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validierungsregeln für das Benutzer-Bearbeiten-Formular
    $regeln = [
        'vorname' => ['required' => true, 'min_length' => 2, 'max_length' => 50],
        'nachname' => ['required' => true, 'min_length' => 2, 'max_length' => 50],
        'email' => ['required' => true, 'email' => true]
    ];
    
    // Serverseitige Validierung durchführen
    $fehler = validiere_formular($_POST, $regeln);
    
    // Prüfen, ob die E-Mail-Adresse bereits von einem anderen Benutzer verwendet wird
    if (empty($fehler['email'])) {
        $stmt = $pdo->prepare("
            SELECT id FROM benutzer 
            WHERE email = :email AND id != :id
        ");
        $stmt->execute([
            'email' => $_POST['email'],
            'id' => $benutzer_id
        ]);
        
        if ($stmt->rowCount() > 0) {
            $fehler['email'] = "Diese E-Mail-Adresse wird bereits von einem anderen Benutzer verwendet.";
        }
    }
    
    // Wenn keine Fehler vorhanden sind, Benutzer aktualisieren
    if (empty($fehler)) {
        // Eingaben bereinigen
        $vorname = bereinige_eingabe($_POST['vorname']);
        $nachname = bereinige_eingabe($_POST['nachname']);
        $email = bereinige_eingabe($_POST['email']);
        
        // Admin-Status (0 oder 1)
        $is_admin = isset($_POST['is_admin']) ? 1 : 0;
        
        // Benutzer in der Datenbank aktualisieren
        $stmt = $pdo->prepare("
            UPDATE benutzer 
            SET vorname = :vorname, nachname = :nachname, email = :email, is_admin = :is_admin 
            WHERE id = :id
        ");
        
        // Parameter für die Anfrage
        $params = [
            'vorname' => $vorname,
            'nachname' => $nachname,
            'email' => $email,
            'is_admin' => $is_admin,
            'id' => $benutzer_id
        ];
        
        try {
            // Benutzer aktualisieren
            $stmt->execute($params);
            
            // Passwort zurücksetzen, falls gewünscht
            if (!empty($_POST['passwort_reset']) && isset($_POST['neues_passwort']) && !empty($_POST['neues_passwort'])) {
                // Neues Passwort validieren
                if (ist_gueltiges_passwort($_POST['neues_passwort'])) {
                    // Passwort hashen und in der Datenbank aktualisieren
                    $passwort_hash = passwort_hash_erstellen($_POST['neues_passwort']);
                    
                    $stmt = $pdo->prepare("UPDATE benutzer SET passwort = :passwort WHERE id = :id");
                    $stmt->execute([
                        'passwort' => $passwort_hash,
                        'id' => $benutzer_id
                    ]);
                } else {
                    $fehler['neues_passwort'] = "Das neue Passwort muss mindestens 8 Zeichen lang sein und Buchstaben sowie Zahlen enthalten.";
                }
            }
            
            // Erfolgreiche Aktualisierung
            if (empty($fehler)) {
                $success = true;
                
                // Wenn der Admin seinen eigenen Account bearbeitet, Session-Daten aktualisieren
                if ($benutzer_id == $_SESSION['user_id']) {
                    $_SESSION['user_name'] = $vorname . ' ' . $nachname;
                    $_SESSION['user_email'] = $email;
                }
            }
        } catch (PDOException $e) {
            $fehler['allgemein'] = "Fehler beim Aktualisieren des Benutzers. Bitte versuchen Sie es später erneut.";
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
                    <h4>Benutzer bearbeiten</h4>
                </div>
                <div class="card-body">
                    
                    <?php if ($success): ?>
                        <!-- Erfolgsanzeige nach erfolgreicher Aktualisierung -->
                        <div class="alert alert-success">
                            Benutzer erfolgreich aktualisiert! <a href="admin_dashboard.php?success=user_updated" class="alert-link">Zurück zum Admin-Dashboard</a>
                        </div>
                    <?php else: ?>
                        <!-- Anzeige von allgemeinen Fehlern -->
                        <?php if (isset($fehler['allgemein'])): ?>
                            <?php echo fehler_meldung($fehler['allgemein']); ?>
                        <?php endif; ?>
                        
                        <!-- Benutzer-Bearbeitungs-Formular -->
                        <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'] . '?id=' . $benutzer_id); ?>" novalidate>
                            <div class="form-group">
                                <label for="vorname">Vorname</label>
                                <input type="text" name="vorname" id="vorname" 
                                       class="form-control <?php echo isset($fehler['vorname']) ? 'is-invalid' : ''; ?>" 
                                       value="<?php echo isset($_POST['vorname']) ? htmlspecialchars($_POST['vorname']) : htmlspecialchars($benutzer['vorname']); ?>" 
                                       required minlength="2" maxlength="50">
                                <?php if (isset($fehler['vorname'])): ?>
                                    <div class="invalid-feedback"><?php echo $fehler['vorname']; ?></div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="form-group mt-3">
                                <label for="nachname">Nachname</label>
                                <input type="text" name="nachname" id="nachname" 
                                       class="form-control <?php echo isset($fehler['nachname']) ? 'is-invalid' : ''; ?>" 
                                       value="<?php echo isset($_POST['nachname']) ? htmlspecialchars($_POST['nachname']) : htmlspecialchars($benutzer['nachname']); ?>" 
                                       required minlength="2" maxlength="50">
                                <?php if (isset($fehler['nachname'])): ?>
                                    <div class="invalid-feedback"><?php echo $fehler['nachname']; ?></div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="form-group mt-3">
                                <label for="email">E-Mail</label>
                                <input type="email" name="email" id="email" 
                                       class="form-control <?php echo isset($fehler['email']) ? 'is-invalid' : ''; ?>" 
                                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : htmlspecialchars($benutzer['email']); ?>" 
                                       required>
                                <?php if (isset($fehler['email'])): ?>
                                    <div class="invalid-feedback"><?php echo $fehler['email']; ?></div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="form-check mt-3">
                                <input type="checkbox" name="is_admin" id="is_admin" class="form-check-input" 
                                       <?php echo (isset($_POST['is_admin']) || $benutzer['is_admin']) ? 'checked' : ''; ?>>
                                <label for="is_admin" class="form-check-label">Administrator-Rechte</label>
                            </div>
                            
                            <hr>
                            
                            <h5>Passwort zurücksetzen (optional)</h5>
                            
                            <div class="form-check mb-3">
                                <input type="checkbox" name="passwort_reset" id="passwort_reset" class="form-check-input">
                                <label for="passwort_reset" class="form-check-label">Passwort zurücksetzen</label>
                            </div>
                            
                            <div class="form-group">
                                <label for="neues_passwort">Neues Passwort</label>
                                <input type="password" name="neues_passwort" id="neues_passwort" 
                                       class="form-control <?php echo isset($fehler['neues_passwort']) ? 'is-invalid' : ''; ?>" 
                                       minlength="8" pattern="(?=.*[A-Za-z])(?=.*\d).{8,}">
                                <small class="form-text text-muted">Mindestens 8 Zeichen mit Buchstaben und Zahlen.</small>
                                <?php if (isset($fehler['neues_passwort'])): ?>
                                    <div class="invalid-feedback"><?php echo $fehler['neues_passwort']; ?></div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="form-group mt-4">
                                <button type="submit" class="btn btn-primary">Benutzer aktualisieren</button>
                                <a href="admin_dashboard.php" class="btn btn-secondary ml-2">Zurück zum Admin-Dashboard</a>
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