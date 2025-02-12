<?php
// index.php – Zeigt alle hochgeladenen Kreationen an (C1, C16)
require_once 'includes/db.php';
require_once 'includes/functions.php';
secure_session_start();

// Hole alle Kreationen (neuste zuerst)
$stmt = $pdo->prepare("SELECT k.id, k.title, k.image, u.username FROM kreationen k JOIN users u ON k.user_id = u.id ORDER BY k.created_at DESC");
$stmt->execute();
$kreationen = $stmt->fetchAll();
?>
<?php include 'header.php'; ?>
<h1>Hochgeladene Kreationen</h1>
<div>
    <?php foreach ($kreationen as $k): ?>
        <div style="border:1px solid #ccc; padding:10px; margin-bottom:10px;">
            <h2><?php echo escape($k['title']); ?></h2>
            <p>Erstellt von: <?php echo escape($k['username']); ?></p>
            <!-- Detailansicht (C16, C17, C18) -->
            <a href="view.php?id=<?php echo $k['id']; ?>">Ansehen</a>
        </div>
    <?php endforeach; ?>
</div>
<?php include 'footer.php'; ?>
