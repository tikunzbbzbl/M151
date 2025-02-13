<?php
// profil/profile_posts.php
require_once '../includes/db.php';
require_once '../includes/functions.php';
secure_session_start();

if (!is_logged_in()) {
    header("Location: ../login.php");
    exit;
}

// Alle Kreationen des aktuell eingeloggten Benutzers abrufen
$stmt = $pdo->prepare("
    SELECT id, title, description, created_at
    FROM kreationen
    WHERE user_id = :uid
    ORDER BY created_at DESC
");
$stmt->execute([':uid' => $_SESSION['user_id']]);
$posts = $stmt->fetchAll();
?>
<?php include '../includes/header.php'; ?>
<div class="container mt-5">
    <h1>Meine Posts</h1>
    <?php if ($posts): ?>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Titel</th>
                    <th>Erstellt am</th>
                    <th>Aktionen</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($posts as $p): ?>
                <tr>
                    <td><?php echo escape($p['id']); ?></td>
                    <td><?php echo escape($p['title']); ?></td>
                    <td><?php echo escape($p['created_at']); ?></td>
                    <td>
                        <a href="../view.php?id=<?php echo $p['id']; ?>" class="btn btn-sm btn-info">Ansehen</a>
                        <a href="edit_own_post.php?id=<?php echo $p['id']; ?>" class="btn btn-sm btn-primary">Bearbeiten</a>
                        <a href="delete_own_post.php?id=<?php echo $p['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Kreation wirklich löschen?')">Löschen</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Du hast noch keine Kreationen hochgeladen.</p>
    <?php endif; ?>
</div>
<?php include '../includes/footer.php'; ?>