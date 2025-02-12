<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Falls der Benutzer eingeloggt ist, leite ihn zum Dashboard weiter
if (isset($_SESSION['user_id'])) {
    header("Location: public/dashboard.php");
    exit();
} else {
    // Falls nicht eingeloggt, leite zur Login-Seite weiter
    header("Location: public/login.php");
    exit();
}
?>