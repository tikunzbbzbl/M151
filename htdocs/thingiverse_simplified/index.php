<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';
secure_session_start();

// Hole alle Kreationen (inkl. Profilbild des Erstellers)
$stmt = $pdo->prepare("
    SELECT 
      k.id, 
      k.title, 
      u.username, 
      u.profile_picture 
    FROM kreationen k 
    JOIN users u ON k.user_id = u.id 
    ORDER BY k.created_at DESC
");
$stmt->execute();
$kreationen = $stmt->fetchAll();
?>
<?php include 'includes/header.php'; ?>
<h1>Posts</h1>
<div class="row">
  <?php foreach ($kreationen as $k): ?>
    <div class="col-md-4">
      <div class="card mb-4 shadow-sm">
        <?php
          // Thumbnail holen
          $stmtThumb = $pdo->prepare("SELECT file_name FROM kreation_files WHERE kreation_id = :kid AND is_thumbnail = 1 LIMIT 1");
          $stmtThumb->execute([':kid' => $k['id']]);
          $thumbnail = $stmtThumb->fetchColumn();
        ?>
        <?php if ($thumbnail): ?>
          <img src="uploads/<?php echo escape($thumbnail); ?>" class="card-img-top" alt="<?php echo escape($k['title']); ?>">
        <?php endif; ?>
        <div class="card-body">
          <h5 class="card-title"><?php echo escape($k['title']); ?></h5>
          <div style="display:flex; align-items:center; margin-bottom:10px;">
            <?php
              // Profilbild oder Platzhalter
              if (!empty($k['profile_picture'])) {
                  $profilePic = 'uploads/' . $k['profile_picture'];
              } else {
                  $profilePic = 'uploads/placeholder.png'; 
              }
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