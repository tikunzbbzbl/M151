<?php
// profil/edit_own_post.php

// Binde die Datei ein, die die Verbindung zur Datenbank herstellt.
// Dadurch können wir SQL-Abfragen durchführen.
require_once '../includes/db.php';

// Binde die Datei mit den gemeinsamen Hilfsfunktionen ein.
// Diese Funktionen beinhalten beispielsweise "secure_session_start()" zum Starten einer sicheren Session
// und "escape()" zum sicheren Ausgeben von Daten.
require_once '../includes/functions.php';

// Starte eine sichere Session, um auf Session-Daten (wie z. B. den Login-Status) zugreifen zu können.
secure_session_start();

// Überprüfe, ob der Benutzer eingeloggt ist.
// Falls der Benutzer nicht eingeloggt ist, leite ihn zur Login-Seite weiter und beende das Skript.
if (!is_logged_in()) {
    header("Location: ../login.php");
    exit;
}

// Überprüfe, ob eine gültige Post-ID übergeben wurde.
// Diese ID wird per GET-Parameter "id" übermittelt und muss numerisch sein.
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Ungültige Anfrage.");
}

// Konvertiere den GET-Parameter "id" in einen Integer und speichere ihn in der Variablen $post_id.
$post_id = (int)$_GET['id'];

// Bereite eine SQL-Abfrage vor, um den Post (die Kreation) abzurufen,
// aber nur, wenn der Post dem aktuell eingeloggten Benutzer gehört.
// Dies stellt sicher, dass Benutzer nur ihre eigenen Kreationen bearbeiten können.
$stmt = $pdo->prepare("
    SELECT id, user_id, title, description
    FROM kreationen
    WHERE id = :id AND user_id = :uid
");
// Führe die Abfrage aus, wobei :id durch $post_id und :uid durch die aktuell eingeloggte Benutzer-ID ersetzt wird.
$stmt->execute([':id' => $post_id, ':uid' => $_SESSION['user_id']]);
// Speichere den abgerufenen Datensatz in der Variablen $post.
$post = $stmt->fetch();

// Wenn kein Post gefunden wurde (d. h. entweder existiert er nicht oder gehört nicht dem aktuellen Benutzer),
// wird das Skript mit einer Fehlermeldung beendet.
if (!$post) {
    die("Kreation nicht gefunden oder du hast keine Berechtigung, sie zu bearbeiten.");
}

// Initialisiere Arrays/Variablen für Fehlermeldungen und Erfolgsmeldungen.
$errors = [];
$success = '';

// Überprüfe, ob das Formular über eine POST-Anfrage abgeschickt wurde.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Hole den neuen Titel und die neue Beschreibung aus den POST-Daten und entferne überflüssige Leerzeichen.
    $title       = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    
    // Überprüfe, ob der Titel leer ist.
    if (empty($title)) {
        $errors[] = "Der Titel darf nicht leer sein.";
    }
    
    // Falls keine Fehler aufgetreten sind, führe die Aktualisierung des Posts durch.
    if (empty($errors)) {
        // Bereite eine SQL-Abfrage vor, um den Titel und die Beschreibung des Posts zu aktualisieren.
        // Es wird nur aktualisiert, wenn die Post-ID und die Benutzer-ID (user_id) mit dem aktuell eingeloggten Benutzer übereinstimmen.
        $stmtUpdate = $pdo->prepare("
            UPDATE kreationen
            SET title = :title,
                description = :description
            WHERE id = :id AND user_id = :uid
        ");
        // Führe die Abfrage aus und ersetze die Platzhalter durch die tatsächlichen Werte.
        $stmtUpdate->execute([
            ':title'       => $title,
            ':description' => $description,
            ':id'          => $post_id,
            ':uid'         => $_SESSION['user_id']
        ]);
        
        // Setze eine Erfolgsmeldung, die anzeigt, dass die Aktualisierung erfolgreich war.
        $success = "Kreation erfolgreich aktualisiert.";
        
        // Lade die aktualisierten Daten des Posts neu aus der Datenbank,
        // damit die Änderungen im Formular sofort sichtbar sind.
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
    <!-- Überschrift der Seite -->
    <h1>Kreation bearbeiten</h1>
    <!-- Anzeige von Fehlermeldungen in einer Bootstrap-Alert-Box -->
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <?php foreach ($errors as $error): ?>
                <p><?php echo escape($error); ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <!-- Anzeige der Erfolgsmeldung in einer Bootstrap-Alert-Box -->
    <?php if ($success): ?>
        <div class="alert alert-success">
            <p><?php echo escape($success); ?></p>
        </div>
    <?php endif; ?>
    <!-- Formular zum Bearbeiten des Posts -->
    <!-- Das Formular sendet die Daten per POST an diese gleiche Datei, wobei die Post-ID als GET-Parameter übergeben wird -->
    <form action="edit_own_post.php?id=<?php echo $post_id; ?>" method="post">
        <div class="form-group">
            <label for="title">Titel:</label>
            <!-- Das Eingabefeld ist mit dem aktuellen Titel des Posts vorbelegt -->
            <input type="text" name="title" id="title" class="form-control"
                   value="<?php echo escape($post['title']); ?>" required>
        </div>
        <div class="form-group">
            <label for="description">Beschreibung (optional):</label>
            <!-- Das Textarea ist mit der aktuellen Beschreibung des Posts vorbelegt -->
            <textarea name="description" id="description" class="form-control"><?php echo escape($post['description']); ?></textarea>
        </div>
        <!-- Sende-Button zum Speichern der Änderungen -->
        <button type="submit" class="btn btn-primary">Speichern</button>
        <!-- Link zum Abbrechen und Rückkehr zur Übersicht der eigenen Posts -->
        <a href="profile_posts.php" class="btn btn-secondary">Abbrechen</a>
    </form>
</div>
<?php include '../includes/footer.php'; ?>
