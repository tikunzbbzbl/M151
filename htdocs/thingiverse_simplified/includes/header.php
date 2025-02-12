<?php
// header.php
require_once 'functions.php';
secure_session_start();
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Thingiverse Simplified</title>
    <!-- HTML5 Validierung via Attribute -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        /* Einfache Styles für Navigation und Layout */
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; }
        header { background: #333; color: #fff; padding: 10px; }
        nav a { color: #fff; margin-right: 10px; text-decoration: none; }
        .container { padding: 20px; }
    </style>
</head>
<body>
<header>
    <nav>
        <a href="index.php">Startseite</a>
        <?php if (is_logged_in()): ?>
            <a href="profile.php">Profil</a>
            <a href="upload.php">Kreation hochladen</a>
            <a href="logout.php">Abmelden</a>
        <?php else: ?>
            <a href="register.php">Registrieren</a>
            <a href="login.php">Anmelden</a>
        <?php endif; ?>
    </nav>
</header>
<div class="container">
