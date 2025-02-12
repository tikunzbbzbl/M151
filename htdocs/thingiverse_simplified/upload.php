<?php
// upload.php – Seite zum Hochladen einer Kreation (C14, C16, C17, C18)
require_once 'includes/db.php';
require_once 'includes/functions.php';
secure_session_start();

if (!is_logged_in()) {
    header("Location: login.php");
    exit;
}

$errors  = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if (empty($title)) {
        $errors[] = "Titel ist erforderlich.";
    }

    // Überprüfe, ob eine Datei hochgeladen wurde
    if (!isset($_FILES['creation_file']) || $_FILES['creation_file']['error'] != UPLOAD_ERR_OK) {
        $errors[] = "Es muss eine Datei hochgeladen werden.";
    } else {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($_FILES['creation_file']['type'], $allowed_types)) {
            $errors[] = "Nur JPEG, PNG und GIF sind erlaubt.";
        }
    }

    if (empty($errors)) {
        // Erstelle einen eindeutigen Dateinamen
        $ext          = pathinfo($_FILES['creation_file']['name'], PATHINFO_EXTENSION);
        $new_filename = 'creation_' . time() . '_' . $_SESSION['user_id'] . '.' . $ext;
        $destination  = 'uploads/' . $new_filename;

        if (move_uploaded_file($_FILES['creation_file']['tmp_name'], $destination)) {
            // Speichere die Kreation in der DB
            $stmt = $pdo->prepare("INSERT INTO kreationen (user_id, title, description, image) VALUES (:user_id, :title, :description, :image)");
            $stmt->execute([
                ':user_id'    => $_SESSION['user_id'],
                ':title'      => $title,
                ':description'=> $description,
                ':image'      => $new_filename
            ]);
            $success = "Kreation erfolgreich hochgeladen.";
        } else {
            $errors[] = "Fehler beim Verschieben der Datei.";
        }
    }
}
?>
<?php include 'header.php'; ?>
<h1>Kreation hochladen</h1>
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
<form action="upload.php" method="post" enctype="multipart/form-data">
    <label for="title">Titel der Kreation:</label>
    <input type="text" name="title" id="title" required><br>

    <label for="description">Beschreibung (optional):</label>
    <textarea name="description" id="description"></textarea><br>

    <label for="creation_file">Datei auswählen (JPEG, PNG, GIF):</label>
    <input type="file" name="creation_file" id="creation_file" accept="image/jpeg, image/png, image/gif" required><br>

    <button type="submit">Hochladen</button>
</form>
<?php include 'footer.php'; ?>
