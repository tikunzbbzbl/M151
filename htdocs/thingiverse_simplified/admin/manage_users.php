<?php
// admin/manage_users.php

// Binde die Datei ein, die die Verbindung zur Datenbank herstellt.
// Dadurch können wir später SQL-Abfragen an die Datenbank senden.
require_once '../includes/db.php';

// Binde die Datei mit gemeinsamen Hilfsfunktionen ein.
// Diese Funktionen können z.B. die Funktion secure_session_start() zum Starten einer sicheren Session und
// die Funktion escape() zur sicheren Ausgabe von Daten enthalten.
require_once '../includes/functions.php';

// Starte eine sichere Session, um auf Session-Daten (wie den Login-Status) zugreifen zu können.
secure_session_start();

// Überprüfe, ob der Benutzer eingeloggt ist und ob er Admin-Rechte besitzt.
// Die Bedingung !is_logged_in() prüft, ob der Benutzer nicht angemeldet ist.
// Außerdem wird geprüft, ob in der Session der Wert 'is_admin' gesetzt ist und ob er den booleschen Wert true hat.
// Falls diese Bedingungen nicht erfüllt sind, wird der Benutzer zur Login-Seite umgeleitet und das Skript beendet.
if (!is_logged_in() || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: ../login.php");
    exit;
}

// Führe eine SQL-Abfrage aus, um alle Benutzer aus der Tabelle "users" abzurufen.
// Die Abfrage wählt die Spalten id, username, email und created_at aus und sortiert die Ergebnisse
// absteigend nach dem Erstellungsdatum (neueste zuerst).
$stmt = $pdo->query("SELECT id, username, email, created_at FROM users ORDER BY created_at DESC");

// Speichere alle abgerufenen Datensätze in der Variable $users als ein Array.
// fetchAll() liefert ein Array aller Zeilen, die durch die Abfrage zurückgegeben werden.
$users = $stmt->fetchAll();
?>

<?php include '../includes/header.php'; ?>
<!-- Der Header wird eingebunden, der das HTML-Grundgerüst, Navigation, CSS (z.B. Bootstrap) etc. enthält -->

<div class="container mt-5">
    <!-- Container mit Bootstrap-Klasse "container" und einem oberen Margin (mt-5) -->
    <h1 class="mb-4">Benutzer verwalten</h1>
    <!-- Überschrift der Seite mit unterem Margin (mb-4) -->

    <!-- Erstelle eine Tabelle mit Bootstrap-Klassen für eine gestreifte Darstellung -->
    <table class="table table-striped">
        <thead>
            <tr>
                <!-- Tabellenkopfzeile mit den Spaltenüberschriften -->
                <th>ID</th>
                <th>Benutzername</th>
                <th>Email</th>
                <th>Registriert am</th>
                <th>Aktionen</th>
            </tr>
        </thead>
        <tbody>
            <!-- Schleife über alle Benutzer, die in $users gespeichert sind -->
            <?php foreach ($users as $user): ?>
            <tr>
                <!-- Ausgabe der Benutzer-ID, sicher maskiert mit der Funktion escape() -->
                <td><?php echo escape($user['id']); ?></td>
                <!-- Ausgabe des Benutzernamens -->
                <td><?php echo escape($user['username']); ?></td>
                <!-- Ausgabe der E-Mail-Adresse -->
                <td><?php echo escape($user['email']); ?></td>
                <!-- Ausgabe des Registrierungsdatums -->
                <td><?php echo escape($user['created_at']); ?></td>
                <td>
                    <!-- Aktionen, die für jeden Benutzer angeboten werden: Bearbeiten und Löschen -->
                    <!-- Link zum Bearbeiten des Benutzers. Es wird die Datei edit_user.php aufgerufen,
                         wobei die Benutzer-ID als GET-Parameter übergeben wird. -->
                    <a href="edit_user.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-primary">Bearbeiten</a>
                    <!-- Link zum Löschen des Benutzers. Es wird die Datei delete_user.php aufgerufen,
                         wobei die Benutzer-ID als GET-Parameter übergeben wird.
                         Der onclick-Handler zeigt einen Bestätigungsdialog an, um unbeabsichtigtes Löschen zu verhindern. -->
                    <a href="delete_user.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Benutzer wirklich löschen?')">Löschen</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include '../includes/footer.php'; ?>
<!-- Binde den Footer ein, der das Ende des HTML-Dokuments (z.B. Copyright, Scripts) enthält -->
