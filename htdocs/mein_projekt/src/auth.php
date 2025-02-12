<?php
require_once __DIR__ . '/../config/db.php';

/**
 * C13: Registriert einen neuen Benutzer mit sicherem Passwort-Hashing.
 */
function registerUser($username, $password) {
    global $db;

    // C5, C6: Validierung der Eingaben
    if (strlen($username) < 3) {
        echo "❌ Der Benutzername muss mindestens 3 Zeichen lang sein.";
        return;
    }
    if (strlen($password) < 6) {
        echo "❌ Das Passwort muss mindestens 6 Zeichen lang sein.";
        return;
    }

    // C7: Script-Injection verhindern
    $username = htmlspecialchars($username);

    // C19: SQL-Injection verhindern durch Prepared Statements
    $stmt = $db->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        echo "❌ Benutzername bereits vergeben.";
        return;
    }

    // C11: Passwort sicher hashen (BCRYPT + Salt)
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    // Benutzer speichern
    $stmt = $db->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
    $stmt->bind_param("ss", $username, $hashedPassword);

    if ($stmt->execute()) {
        echo "✅ Registrierung erfolgreich!";
        header("Location: ?page=login");
        exit();
    } else {
        echo "❌ Fehler bei der Registrierung.";
    }
}

/**
 * C14: Loggt einen Benutzer ein.
 * C10: Erschwert Session-Fixation.
 */
function loginUser($username, $password) {
    global $db;

    $stmt = $db->prepare("SELECT id, password FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $hashedPassword);
        $stmt->fetch();

        if (password_verify($password, $hashedPassword)) {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            // C10: Session-Fixation verhindern
            session_regenerate_id(true);

            $_SESSION['user_id'] = $id;
            header("Location: ?page=dashboard");
            exit();
        } else {
            echo "❌ Falsches Passwort.";
        }
    } else {
        echo "❌ Benutzer nicht gefunden.";
    }
}
?>