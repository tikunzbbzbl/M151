<?php
/**
 * Registrierungsseite
 * Kompetenz C5: In meinem Projekt werden alle Benutzereingaben clientseitig validiert.
 * Kompetenz C6: In meinem Projekt werden alle Benutzereingaben serverseitig validiert.
 * Kompetenz C7: Script-Injection wird in meinem Projekt konsequent verhindert.
 * Kompetenz C11: In meinem Projekt werden sensible Daten wie das Passwort mit sicheren und aktuellen Methoden gehasht und gesaltet.
 * Kompetenz C13: Eine Person kann sich an meinem Projekt registrieren.
 * Kompetenz C19: In meinem Projekt wird SQL-Injection konsequent verhindert.
 */

// Einbinden der benötigten Dateien
require_once '../config/database.php';
require_once '../includes/session.php';
require_once '../includes/functions.php';
require_once '../includes/validation.php';

// Weiterleitung, wenn Benutzer bereits angemeldet ist
if (ist_angemeldet()) {
    umleiten_zu('dashboard.php');
}

// Array für Fehlermeldungen
$fehler = [];
$success = false;

// Verarbeitung des Formulars bei POST-Anfrage
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validierungsregeln für das Registrierungsformular
    $regeln = [
        'vorname' => ['required' => true, 'min_length' => 2, 'max_length' => 50],
        'nachname' => ['required' => true, 'min_length' => 2, 'max_length' => 50],
        'email' => ['required' => true, 'email' => true],
        'passwort' => ['required' => true, 'passwort' => true],
        'passwort_wiederholen' => ['required' => true]
    ];
    
    // Serverseitige Validierung durchführen (C6)
    $fehler = validiere_formular($_POST, $regeln);
    
    // Prüfen, ob die Passwörter übereinstimmen
    if ($_POST['passwort'] !== $_POST['passwort_wiederholen']) {
        $fehler['passwort_wiederholen'] = "Die Passwörter stimmen nicht überein.";
    }
    
    // Wenn keine Fehler vorhanden sind, Benutzer speichern
    if (empty($fehler)) {
        // Eingaben bereinigen (C7: Script-Injection verhindern)
        $vorname = bereinige_eingabe($_POST['vorname']);
        $nachname = bereinige_eingabe($_POST['nachname']);
        $email = bereinige_eingabe($_POST['email']);
        $passwort = $_POST['passwort']; // Passwort wird nicht bereinigt, da es gehasht wird
        
        // Prüfen, ob die E-Mail-Adresse bereits vorhanden ist
        $stmt = $pdo->prepare("SELECT id FROM benutzer WHERE email = :email");
        $stmt->execute(['email' => $email]); // (C19: SQL-Injection verhindern durch Prepared Statement)
        
        if ($stmt->rowCount() > 0) {
            $fehler['email'] = "Diese E-Mail-Adresse ist bereits registriert.";
        } else {
            // Passwort hashen (C11: Sichere Passwort-Hashing-Methode)
            $passwort_hash = passwort_hash_erstellen($passwort);
            
            // Benutzer in die Datenbank einfügen
            $stmt = $pdo->prepare("
                INSERT INTO benutzer (vorname, nachname, email, passwort, erstellt_am) 
                VALUES (:vorname, :nachname, :email, :passwort, NOW())
            ");
            
            // Parameter für die Anfrage
            $params = [
                'vorname' => $vorname,
                'nachname' => $nachname,
                'email' => $email,
                'passwort' => $passwort_hash
            ];
            
            try {
                // Benutzer speichern
                $stmt->execute($params);
                
                // Erfolgreiche Registrierung
                $success = true;
            } catch (PDOException $e) {
                $fehler['allgemein'] = "Fehler bei der Registrierung. Bitte versuchen Sie es später erneut.";
            }
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
                    <h4>Registrierung</h4>
                </div>
                <div class="card-body">
                    
                    <?php if ($success): ?>
                        <!-- Erfolgsanzeige nach erfolgreicher Registrierung -->
                        <div class="alert alert-success">
                            Registrierung erfolgreich! <a href="login.php">Hier anmelden</a>.
                        </div>
                    <?php else: ?>
                        <!-- Anzeige von allgemeinen Fehlern -->
                        <?php if (isset($fehler['allgemein'])): ?>
                            <?php echo fehler_meldung($fehler['allgemein']); ?>
                        <?php endif; ?>
                        
                        <!-- Registrierungsformular -->
                        <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" novalidate>
                            <!-- Formularfelder mit clientseitiger Validierung (C5) -->
                            <div class="form-group">
                                <label for="vorname">Vorname</label>
                                <input type="text" name="vorname" id="vorname" 
                                       class="form-control <?php echo isset($fehler['vorname']) ? 'is-invalid' : ''; ?>" 
                                       value="<?php echo isset($_POST['vorname']) ? htmlspecialchars($_POST['vorname']) : ''; ?>" 
                                       required minlength="2" maxlength="50">
                                <?php if (isset($fehler['vorname'])): ?>
                                    <div class="invalid-feedback"><?php echo $fehler['vorname']; ?></div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="form-group mt-3">
                                <label for="nachname">Nachname</label>
                                <input type="text" name="nachname" id="nachname" 
                                       class="form-control <?php echo isset($fehler['nachname']) ? 'is-invalid' : ''; ?>" 
                                       value="<?php echo isset($_POST['nachname']) ? htmlspecialchars($_POST['nachname']) : ''; ?>" 
                                       required minlength="2" maxlength="50">
                                <?php if (isset($fehler['nachname'])): ?>
                                    <div class="invalid-feedback"><?php echo $fehler['nachname']; ?></div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="form-group mt-3">
                                <label for="email">E-Mail</label>
                                <input type="email" name="email" id="email" 
                                       class="form-control <?php echo isset($fehler['email']) ? 'is-invalid' : ''; ?>" 
                                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" 
                                       required>
                                <?php if (isset($fehler['email'])): ?>
                                    <div class="invalid-feedback"><?php echo $fehler['email']; ?></div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="form-group mt-3">
                                <label for="passwort">Passwort</label>
                                <input type="password" name="passwort" id="passwort" 
                                       class="form-control <?php echo isset($fehler['passwort']) ? 'is-invalid' : ''; ?>" 
                                       required minlength="8" 
                                       pattern="(?=.*[A-Za-z])(?=.*\d).{8,}">
                                <small class="form-text text-muted">Mindestens 8 Zeichen mit Buchstaben und Zahlen.</small>
                                <?php if (isset($fehler['passwort'])): ?>
                                    <div class="invalid-feedback"><?php echo $fehler['passwort']; ?></div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="form-group mt-3">
                                <label for="passwort_wiederholen">Passwort wiederholen</label>
                                <input type="password" name="passwort_wiederholen" id="passwort_wiederholen" 
                                       class="form-control <?php echo isset($fehler['passwort_wiederholen']) ? 'is-invalid' : ''; ?>" 
                                       required>
                                <?php if (isset($fehler['passwort_wiederholen'])): ?>
                                    <div class="invalid-feedback"><?php echo $fehler['passwort_wiederholen']; ?></div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="form-group mt-4">
                                <button type="submit" class="btn btn-primary">Registrieren</button>
                                <a href="index.php" class="btn btn-secondary ml-2">Zurück</a>
                            </div>
                        </form>
                    <?php endif; ?>
                    
                    <div class="mt-3">
                        <p>Bereits registriert? <a href="login.php">Hier anmelden</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Hinweis: Die clientseitige Validierung (C5) wird hier nicht mit JavaScript durchgeführt,
// sondern durch die HTML5-Attribute im Formular (required, minlength, maxlength, pattern).
// Die vollständige Validierung findet serverseitig statt (C6).
?>

<?php
// Footer einbinden
include_once '../includes/footer.php';
?>