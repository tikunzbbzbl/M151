<?php
// profil/edit_own_post.php
require_once '../includes/db.php';
require_once '../includes/functions.php';
secure_session_start();

if (!is_logged_in()) {
    header("Location: ../login.php");
    exit;
}

// Prüfen, ob eine ID übergeben wurde
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Ungültige Anfrage.");
}
$post_id = (int)$_GET['id'];

// Hole die Kreation, aber nur, wenn sie dem aktuell eingeloggten Benutzer gehört
$stmt = $pdo->prepare("
    SELECT id, user_id, title, description
    FROM kreationen
    WHERE id = :id AND user_id = :uid
");
$stmt->execute([':id' => $post_id, ':uid' => $_SESSION['user_id']]);
$post = $stmt->fetch();

if (!$post) {
    die("Kreation nicht gefunden oder du hast keine Berechtigung, sie zu bearbeiten.");
}

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    
    if (empty($title)) {
        $errors[] = "Der Titel darf nicht leer sein.";
    }
    
    if (empty($errors)) {
        // Aktualisiere Titel und Beschreibung
        $stmtUpdate = $pdo->prepare("
            UPDATE kreationen
            SET title = :title,
                description = :description
            WHERE id = :id AND user_id = :uid
        ");
        $stmtUpdate->execute([
            ':title'       => $title,
            ':description' => $description,
            ':id'          => $post_id,
            ':uid'         => $_SESSION['user_id']
        ]);
        
        $success = "Kreation erfolgreich aktualisiert.";
        
        // Lade die aktualisierten Daten neu
        $stmt = $pdo->prepare("
            SELECT id, user_id, title, description
            FROM kreationen
            WHERE id = :id AND user_id = :uid
        ");
        $stmt->execute([':id' => $post_id, ':uid' => $_SESSION['user_id']]);
        $post = $stmt->fetch();
    }
}
?>
<?php include '../includes/header.php'; ?>
<div class="container mt-5">
    <h1>Kreation bearbeiten</h1>
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <?php foreach ($errors as $error): ?>
                <p><?php echo escape($error); ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success">
            <p><?php echo escape($success); ?></p>
        </div>
    <?php endif; ?>
    <form action="edit_own_post.php?id=<?php echo $post_id; ?>" method="post">
        <div class="form-group">
            <label for="title">Titel:</label>
            <input type="text" name="title" id="title" class="form-control"
                   value="<?php echo escape($post['title']); ?>" required>
        </div>
        <div class="form-group">
            <label for="description">Beschreibung (optional):</label>
            <textarea name="description" id="description" class="form-control"><?php echo escape($post['description']); ?></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Speichern</button>
        <a href="profile_posts.php" class="btn btn-secondary">Abbrechen</a>
    </form>
</div>
<?php include '../includes/footer.php'; ?>