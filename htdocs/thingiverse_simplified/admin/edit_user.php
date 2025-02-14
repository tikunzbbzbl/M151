<?php
// admin/edit_user.php

// Binde die Datei für die Datenbankverbindung ein,
// damit wir später SQL-Abfragen ausführen können.
require_once '../includes/db.php';

// Binde die Datei mit gemeinsamen Hilfsfunktionen ein.
// Diese enthält Funktionen wie "secure_session_start()" und "escape()".
require_once '../includes/functions.php';

// Starte eine sichere Session, damit wir auf Session-Daten (wie Login-Status)
// zugreifen können.
secure_session_start();

// Überprüfe, ob der aktuelle Benutzer eingeloggt ist und Admin-Rechte besitzt.
// Falls der Benutzer nicht eingeloggt ist oder nicht als Admin markiert ist,
// wird er zur Login-Seite weitergeleitet und das Skript beendet.
if (!is_logged_in() || empty($_SESSION['is_admin'])) {
    header("Location: ../login.php");
    exit;
}

// Überprüfe, ob eine Benutzer-ID (über GET-Parameter "id") übergeben wurde und ob sie numerisch ist.
// Diese ID gibt an, welchen Benutzer der Admin bearbeiten möchte.
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Ungültige Anfrage. Keine Benutzer-ID übergeben.");
}

// Konvertiere den GET-Parameter in einen Integer und speichere ihn in $user_id.
$user_id = (int)$_GET['id'];

// Bereite eine SQL-Abfrage vor, um die Daten des Benutzers mit der angegebenen ID aus der Tabelle "users" abzurufen.
$stmt = $pdo->prepare("SELECT id, username, email, is_admin FROM users WHERE id = :id");
// Führe die Abfrage aus, wobei ":id" durch den Wert von $user_id ersetzt wird.
$stmt->execute([':id' => $user_id]);
// Speichere den abgerufenen Datensatz in der Variable $user.
$user = $stmt->fetch();

// Wenn kein Benutzer mit dieser ID gefunden wurde, beende das Skript mit einer Fehlermeldung.
if (!$user) {
    die("Benutzer nicht gefunden.");
}

// Initialisiere leere Arrays/Variablen für Fehlermeldungen und Erfolgsmeldungen.
$errors = [];
$success = '';

