<?php
// view.php – Detailansicht einer Kreation
require_once 'includes/db.php';
require_once 'includes/functions.php';
secure_session_start();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Ungültige Anfrage.");
}
$kreation_id = (int)$_GET['id'];

// Hole die Kreation (Titel, Beschreibung, Ersteller etc.)
$stmt = $pdo->prepare("SELECT k.id, k.title, k.description, u.username 
                       FROM kreationen k 
                       JOIN users u ON k.user_id = u.id 
                       WHERE k.id = :id");
$stmt->execute([':id' => $kreation_id]);
$kreation = $stmt->fetch();

if (!$kreation) {
    die("Kreation nicht gefunden.");
}

// Hole alle Dateien zu dieser Kreation
$stmtFiles = $pdo->prepare("SELECT file_name, file_type, is_thumbnail, uploaded_at FROM kreation_files WHERE kreation_id = :kid");
$stmtFiles->execute([':kid' => $kreation_id]);
$files = $stmtFiles->fetchAll();
?>
<?php include 'includes/header.php'; ?>
<h1><?php echo escape($kreation['title']); ?></h1>
<p>Erstellt von: <?php echo escape($kreation['username']); ?></p>
<?php if (!empty($kreation['description'])): ?>
    <p><?php echo nl2br(escape($kreation['description'])); ?></p>
<?php endif; ?>

<h2>Dateien:</h2>
<div>
    <?php if ($files): ?>
        <?php foreach ($files as $file): ?>
            <div style="margin-bottom:20px; border:1px solid #ccc; padding:10px;">
                <?php 
                // Wenn es sich um ein Bild handelt, direkt anzeigen
                if (strpos($file['file_type'], 'image/') === 0): ?>
                    <img src="uploads/<?php echo escape($file['file_name']); ?>" alt="Datei" style="max-width:300px; display:block;">
                <?php else: ?>
                    <p>Datei: <?php echo escape($file['file_name']); ?></p>
                <?php endif; ?>
                <!-- Download-Button -->
                <a href="download.php?file=<?php echo urlencode($file['file_name']); ?>" download>
                    <button>Download</button>
                </a>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>Keine Dateien gefunden.</p>
    <?php endif; ?>
</div>
<?php include 'includes/footer.php'; ?>
