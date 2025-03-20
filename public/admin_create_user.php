<?php
/**
 * Admin-Benutzer erstellen
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

// Array für Fehlermeldungen
$fehler = [];
$success = false;

// Verarbeitung des Formulars bei POST-Anfrage
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validierungsregeln für das Benutzer-Erstellen-Formular
    $regeln = [
        'vorname' => ['required' => true, 'min_length' => 2, 'max_length' => 50],
        'nachname' => ['required' => true, 'min_length' => 2, 'max_length' => 50],
        'email' => ['required' => true, 'email' => true],
        'passwort' => ['required' => true, 'passwort' => true]
    ];
    
    // Serverseitige Validierung durchführen
    $fehler = validiere_formular($_POST, $regeln);
    
    // Prüfen, ob die E-Mail-Adresse bereits existiert
    if (empty($fehler['email'])) {
        $stmt = $pdo->prepare("SELECT id FROM benutzer WHERE email = :email");
        $stmt->execute(['email' => $_POST['email']]);
        
        if ($stmt->rowCount() > 0) {
            $fehler['email'] = "Diese E-Mail-Adresse ist bereits registriert.";
        }
    }
    
    // Wenn keine Fehler vorhanden sind, Benutzer erstellen
    if (empty($fehler)) {
        // Eingaben bereinigen
        $vorname = bereinige_eingabe($_POST['vorname']);
        $nachname = bereinige_eingabe($_POST['nachname']);
        $email = bereinige_eingabe($_POST['email']);
        $passwort = $_POST['passwort']; // Passwort wird nicht bereinigt, da es gehasht wird
        
        // Admin-Status (0 oder 1)
        $is_admin = isset($_POST['is_admin']) ? 1 : 0;
        
        // Passwort hashen
        $passwort_hash = passwort_hash_erstellen($passwort);
        
        // Benutzer in die Datenbank einfügen
        $stmt = $pdo->prepare("
            INSERT INTO benutzer (vorname, nachname, email, passwort, is_admin, erstellt_am) 
            VALUES (:vorname, :nachname, :email, :passwort, :is_admin, NOW())
        ");
        
        // Parameter für die Anfrage
        $params = [
            'vorname' => $vorname,
            'nachname' => $nachname,
            'email' => $email,
            'passwort' => $passwort_hash,
            'is_admin' => $is_admin
        ];
        
        try {
            // Benutzer speichern
            $stmt->execute($params);
            
            // Erfolgreiche Erstellung
            $success = true;
        } catch (PDOException $e) {
            $fehler['allgemein'] = "Fehler bei der Erstellung des Benutzers. Bitte versuchen Sie es später erneut.";
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
                    <h4>Neuen Benutzer erstellen</h4>
                </div>
                <div class="card-body">
                    
                    <?php if ($success): ?>
                        <!-- Erfolgsanzeige nach erfolgreicher Erstellung -->
                        <div class="alert alert-success">
                            Benutzer erfolgreich erstellt! <a href="admin_dashboard.php?success=user_created" class="alert-link">Zurück zum Admin-Dashboard</a>
                        </div>
                    <?php else: ?>
                        <!-- Anzeige von allgemeinen Fehlern -->
                        <?php if (isset($fehler['allgemein'])): ?>
                            <?php echo fehler_meldung($fehler['allgemein']); ?>
                        <?php endif; ?>
                        
                        <!-- Benutzer-Erstellungs-Formular -->
                        <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" novalidate>
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
                                       required minlength="8" pattern="(?=.*[A-Za-z])(?=.*\d).{8,}">
                                <small class="form-text text-muted">Mindestens 8 Zeichen mit Buchstaben und Zahlen.</small>
                                <?php if (isset($fehler['passwort'])): ?>
                                    <div class="invalid-feedback"><?php echo $fehler['passwort']; ?></div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="form-check mt-3">
                                <input type="checkbox" name="is_admin" id="is_admin" class="form-check-input" 
                                       <?php echo isset($_POST['is_admin']) ? 'checked' : ''; ?>>
                                <label for="is_admin" class="form-check-label">Administrator-Rechte</label>
                            </div>
                            
                            <div class="form-group mt-4">
                                <button type="submit" class="btn btn-success">Benutzer erstellen</button>
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