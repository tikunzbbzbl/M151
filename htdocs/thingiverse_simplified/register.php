<?php
// register.php

// Binde die Datei "db.php" ein, die die Verbindung zur Datenbank herstellt.
// Dadurch können wir SQL-Abfragen ausführen, um Benutzerdaten zu speichern.
require_once 'includes/db.php';

// Binde die Datei "functions.php" ein, welche gemeinsame Funktionen enthält,
// zum Beispiel secure_session_start() zum sicheren Starten einer Session und escape() zum sicheren Ausgeben von Daten.
require_once 'includes/functions.php';

// Starte eine sichere Session, damit wir auf Session-Daten (wie z. B. den Login-Status) zugreifen können.
secure_session_start();

// Initialisiere ein Array für Fehlermeldungen und Variablen für Benutzername und E-Mail.
// Diese Variablen werden verwendet, um das Formular nach einem fehlgeschlagenen Versuch wieder vorzubelegen.
$errors = [];
$username = $email = '';

// Überprüfe, ob das Formular per POST gesendet wurde.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Hole die Benutzereingaben und entferne überflüssige Leerzeichen.
    $username         = trim($_POST['username'] ?? '');
    $email            = trim($_POST['email'] ?? '');
    $password         = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Serverseitige Validierung:
    // Prüfe, ob alle Felder ausgefüllt sind.
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $errors[] = "Alle Felder müssen ausgefüllt werden.";
    }
    // Prüfe, ob die E-Mail-Adresse ein gültiges Format hat.
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Ungültige E-Mail-Adresse.";
    }
    // Überprüfe, ob das Passwort und die Passwortbestätigung übereinstimmen.
    if ($password !== $confirm_password) {
        $errors[] = "Die Passwörter stimmen nicht überein.";
    }

    // Hier könnten weitere Validierungen eingefügt werden...

    // Falls keine Fehler aufgetreten sind, fahre mit der Registrierung fort.
    if (empty($errors)) {
        // Überprüfe, ob der Benutzername oder die E-Mail-Adresse bereits in der Datenbank existiert.
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = :username OR email = :email");
        $stmt->execute([':username' => $username, ':email' => $email]);
        // Wenn ein Datensatz gefunden wird, füge eine Fehlermeldung hinzu.
        if ($stmt->fetch()) {
            $errors[] = "Benutzername oder E-Mail existiert bereits.";
        } else {
            // Wenn der Benutzername und die E-Mail-Adresse noch nicht existieren,
            // hashe das Passwort mit password_hash(), um es sicher zu speichern.
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            // Bereite eine SQL-Abfrage vor, um einen neuen Benutzer in der Datenbank anzulegen.
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (:username, :email, :password)");
            // Führe die Abfrage aus und ersetze die Platzhalter durch die tatsächlichen Werte.
            if ($stmt->execute([':username' => $username, ':email' => $email, ':password' => $password_hash])) {
                // Bei erfolgreicher Registrierung leite den Benutzer zur Login-Seite weiter.
                header("Location: login.php");
                exit;
            } else {
                // Falls beim Einfügen ein Fehler auftritt, füge eine Fehlermeldung hinzu.
                $errors[] = "Fehler beim Registrieren. Bitte erneut versuchen.";
            }
        }
    }
}
?>
<?php include 'includes/header.php'; ?>
<!-- Binde den Header ein, der das HTML-Grundgerüst, Navigation und CSS (z. B. Bootstrap) enthält. -->
<div class="container mt-5">
    <h1>Registrieren</h1>
    <!-- Wenn Fehler vorhanden sind, werden diese in einer roten Bootstrap-Alert-Box angezeigt -->
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <?php foreach ($errors as $error): ?>
                <p><?php echo escape($error); ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <!-- Formular zur Registrierung -->
    <form action="register.php" method="post">
        <div class="form-group">
            <label for="username">Benutzername:</label>
            <!-- Das Eingabefeld für den Benutzernamen ist erforderlich und wird mit dem zuvor eingegebenen Wert vorbelegt -->
            <input type="text" name="username" id="username" class="form-control" required value="<?php echo escape($username); ?>">
        </div>
        <div class="form-group">
            <label for="email">E-Mail:</label>
            <!-- Das Eingabefeld für die E-Mail-Adresse ist erforderlich und wird mit dem zuvor eingegebenen Wert vorbelegt -->
            <input type="email" name="email" id="email" class="form-control" required value="<?php echo escape($email); ?>">
        </div>
        <div class="form-group">
            <label for="password">Passwort:</label>
            <!-- Das Passwortfeld ist erforderlich -->
            <input type="password" name="password" id="password" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="confirm_password">Passwort wiederholen:</label>
            <!-- Das Passwortbestätigungsfeld ist erforderlich -->
            <input type="password" name="confirm_password" id="confirm_password" class="form-control" required>
        </div>
        <!-- Sende-Button zum Absenden des Formulars -->
        <button type="submit" class="btn btn-primary">Registrieren</button>
    </form>
</div>
<?php include 'includes/footer.php'; ?>
<!-- Binde den Footer ein, der das Ende des HTML-Dokuments darstellt -->