// Prüfe, ob das Formular per POST abgeschickt wurde.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Hole die neuen Werte für Benutzername und E-Mail aus dem Formular und entferne
    // dabei unnötige Leerzeichen.
    $username    = trim($_POST['username'] ?? '');
    $email       = trim($_POST['email'] ?? '');
    // Falls das Kontrollkästchen "is_admin" gesetzt wurde, wird $is_admin zu 1, sonst zu 0.
    $is_admin    = isset($_POST['is_admin']) ? 1 : 0;
    // Hole das neue Passwort aus dem Formular (optional). Wird es nicht ausgefüllt, bleibt es leer.
    $newPassword = trim($_POST['password'] ?? '');
    
    // Überprüfe, ob der neue Benutzername leer ist.
    if (empty($username)) {
        $errors[] = "Der Benutzername darf nicht leer sein.";
    }
    // Überprüfe, ob die E-Mail leer ist.
    if (empty($email)) {
        $errors[] = "Die E-Mail darf nicht leer sein.";
    }
    
    // Falls es keine Fehler gibt, fahre mit der Aktualisierung fort.
    if (empty($errors)) {
        // Prüfe, ob ein neues Passwort eingegeben wurde.
        if (!empty($newPassword)) {
            // Hash das neue Passwort, bevor es in die Datenbank geschrieben wird.
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            // Bereite eine SQL-Abfrage vor, um den Benutzerdatensatz mit den neuen Werten zu aktualisieren,
            // inklusive des neuen Passwortes.
            $stmtUpdate = $pdo->prepare("
                UPDATE users
                SET username = :username,
                    email    = :email,
                    is_admin = :is_admin,
                    password = :password
                WHERE id = :id
            ");
            // Führe die Abfrage aus und ersetze die Platzhalter durch die tatsächlichen Werte.
            $stmtUpdate->execute([
                ':username' => $username,
                ':email'    => $email,
                ':is_admin' => $is_admin,
                ':password' => $hashedPassword,
                ':id'       => $user_id
            ]);
        } else {
            // Falls kein neues Passwort eingegeben wurde, aktualisiere nur Benutzername, E-Mail und den Admin-Status.
            $stmtUpdate = $pdo->prepare("
                UPDATE users
                SET username = :username,
                    email    = :email,
                    is_admin = :is_admin
                WHERE id = :id
            ");
            $stmtUpdate->execute([
                ':username' => $username,
                ':email'    => $email,
                ':is_admin' => $is_admin,
                ':id'       => $user_id
            ]);
        }
        
        // Setze eine Erfolgsmeldung, die anzeigt, dass die Aktualisierung erfolgreich war.
        $success = "Benutzerdaten erfolgreich aktualisiert.";
        
        // Lade die aktualisierten Benutzerdaten neu aus der Datenbank, damit die Änderungen im Formular sichtbar sind.
        $stmt = $pdo->prepare("SELECT id, username, email, is_admin FROM users WHERE id = :id");
        $stmt->execute([':id' => $user_id]);
        $user = $stmt->fetch();
    }
}
?>
<?php include '../includes/header.php'; ?>
<div class="container mt-5">
    <h1>Benutzer bearbeiten</h1>
    <!-- Wenn Fehler vorhanden sind, werden diese in einer roten Alert-Box angezeigt -->
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <?php foreach ($errors as $error): ?>
                <p><?php echo escape($error); ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <!-- Wenn eine Erfolgsmeldung vorhanden ist, wird diese in einer grünen Alert-Box angezeigt -->
    <?php if (!empty($success)): ?>
        <div class="alert alert-success">
            <p><?php echo escape($success); ?></p>
        </div>
    <?php endif; ?>
    
    <!-- Formular zum Bearbeiten des Benutzers -->
    <!-- Das Formular sendet die Daten per POST an diese gleiche Datei, wobei die Benutzer-ID als GET-Parameter übergeben wird -->
    <form action="edit_user.php?id=<?php echo $user_id; ?>" method="post">
        <div class="form-group">
            <label for="username">Benutzername:</label>
            <!-- Das Eingabefeld wird mit dem aktuellen Benutzernamen vorausgefüllt -->
            <input type="text" name="username" id="username" class="form-control"
                   value="<?php echo escape($user['username']); ?>" required>
        </div>
        <div class="form-group">
            <label for="email">E-Mail:</label>
            <!-- Das Eingabefeld wird mit der aktuellen E-Mail vorausgefüllt -->
            <input type="email" name="email" id="email" class="form-control"
                   value="<?php echo escape($user['email']); ?>" required>
        </div>
        <div class="form-group form-check">
            <!-- Checkbox, um anzugeben, ob der Benutzer Admin-Rechte haben soll -->
            <input type="checkbox" name="is_admin" id="is_admin" class="form-check-input"
                   <?php echo $user['is_admin'] ? 'checked' : ''; ?>>
            <label for="is_admin" class="form-check-label">Admin?</label>
        </div>
        <div class="form-group">
            <label for="password">Neues Passwort (optional):</label>
            <!-- Passwortfeld: Wird leer gelassen, wenn das Passwort nicht geändert werden soll -->
            <input type="password" name="password" id="password" class="form-control"
                   placeholder="Leer lassen, um das Passwort nicht zu ändern">
        </div>
        <button type="submit" class="btn btn-primary">Speichern</button>
        <!-- Link zurück zur Benutzerverwaltung -->
        <a href="manage_users.php" class="btn btn-secondary">Abbrechen</a>
    </form>
</div>
<?php include '../includes/footer.php'; ?>
