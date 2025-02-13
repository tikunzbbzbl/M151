<?php
// index.php – Anzeige aller Kreationen
require_once 'includes/db.php';
require_once 'includes/functions.php';
secure_session_start();

// Hole alle Kreationen (neuste zuerst)
$stmt = $pdo->prepare("SELECT k.id, k.title, u.username FROM kreationen k JOIN users u ON k.user_id = u.id ORDER BY k.created_at DESC");
$stmt->execute();
$kreationen = $stmt->fetchAll();
?>
<?php include 'includes/header.php'; ?>
<h1 class="my-4">Hochgeladene Kreationen</h1>
<div class="row">
  <?php foreach ($kreationen as $k): ?>
    <div class="col-md-4">
      <div class="card mb-4 shadow-sm">
        <?php 
          // Thumbnail für diese Kreation abrufen
          $stmtThumb = $pdo->prepare("SELECT file_name FROM kreation_files WHERE kreation_id = :kid AND is_thumbnail = 1 LIMIT 1");
          $stmtThumb->execute([':kid' => $k['id']]);
          $thumbnail = $stmtThumb->fetchColumn();
          if ($thumbnail):
        ?>
          <img src="uploads/<?php echo escape($thumbnail); ?>" class="card-img-top" alt="<?php echo escape($k['title']); ?>">
        <?php endif; ?>
        <div class="card-body">
          <h5 class="card-title"><?php echo escape($k['title']); ?></h5>
          <p class="card-text">Erstellt von: <?php echo escape($k['username']); ?></p>
          <a href="view.php?id=<?php echo $k['id']; ?>" class="btn btn-primary">Ansehen</a>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
</div>
<?php include 'includes/footer.php'; ?>
