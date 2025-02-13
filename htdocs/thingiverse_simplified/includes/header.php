<?php
require_once 'functions.php';
secure_session_start();
?>
<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8">
  <title>Thingiverse Simplified</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Bootstrap CSS über CDN -->
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <style>
    body { margin: 0; padding: 0; }
    /* Fixierter Navbar */
    .navbar {
      position: fixed;
      top: 0;
      width: 100%;
      z-index: 1000;
    }
    /* Platzhalter für den fixierten Navbar */
    .content-container {
      margin-top: 56px; /* Höhe des Navbar anpassen */
    }
  </style>
</head>
<body>
<!-- Navigation -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container">
    <a class="navbar-brand" href="../index.php">Share your Triumph</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarContent">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarContent">
      <ul class="navbar-nav ml-auto">
        <li class="nav-item">
          <a class="nav-link" href="../index.php">Startseite</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="../upload.php">Kreation hochladen</a>
        </li>
        <?php if (is_logged_in()): ?>
          <li class="nav-item">
            <a class="nav-link" href="../profile.php">Profil</a>
          </li>
          <?php if (!empty($_SESSION['is_admin'])): ?>
            <li class="nav-item">
              <a class="nav-link" href="../admin/dashboard.php">Admin Dashboard</a>
            </li>
          <?php endif; ?>
          <li class="nav-item">
            <a class="nav-link" href="../logout.php">Abmelden</a>
          </li>
        <?php else: ?>
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
<div class="container content-container">