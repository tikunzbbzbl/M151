<?php
// profil/profile_posts.php

// Binde die Datei "db.php" ein, die die Verbindung zur Datenbank herstellt.
// Dadurch können wir SQL-Abfragen durchführen.
require_once '../includes/db.php';

// Binde die Datei "functions.php" ein, die hilfreiche Funktionen enthält, wie z. B. secure_session_start() und escape().
// Diese Funktionen helfen uns, Sessions sicher zu starten und Ausgaben vor XSS-Angriffen zu schützen.
require_once '../includes/functions.php';

// Starte eine sichere Session. Das stellt sicher, dass wir auf Session-Daten (z. B. den Login-Status) zugreifen können.
secure_session_start();

// Überprüfe, ob der Benutzer eingeloggt ist.
// Falls der Benutzer nicht eingeloggt ist, leite ihn zur Login-Seite weiter und beende das Skript.
if (!is_logged_in()) {
    header("Location: ../login.php");
    exit;
}

// Bereite eine SQL-Abfrage vor, um alle Kreationen (Posts) des aktuell eingeloggten Benutzers aus der Tabelle "kreationen" abzurufen.
// Es werden die Spalten id, title, description und created_at ausgewählt.
$stmt = $pdo->prepare("
    SELECT id, title, description, created_at
    FROM kreationen
    WHERE user_id = :uid
    ORDER BY created_at DESC
");
// Führe die Abfrage aus, wobei der Platzhalter :uid mit der aktuellen Benutzer-ID (aus der Session) ersetzt wird.
$stmt->execute([':uid' => $_SESSION['user_id']]);
// Hole alle Ergebnisse der Abfrage und speichere sie als Array in der Variable $posts.
$posts = $stmt->fetchAll();
?>
<!-- Binde den Header ein, der das HTML-Grundgerüst, die Navigation und eventuell eingebundene CSS-Dateien enthält -->
<?php include '../includes/header.php'; ?>
<div class="container mt-5">
    <!-- Überschrift der Seite -->
    <h1>Meine Posts</h1>
    <!-- Überprüfe, ob es Posts gibt -->
    <?php if ($posts): ?>
        <!-- Erstelle eine Tabelle mit Bootstrap-Klassen, um die Posts anzuzeigen -->
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
            <!-- Schleife über jedes Post-Element im Array $posts -->
            <?php foreach ($posts as $p): ?>
                <tr>
                    <!-- Ausgabe der Post-ID, sicher maskiert mit der Funktion escape() -->
                    <td><?php echo escape($p['id']); ?></td>
                    <!-- Ausgabe des Post-Titels -->
                    <td><?php echo escape($p['title']); ?></td>
                    <!-- Ausgabe des Erstellungsdatums des Posts -->
                    <td><?php echo escape($p['created_at']); ?></td>
                    <td>
                        <!-- Link zum Anschauen des Posts. Dieser verweist auf view.php und übergibt die Post-ID per GET-Parameter -->
                        <a href="../view.php?id=<?php echo $p['id']; ?>" class="btn btn-sm btn-info">Ansehen</a>
                        <!-- Link zum Bearbeiten des Posts. Hier wird edit_own_post.php aufgerufen -->
                        <a href="edit_own_post.php?id=<?php echo $p['id']; ?>" class="btn btn-sm btn-primary">Bearbeiten</a>
                        <!-- Link zum Löschen des Posts. Beim Klicken wird ein Bestätigungsdialog angezeigt -->
                        <a href="delete_own_post.php?id=<?php echo $p['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Kreation wirklich löschen?')">Löschen</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <!-- Falls keine Posts vorhanden sind, wird diese Meldung angezeigt -->
        <p>Du hast noch keine Kreationen hochgeladen.</p>
    <?php endif; ?>
</div>
<!-- Binde den Footer ein, der das Ende des HTML-Dokuments darstellt -->
<?php include '../includes/footer.php'; ?>
