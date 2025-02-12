<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user_id'])) {
    echo "❌ Zugriff verweigert. Bitte <a href='?page=login'>anmelden</a>.";
    exit();
}

// C16: Benutzer kann Daten speichern
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["content"])) {
    $content = htmlspecialchars($_POST["content"]);
    $userId = $_SESSION['user_id'];

    $stmt = $db->prepare("INSERT INTO user_content (user_id, content) VALUES (?, ?)");
    $stmt->bind_param("is", $userId, $content);
    $stmt->execute();
}

// C17, C18: Benutzer kann nur eigene Inhalte ändern/löschen
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["delete_content"])) {
    $contentId = $_POST["content_id"];
    $userId = $_SESSION['user_id'];

    $stmt = $db->prepare("DELETE FROM user_content WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $contentId, $userId);
    $stmt->execute();
}
?>