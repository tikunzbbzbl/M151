<?php
// profil/edit_profile.php

// Binde die Datei ein, die die Datenbankverbindung herstellt,
// damit wir SQL-Abfragen an die Datenbank senden können.
require_once '../includes/db.php';

// Binde die Datei mit gemeinsamen Hilfsfunktionen ein,
// welche Funktionen wie secure_session_start() und escape() enthält.
require_once '../includes/functions.php';

// Starte eine sichere Session, um auf Session-Daten (z.B. Login-Status) zugreifen zu können.
secure_session_start();

// Überprüfe, ob der Benutzer eingeloggt ist. Falls nicht, leite ihn zur Login-Seite weiter.
if (!is_logged_in()) {
    header("Location: ../login.php");
    exit;
}

$errors  = []; // Array zur Speicherung von Fehlermeldungen
$success = ''; // Variable zur Speicherung einer Erfolgsmeldung

// Aktuelle Benutzerdaten aus der Datenbank abrufen.
// Wir wählen id, username, email und profile_picture aus der Tabelle "users" für den aktuell eingeloggten Benutzer.
$stmt = $pdo->prepare("SELECT id, username, email, profile_picture FROM users WHERE id = :id");
$stmt->execute([':id' => $_SESSION['user_id']]);
$user = $stmt->fetch();

// Falls der Benutzer nicht gefunden wird, beende das Skript mit einer Fehlermeldung.
if (!$user) {
    die("Benutzer nicht gefunden.");
}

