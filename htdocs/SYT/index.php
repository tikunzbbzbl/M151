<?php
// index.php – Anzeige aller Kreationen (Posts) von Benutzern

// Binde die Datei ein, die die Datenbankverbindung herstellt.
// Dadurch können wir SQL-Abfragen an die Datenbank senden.
require_once 'includes/db.php';

// Binde die Datei mit gemeinsamen Hilfsfunktionen ein.
// Diese enthält beispielsweise Funktionen wie secure_session_start() zum sicheren Starten einer Session
// und escape() zur sicheren Ausgabe von Daten (Verhinderung von Cross-Site Scripting).
require_once 'includes/functions.php';

// Starte eine sichere Session, damit wir auf Session-Daten (wie den Login-Status) zugreifen können.
secure_session_start();

// Bereite eine SQL-Abfrage vor, um alle Kreationen (Posts) zusammen mit dem Benutzernamen und Profilbild des Erstellers abzurufen.
// Die Abfrage holt aus der Tabelle "kreationen" (alias k) die Spalten id und title und aus der Tabelle "users" (alias u)
// den Benutzernamen (username) sowie das Profilbild (profile_picture). Die Tabellen werden über den Fremdschlüssel (user_id) verbunden.
// Die Ergebnisse werden absteigend (DESC) nach dem Erstellungsdatum sortiert (die neusten Posts zuerst).
$stmt = $pdo->prepare("
    SELECT k.id, k.title, u.username, u.profile_picture 
    FROM kreationen k 
    JOIN users u ON k.user_id = u.id 
    ORDER BY k.created_at DESC
");

// Führe die Abfrage aus.
$stmt->execute();

// Hole alle Ergebnisse der Abfrage und speichere sie in einem Array namens $kreationen.
$kreationen = $stmt->fetchAll();
?>

<?php include 'includes/header.php'; ?>
<!-- Binde den Header ein, der das HTML-Grundgerüst, die Navigation und eingebundene CSS/Bootstrap enthält. -->

<h1 class="my-4">Posts von Benutzern</h1>

<!-- Starte einen Bootstrap-Grid-Container -->
<div class="row">
  <!-- Durchlaufe alle Posts (Kreationen), die im Array $kreationen gespeichert sind -->
  <?php foreach ($kreationen as $k): ?>
    <!-- Jede Kreation wird in einer Spalte mit einer Breite von 4 (von 12) angezeigt -->
    <div class="col-md-4">
      <!-- Erstelle eine Bootstrap Card zur Darstellung des Posts -->
      <div class="card mb-4 shadow-sm">
        <?php 
          // Versuche, das Thumbnail für die Kreation abzurufen.
          // Hier wird eine SQL-Abfrage vorbereitet, die in der Tabelle "kreation_files" nach einem Eintrag sucht,
          // bei dem die kreation_id dem aktuellen Post entspricht und das Flag is_thumbnail = 1 gesetzt ist.
          $stmtThumb = $pdo->prepare("SELECT file_name FROM kreation_files WHERE kreation_id = :kid AND is_thumbnail = 1 LIMIT 1");
          $stmtThumb->execute([':kid' => $k['id']]);
          // fetchColumn() gibt den Dateinamen des Thumbnails zurück, falls vorhanden.
          $thumbnail = $stmtThumb->fetchColumn();
          
          // Falls kein Thumbnail gefunden wurde...
          if (!$thumbnail) {
              // ...hole als Fallback die erste Bilddatei, bei der der Dateityp mit "image/" beginnt.
              $stmtFallback = $pdo->prepare("SELECT file_name FROM kreation_files WHERE kreation_id = :kid AND file_type LIKE 'image/%' ORDER BY uploaded_at ASC LIMIT 1");
              $stmtFallback->execute([':kid' => $k['id']]);
              $thumbnail = $stmtFallback->fetchColumn();
          }
        ?>
        <?php if ($thumbnail): ?>
          <!-- Falls ein Thumbnail oder Fallback-Bild gefunden wurde,
               wird dieses Bild aus dem Ordner "uploads" geladen und in der Card angezeigt. -->
          <img src="uploads/<?php echo escape($thumbnail); ?>" class="card-img-top" alt="<?php echo escape($k['title']); ?>">
        <?php else: ?>
          <!-- Falls kein Bild vorhanden ist, wird ein Platzhalterbild angezeigt. -->
          <img src="uploads/placeholder.png" class="card-img-top" alt="Kein Bild vorhanden">
        <?php endif; ?>
        <div class="card-body">
          <!-- Zeige den Titel der Kreation in der Card an -->
          <h5 class="card-title"><?php echo escape($k['title']); ?></h5>
          <!-- Container für den Ersteller des Posts -->
          <div class="d-flex align-items-center mb-2">
            <?php
              // Bestimme das Profilbild des Erstellers.
              // Falls ein Profilbild vorhanden ist, verwende es aus dem Ordner "uploads".
              // Ansonsten verwende einen Platzhalter.
              $profilePic = !empty($k['profile_picture']) ? 'uploads/' . $k['profile_picture'] : 'uploads/placeholder.png';
            ?>
            <!-- Zeige das Profilbild in kleiner Größe als rundes Bild an -->
            <img src="<?php echo escape($profilePic); ?>" alt="Profilbild" style="width:40px; height:40px; object-fit:cover; border-radius:50%; margin-right:10px;">
            <!-- Zeige den Benutzernamen an -->
            <span><?php echo escape($k['username']); ?></span>
          </div>
          <!-- Link zur Detailansicht des Posts -->
          <a href="view.php?id=<?php echo $k['id']; ?>" class="btn btn-primary">Ansehen</a>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<?php include 'includes/footer.php'; ?>
<!-- Binde den Footer ein, der das Ende des HTML-Dokuments enthält -->
