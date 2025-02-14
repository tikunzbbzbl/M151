<?php
// admin/manage_files.php

// Binde die Datei ein, die die Datenbankverbindung herstellt.
// Dadurch können wir SQL-Abfragen ausführen.
require_once '../includes/db.php';

// Binde die Datei mit gemeinsamen Hilfsfunktionen ein.
// Diese enthält Funktionen wie secure_session_start() und escape(), um Ausgaben zu sichern.
require_once '../includes/functions.php';

// Starte eine sichere Session, damit wir auf Session-Daten (wie Login-Status) zugreifen können.
secure_session_start();

// Überprüfe, ob der Benutzer eingeloggt ist und ob er Admin-Rechte besitzt.
// Falls nicht, leite ihn zur Login-Seite weiter und beende das Skript.
if (!is_logged_in() || empty($_SESSION['is_admin'])) {
    header("Location: ../login.php");
    exit;
}

// Führe eine SQL-Abfrage aus, um alle Dateien (aus der Tabelle "kreation_files")
// zusammen mit der zugehörigen Kreation (aus der Tabelle "kreationen") abzurufen.
// Es werden folgende Felder ausgewählt:
// f.id: Die ID der Datei,
// f.kreation_id: Die ID der zugehörigen Kreation,
// f.file_name: Der Name der hochgeladenen Datei,
// f.file_type: Der MIME-Typ der Datei,
// f.is_thumbnail: Flag, ob die Datei als Thumbnail markiert ist,
// f.uploaded_at: Das Datum der Datei-Hochladung,
// k.title AS kreation_title: Der Titel der zugehörigen Kreation (um ihn später anzuzeigen).
$stmt = $pdo->query("SELECT f.id, f.kreation_id, f.file_name, f.file_type, f.is_thumbnail, f.uploaded_at, k.title AS kreation_title 
                      FROM kreation_files f 
                      JOIN kreationen k ON f.kreation_id = k.id 
                      ORDER BY f.uploaded_at DESC");
// Alle Datensätze werden in einem Array gespeichert.
$files = $stmt->fetchAll();
?>
<?php include '../includes/header.php'; ?>
<div class="container mt-5">
  <!-- Überschrift der Seite -->
  <h1 class="mb-4">Dateien verwalten</h1>
  <!-- Erstelle eine Tabelle mit Bootstrap-Klassen, um die Dateien anzuzeigen -->
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
      <!-- Schleife über alle abgerufenen Dateien -->
      <?php foreach ($files as $file): ?>
      <tr>
        <!-- Zeige die ID der Datei -->
        <td><?php echo escape($file['id']); ?></td>
        <!-- Zeige den Titel der zugehörigen Kreation an -->
        <td><?php echo escape($file['kreation_title']); ?></td>
        <!-- Zeige den Dateinamen an -->
        <td><?php echo escape($file['file_name']); ?></td>
        <!-- Zeige den Dateityp (z. B. image/jpeg) an -->
        <td><?php echo escape($file['file_type']); ?></td>
        <!-- Zeige, ob die Datei als Thumbnail markiert ist ("Ja" wenn true, sonst "Nein") -->
        <td><?php echo ($file['is_thumbnail'] ? 'Ja' : 'Nein'); ?></td>
        <!-- Zeige das Datum, an dem die Datei hochgeladen wurde -->
        <td><?php echo escape($file['uploaded_at']); ?></td>
        <td>
          <!-- Download-Link: Ruft download.php mit dem Dateinamen als Parameter auf.
               urlencode() stellt sicher, dass Sonderzeichen korrekt übertragen werden. -->
          <a href="../download.php?file=<?php echo urlencode($file['file_name']); ?>" class="btn btn-sm btn-success">Download</a>
          <!-- Löschen-Link: Ruft delete_file.php mit der Datei-ID auf.
               Der onclick-Handler zeigt ein Bestätigungsdialog an. -->
          <a href="delete_file.php?id=<?php echo $file['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Datei wirklich löschen?')">Löschen</a>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php include '../includes/footer.php'; ?>
