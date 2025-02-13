<?php
// view.php – Detailansicht einer Kreation
require_once 'includes/db.php';
require_once 'includes/functions.php';
secure_session_start();

// Serverseitige Validierung
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Ungültige Anfrage.");
}
$id = (int)$_GET['id'];

// Hole die Kreation und das Profilbild
$stmt = $pdo->prepare("
    SELECT 
      k.*, 
      u.username,
      u.profile_picture
    FROM kreationen k
    JOIN users u ON k.user_id = u.id
    WHERE k.id = :id
");
$stmt->execute([':id' => $id]);
$kreation = $stmt->fetch();

if (!$kreation) {
    die("Kreation nicht gefunden.");
}

// Dateien abrufen (falls du sie anzeigen möchtest)
$stmtFiles = $pdo->prepare("SELECT file_name, file_type, is_thumbnail FROM kreation_files WHERE kreation_id = :kid");
$stmtFiles->execute([':kid' => $id]);
$files = $stmtFiles->fetchAll();
?>
<?php include 'includes/header.php'; ?>
<h1><?php echo escape($kreation['title']); ?></h1>
<div style="display:flex; align-items:center; margin-bottom:10px;">
  <?php
    if (!empty($kreation['profile_picture'])) {
        $profilePic = 'uploads/' . $kreation['profile_picture'];
    } else {
        $profilePic = 'uploads/placeholder.png'; 
    }
  ?>
  <img src="<?php echo escape($profilePic); ?>" alt="Profilbild" style="width:40px; height:40px; object-fit:cover; border-radius:50%; margin-right:10px;">
  <p>Erstellt von: <?php echo escape($kreation['username']); ?></p>
</div>
<?php if (!empty($kreation['description'])): ?>
  <p><?php echo nl2br(escape($kreation['description'])); ?></p>
<?php endif; ?>

<!-- Beispiel: Dateien anzeigen -->
<div class="row">
  <?php foreach ($files as $file): ?>
    <div class="col-md-4 mb-3">
      <div class="card">
        <?php if (strpos($file['file_type'], 'image/') === 0): ?>
          <img src="uploads/<?php echo escape($file['file_name']); ?>" class="card-img-top" alt="Datei">
        <?php else: ?>
          <div class="card-body">
            <p><?php echo escape($file['file_name']); ?></p>
          </div>
        <?php endif; ?>
        <div class="card-footer">
          <a href="download.php?file=<?php echo urlencode($file['file_name']); ?>" class="btn btn-success">Download</a>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
</div>
<?php include 'includes/footer.php'; ?>