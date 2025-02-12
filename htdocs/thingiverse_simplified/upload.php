<?php
// upload.php – Mehrfachupload mit automatischer Thumbnail-Auswahl
require_once 'includes/db.php';
require_once 'includes/functions.php';
secure_session_start();

$errors  = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    
    if (empty($title)) {
        $errors[] = "Titel ist erforderlich.";
    }
    
    // Es muss mindestens eine Datei in creation_files[] hochgeladen werden.
    if (!isset($_FILES['creation_files']) || empty($_FILES['creation_files']['name'][0])) {
        $errors[] = "Es muss mindestens eine Datei hochgeladen werden.";
    }
    
    // Prüfe, ob ein separates Thumbnail hochgeladen wurde.
    $thumbnailProvided = false;
    $thumbnailFilename = '';
    if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
        $allowed_image_types = ['image/jpeg', 'image/png', 'image/gif'];
        if (in_array($_FILES['thumbnail']['type'], $allowed_image_types)) {
            $ext = strtolower(pathinfo($_FILES['thumbnail']['name'], PATHINFO_EXTENSION));
            $thumbnailFilename = 'thumbnail_' . time() . '_' . $_SESSION['user_id'] . '.' . $ext;
            $thumbDestination = 'uploads/' . $thumbnailFilename;
            if (move_uploaded_file($_FILES['thumbnail']['tmp_name'], $thumbDestination)) {
                $thumbnailProvided = true;
            } else {
                $errors[] = "Fehler beim Hochladen des separaten Thumbnails.";
            }
        } else {
            $errors[] = "Das separate Thumbnail muss ein Bild sein (JPEG, PNG, GIF).";
        }
    }
    
    if (empty($errors)) {
        // Neuen Kreationseintrag erstellen.
        $stmt = $pdo->prepare("INSERT INTO kreationen (user_id, title, description) VALUES (:user_id, :title, :description)");
        $stmt->execute([
            ':user_id' => $_SESSION['user_id'],
            ':title' => $title,
            ':description' => $description
        ]);
        $kreation_id = $pdo->lastInsertId();
        
        // Falls ein separates Thumbnail hochgeladen wurde, in der DB als Thumbnail speichern.
        if ($thumbnailProvided) {
            $stmtThumb = $pdo->prepare("INSERT INTO kreation_files (kreation_id, file_name, file_type, is_thumbnail) VALUES (:kreation_id, :file_name, :file_type, 1)");
            $stmtThumb->execute([
                ':kreation_id' => $kreation_id,
                ':file_name' => $thumbnailFilename,
                ':file_type' => mime_content_type($thumbDestination)
            ]);
        }
        
        // Verarbeitung der Mehrfachuploads aus creation_files[].
        $autoThumbnailSet = false; // Flag: Wurde schon automatisch ein Thumbnail gesetzt?
        $allowed_image_ext = ['jpg', 'jpeg', 'png', 'gif'];  // Erlaubte Bildformate
        $allowed_other_ext = ['stl'];                        // Erlaubte STL-Dateien
        $allowed_extensions = array_merge($allowed_image_ext, $allowed_other_ext);
        
        foreach ($_FILES['creation_files']['name'] as $key => $name) {
            if ($_FILES['creation_files']['error'][$key] !== UPLOAD_ERR_OK) {
                continue; // Fehlerhaften Upload überspringen
            }
            $tmp_name = $_FILES['creation_files']['tmp_name'][$key];
            $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            if (!in_array($ext, $allowed_extensions)) {
                continue; // Nicht erlaubte Dateitypen überspringen
            }
            $new_filename = 'creation_' . time() . '_' . $_SESSION['user_id'] . '_' . $key . '.' . $ext;
            $destination = 'uploads/' . $new_filename;
            if (move_uploaded_file($tmp_name, $destination)) {
                // Falls es ein Bild ist und noch kein Thumbnail (weder separat noch automatisch) gesetzt wurde, wird es automatisch als Thumbnail genutzt.
                if (in_array($ext, $allowed_image_ext) && !$thumbnailProvided && !$autoThumbnailSet) {
                    $is_thumbnail = 1;
                    $autoThumbnailSet = true;
                } else {
                    $is_thumbnail = 0;
                }
                $stmtFile = $pdo->prepare("INSERT INTO kreation_files (kreation_id, file_name, file_type, is_thumbnail) VALUES (:kreation_id, :file_name, :file_type, :is_thumbnail)");
                $stmtFile->execute([
                    ':kreation_id' => $kreation_id,
                    ':file_name' => $new_filename,
                    ':file_type' => mime_content_type($destination),
                    ':is_thumbnail' => $is_thumbnail
                ]);
            }
        }
        
        // Falls kein Thumbnail (separat oder automatisch) gesetzt wurde und mindestens eine .stl-Datei hochgeladen wurde,
        // erzwingen wir einen Fehler.
        if (!$thumbnailProvided && !$autoThumbnailSet) {
            $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM kreation_files WHERE kreation_id = :kid AND file_name LIKE '%.stl'");
            $stmtCheck->execute([':kid' => $kreation_id]);
            $stlCount = $stmtCheck->fetchColumn();
            if ($stlCount > 0) {
                $errors[] = "Für .stl-Dateien muss ein Thumbnail hochgeladen werden.";
                // Optional: Den gerade erstellten Eintrag wieder löschen.
            }
        }
        
        if (empty($errors)) {
            $success = "Kreation erfolgreich hochgeladen.";
        }
    }
}
?>
<?php include 'includes/header.php'; ?>
<h1>Kreation hochladen</h1>
<?php if (!empty($errors)): ?>
    <div style="color:red;">
        <?php foreach ($errors as $error): ?>
            <p><?php echo escape($error); ?></p>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
<?php if ($success): ?>
    <div style="color:green;">
        <p><?php echo escape($success); ?></p>
    </div>
<?php endif; ?>
<form action="upload.php" method="post" enctype="multipart/form-data">
    <label for="title">Titel der Kreation:</label>
    <input type="text" name="title" id="title" required><br>

    <label for="description">Beschreibung (optional):</label>
    <textarea name="description" id="description"></textarea><br>

    <label for="creation_files">Dateien auswählen (Mehrfachauswahl möglich):</label>
    <input type="file" name="creation_files[]" id="creation_files" multiple><br>

    <label for="thumbnail">Thumbnail hochladen (optional, Pflicht bei .stl):</label>
    <input type="file" name="thumbnail" id="thumbnail" accept="image/jpeg, image/png, image/gif"><br>

    <button type="submit">Hochladen</button>
</form>
<?php include 'includes/footer.php'; ?>
