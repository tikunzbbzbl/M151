<?php
// Binde die Datei "functions.php" ein, die wichtige Hilfsfunktionen (wie z. B. secure_session_start() und escape()) enthält.
require_once 'functions.php';

// Starte eine sichere Session. Das ist wichtig, um Benutzerdaten (z. B. Login-Status) über verschiedene Seiten hinweg zu speichern.
secure_session_start();
?>
<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8">
  <!-- Der Titel der Website, der in der Titelleiste des Browsers angezeigt wird -->
  <title>Thingiverse Simplified</title>
  <!-- Stellt sicher, dass die Seite responsiv ist und auf mobilen Geräten gut dargestellt wird -->
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Einbindung von Bootstrap CSS über ein Content Delivery Network (CDN) -->
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <link rel="icon" href="uploads/motorcycle_icon.ico" type="image/x-icon">

  <style>
    /* Setzt den Standard-Margin und Padding des Body-Elements auf 0 */
    body { margin: 0; padding: 0; }
    /* Stildefinition für den fixierten Navbar */
    .navbar {
      position: fixed;  /* Der Navbar bleibt immer oben auf der Seite sichtbar */
      top: 0;           /* Der Navbar wird am oberen Rand positioniert */
      width: 100%;      /* Der Navbar erstreckt sich über die gesamte Seitenbreite */
      z-index: 1000;    /* Stellt sicher, dass der Navbar immer oben angezeigt wird, selbst wenn andere Elemente darüber liegen könnten */
    }
    /* Platzhalter-Klasse, um den Inhalt nicht vom fixierten Navbar überdecken zu lassen */
    .content-container {
      margin-top: 56px; /* Fügt einen oberen Abstand hinzu, der der Höhe des Navbar entspricht */
    }
  </style>
</head>
<body>
<!-- Beginn der Navigation (Navbar) -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container">
    <!-- Der Brand-Name, der als Link zur Startseite dient -->
    <a class="navbar-brand" href="../index.php">Share your Triumph</a>
    <!-- Button für mobile Geräte, um das Menü ein- und auszublenden -->
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarContent">
      <span class="navbar-toggler-icon"></span>
    </button>
    <!-- Der zusammenklappbare Bereich der Navigation -->
    <div class="collapse navbar-collapse" id="navbarContent">
      <ul class="navbar-nav ml-auto">
        <!-- Navigationslink zur Startseite -->
        <li class="nav-item">
          <a class="nav-link" href="../index.php">Startseite</a>
        </li>
        <!-- Navigationslink zum Hochladen einer Kreation -->
        <li class="nav-item">
          <a class="nav-link" href="../upload.php">Kreation hochladen</a>
        </li>
        <!-- Überprüfe, ob der Benutzer eingeloggt ist -->
        <?php if (is_logged_in()): ?>
          <!-- Navigationslink zum Profil, wenn der Benutzer eingeloggt ist -->
          <li class="nav-item">
            <a class="nav-link" href="../profile.php">Profil</a>
          </li>
          <!-- Überprüfe, ob der Benutzer Admin-Rechte hat -->
          <?php if (!empty($_SESSION['is_admin'])): ?>
            <!-- Wenn ja, zeige einen Link zum Admin Dashboard an -->
            <li class="nav-item">
              <a class="nav-link" href="../admin/dashboard.php">Admin Dashboard</a>
            </li>
          <?php endif; ?>
          <!-- Navigationslink zum Ausloggen -->
          <li class="nav-item">
            <a class="nav-link" href="../logout.php">Abmelden</a>
          </li>
        <?php else: ?>
          <!-- Falls der Benutzer nicht eingeloggt ist, werden Links für Anmeldung und Registrierung angezeigt -->
          <li class="nav-item">
            <a class="nav-link" href="../login.php">Anmelden</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="../register.php">Registrieren</a>
          </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>
<!-- Container, in dem der Seiteninhalt angezeigt wird. Die Klasse "content-container" sorgt für Abstand zum fixierten Navbar -->
<div class="container content-container">
