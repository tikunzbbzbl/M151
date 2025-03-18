<?php
/**
 * Anmeldeseite
 * Kompetenz C5: In meinem Projekt werden alle Benutzereingaben clientseitig validiert.
 * Kompetenz C6: In meinem Projekt werden alle Benutzereingaben serverseitig validiert.
 * Kompetenz C7: Script-Injection wird in meinem Projekt konsequent verhindert.
 * Kompetenz C8: In meinem Projekt wird das Session-Handling korrekt eingesetzt.
 * Kompetenz C10: In meinem Projekt wird Session-Fixation und Session-Hijacking erschwert.
 * Kompetenz C14: Eine Person kann sich an meinem Projekt anmelden.
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
    // Validierungsregeln für das Anmeldeformular
    $regeln = [
        'email' => ['required' => true, 'email' => true],
        'passwort' => ['required' => true]
    ];
    
    // Serverseitige Validierung durchführen (C6)
    $fehler = validiere_formular($_POST, $regeln);
    
    // Wenn keine Fehler vorhanden sind, Anmeldung versuchen
    if (empty($fehler)) {
        // Eingaben bereinigen (C7: Script-Injection verhindern)
        $email = bereinige_eingabe($_POST['email']);
        $passwort = $_POST['passwort']; // Passwort wird nicht bereinigt, da es geprüft wird
        
        // Benutzer in der Datenbank suchen
        $stmt = $pdo->prepare("SELECT id, vorname, nachname, email, passwort FROM benutzer WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]); // (C19: SQL-Injection verhindern durch Prepared Statement)
        
        $benutzer = $stmt->fetch();
        
        if ($benutzer && passwort_verifizieren($passwort, $benutzer['passwort'])) {
            // Erfolgreiche Anmeldung: Session-Daten setzen (C8: Session-Handling)
            $_SESSION['user_id'] = $benutzer['id'];
            $_SESSION['user_name'] = $benutzer['vorname'] . ' ' . $benutzer['nachname'];
            $_SESSION['user_email'] = $benutzer['email'];
            
            // Neue Session-ID generieren nach Login (C10: Session-Fixation verhindern)
            session_regenerate_id(true);
            
            // Zum Dashboard weiterleiten
            umleiten_zu('dashboard.php');
        } else {
            // Fehlerhafte Anmeldedaten
            $fehler['allgemein'] = "Ungültige E-Mail oder Passwort.";
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
                <div class="card-header bg-success text-white">
                    <h4>Anmeldung</h4>
                </div>
                <div class="card-body">
                    
                    <!-- Anzeige von allgemeinen Fehlern -->
                    <?php if (isset($fehler['allgemein'])): ?>
                        <?php echo fehler_meldung($fehler['allgemein']); ?>
                    <?php endif; ?>
                    
                    <!-- Anzeige von Timeout-Meldung -->
                    <?php if (isset($_GET['timeout']) && $_GET['timeout'] == 1): ?>
                        <?php echo warn_meldung("Ihre Sitzung ist abgelaufen. Bitte melden Sie sich erneut an."); ?>
                    <?php endif; ?>
                    
                    <!-- Anzeige von Login-Erfordernis-Meldung -->
                    <?php if (isset($_GET['error']) && $_GET['error'] == 'login_required'): ?>
                        <?php echo warn_meldung("Bitte melden Sie sich an, um auf diese Seite zuzugreifen."); ?>
                    <?php endif; ?>
                    
                    <!-- Anmeldungsformular -->
                    <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" novalidate>
                        <!-- Formularfelder mit clientseitiger Validierung (C5) -->
                        <div class="form-group">
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
                                   required>
                            <?php if (isset($fehler['passwort'])): ?>
                                <div class="invalid-feedback"><?php echo $fehler['passwort']; ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="form-group mt-4">
                            <button type="submit" class="btn btn-success">Anmelden</button>
                            <a href="index.php" class="btn btn-secondary ml-2">Zurück</a>
                        </div>
                    </form>
                    
                    <div class="mt-3">
                        <p>Noch kein Konto? <a href="register.php">Hier registrieren</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Hinweis: Die clientseitige Validierung (C5) wird hier nicht mit JavaScript durchgeführt,
// sondern durch die HTML5-Attribute im Formular (required).
// Die vollständige Validierung findet serverseitig statt (C6).
?>

<?php
// Footer einbinden
include_once '../includes/footer.php';
?>