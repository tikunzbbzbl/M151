<?php
// admin/manage_kreationen.php

// Binde die Datei ein, die die Datenbankverbindung herstellt.
// Dadurch können wir SQL-Abfragen an die Datenbank senden.
require_once '../includes/db.php';

// Binde die Datei mit den gemeinsamen Hilfsfunktionen ein.
// Diese Datei enthält Funktionen wie secure_session_start() und escape(), um Ausgaben zu sichern.
require_once '../includes/functions.php';

// Starte eine sichere Session, damit wir auf Session-Daten (wie den Login-Status) zugreifen können.
secure_session_start();

// Überprüfe, ob der Benutzer eingeloggt ist und ob er Admin-Rechte besitzt.
// Falls nicht, wird der Benutzer zur Login-Seite weitergeleitet und das Skript beendet.
if (!is_logged_in() || empty($_SESSION['is_admin'])) {
    header("Location: ../login.php");
    exit;
}

// Führe eine SQL-Abfrage aus, um alle Kreationen (Posts) zusammen mit den zugehörigen Benutzerinformationen abzurufen.
// Die Abfrage wählt folgende Felder aus:
// k.id, k.title, k.description, k.created_at aus der Tabelle "kreationen"
// u.username aus der Tabelle "users"
// Die Tabellen werden über die Bedingung JOIN users u ON k.user_id = u.id verbunden.
$stmt = $pdo->query("SELECT k.id, k.title, k.description, k.created_at, u.username 
                      FROM kreationen k 
                      JOIN users u ON k.user_id = u.id 
                      ORDER BY k.created_at DESC");

// Speichere alle abgefragten Datensätze in der Variable $kreationen.
// fetchAll() liefert ein Array mit allen Zeilen aus der Abfrage.
$kreationen = $stmt->fetchAll();
?>
<?php include '../includes/header.php'; ?>
<div class="container mt-5">
  <!-- Überschrift der Seite -->
  <h1 class="mb-4">Posts verwalten</h1>
  <!-- Tabelle mit Bootstrap-Klassen, um die Posts anzuzeigen -->
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
      <!-- Schleife über alle Kreationen, die in $kreationen gespeichert sind -->
      <?php foreach ($kreationen as $kreation): ?>
      <tr>
        <!-- Zeige die ID der Kreation an -->
        <td><?php echo escape($kreation['id']); ?></td>
        <!-- Zeige den Titel der Kreation an -->
        <td><?php echo escape($kreation['title']); ?></td>
        <!-- Zeige den Benutzernamen des Erstellers an -->
        <td><?php echo escape($kreation['username']); ?></td>
        <!-- Zeige das Erstellungsdatum der Kreation an -->
        <td><?php echo escape($kreation['created_at']); ?></td>
        <td>
          <!-- Aktionen, die der Admin ausführen kann -->
          <!-- "Ansehen" leitet zur Detailansicht der Kreation (view.php) weiter.
               Der Link nutzt die Kreations-ID, um die richtige Kreation anzuzeigen. -->
          <a href="../view.php?id=<?php echo $kreation['id']; ?>" class="btn btn-sm btn-info">Ansehen</a>
          <!-- "Bearbeiten" leitet zur Bearbeitungsseite (edit_post.php) weiter,
               wo der Admin den Post ändern kann. -->
          <a href="edit_post.php?id=<?php echo $kreation['id']; ?>" class="btn btn-sm btn-primary">Bearbeiten</a>
          <!-- "Löschen" ruft die Löschseite (delete_post.php) auf, um den Post zu entfernen.
               Das onclick-Attribut fragt den Benutzer per Bestätigungsdialog, ob er fortfahren möchte. -->
          <a href="delete_post.php?id=<?php echo $kreation['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Diese Kreation wirklich löschen?')">Löschen</a>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php include '../includes/footer.php'; ?>
