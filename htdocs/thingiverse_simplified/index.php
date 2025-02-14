<?php
// index.php – Anzeige aller Kreationen
require_once 'includes/db.php';
require_once 'includes/functions.php';
secure_session_start();

// Alle Kreationen inkl. Profilbild etc. abrufen
$stmt = $pdo->prepare("
    SELECT k.id, k.title, u.username, u.profile_picture 
    FROM kreationen k 
    JOIN users u ON k.user_id = u.id 
    ORDER BY k.created_at DESC
");
$stmt->execute();
$kreationen = $stmt->fetchAll();
?>
<?php include 'includes/header.php'; ?>
<h1 class="my-4">Posts von Benutzern</h1>
<div class="row">
  <?php foreach ($kreationen as $k): ?>
    <div class="col-md-4">
      <div class="card mb-4 shadow-sm">
        <?php 
          // Hole das Thumbnail – falls nicht vorhanden, hole alternativ die erste Bilddatei
          $stmtThumb = $pdo->prepare("SELECT file_name FROM kreation_files WHERE kreation_id = :kid AND is_thumbnail = 1 LIMIT 1");
          $stmtThumb->execute([':kid' => $k['id']]);
          $thumbnail = $stmtThumb->fetchColumn();
          if (!$thumbnail) {
              // Fallback: erste Bilddatei holen
              $stmtFallback = $pdo->prepare("SELECT file_name FROM kreation_files WHERE kreation_id = :kid AND file_type LIKE 'image/%' ORDER BY uploaded_at ASC LIMIT 1");
              $stmtFallback->execute([':kid' => $k['id']]);
              $thumbnail = $stmtFallback->fetchColumn();
          }
        ?>
        <?php if ($thumbnail): ?>
          <img src="uploads/<?php echo escape($thumbnail); ?>" class="card-img-top" alt="<?php echo escape($k['title']); ?>">
        <?php else: ?>
          <!-- Optional: Platzhalter anzeigen, falls gar kein Bild existiert -->
          <img src="uploads/placeholder.png" class="card-img-top" alt="Kein Bild vorhanden">
        <?php endif; ?>
        <div class="card-body">
          <h5 class="card-title"><?php echo escape($k['title']); ?></h5>
          <div class="d-flex align-items-center mb-2">
            <?php
              // Profilbild oder Platzhalter
              $profilePic = !empty($k['profile_picture']) ? 'uploads/' . $k['profile_picture'] : 'uploads/placeholder.png';
            ?>
            <img src="<?php echo escape($profilePic); ?>" alt="Profilbild" style="width:40px; height:40px; object-fit:cover; border-radius:50%; margin-right:10px;">
            <span><?php echo escape($k['username']); ?></span>
          </div>
          <a href="view.php?id=<?php echo $k['id']; ?>" class="btn btn-primary">Ansehen</a>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
</div>
<?php include 'includes/footer.php'; ?>
