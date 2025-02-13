<?php
// admin/manage_kreationen.php
require_once '../includes/db.php';
require_once '../includes/functions.php';
secure_session_start();

if (!is_logged_in() || empty($_SESSION['is_admin'])) {
    header("Location: ../login.php");
    exit;
}

// Alle Kreationen samt Benutzerinformationen abrufen
$stmt = $pdo->query("SELECT k.id, k.title, k.description, k.created_at, u.username 
                      FROM kreationen k 
                      JOIN users u ON k.user_id = u.id 
                      ORDER BY k.created_at DESC");
$kreationen = $stmt->fetchAll();
?>
<?php include '../includes/header.php'; ?>
<div class="container mt-5">
  <h1 class="mb-4">Posts verwalten</h1>
  <table class="table table-striped">
    <thead>
      <tr>
        <th>ID</th>
        <th>Titel</th>
        <th>Erstellt von</th>
        <th>Erstellt am</th>
        <th>Aktionen</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($kreationen as $kreation): ?>
      <tr>
        <td><?php echo escape($kreation['id']); ?></td>
        <td><?php echo escape($kreation['title']); ?></td>
        <td><?php echo escape($kreation['username']); ?></td>
        <td><?php echo escape($kreation['created_at']); ?></td>
        <td>
          <!-- Beispielaktionen: Ansehen, Bearbeiten, Löschen -->
          <a href="../view.php?id=<?php echo $kreation['id']; ?>" class="btn btn-sm btn-info">Ansehen</a>
          <a href="edit_post.php?id=<?php echo $kreation['id']; ?>" class="btn btn-sm btn-primary">Bearbeiten</a>
          <a href="delete_post.php?id=<?php echo $kreation['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Diese Kreation wirklich löschen?')">Löschen</a>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php include '../includes/footer.php'; ?>