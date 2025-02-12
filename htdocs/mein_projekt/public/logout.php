<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// C9: Session sicher beenden
session_unset();
session_destroy();

// C10: Session-Hijacking erschweren
setcookie(session_name(), '', time() - 3600, '/');

header("Location: ?page=home");
exit();
?>