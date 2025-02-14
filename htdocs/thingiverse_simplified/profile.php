<?php
// profile.php

// Binde die Datei ein, die die Verbindung zur Datenbank herstellt.
// Dadurch können wir SQL-Abfragen ausführen.
require_once 'includes/db.php';

// Binde die Datei mit den gemeinsamen Hilfsfunktionen ein.
// Diese enthält Funktionen wie secure_session_start() zum sicheren Starten von Sessions
// und escape() zum sicheren Ausgeben von Daten (z.B. Schutz vor XSS).
require_once 'includes/functions.php';

// Starte eine sichere Session, damit wir auf Session-Daten (wie den Login-Status) zugreifen können.
secure_session_start();

// Überprüfe, ob der Benutzer eingeloggt ist.
// Falls der Benutzer nicht eingeloggt ist, leite ihn zur Login-Seite weiter und beende das Skript.
if (!is_logged_in()) {
    header("Location: login.php");
    exit;
}

// Bereite eine SQL-Abfrage vor, um die aktuellen Benutzerdaten (ID, Benutzername, E-Mail, Profilbild)
// aus der Tabelle "users" abzurufen, basierend auf der in der Session gespeicherten Benutzer-ID.
$stmt = $pdo->prepare("SELECT id, username, email, profile_picture FROM users WHERE id = :id");
// Führe die Abfrage aus, wobei :id durch die aktuelle Benutzer-ID ersetzt wird.
$stmt->execute([':id' => $_SESSION['user_id']]);
// Speichere das Ergebnis der Abfrage in der Variable $user.
$user = $stmt->fetch();

// Wenn kein Benutzer gefunden wurde, wird das Skript mit einer Fehlermeldung beendet.
if (!$user) {
    die("Benutzer nicht gefunden.");
}

// Bestimme das Profilbild, das angezeigt werden soll.
// Falls ein Profilbild vorhanden ist (profile_picture ist nicht leer),
// wird der Pfad zum Bild im Ordner "uploads" zusammengestellt.
// Andernfalls wird ein Platzhalterbild (placeholder.png) verwendet.
if (!empty($user['profile_picture'])) {
    $profilePic = 'uploads/' . $user['profile_picture'];
} else {
    $profilePic = 'uploads/placeholder.png';
}
?>
<?php include 'includes/header.php'; ?>
<!-- Binde den Header ein, der das HTML-Grundgerüst, Navigation und CSS (z. B. Bootstrap) enthält. -->

<div class="container mt-5">
    <!-- Überschrift der Seite -->
    <h1>Mein Profil</h1>
    <div class="row">
        <!-- Linke Spalte: Anzeige des Profilbilds -->
        <div class="col-md-3 text-center">
            <!-- Zeige das Profilbild oder den Platzhalter als rundes Bild an.
                 Die Funktion escape() sorgt dafür, dass der Dateipfad sicher ausgegeben wird. -->
            <img src="<?php echo escape($profilePic); ?>" alt="Profilbild" class="img-fluid rounded-circle" style="max-width:200px;">
        </div>
        <!-- Rechte Spalte: Anzeige der Benutzerinformationen -->
        <div class="col-md-9">
            <!-- Zeige den Benutzernamen an -->
            <h3><?php echo escape($user['username']); ?></h3>
            <!-- Zeige die E-Mail-Adresse an -->
            <p>Email: <?php echo escape($user['email']); ?></p>
            <!-- Hier können weitere Profileinstellungen oder Links ergänzt werden -->
            <!-- Link zum Bearbeiten des Profils. Dieser führt zu edit_profile.php im Unterordner "profile". -->
            <a href="profile/edit_profile.php" class="btn btn-primary mb-2">Profil bearbeiten</a>
            <!-- Link zur Verwaltung der eigenen Kreationen (Posts).
                 Dieser führt zu profile_posts.php im Unterordner "profile". -->
            <a href="profile/profile_posts.php" class="btn btn-secondary mb-2">Meine Kreationen verwalten</a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
<!-- Binde den Footer ein, der das Ende des HTML-Dokuments darstellt -->
