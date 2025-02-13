<?php
// admin/delete_user.php
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

$delete_id = (int)$_GET['id'];

// Optional: verhindern, dass sich der Admin selbst löscht
if ($delete_id === $_SESSION['user_id']) {
    die("Du kannst dich nicht selbst löschen.");
}

// Existenz des Benutzers prüfen
$stmt = $pdo->prepare("SELECT id FROM users WHERE id = :id");
$stmt->execute([':id' => $delete_id]);
$user = $stmt->fetch();

if (!$user) {
    die("Benutzer nicht gefunden.");
}

// Benutzer löschen
$stmtDelete = $pdo->prepare("DELETE FROM users WHERE id = :id");
$stmtDelete->execute([':id' => $delete_id]);

header("Location: manage_users.php");
exit;
?>