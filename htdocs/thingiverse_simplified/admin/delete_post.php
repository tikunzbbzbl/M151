<?php
// admin/delete_post.php
require_once '../includes/db.php';
require_once '../includes/functions.php';
secure_session_start();

if (!is_logged_in() || empty($_SESSION['is_admin'])) {
    header("Location: ../login.php");
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Ungültige Anfrage.");
}

$post_id = (int)$_GET['id'];

// Optional: Existenz prüfen
$stmt = $pdo->prepare("SELECT * FROM kreationen WHERE id = :id");
$stmt->execute([':id' => $post_id]);
$post = $stmt->fetch();

if (!$post) {
    die("Post nicht gefunden.");
}

// Post löschen (und damit automatisch zugehörige Dateien)
$stmt = $pdo->prepare("DELETE FROM kreationen WHERE id = :id");
$stmt->execute([':id' => $post_id]);

header("Location: manage_posts.php");
exit;
?>