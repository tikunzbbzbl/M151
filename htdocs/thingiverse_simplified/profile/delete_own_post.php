<?php
// profil/delete_own_post.php
require_once '../includes/db.php';
require_once '../includes/functions.php';
secure_session_start();

if (!is_logged_in()) {
    header("Location: ../login.php");
    exit;
}

// Prüfe, ob eine gültige ID übergeben wurde
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Ungültige Anfrage.");
}
$post_id = (int)$_GET['id'];

// Prüfe, ob der Post zum aktuell eingeloggten Benutzer gehört
$stmt = $pdo->prepare("SELECT id FROM kreationen WHERE id = :id AND user_id = :uid");
$stmt->execute([':id' => $post_id, ':uid' => $_SESSION['user_id']]);
$post = $stmt->fetch();

if (!$post) {
    die("Kreation nicht gefunden oder keine Berechtigung.");
}

// Alle zu diesem Post gehörenden Dateien abrufen
$stmtFiles = $pdo->prepare("SELECT file_name FROM kreation_files WHERE kreation_id = :kid");
$stmtFiles->execute([':kid' => $post_id]);
$files = $stmtFiles->fetchAll();

// Jede Datei aus dem Dateisystem löschen
foreach ($files as $file) {
    $filePath = '../uploads/' . $file['file_name'];
    if (file_exists($filePath)) {
        unlink($filePath);
    }
}

// Lösche die Dateieinträge in der Datenbank
$stmtDelFiles = $pdo->prepare("DELETE FROM kreation_files WHERE kreation_id = :kid");
$stmtDelFiles->execute([':kid' => $post_id]);

// Lösche den Post (Kreation)
$stmtDelPost = $pdo->prepare("DELETE FROM kreationen WHERE id = :id AND user_id = :uid");
$stmtDelPost->execute([':id' => $post_id, ':uid' => $_SESSION['user_id']]);

header("Location: profile_posts.php");
exit;
?>
