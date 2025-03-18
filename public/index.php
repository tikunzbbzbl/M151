<?php
/**
 * Startseite des Projekts mit Anzeige aller Einträge und Thumbnails
 */

// Einbinden der benötigten Dateien
require_once '../includes/session.php';
require_once '../includes/functions.php';
require_once '../config/database.php';

// Alle Einträge aus der Datenbank abrufen, zusammen mit den Benutzernamen
$stmt = $pdo->prepare("
    SELECT e.id, e.titel, e.beschreibung, e.erstellt_am, 
           b.id as benutzer_id, b.vorname, b.nachname, b.profilbild
    FROM eintraege e
    JOIN benutzer b ON e.benutzer_id = b.id
    ORDER BY e.erstellt_am DESC
    LIMIT 10
");
$stmt->execute();
$alle_eintraege = $stmt->fetchAll();

// Header einbinden
include_once '../includes/header.php';
?>

<div class="container mt-5">
    <div class="row">
        <div class="col-md-12">
            <div class="jumbotron">
                <h1 class="display-4">Willkommen zu SYT</h1>
                <p class="lead">Hier können Sie sich registrieren, anmelden und Ihre Einträge verwalten.</p>
                <hr class="my-4">
                <p>Die Website für Triumph fahrer.</p>
                
                <?php if (!ist_angemeldet()): ?>
                    <!-- Anzeige für nicht angemeldete Benutzer -->
                    <p class="lead">
                        <a class="btn btn-primary btn-lg" href="register.php" role="button">Registrieren</a>
                        <a class="btn btn-success btn-lg" href="login.php" role="button">Anmelden</a>
                    </p>
                <?php else: ?>
                    <!-- Anzeige für angemeldete Benutzer -->
                    <p class="lead">
                        <a class="btn btn-primary btn-lg" href="dashboard.php" role="button">Zum Dashboard</a>
                        <a class="btn btn-danger btn-lg" href="logout.php" role="button">Abmelden</a>
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Neueste Einträge anzeigen -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h4>Neueste Einträge aller Benutzer</h4>
                </div>
                <div class="card-body">
                    <?php if (count($alle_eintraege) > 0): ?>
                        <div class="row">
                            <?php foreach ($alle_eintraege as $eintrag): ?>
                                <?php
                                // Thumbnail für den Eintrag abrufen
                                $stmt = $pdo->prepare("
                                    SELECT dateiname 
                                    FROM eintrag_bilder 
                                    WHERE eintrag_id = :eintrag_id AND ist_thumbnail = 1 
                                    LIMIT 1
                                ");
                                $stmt->execute(['eintrag_id' => $eintrag['id']]);
                                $thumbnail = $stmt->fetchColumn();
                                
                                // Profilbild anzeigen oder Default verwenden
                                $profilbild_pfad = 'uploads/placeholder.png'; // Default path
                                if ($eintrag['profilbild'] && file_exists('uploads/' . $eintrag['profilbild'])) {
                                    $profilbild_pfad = 'uploads/' . $eintrag['profilbild'];
                                }
                                ?>
                                
                                <div class="col-md-6 mb-4">
                                    <div class="card h-100">
                                        <?php if ($thumbnail && file_exists('uploads/' . $thumbnail)): ?>
                                            <img src="uploads/<?php echo htmlspecialchars($thumbnail); ?>" class="card-img-top" alt="Thumbnail" style="height: 200px; object-fit: cover;">
                                        <?php endif; ?>
                                        <div class="card-header">
                                            <div class="d-flex align-items-center">
                                                <img src="<?php echo htmlspecialchars($profilbild_pfad); ?>" alt="Profilbild" class="rounded-circle mr-2" style="width: 30px; height: 30px; object-fit: cover;">
                                                <h5 class="card-title mb-0 ms-2">
                                                <?php 
                                                    $titel = htmlspecialchars($eintrag['titel']);
                                                    if (strlen($titel) > 40) {  // Kürzt Titel auf 40 Zeichen
                                                        echo substr($titel, 0, 37) . '...';
                                                    } else {
                                                        echo $titel;
                                                    }
                                                ?>
                                                </h5>
                                            </div>
                                            <small class="text-muted">Von <?php echo htmlspecialchars($eintrag['vorname'] . ' ' . $eintrag['nachname']); ?> 
                                            am <?php echo date('d.m.Y H:i', strtotime($eintrag['erstellt_am'])); ?></small>
                                        </div>
                                        <div class="card-body">
                                            <p class="card-text">
                                                <?php 
                                                // Beschreibung kürzen, wenn sie zu lang ist
                                                $beschreibung = htmlspecialchars($eintrag['beschreibung']);
                                                if (strlen($beschreibung) > 150) {
                                                    echo substr($beschreibung, 0, 150) . '...';
                                                } else {
                                                    echo $beschreibung;
                                                }
                                                ?>
                                            </p>
                                        </div>
                                        <div class="card-footer">
                                        <?php if (ist_angemeldet() && $_SESSION['user_id'] == $eintrag['benutzer_id']): ?>
                                            <!-- Eigene Einträge bearbeiten -->
                                            <a href="edit_entry.php?id=<?php echo $eintrag['id']; ?>" class="btn btn-primary btn-sm">Bearbeiten</a>
                                        <?php endif; ?>
                                        
                                        <!-- Ansehen-Button für jeden Eintrag, unabhängig vom Login-Status -->
                                        <a href="view_entry.php?id=<?php echo $eintrag['id']; ?>" class="btn btn-info btn-sm">Ansehen</a>
                                    </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            Es wurden noch keine Einträge erstellt.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

<?php
// Footer einbinden
include_once '../includes/footer.php';
?>