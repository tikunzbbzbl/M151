<?php
// index.php – Anzeige aller Kreationen mit Thumbnail
require_once 'includes/db.php';
require_once 'includes/functions.php';
secure_session_start();

// Alle Kreationen (neuste zuerst) abrufen
$stmt = $pdo->prepare("SELECT k.id, k.title, u.username FROM kreationen k JOIN users u ON k.user_id = u.id ORDER BY k.created_at DESC");
$stmt->execute();
$kreationen = $stmt->fetchAll();
?>
<?php include 'includes/header.php'; ?>
<h1>Hochgeladene Kreationen</h1>
<div>
    <?php foreach ($kreationen as $k): ?>
        <div style="border:1px solid #ccc; padding:10px; margin-bottom:10px;">
            <h2><?php echo escape($k['title']); ?></h2>
            <?php 
                // Thumbnail für diese Kreation abrufen (nur das erste mit is_thumbnail = 1)
                $stmtThumb = $pdo->prepare("SELECT file_name FROM kreation_files WHERE kreation_id = :kid AND is_thumbnail = 1 LIMIT 1");
                $stmtThumb->execute([':kid' => $k['id']]);
                $thumbnail = $stmtThumb->fetchColumn();
                if ($thumbnail):
            ?>
                <img src="uploads/<?php echo escape($thumbnail); ?>" alt="<?php echo escape($k['title']); ?>" style="max-width:150px; display:block;">
            <?php endif; ?>
            <p>Erstellt von: <?php echo escape($k['username']); ?></p>
            <a href="view.php?id=<?php echo $k['id']; ?>">Ansehen</a>
        </div>
    <?php endforeach; ?>
</div>
<?php include 'includes/footer.php'; ?>
