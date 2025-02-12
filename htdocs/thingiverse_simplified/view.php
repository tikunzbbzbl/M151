<?php
// view.php – Zeigt Details zu einer einzelnen Kreation (C16)
require_once 'db.php';
require_once 'functions.php';
secure_session_start();

// Serverseitige Validierung (C6)
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Ungültige Anfrage.");
}
$id = (int)$_GET['id'];

// Hole die Kreation (Prepared Statement, C19)
$stmt = $pdo->prepare("SELECT k.*, u.username FROM kreationen k JOIN users u ON k.user_id = u.id WHERE k.id = :id");
$stmt->bindValue(':id', $id, PDO::PARAM_INT);
$stmt->execute();
$kreation = $stmt->fetch();

if (!$kreation) {
    die("Kreation nicht gefunden.");
}
?>
<?php include 'header.php'; ?>
<h1><?php echo escape($kreation['title']); ?></h1>
<p>Erstellt von: <?php echo escape($kreation['username']); ?></p>
<?php if (!empty($kreation['description'])): ?>
    <p><?php echo nl2br(escape($kreation['description'])); ?></p>
<?php endif; ?>
<?php if (!empty($kreation['image'])): ?>
    <img src="uploads/<?php echo escape($kreation['image']); ?>" alt="<?php echo escape($kreation['title']); ?>" style="max-width:100%;">
<?php endif; ?>
<?php include 'footer.php'; ?>
