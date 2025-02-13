<?php
// admin/manage_files.php
require_once '../includes/db.php';
require_once '../includes/functions.php';
secure_session_start();

if (!is_logged_in() || empty($_SESSION['is_admin'])) {
    header("Location: ../login.php");
    exit;
}

// Alle Dateien samt zugehöriger Kreation abrufen
$stmt = $pdo->query("SELECT f.id, f.kreation_id, f.file_name, f.file_type, f.is_thumbnail, f.uploaded_at, k.title AS kreation_title 
                      FROM kreation_files f 
                      JOIN kreationen k ON f.kreation_id = k.id 
                      ORDER BY f.uploaded_at DESC");
$files = $stmt->fetchAll();
?>
<?php include '../includes/header.php'; ?>
<div class="container mt-5">
  <h1 class="mb-4">Dateien verwalten</h1>
  <table class="table table-striped">
    <thead>
      <tr>
        <th>ID</th>
        <th>Kreation</th>
        <th>Dateiname</th>
        <th>Dateityp</th>
        <th>Thumbnail</th>
        <th>Hochgeladen am</th>
        <th>Aktionen</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($files as $file): ?>
      <tr>
        <td><?php echo escape($file['id']); ?></td>
        <td><?php echo escape($file['kreation_title']); ?></td>
        <td><?php echo escape($file['file_name']); ?></td>
        <td><?php echo escape($file['file_type']); ?></td>
        <td><?php echo ($file['is_thumbnail'] ? 'Ja' : 'Nein'); ?></td>
        <td><?php echo escape($file['uploaded_at']); ?></td>
        <td>
          <a href="../download.php?file=<?php echo urlencode($file['file_name']); ?>" class="btn btn-sm btn-success">Download</a>
          <a href="delete_file.php?id=<?php echo $file['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Datei wirklich löschen?')">Löschen</a>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php include '../includes/footer.php'; ?>