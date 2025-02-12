<?php
// profile.php – Benutzerprofil (C8, C15)
require_once 'includes/db.php';
require_once 'includes/functions.php';
secure_session_start();

if (!is_logged_in()) {
    header("Location: login.php");
    exit;
}

$errors  = [];
$success = '';

// Hole aktuelle Benutzerdaten
$stmt = $pdo->prepare("SELECT username, email, profile_picture FROM users WHERE id = :id");
$stmt->execute([':id' => $_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user) {
    die("Benutzer nicht gefunden.");
}

// Verarbeitung des Formulars:
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Benutzername ändern
    if (isset($_POST['new_username'])) {
        $new_username = trim($_POST['new_username']);
        if (empty($new_username)) {
            $errors[] = "Benutzername darf nicht leer sein.";
        } else {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = :username AND id != :id");
            $stmt->execute([':username' => $new_username, ':id' => $_SESSION['user_id']]);
            if ($stmt->fetch()) {
                $errors[] = "Benutzername ist bereits vergeben.";
            } else {
                $stmt = $pdo->prepare("UPDATE users SET username = :username WHERE id = :id");
                $stmt->execute([':username' => $new_username, ':id' => $_SESSION['user_id']]);
                $_SESSION['username'] = $new_username;
                $success = "Benutzername erfolgreich geändert.";
            }
        }
    }
    // Passwort ändern
    if (isset($_POST['new_password']) && isset($_POST['confirm_new_password'])) {
        $new_password         = $_POST['new_password'];
        $confirm_new_password = $_POST['confirm_new_password'];
        if (!empty($new_password)) {
            if ($new_password !== $confirm_new_password) {
                $errors[] = "Die neuen Passwörter stimmen nicht überein.";
            } else {
                $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET password = :password WHERE id = :id");
                $stmt->execute([':password' => $password_hash, ':id' => $_SESSION['user_id']]);
                $success = "Passwort erfolgreich geändert.";
            }
        }
    }
    // Profilbild ändern
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        if (in_array($_FILES['profile_picture']['type'], $allowed_types)) {
            $ext = pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION);
            $new_filename = 'profile_' . $_SESSION['user_id'] . '.' . $ext;
            $destination  = 'uploads/' . $new_filename;
            if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $destination)) {
                $stmt = $pdo->prepare("UPDATE users SET profile_picture = :picture WHERE id = :id");
                $stmt->execute([':picture' => $new_filename, ':id' => $_SESSION['user_id']]);
                $success = "Profilbild erfolgreich geändert.";
            } else {
                $errors[] = "Fehler beim Hochladen des Profilbildes.";
            }
        } else {
            $errors[] = "Nur JPEG, PNG und GIF sind erlaubt.";
        }
    }
    // Hole die aktuellen Daten neu
    $stmt = $pdo->prepare("SELECT username, email, profile_picture FROM users WHERE id = :id");
    $stmt->execute([':id' => $_SESSION['user_id']]);
    $user = $stmt->fetch();
}
?>
<?php include 'header.php'; ?>
<h1>Profil von <?php echo escape($user['username']); ?></h1>
<?php if ($success): ?>
    <div style="color:green;">
        <p><?php echo escape($success); ?></p>
    </div>
<?php endif; ?>
<?php if (!empty($errors)): ?>
    <div style="color:red;">
        <?php foreach ($errors as $error) echo "<p>" . escape($error) . "</p>"; ?>
    </div>
<?php endif; ?>

<!-- Formular zur Änderung von Benutzername, Passwort und Profilbild -->
<form action="profile.php" method="post" enctype="multipart/form-data">
    <fieldset>
        <legend>Benutzername ändern</legend>
        <label for="new_username">Neuer Benutzername:</label>
        <input type="text" name="new_username" id="new_username" value="<?php echo escape($user['username']); ?>" required>
        <button type="submit">Ändern</button>
    </fieldset>
</form>
<br>
<form action="profile.php" method="post">
    <fieldset>
        <legend>Passwort ändern</legend>
        <label for="new_password">Neues Passwort:</label>
        <input type="password" name="new_password" id="new_password" required>
        <label for="confirm_new_password">Neues Passwort wiederholen:</label>
        <input type="password" name="confirm_new_password" id="confirm_new_password" required>
        <button type="submit">Ändern</button>
    </fieldset>
</form>
<br>
<form action="profile.php" method="post" enctype="multipart/form-data">
    <fieldset>
        <legend>Profilbild ändern</legend>
        <?php if (!empty($user['profile_picture'])): ?>
            <img src="uploads/<?php echo escape($user['profile_picture']); ?>" alt="Profilbild" style="max-width:100px;"><br>
        <?php endif; ?>
        <label for="profile_picture">Neues Profilbild (JPEG, PNG, GIF):</label>
        <input type="file" name="profile_picture" id="profile_picture" accept="image/jpeg, image/png, image/gif" required>
        <button type="submit">Ändern</button>
    </fieldset>
</form>
<?php include 'footer.php'; ?>
