<?php
// view.php – Detailansicht einer Kreation
require_once 'includes/db.php';
require_once 'includes/functions.php';
secure_session_start();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
  die("Ungültige Anfrage.");
}
$kreation_id = (int)$_GET['id'];

$stmt = $pdo->prepare("SELECT k.id, k.title, k.description, u.username 
                       FROM kreationen k 
                       JOIN users u ON k.user_id = u.id 
                       WHERE k.id = :id");
$stmt->execute([':id' => $kreation_id]);
$kreation = $stmt->fetch();

if (!$kreation) {
  die("Kreation nicht gefunden.");
}

$stmtFiles = $pdo->prepare("SELECT file_name, file_type, is_thumbnail, uploaded_at FROM kreation_files WHERE kreation_id = :kid");
$stmtFiles->execute([':kid' => $kreation_id]);
$files = $stmtFiles->fetchAll();
?>
<?php include 'includes/header.php'; ?>
<div class="row">
  <div class="col-md-10 offset-md-1">
    <h1 class="my-4"><?php echo escape($kreation['title']); ?></h1>
    <p class="lead">Hochgeladen von: <?php echo escape($kreation['username']); ?></p>
    <?php if (!empty($kreation['description'])): ?>
      <p><?php echo nl2br(escape($kreation['description'])); ?></p>
    <?php endif; ?>
    <hr>
    <h2>Dateien</h2>
    <?php if ($files): ?>
      <div class="row">
        <?php foreach ($files as $file): ?>
          <div class="col-md-6 mb-4">
            <div class="card">
              <?php if (strpos($file['file_type'], 'image/') === 0): ?>
                <img src="uploads/<?php echo escape($file['file_name']); ?>" class="card-img-top" alt="Datei">
              <?php else: ?>
                <div class="card-body">
                  <p>Datei: <?php echo escape($file['file_name']); ?></p>
                </div>
              <?php endif; ?>
              <div class="card-footer text-center">
                <a href="download.php?file=<?php echo urlencode($file['file_name']); ?>" class="btn btn-success">Download</a>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <p>Keine Dateien gefunden.</p>
    <?php endif; ?>
  </div>
</div>
<?php include 'includes/footer.php'; ?>