// Verarbeitung des Formulars, wenn die Seite per POST aufgerufen wird.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // -------------------------------
    // Profil-Update (Benutzername und E-Mail)
    // -------------------------------
    if (isset($_POST['update_profile'])) {
        // Neue Werte aus dem Formular abrufen und mit trim() von Leerzeichen befreien.
        $new_username = trim($_POST['new_username'] ?? '');
        $new_email    = trim($_POST['new_email'] ?? '');

        // Validierung: Prüfe, ob der neue Benutzername leer ist.
        if (empty($new_username)) {
            $errors[] = "Benutzername darf nicht leer sein.";
        }
        // Validierung: Prüfe, ob die E-Mail leer ist oder ungültig formatiert ist.
        if (empty($new_email) || !filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Bitte eine gültige E-Mail-Adresse eingeben.";
        }

        // Prüfe, ob der neue Benutzername oder die E-Mail bereits von einem anderen Benutzer genutzt werden.
        $stmtCheck = $pdo->prepare("SELECT id FROM users WHERE (username = :username OR email = :email) AND id != :id");
        $stmtCheck->execute([
            ':username' => $new_username,
            ':email'    => $new_email,
            ':id'       => $_SESSION['user_id']
        ]);
        if ($stmtCheck->fetch()) {
            $errors[] = "Benutzername oder E-Mail ist bereits vergeben.";
        }

        // Wenn keine Fehler vorliegen, aktualisiere die Benutzerdaten in der Datenbank.
        if (empty($errors)) {
            $stmtUpdate = $pdo->prepare("UPDATE users SET username = :username, email = :email WHERE id = :id");
            $stmtUpdate->execute([
                ':username' => $new_username,
                ':email'    => $new_email,
                ':id'       => $_SESSION['user_id']
            ]);
            // Aktualisiere die Session-Variable für den Benutzernamen, damit der neue Name überall verwendet wird.
            $_SESSION['username'] = $new_username;
            $success = "Profil erfolgreich aktualisiert.";
        }
    }

    // -------------------------------
    // Profilbild-Update
    // -------------------------------
    // Prüfe, ob ein neues Profilbild hochgeladen wurde und ob der Upload-Fehlerstatus OK ist.
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        // Definiere erlaubte MIME-Typen für das Profilbild.
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        // Prüfe, ob der Typ des hochgeladenen Bildes in der Liste der erlaubten Typen enthalten ist.
        if (!in_array($_FILES['profile_picture']['type'], $allowed_types)) {
            $errors[] = "Nur JPEG, PNG und GIF sind erlaubt.";
        } else {
            // Ermittle die Dateiendung des hochgeladenen Bildes und konvertiere sie in Kleinbuchstaben.
            $ext = strtolower(pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION));
            // Generiere einen neuen Dateinamen im Format "profile_{user_id}.{ext}".
            $new_filename = 'profile_' . $_SESSION['user_id'] . '.' . $ext;
            // Setze den Zielpfad für das hochgeladene Bild. Da diese Datei im Ordner "profil" liegt,
            // greifen wir mit "../uploads/" auf den Upload-Ordner im Root zu.
            $destination = __DIR__ . "/../uploads/" . $new_filename;
            // Versuche, die hochgeladene Datei vom temporären Speicherort in den Upload-Ordner zu verschieben.
            if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $destination)) {
                // Falls das Verschieben erfolgreich war, aktualisiere den Dateinamen in der Datenbank.
                $stmtPic = $pdo->prepare("UPDATE users SET profile_picture = :picture WHERE id = :id");
                $stmtPic->execute([':picture' => $new_filename, ':id' => $_SESSION['user_id']]);
                $success = "Profilbild erfolgreich aktualisiert.";
            } else {
                $errors[] = "Fehler beim Hochladen des Profilbildes.";
            }
        }
    }
    
    // Nach den Updates: Lade die aktuellen Benutzerdaten neu, damit die Änderungen angezeigt werden.
    $stmt = $pdo->prepare("SELECT id, username, email, profile_picture FROM users WHERE id = :id");
    $stmt->execute([':id' => $_SESSION['user_id']]);
    $user = $stmt->fetch();
}
?>
<?php include '../includes/header.php'; ?>
<div class="container mt-5">
    <h1>Profil bearbeiten</h1>
    <!-- Anzeige der Erfolgsmeldung, falls vorhanden -->
    <?php if ($success): ?>
        <div class="alert alert-success">
            <p><?php echo escape($success); ?></p>
        </div>
    <?php endif; ?>
    <!-- Anzeige von Fehlermeldungen, falls vorhanden -->
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <?php foreach ($errors as $error): ?>
                <p><?php echo escape($error); ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <!-- Formular zur Aktualisierung von Benutzername und E-Mail -->
    <form action="edit_profile.php" method="post" class="mb-4">
        <fieldset class="border p-3">
            <legend>Profil aktualisieren</legend>
            <div class="form-group">
                <label for="new_username">Benutzername:</label>
                <!-- Das Eingabefeld wird mit dem aktuellen Benutzernamen vorbelegt -->
                <input type="text" name="new_username" id="new_username" class="form-control" value="<?php echo escape($user['username']); ?>" required>
            </div>
            <div class="form-group">
                <label for="new_email">E-Mail:</label>
                <!-- Das Eingabefeld wird mit der aktuellen E-Mail-Adresse vorbelegt -->
                <input type="email" name="new_email" id="new_email" class="form-control" value="<?php echo escape($user['email']); ?>" required>
            </div>
            <button type="submit" name="update_profile" class="btn btn-primary">Profil aktualisieren</button>
        </fieldset>
    </form>
    <!-- Formular zum Ändern des Profilbildes -->
    <form action="edit_profile.php" method="post" enctype="multipart/form-data">
        <fieldset class="border p-3 mb-3">
            <legend>Profilbild ändern</legend>
            <?php 
            // Ermittle, welches Bild angezeigt werden soll:
            // Falls ein Profilbild vorhanden ist, wird es aus dem Ordner "uploads" geladen,
            // andernfalls wird ein Platzhalterbild verwendet.
            $profilePic = !empty($user['profile_picture']) ? '../uploads/' . $user['profile_picture'] : '../uploads/placeholder.png';
            ?>
            <div class="form-group">
                <img src="<?php echo escape($profilePic); ?>" alt="Profilbild" style="max-width:150px; margin-bottom:10px;">
            </div>
            <div class="form-group">
                <label for="profile_picture">Neues Profilbild (JPEG, PNG, GIF):</label>
                <!-- Dateiupload-Feld für das neue Profilbild -->
                <input type="file" name="profile_picture" id="profile_picture" class="form-control" accept="image/jpeg, image/png, image/gif">
            </div>
            <button type="submit" class="btn btn-primary">Profilbild ändern</button>
        </fieldset>
    </form>
    <!-- Link, um zurück zum Profil anzuzeigen -->
    <a href="../profile.php" class="btn btn-secondary">Zurück zum Profil</a>
</div>
<?php include '../includes/footer.php'; ?>
