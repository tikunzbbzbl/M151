<?php
// upload.php – Nur für eingeloggte Benutzer, Mehrfachupload & automatische Thumbnail-Auswahl
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
  
  if (!isset($_FILES['creation_files']) || empty($_FILES['creation_files']['name'][0])) {
      $errors[] = "Es muss mindestens eine Datei hochgeladen werden.";
  }
  
  // Prüfe separates Thumbnail
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
      $stmt = $pdo->prepare("INSERT INTO kreationen (user_id, title, description) VALUES (:user_id, :title, :description)");
      $stmt->execute([
          ':user_id' => $_SESSION['user_id'],
          ':title' => $title,
          ':description' => $description
      ]);
      $kreation_id = $pdo->lastInsertId();
      
      if ($thumbnailProvided) {
          $stmtThumb = $pdo->prepare("INSERT INTO kreation_files (kreation_id, file_name, file_type, is_thumbnail) VALUES (:kreation_id, :file_name, :file_type, 1)");
          $stmtThumb->execute([
              ':kreation_id' => $kreation_id,
              ':file_name' => $thumbnailFilename,
              ':file_type' => mime_content_type($thumbDestination)
          ]);
      }
      
      $autoThumbnailSet = false;
      $allowed_image_ext = ['jpg', 'jpeg', 'png', 'gif'];
      $allowed_other_ext = ['stl'];
      $allowed_extensions = array_merge($allowed_image_ext, $allowed_other_ext);
      
      foreach ($_FILES['creation_files']['name'] as $key => $name) {
          if ($_FILES['creation_files']['error'][$key] !== UPLOAD_ERR_OK) {
              continue;
          }
          $tmp_name = $_FILES['creation_files']['tmp_name'][$key];
          $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
          if (!in_array($ext, $allowed_extensions)) {
              continue;
          }
          $new_filename = 'creation_' . time() . '_' . $_SESSION['user_id'] . '_' . $key . '.' . $ext;
          $destination = 'uploads/' . $new_filename;
          if (move_uploaded_file($tmp_name, $destination)) {
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
      
      if (!$thumbnailProvided && !$autoThumbnailSet) {
          $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM kreation_files WHERE kreation_id = :kid AND file_name LIKE '%.stl'");
          $stmtCheck->execute([':kid' => $kreation_id]);
          $stlCount = $stmtCheck->fetchColumn();
          if ($stlCount > 0) {
              $errors[] = "Für .stl-Dateien muss ein Thumbnail hochgeladen werden.";
          }
      }
      
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
    <?php if (!empty($errors)): ?>
      <div class="alert alert-danger">
        <?php foreach ($errors as $error): ?>
          <p><?php echo escape($error); ?></p>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
    <?php if ($success): ?>
      <div class="alert alert-success">
        <p><?php echo escape($success); ?></p>
      </div>
    <?php endif; ?>
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