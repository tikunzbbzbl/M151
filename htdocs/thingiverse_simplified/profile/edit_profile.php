<?php
// profil/edit_profile.php
require_once '../includes/db.php';
require_once '../includes/functions.php';
secure_session_start();

if (!is_logged_in()) {
    header("Location: ../login.php");
    exit;
}

$errors  = [];
$success = '';

// Aktuelle Benutzerdaten abrufen
$stmt = $pdo->prepare("SELECT id, username, email, profile_picture FROM users WHERE id = :id");
$stmt->execute([':id' => $_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user) {
    die("Benutzer nicht gefunden.");
}

// Verarbeitung des Formulars
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Wenn das Profil-Update (Benutzername und E-Mail) gesendet wurde
    if (isset($_POST['update_profile'])) {
        $new_username = trim($_POST['new_username'] ?? '');
        $new_email    = trim($_POST['new_email'] ?? '');

        if (empty($new_username)) {
            $errors[] = "Benutzername darf nicht leer sein.";
        }
        if (empty($new_email) || !filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Bitte eine gültige E-Mail-Adresse eingeben.";
        }

        // Prüfen, ob der neue Benutzername oder die E-Mail bereits vergeben ist (außer dem eigenen Datensatz)
        $stmtCheck = $pdo->prepare("SELECT id FROM users WHERE (username = :username OR email = :email) AND id != :id");
        $stmtCheck->execute([
            ':username' => $new_username,
            ':email'    => $new_email,
            ':id'       => $_SESSION['user_id']
        ]);
        if ($stmtCheck->fetch()) {
            $errors[] = "Benutzername oder E-Mail ist bereits vergeben.";
        }

        if (empty($errors)) {
            $stmtUpdate = $pdo->prepare("UPDATE users SET username = :username, email = :email WHERE id = :id");
            $stmtUpdate->execute([
                ':username' => $new_username,
                ':email'    => $new_email,
                ':id'       => $_SESSION['user_id']
            ]);
            $_SESSION['username'] = $new_username;
            $success = "Profil erfolgreich aktualisiert.";
        }
    }

    // Wenn ein neues Profilbild hochgeladen wurde
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($_FILES['profile_picture']['type'], $allowed_types)) {
            $errors[] = "Nur JPEG, PNG und GIF sind erlaubt.";
        } else {
            $ext = strtolower(pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION));
            // Neuen Dateinamen generieren, z. B. "profile_{user_id}.{ext}"
            $new_filename = 'profile_' . $_SESSION['user_id'] . '.' . $ext;
            // Da sich diese Datei im Ordner "profil" befindet, greifen wir auf den uploads-Ordner im Root zu:
            $destination = __DIR__ . "/../uploads/" . $new_filename;
            if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $destination)) {
                $stmtPic = $pdo->prepare("UPDATE users SET profile_picture = :picture WHERE id = :id");
                $stmtPic->execute([':picture' => $new_filename, ':id' => $_SESSION['user_id']]);
                $success = "Profilbild erfolgreich aktualisiert.";
            } else {
                $errors[] = "Fehler beim Hochladen des Profilbildes.";
            }
        }
    }
    
    // Benutzerinformationen neu laden
    $stmt = $pdo->prepare("SELECT id, username, email, profile_picture FROM users WHERE id = :id");
    $stmt->execute([':id' => $_SESSION['user_id']]);
    $user = $stmt->fetch();
}
?>
<?php include '../includes/header.php'; ?>
<div class="container mt-5">
    <h1>Profil bearbeiten</h1>
    <?php if ($success): ?>
        <div class="alert alert-success">
            <p><?php echo escape($success); ?></p>
        </div>
    <?php endif; ?>
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
                <input type="text" name="new_username" id="new_username" class="form-control" value="<?php echo escape($user['username']); ?>" required>
            </div>
            <div class="form-group">
                <label for="new_email">E-Mail:</label>
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
            // Zeige aktuelles Profilbild oder Platzhalter an
            $profilePic = !empty($user['profile_picture']) ? '../uploads/' . $user['profile_picture'] : '../uploads/placeholder.png';
            ?>
            <div class="form-group">
                <img src="<?php echo escape($profilePic); ?>" alt="Profilbild" style="max-width:150px; margin-bottom:10px;">
            </div>
            <div class="form-group">
                <label for="profile_picture">Neues Profilbild (JPEG, PNG, GIF):</label>
                <input type="file" name="profile_picture" id="profile_picture" class="form-control" accept="image/jpeg, image/png, image/gif">
            </div>
            <button type="submit" class="btn btn-primary">Profilbild ändern</button>
        </fieldset>
    </form>
    <a href="../profile.php" class="btn btn-secondary">Zurück zum Profil</a>
</div>
<?php include '../includes/footer.php'; ?>
