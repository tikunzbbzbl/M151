<?php
// admin/manage_users.php
require_once '../includes/db.php';
require_once '../includes/functions.php';
secure_session_start();

if (!is_logged_in() || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: ../login.php");
    exit;
}

$stmt = $pdo->query("SELECT id, username, email, created_at FROM users ORDER BY created_at DESC");
$users = $stmt->fetchAll();
?>
<?php include '../includes/header.php'; ?>
<div class="container mt-5">
    <h1 class="mb-4">Benutzer verwalten</h1>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Benutzername</th>
                <th>Email</th>
                <th>Registriert am</th>
                <th>Aktionen</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
            <tr>
                <td><?php echo escape($user['id']); ?></td>
                <td><?php echo escape($user['username']); ?></td>
                <td><?php echo escape($user['email']); ?></td>
                <td><?php echo escape($user['created_at']); ?></td>
                <td>
                    <!-- Beispielaktionen: Bearbeiten oder Löschen -->
                    <a href="edit_user.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-primary">Bearbeiten</a>
                    <a href="delete_user.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Benutzer wirklich löschen?')">Löschen</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php include '../includes/footer.php'; ?>