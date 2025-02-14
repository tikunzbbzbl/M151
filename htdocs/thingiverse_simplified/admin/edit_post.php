<?php
// admin/edit_post.php

// Binde die Datei ein, die die Datenbankverbindung herstellt.
// Dadurch können wir SQL-Abfragen ausführen.
require_once '../includes/db.php';

// Binde die Datei mit gemeinsamen Funktionen ein.
// Diese enthält beispielsweise Funktionen zum sicheren Starten von Sessions und zum Escapen von Ausgaben.
require_once '../includes/functions.php';

// Starte eine sichere Session, um auf Session-Daten (wie den Login-Status) zugreifen zu können.
secure_session_start();

// Überprüfe, ob der Benutzer eingeloggt ist und Admin-Rechte besitzt.
// Falls nicht, leite ihn zur Login-Seite weiter und beende das Skript.
if (!is_logged_in() || empty($_SESSION['is_admin'])) {
    header("Location: ../login.php");
    exit;
}

// Überprüfe, ob eine gültige Post-ID (als GET-Parameter "id") übergeben wurde.
// Die ID muss existieren und numerisch sein.
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Ungültige Anfrage.");
}

// Konvertiere den GET-Parameter in einen Integer und speichere ihn in $post_id.
$post_id = (int)$_GET['id'];

// Bereite eine SQL-Abfrage vor, um alle Daten des Posts (Kreation) mit der gegebenen ID aus der Tabelle "kreationen" abzurufen.
$stmt = $pdo->prepare("SELECT * FROM kreationen WHERE id = :id");
$stmt->execute([':id' => $post_id]);

// Hole das Ergebnis der Abfrage. Falls kein Post gefunden wurde, wird false zurückgegeben.
$post = $stmt->fetch();

// Wenn kein Post gefunden wurde, beende das Skript mit einer Fehlermeldung.
if (!$post) {
    die("Post nicht gefunden.");
}

// Initialisiere zwei Variablen für Fehler- und Erfolgsmeldungen.
$errors = [];
$success = '';

// Prüfe, ob das Formular über eine POST-Anfrage abgeschickt wurde.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Hole den neuen Titel und die neue Beschreibung aus dem Formular und entferne unnötige Leerzeichen.
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    
    // Überprüfe, ob der Titel leer ist.
    if (empty($title)) {
        $errors[] = "Titel darf nicht leer sein.";
    }
    
    // Falls keine Fehler aufgetreten sind, führe die Aktualisierung in der Datenbank durch.
    if (empty($errors)) {
        // Bereite eine SQL-Abfrage vor, um den Titel und die Beschreibung des Posts zu aktualisieren.
        $stmt = $pdo->prepare("UPDATE kreationen SET title = :title, description = :description WHERE id = :id");
        // Führe die Abfrage mit den neuen Werten und der Post-ID aus.
        $stmt->execute([
            ':title' => $title,
            ':description' => $description,
            ':id' => $post_id
        ]);
        // Setze eine Erfolgsmeldung, die später angezeigt wird.
        $success = "Post erfolgreich aktualisiert.";
        
        // Lade die aktualisierten Daten des Posts neu, damit das Formular die aktuellen Werte anzeigt.
        $stmt = $pdo->prepare("SELECT * FROM kreationen WHERE id = :id");
        $stmt->execute([':id' => $post_id]);
        $post = $stmt->fetch();
    }
}
?>
<!-- Binde den Header ein, der das HTML-Grundgerüst, Navigation, CSS und Bootstrap enthält -->
<?php include '../includes/header.php'; ?>
<div class="container mt-5">
    <h1>Post bearbeiten</h1>
    <!-- Falls Fehler vorhanden sind, werden diese in einer Bootstrap-Alert-Box angezeigt -->
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <?php foreach ($errors as $error): ?>
                <p><?php echo escape($error); ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <!-- Falls eine Erfolgsmeldung gesetzt wurde, wird diese in einer Bootstrap-Alert-Box angezeigt -->
    <?php if ($success): ?>
        <div class="alert alert-success">
            <p><?php echo escape($success); ?></p>
        </div>
    <?php endif; ?>
    <!-- Formular zum Bearbeiten des Posts -->
    <!-- Das Formular sendet per POST an diese gleiche Datei, wobei die Post-ID als GET-Parameter übergeben wird -->
    <form action="edit_post.php?id=<?php echo $post_id; ?>" method="post">
        <div class="form-group">
            <label for="title">Titel</label>
            <!-- Das Eingabefeld wird mit dem aktuellen Titel des Posts vorausgefüllt -->
            <input type="text" name="title" id="title" class="form-control" value="<?php echo escape($post['title']); ?>" required>
        </div>
        <div class="form-group">
            <label for="description">Beschreibung</label>
            <!-- Das Textarea wird mit der aktuellen Beschreibung des Posts vorausgefüllt -->
            <textarea name="description" id="description" class="form-control"><?php echo escape($post['description']); ?></textarea>
        </div>
        <!-- Sende-Button, um die Änderungen zu speichern -->
        <button type="submit" class="btn btn-primary">Speichern</button>
        <!-- Link, um zur Verwaltungsseite der Posts zurückzukehren -->
        <a href="manage_kreationen.php" class="b
