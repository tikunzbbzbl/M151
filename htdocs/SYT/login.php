<?php
// login.php

// Binde die Datei ein, die die Datenbankverbindung herstellt.
// Dadurch können wir SQL-Abfragen ausführen, um Benutzerinformationen abzurufen.
require_once 'includes/db.php';

// Binde die Datei mit gemeinsamen Hilfsfunktionen ein.
// Diese Funktionen beinhalten unter anderem "secure_session_start()" zum sicheren Starten der Session
// und "escape()" zum sicheren Ausgeben von Daten (Schutz vor XSS).
require_once 'includes/functions.php';

// Starte eine sichere Session, damit wir auf Session-Daten (z. B. den Login-Status) zugreifen können.
secure_session_start();

// Initialisiere ein Array, in dem Fehlernachrichten gespeichert werden.
$errors = [];

// Prüfe, ob das Formular per POST übermittelt wurde.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Hole die Benutzereingaben für "username" und "password" aus dem POST-Array.
    // Verwende trim(), um überflüssige Leerzeichen am Anfang und Ende zu entfernen.
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    
    // Überprüfe, ob einer der Felder leer ist.
    // Wenn ja, füge eine Fehlermeldung zum $errors-Array hinzu.
    if (empty($username) || empty($password)) {
        $errors[] = "Bitte alle Felder ausfüllen.";
    }
    
    // Falls keine Fehler aufgetreten sind, fahre mit der Überprüfung des Logins fort.
    if (empty($errors)) {
        // Bereite eine SQL-Abfrage vor, um den Benutzer anhand des eingegebenen Benutzernamens aus der Datenbank abzurufen.
        $stmt = $pdo->prepare("SELECT id, username, password, is_admin FROM users WHERE username = :username");
        // Führe die Abfrage aus und ersetze den Platzhalter :username durch den eingegebenen Benutzernamen.
        $stmt->execute([':username' => $username]);
        // Hole den Datensatz des Benutzers. Falls kein Benutzer gefunden wird, ist $user false.
        $user = $stmt->fetch();
        
        // Überprüfe, ob ein Benutzer gefunden wurde und ob das eingegebene Passwort mit dem in der Datenbank gehashten Passwort übereinstimmt.
        if ($user && password_verify($password, $user['password'])) {
            // Wenn die Authentifizierung erfolgreich ist:
            // Regeneriere die Session-ID, um Session-Fixation zu verhindern.
            session_regenerate_id(true);
            
            // Speichere wichtige Benutzerdaten in der Session, damit sie auf anderen Seiten verfügbar sind.
            $_SESSION['user_id']  = $user['id'];
            $_SESSION['username'] = $user['username'];
            // Setze den Admin-Status in der Session als booleschen Wert (true, wenn is_admin gleich 1 ist).
            $_SESSION['is_admin'] = ($user['is_admin'] == 1);
            
            // Leite den Benutzer zur Startseite (index.php) weiter.
            header("Location: index.php");
            exit;
        } else {
            // Falls die Authentifizierung fehlschlägt, füge eine Fehlermeldung hinzu.
            $errors[] = "Ungültiger Benutzername oder Passwort.";
        }
    }
}
?>
<?php include 'includes/header.php'; ?>
<div class="container mt-5">
  <!-- Überschrift der Seite -->
  <h1>Anmelden</h1>
  <!-- Falls es Fehler gibt, werden diese in einer roten Bootstrap-Alert-Box angezeigt -->
  <?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
      <?php foreach ($errors as $error): ?>
        <p><?php echo escape($error); ?></p>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
  <!-- Formular zur Eingabe des Benutzernamens und Passworts -->
  <form action="login.php" method="post">
    <div class="form-group">
      <label for="username">Benutzername:</label>
      <!-- Textfeld für den Benutzernamen -->
      <input type="text" name="username" id="username" class="form-control" required>
    </div>
    <div class="form-group">
      <label for="password">Passwort:</label>
      <!-- Passwortfeld für das Passwort -->
      <input type="password" name="password" id="password" class="form-control" required>
    </div>
    <!-- Sende-Button -->
    <button type="submit" class="btn btn-primary">Anmelden</button>
  </form>
</div>
<?php include 'includes/footer.php'; ?>
