<?php
// register.php – Ermöglicht die Registrierung (C13)
require_once 'includes/db.php';
require_once 'includes/functions.php';
secure_session_start();

$errors = [];

function get_post($key) {
    return trim($_POST[$key] ?? '');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Daten aus POST holen
    $username = get_post('username');
    $email = get_post('email');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validierung
    if (!$username || !$email || !$password || !$confirm_password) {
        $errors[] = "Alle Felder müssen ausgefüllt sein.";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Ungültige E-Mail-Adresse.";
    }
    if ($password !== $confirm_password) {
        $errors[] = "Passwörter stimmen nicht überein.";
    }
    
    if (!$errors) {
        // Prüfen, ob Benutzername oder E-Mail existiert
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = :username OR email = :email");
        $stmt->execute([':username' => $username, ':email' => $email]);
        
        if ($stmt->fetch()) {
            $errors[] = "Benutzername oder E-Mail existiert bereits.";
        } else {
            // Passwort hashen und speichern
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (:username, :email, :password)");
            if ($stmt->execute([':username' => $username, ':email' => $email, ':password' => $password_hash])) {
                header("Location: login.php");
                exit;
            } else {
                $errors[] = "Fehler beim Registrieren.";
            }
        }
    }
}
?>
<?php include 'includes/header.php'; ?>
<h1>Registrieren</h1>
<?php if ($errors): ?>
    <div style="color:red;">
        <?php foreach ($errors as $error) echo "<p>" . escape($error) . "</p>"; ?>
    </div>
<?php endif; ?>

<form action="register.php" method="post">
    <label for="username">Benutzername:</label>
    <input type="text" name="username" id="username" required value="<?php echo escape(get_post('username')); ?>"><br>

    <label for="email">E-Mail:</label>
    <input type="email" name="email" id="email" required value="<?php echo escape(get_post('email')); ?>"><br>

    <label for="password">Passwort:</label>
    <input type="password" name="password" id="password" required><br>

    <label for="confirm_password">Passwort wiederholen:</label>
    <input type="password" name="confirm_password" id="confirm_password" required><br>

    <button type="submit">Registrieren</button>
</form>
<?php include 'includes/footer.php'; ?>