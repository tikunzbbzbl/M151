<?php
/**
 * Admin-Dashboard
 * Nur Administratoren haben Zugriff auf diese Seite
 */

// Einbinden der benötigten Dateien
require_once '../config/database.php';
require_once '../includes/session.php';
require_once '../includes/functions.php';

// Zugriffsschutz: Nur für angemeldete Benutzer
nur_angemeldet_zugriff();

// Prüfen, ob der Benutzer Admin-Rechte hat
if (!ist_admin()) {
    // Keine Admin-Rechte, zum normalen Dashboard umleiten
    umleiten_zu('dashboard.php?error=admin_required');
}

// Benutzer aus der Datenbank abrufen
$stmt = $pdo->prepare("
    SELECT id, vorname, nachname, email, profilbild, erstellt_am, is_admin 
    FROM benutzer 
    ORDER BY erstellt_am DESC
");
$stmt->execute();
$benutzer = $stmt->fetchAll();

// Einträge aus der Datenbank abrufen
$stmt = $pdo->prepare("
    SELECT e.id, e.titel, e.beschreibung, e.erstellt_am, e.aktualisiert_am,
           b.vorname, b.nachname, b.id as benutzer_id
    FROM eintraege e
    JOIN benutzer b ON e.benutzer_id = b.id
    ORDER BY e.erstellt_am DESC
");
$stmt->execute();
$eintraege = $stmt->fetchAll();

// Header einbinden
include_once '../includes/header.php';
?>

<div class="container mt-5">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <h4>Admin-Dashboard</h4>
                </div>
                <div class="card-body">
                    
                    <!-- Erfolgsmeldungen -->
                    <?php if (isset($_GET['success']) && $_GET['success'] == 'user_deleted'): ?>
                        <?php echo erfolgs_meldung("Benutzer erfolgreich gelöscht!"); ?>
                    <?php elseif (isset($_GET['success']) && $_GET['success'] == 'user_updated'): ?>
                        <?php echo erfolgs_meldung("Benutzer erfolgreich aktualisiert!"); ?>
                    <?php elseif (isset($_GET['success']) && $_GET['success'] == 'user_created'): ?>
                        <?php echo erfolgs_meldung("Benutzer erfolgreich erstellt!"); ?>
                    <?php elseif (isset($_GET['success']) && $_GET['success'] == 'entry_deleted'): ?>
                        <?php echo erfolgs_meldung("Eintrag erfolgreich gelöscht!"); ?>
                    <?php endif; ?>
                    
                    <!-- Fehlermeldungen -->
                    <?php if (isset($_GET['error']) && $_GET['error'] == 'deletion_failed'): ?>
                        <?php echo fehler_meldung("Fehler beim Löschen!"); ?>
                    <?php endif; ?>
                    
                    <!-- Tabs für die Admin-Bereiche -->
                    <ul class="nav nav-tabs" id="adminTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="users-tab" data-bs-toggle="tab" data-bs-target="#users" type="button" role="tab" aria-controls="users" aria-selected="true">Benutzer verwalten</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="entries-tab" data-bs-toggle="tab" data-bs-target="#entries" type="button" role="tab" aria-controls="entries" aria-selected="false">Einträge verwalten</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="stats-tab" data-bs-toggle="tab" data-bs-target="#stats" type="button" role="tab" aria-controls="stats" aria-selected="false">Statistiken</button>
                        </li>
                    </ul>
                    
                    <div class="tab-content p-3" id="adminTabsContent">
                        <!-- Benutzer Tab -->
                        <div class="tab-pane fade show active" id="users" role="tabpanel" aria-labelledby="users-tab">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5>Benutzerverwaltung</h5>
                                <a href="admin_create_user.php" class="btn btn-success">Neuen Benutzer erstellen</a>
                            </div>
                            
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Name</th>
                                            <th>E-Mail</th>
                                            <th>Registriert am</th>
                                            <th>Admin</th>
                                            <th>Aktionen</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($benutzer as $user): ?>
                                            <tr>
                                                <td><?php echo $user['id']; ?></td>
                                                <td><?php echo htmlspecialchars($user['vorname'] . ' ' . $user['nachname']); ?></td>
                                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                                <td><?php echo date('d.m.Y H:i', strtotime($user['erstellt_am'])); ?></td>
                                                <td>
                                                    <?php if ($user['is_admin']): ?>
                                                        <span class="badge bg-success">Admin</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary">Benutzer</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <a href="admin_edit_user.php?id=<?php echo $user['id']; ?>" class="btn btn-primary btn-sm">Bearbeiten</a>
                                                    <?php if ($user['id'] != $_SESSION['user_id']): // Verhindere Selbstlöschung ?>
                                                        <a href="admin_delete_user.php?id=<?php echo $user['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Benutzer wirklich löschen? Alle seine Einträge werden ebenfalls gelöscht!');">Löschen</a>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                        <!-- Einträge Tab -->
                        <div class="tab-pane fade" id="entries" role="tabpanel" aria-labelledby="entries-tab">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5>Einträgsverwaltung</h5>
                                <a href="create_entry.php" class="btn btn-success">Neuen Eintrag erstellen</a>
                            </div>
                            
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
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
                                        <?php foreach ($eintraege as $eintrag): ?>
                                            <tr>
                                                <td><?php echo $eintrag['id']; ?></td>
                                                <td><?php echo htmlspecialchars($eintrag['titel']); ?></td>
                                                <td><?php echo htmlspecialchars($eintrag['vorname'] . ' ' . $eintrag['nachname']); ?></td>
                                                <td><?php echo date('d.m.Y H:i', strtotime($eintrag['erstellt_am'])); ?></td>
                                                <td>
                                                    <a href="view_entry.php?id=<?php echo $eintrag['id']; ?>" class="btn btn-info btn-sm">Ansehen</a>
                                                    <a href="admin_edit_entry.php?id=<?php echo $eintrag['id']; ?>" class="btn btn-primary btn-sm">Bearbeiten</a>
                                                    <a href="admin_delete_entry.php?id=<?php echo $eintrag['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Eintrag wirklich löschen?');">Löschen</a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                        <!-- Statistiken Tab -->
                        <div class="tab-pane fade" id="stats" role="tabpanel" aria-labelledby="stats-tab">
                            <h5>Statistiken</h5>
                            
                            <?php
                            // Statistiken berechnen
                            $anzahl_benutzer = count($benutzer);
                            $anzahl_eintraege = count($eintraege);
                            
                            // Anzahl der Bilder
                            $stmt = $pdo->query("SELECT COUNT(*) FROM eintrag_bilder");
                            $anzahl_bilder = $stmt->fetchColumn();
                            
                            // Neueste Registrierung
                            $stmt = $pdo->query("
                                SELECT vorname, nachname, erstellt_am 
                                FROM benutzer 
                                ORDER BY erstellt_am DESC 
                                LIMIT 1
                            ");
                            $neuester_benutzer = $stmt->fetch();
                            ?>
                            
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <div class="card bg-primary text-white">
                                        <div class="card-body">
                                            <h5 class="card-title">Benutzer</h5>
                                            <p class="card-text display-4"><?php echo $anzahl_benutzer; ?></p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <div class="card bg-success text-white">
                                        <div class="card-body">
                                            <h5 class="card-title">Einträge</h5>
                                            <p class="card-text display-4"><?php echo $anzahl_eintraege; ?></p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <div class="card bg-info text-white">
                                        <div class="card-body">
                                            <h5 class="card-title">Bilder</h5>
                                            <p class="card-text display-4"><?php echo $anzahl_bilder; ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <?php if ($neuester_benutzer): ?>
                                <div class="alert alert-info mt-3">
                                    Neueste Registrierung: <strong><?php echo htmlspecialchars($neuester_benutzer['vorname'] . ' ' . $neuester_benutzer['nachname']); ?></strong> 
                                    am <?php echo date('d.m.Y H:i', strtotime($neuester_benutzer['erstellt_am'])); ?> Uhr
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Footer einbinden
include_once '../includes/footer.php';
?>