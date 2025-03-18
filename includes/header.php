<?php
/**
 * Header-Datei für alle Seiten
 * Kompetenz C4: In meinem Projekt sind alle HTML-Dateien validiert und fehlerfrei.
 */

// Profilbild des angemeldeten Benutzers abrufen, falls angemeldet
$profilbild_url = 'uploads/placeholder.png'; // Default image path
if (function_exists('ist_angemeldet') && ist_angemeldet() && isset($_SESSION['user_id']) && isset($pdo)) {
    $stmt = $pdo->prepare("SELECT profilbild FROM benutzer WHERE id = :id");
    $stmt->execute(['id' => $_SESSION['user_id']]);
    $profilbild = $stmt->fetchColumn();
    
    if ($profilbild && file_exists('uploads/' . $profilbild)) {
        $profilbild_url = 'uploads/' . $profilbild;
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SYT</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome Icons für Symbole -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- Eigenes CSS -->
    <link href="css/style.css" rel="stylesheet">
    <link rel="icon" href="uploads/motorcycle_icon.ico" type="image/x-icon">
</head>
<body>
    <header>
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
            <div class="container">
                <a class="navbar-brand" href="index.php">Share your Triumph</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                    aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="index.php">Startseite</a>
                        </li>
                        <?php if (function_exists('ist_angemeldet') && ist_angemeldet()): ?>
                            <!-- Menüpunkte für angemeldete Benutzer -->
                            <li class="nav-item">
                                <a class="nav-link" href="dashboard.php">Dashboard</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="create_entry.php">Neuer Eintrag</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="change_password.php">Passwort ändern</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="profile_edit.php">
                                    <img src="<?php echo htmlspecialchars($profilbild_url); ?>" alt="Profilbild" class="rounded-circle" style="width: 30px; height: 30px; object-fit: cover;">
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="logout.php">Abmelden</a>
                            </li>
                        <?php else: ?>
                            <!-- Menüpunkte für nicht angemeldete Benutzer -->
                            <li class="nav-item">
                                <a class="nav-link" href="register.php">Registrieren</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="login.php">Anmelden</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </nav>
    </header>
    <main>
        <!-- Hauptinhalt wird in den jeweiligen Dateien eingefügt -->