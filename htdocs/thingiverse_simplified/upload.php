<?php
// upload.php – Nur für eingeloggte Benutzer, Mehrfachupload & automatische Thumbnail-Auswahl

// Binde die Datei ein, die die Verbindung zur Datenbank herstellt.
// Dadurch können wir SQL-Abfragen an die Datenbank senden.
require_once 'includes/db.php';

// Binde die Datei mit den gemeinsamen Hilfsfunktionen ein.
// Diese Funktionen beinhalten u.a. secure_session_start() zum sicheren Starten einer Session
// und escape() zum sicheren Ausgeben von Daten.
require_once 'includes/functions.php';

// Starte eine sichere Session, damit wir auf Session-Daten (wie den Login-Status) zugreifen können.
secure_session_start();

// Überprüfe, ob der Benutzer eingeloggt ist.
// Falls der Benutzer nicht eingeloggt ist, leite ihn zur Login-Seite weiter und beende das Skript.
if (!is_logged_in()) {
  header("Location: login.php");
  exit;
}

// Initialisiere Variablen für Fehler- und Erfolgsmeldungen.
$errors  = [];
$success = '';

// Prüfe, ob das Formular per POST übermittelt wurde.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Hole den Titel und die Beschreibung aus den POST-Daten und trimme (entferne) überflüssige Leerzeichen.
  $title       = trim($_POST['title'] ?? '');
  $description = trim($_POST['description'] ?? '');
  
  // Validierung: Prüfe, ob ein Titel eingegeben wurde.
  if (empty($title)) {
      $errors[] = "Titel ist erforderlich.";
  }
  
  // Validierung: Prüfe, ob mindestens eine Datei im Mehrfach-Upload-Feld ausgewählt wurde.
  if (!isset($_FILES['creation_files']) || empty($_FILES['creation_files']['name'][0])) {
      $errors[] = "Es muss mindestens eine Datei hochgeladen werden.";
  }
  
  // -------------------------------
  // Separates Thumbnail verarbeiten (optional)
  // -------------------------------
  $thumbnailProvided = false; // Flag, ob ein separates Thumbnail hochgeladen wurde.
  $thumbnailFilename = '';    // Variable zur Speicherung des neuen Dateinamens des Thumbnails.
  
  // Prüfe, ob im separaten Thumbnail-Feld eine Datei hochgeladen wurde und ob der Upload-Fehlerstatus OK ist.
  if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
      // Erlaubte MIME-Typen für das Thumbnail.
      $allowed_image_types = ['image/jpeg', 'image/png', 'image/gif'];
      
      // Prüfe, ob der MIME-Typ der hochgeladenen Datei in der Liste der erlaubten Typen enthalten ist.
      if (in_array($_FILES['thumbnail']['type'], $allowed_image_types)) {
          // Ermittle die Dateiendung (Extension) der hochgeladenen Datei und wandle sie in Kleinbuchstaben um.
          $ext = strtolower(pathinfo($_FILES['thumbnail']['name'], PATHINFO_EXTENSION));
          // Generiere einen neuen Dateinamen im Format "thumbnail_{timestamp}_{user_id}.{ext}".
          $thumbnailFilename = 'thumbnail_' . time() . '_' . $_SESSION['user_id'] . '.' . $ext;
          // Setze den Zielpfad für das Thumbnail im Ordner "uploads".
          $thumbDestination = 'uploads/' . $thumbnailFilename;
          // Versuche, die hochgeladene Datei vom temporären Speicherort in den Zielordner zu verschieben.
          if (move_uploaded_file($_FILES['thumbnail']['tmp_name'], $thumbDestination)) {
              // Falls das Verschieben erfolgreich war, setze das Flag auf true.
              $thumbnailProvided = true;
          } else {
              // Falls ein Fehler auftritt, füge eine Fehlermeldung hinzu.
              $errors[] = "Fehler beim Hochladen des separaten Thumbnails.";
          }
      } else {
          // Falls der Dateityp nicht erlaubt ist, füge eine Fehlermeldung hinzu.
          $errors[] = "Das separate Thumbnail muss ein Bild sein (JPEG, PNG, GIF).";
      }
  }
  
  // -------------------------------
  // Wenn keine Fehler vorhanden sind, fahre mit dem Upload fort.
  // -------------------------------
  if (empty($errors)) {
      // Füge einen neuen Eintrag in der Tabelle "kreationen" hinzu, der den Post repräsentiert.
      // Die Tabelle "kreationen" speichert den Benutzer (user_id), den Titel und die Beschreibung.
      $stmt = $pdo->prepare("INSERT INTO kreationen (user_id, title, description) VALUES (:user_id, :title, :description)");
      $stmt->execute([
          ':user_id' => $_SESSION['user_id'],
          ':title' => $title,
          ':description' => $description
      ]);
      // Hole die ID des gerade eingefügten Posts.
      $kreation_id = $pdo->lastInsertId();
      
      // Falls ein separates Thumbnail hochgeladen wurde, füge es als Datei-Eintrag in der Tabelle "kreation_files" hinzu.
      if ($thumbnailProvided) {
          $stmtThumb = $pdo->prepare("INSERT INTO kreation_files (kreation_id, file_name, file_type, is_thumbnail) VALUES (:kreation_id, :file_name, :file_type, 1)");
          $stmtThumb->execute([
              ':kreation_id' => $kreation_id,
              ':file_name' => $thumbnailFilename,
              // mime_content_type() ermittelt den MIME-Typ der Datei am Zielort.
              ':file_type' => mime_content_type($thumbDestination)
          ]);
      }
      
      // -------------------------------
      // Verarbeitung der Mehrfachauswahl-Dateien (zusätzliche Dateien zur Kreation)
      // -------------------------------
      $autoThumbnailSet = false; // Flag, ob automatisch ein Thumbnail gesetzt wurde.
      // Definiere erlaubte Bildformate.
      $allowed_image_ext = ['jpg', 'jpeg', 'png', 'gif'];
      // Definiere weitere erlaubte Dateiendungen, z.B. für STL-Dateien.
      $allowed_other_ext = ['stl'];
      // Kombiniere beide Arrays in ein Array der erlaubten Extensions.
      $allowed_extensions = array_merge($allowed_image_ext, $allowed_other_ext);
      
      // Durchlaufe alle Dateien, die im Mehrfachauswahl-Feld "creation_files[]" hochgeladen wurden.
      foreach ($_FILES['creation_files']['name'] as $key => $name) {
          // Überspringe den Upload, falls ein Fehler bei der Datei vorliegt.
          if ($_FILES['creation_files']['error'][$key] !== UPLOAD_ERR_OK) {
              continue;
          }
          // Hole den temporären Pfad der Datei.
          $tmp_name = $_FILES['creation_files']['tmp_name'][$key];
          // Ermittle die Dateiendung und wandle sie in Kleinbuchstaben um.
          $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
          // Prüfe, ob die Dateiendung in der Liste der erlaubten Extensions enthalten ist.
          if (!in_array($ext, $allowed_extensions)) {
              continue; // Überspringe Dateien, die nicht erlaubt sind.
          }
          // Generiere einen neuen Dateinamen im Format "creation_{timestamp}_{user_id}_{index}.{ext}".
          $new_filename = 'creation_' . time() . '_' . $_SESSION['user_id'] . '_' . $key . '.' . $ext;
          // Setze den Zielpfad für die Datei im Ordner "uploads".
          $destination = 'uploads/' . $new_filename;
          // Versuche, die Datei vom temporären Speicherort in den Zielordner zu verschieben.
          if (move_uploaded_file($tmp_name, $destination)) {
              // Falls die Datei ein Bild ist, und wenn noch kein Thumbnail (separat oder automatisch) gesetzt wurde,
              // wird diese Datei als Thumbnail markiert.
              if (in_array($ext, $allowed_image_ext) && !$thumbnailProvided && !$autoThumbnailSet) {
                  $is_thumbnail = 1;
                  $autoThumbnailSet = true; // Verhindere, dass mehr als ein automatisch gesetztes Thumbnail entsteht.
              } else {
                  $is_thumbnail = 0;
              }
              // Füge einen Eintrag in der Tabelle "kreation_files" hinzu, der diese Datei der Kreation zuordnet.
              $stmtFile = $pdo->prepare("INSERT INTO kreation_files (kreation_id, file_name, file_type, is_thumbnail) VALUES (:kreation_id, :file_name, :file_type, :is_thumbnail)");
              $stmtFile->execute([
                  ':kreation_id' => $kreation_id,
                  ':file_name' => $new_filename,
                  ':file_type' => mime_content_type($destination),
                  ':is_thumbnail' => $is_thumbnail
              ]);
          }
      }
      
      // Falls kein separates Thumbnail und auch kein automatisches Thumbnail gesetzt wurde,
      // prüfe, ob mindestens eine .stl-Datei hochgeladen wurde.
      // Falls ja, füge eine Fehlermeldung hinzu, da für .stl-Dateien zwingend ein Thumbnail benötigt wird.
      if (!$thumbnailProvided && !$autoThumbnailSet) {
          $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM kreation_files WHERE kreation_id = :kid AND file_name LIKE '%.stl'");
          $stmtCheck->execute([':kid' => $kreation_id]);
          $stlCount = $stmtCheck->fetchColumn();
          if ($stlCount > 0) {
              $errors[] = "Für .stl-Dateien muss ein Thumbnail hochgeladen werden.";
          }
      }
      
      // Falls keine Fehler aufgetreten sind, setze eine Erfolgsmeldung.
      if (empty($errors)) {
          $success = "Kreation erfolgreich hochgeladen.";
      }
  }
}
?>
<?php include 'includes/header.php'; ?>
<div class="row">
  <div class="col-md-8 offset-md-2">
    <h1 class="my-4">Kreation hochladen</h1>
    <!-- Anzeige von Fehlermeldungen in einer roten Alert-Box -->
    <?php if (!empty($errors)): ?>
      <div class="alert alert-danger">
        <?php foreach ($errors as $error): ?>
          <p><?php echo escape($error); ?></p>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
    <!-- Anzeige der Erfolgsmeldung in einer grünen Alert-Box -->
    <?php if ($success): ?>
      <div class="alert alert-success">
        <p><?php echo escape($success); ?></p>
      </div>
    <?php endif; ?>
    <!-- Formular für den Upload einer Kreation -->
    <form action="upload.php" method="post" enctype="multipart/form-data">
      <div class="form-group">
        <label for="title">Titel:</label>
        <input type="text" name="title" id="title" class="form-control" required>
      </div>
      <div class="form-group">
        <label for="description">Beschreibung (optional):</label>
        <textarea name="description" id="description" class="form-control"></textarea>
      </div>
      <div class="form-group">
        <label for="creation_files">Dateien auswählen (Mehrfachauswahl möglich):</label>
        <input type="file" name="creation_files[]" id="creation_files" class="form-control-file" multiple>
      </div>
      <div class="form-group">
        <label for="thumbnail">Thumbnail hochladen (optional, Pflicht bei .stl):</label>
        <input type="file" name="thumbnail" id="thumbnail" class="form-control-file" accept="image/jpeg, image/png, image/gif">
      </div>
      <button type="submit" class="btn btn-primary">Hochladen</button>
    </form>
  </div>
</div>
<?php include 'includes/footer.php'; ?>
