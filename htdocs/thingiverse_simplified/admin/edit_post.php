<?php
// admin/edit_post.php
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

// Post-Daten abrufen
$stmt = $pdo->prepare("SELECT * FROM kreationen WHERE id = :id");
$stmt->execute([':id' => $post_id]);
$post = $stmt->fetch();

if (!$post) {
    die("Post nicht gefunden.");
}

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    
    if (empty($title)) {
        $errors[] = "Titel darf nicht leer sein.";
    }
    
    if (empty($errors)) {
        $stmt = $pdo->prepare("UPDATE kreationen SET title = :title, description = :description WHERE id = :id");
        $stmt->execute([
            ':title' => $title,
            ':description' => $description,
            ':id' => $post_id
        ]);
        $success = "Post erfolgreich aktualisiert.";
        
        // Daten neu laden
        $stmt = $pdo->prepare("SELECT * FROM kreationen WHERE id = :id");
        $stmt->execute([':id' => $post_id]);
        $post = $stmt->fetch();
    }
}
?>
<?php include '../includes/header.php'; ?>
<div class="container mt-5">
    <h1>Post bearbeiten</h1>
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
    <form action="edit_post.php?id=<?php echo $post_id; ?>" method="post">
        <div class="form-group">
            <label for="title">Titel</label>
            <input type="text" name="title" id="title" class="form-control" value="<?php echo escape($post['title']); ?>" required>
        </div>
        <div class="form-group">
            <label for="description">Beschreibung</label>
            <textarea name="description" id="description" class="form-control"><?php echo escape($post['description']); ?></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Speichern</button>
        <a href="manage_kreationen.php" class="btn btn-secondary">Abbrechen</a>
    </form>
</div>
<?php include '../includes/footer.php'; ?>