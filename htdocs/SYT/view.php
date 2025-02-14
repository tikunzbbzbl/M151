<?php
// view.php – Detailansicht einer Kreation

// Binde die Datei ein, die die Datenbankverbindung herstellt.
// Dadurch können wir SQL-Abfragen an die Datenbank senden.
require_once 'includes/db.php';

// Binde die Datei mit den gemeinsamen Hilfsfunktionen ein.
// Diese enthält Funktionen wie secure_session_start() und escape() (zum sicheren Ausgeben von Daten).
require_once 'includes/functions.php';

// Starte eine sichere Session, damit wir auf Session-Daten (z.B. Login-Status) zugreifen können.
secure_session_start();

// Überprüfe, ob der GET-Parameter "id" vorhanden und numerisch ist.
// Diese ID steht für die Kreation, die angezeigt werden soll.
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Ungültige Anfrage.");
}
// Konvertiere den GET-Parameter in einen Integer und speichere ihn in der Variablen $id.
$id = (int)$_GET['id'];

// Bereite eine SQL-Abfrage vor, um die Kreation und die zugehörigen Erstellerinformationen abzurufen.
// Die Abfrage wählt alle Spalten aus der Tabelle "kreationen" (alias k) und zusätzlich
// den Benutzernamen sowie das Profilbild des Erstellers aus der Tabelle "users" (alias u).
// Die Tabellen werden über den Fremdschlüssel k.user_id = u.id verbunden.
$stmt = $pdo->prepare("
    SELECT k.*, u.username, u.profile_picture
    FROM kreationen k
    JOIN users u ON k.user_id = u.id
    WHERE k.id = :id
");
// Führe die Abfrage aus und ersetze den Platzhalter :id durch den Wert von $id.
$stmt->execute([':id' => $id]);
// Speichere den abgerufenen Datensatz in der Variablen $kreation.
$kreation = $stmt->fetch();

// Falls keine Kreation gefunden wurde, beende das Skript mit einer Fehlermeldung.
if (!$kreation) {
    die("Kreation nicht gefunden.");
}

// Bereite eine SQL-Abfrage vor, um alle Dateien abzurufen, die zu dieser Kreation gehören.
// Es werden die Spalten file_name, file_type und is_thumbnail aus der Tabelle "kreation_files" ausgewählt.
// Die Ergebnisse werden nach dem Hochladedatum (uploaded_at) in aufsteigender Reihenfolge sortiert.
$stmtFiles = $pdo->prepare("SELECT file_name, file_type, is_thumbnail FROM kreation_files WHERE kreation_id = :kid ORDER BY uploaded_at ASC");
// Führe die Abfrage aus, wobei :kid durch die ID der Kreation ersetzt wird.
$stmtFiles->execute([':kid' => $id]);
// Speichere alle abgerufenen Datensätze in der Variable $files.
$files = $stmtFiles->fetchAll();
?>
<?php include 'includes/header.php'; ?>
<!-- Binde den Header ein, der das HTML-Grundgerüst, Navigation und CSS (z.B. Bootstrap) enthält. -->

<!-- Zeige den Titel der Kreation als Hauptüberschrift an -->
<h1><?php echo escape($kreation['title']); ?></h1>

<!-- Bereich zur Anzeige der Erstellerinformationen -->
<div class="d-flex align-items-center mb-3">
  <?php
    // Bestimme das Profilbild des Erstellers.
    // Falls ein Profilbild vorhanden ist, wird es aus dem Ordner "uploads" geladen;
    // andernfalls wird ein Platzhalterbild verwendet.
    $profilePic = !empty($kreation['profile_picture']) ? 'uploads/' . $kreation['profile_picture'] : 'uploads/placeholder.png';
  ?>
  <!-- Zeige das Profilbild als kleines, rundes Bild an -->
  <img src="<?php echo escape($profilePic); ?>" alt="Profilbild" style="width:40px; height:40px; object-fit:cover; border-radius:50%; margin-right:10px;">
  <!-- Zeige den Benutzernamen des Erstellers an -->
  <p>Erstellt von: <?php echo escape($kreation['username']); ?></p>
</div>

<!-- Falls eine Beschreibung vorhanden ist, wird diese hier angezeigt -->
<?php if (!empty($kreation['description'])): ?>
  <p><?php echo nl2br(escape($kreation['description'])); ?></p>
<?php endif; ?>

<!-- Überschrift für den Dateien-Bereich -->
<h2>Dateien</h2>
<div class="row">
  <!-- Durchlaufe alle Dateien, die zu dieser Kreation gehören -->
  <?php foreach ($files as $file): ?>
    <div class="col-md-4 mb-3">
      <!-- Verwende eine Bootstrap Card zur Darstellung der Datei -->
      <div class="card">
        <?php 
          // Überprüfe, ob der Dateityp der Datei mit "image/" beginnt,
          // was darauf hinweist, dass es sich um ein Bild handelt.
          if (strpos($file['file_type'], 'image/') === 0):
        ?>
          <!-- Wenn es ein Bild ist, zeige es als Bild in der Card an -->
          <img src="uploads/<?php echo escape($file['file_name']); ?>" class="card-img-top" alt="Datei">
        <?php else: ?>
          <!-- Falls es kein Bild ist, zeige stattdessen den Dateinamen in der Card an -->
          <div class="card-body">
            <p>Datei: <?php echo escape($file['file_name']); ?></p>
          </div>
        <?php endif; ?>
        <!-- Footer der Card mit einem Download-Button -->
        <div class="card-footer text-center">
          <!-- Der Download-Link ruft download.php auf und übergibt den Dateinamen als GET-Parameter.
               urlencode() sorgt dafür, dass Sonderzeichen korrekt codiert werden. -->
          <a href="download.php?file=<?php echo urlencode($file['file_name']); ?>" class="btn btn-success">Download</a>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<?php include 'includes/footer.php'; ?>
<!-- Binde den Footer ein, der das Ende des HTML-Dokuments darstellt -->
