<?php
// register.php – Ermöglicht die Registrierung (C13)
require_once 'includes/db.php';
require_once 'includes/functions.php';
secure_session_start();

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Serverseitige Validierung (C6)
    $username         = trim($_POST['username'] ?? '');
    $email            = trim($_POST['email'] ?? '');
    $password         = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $errors[] = "Alle Felder müssen ausgefüllt sein.";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Ungültige E-Mail-Adresse.";
    }
    if ($password !== $confirm_password) {
        $errors[] = "Passwörter stimmen nicht überein.";
    }
    // Weitere Prüfungen (z. B. Mindestlänge) können hier ergänzt werden

    if (empty($errors)) {
        // Überprüfe, ob Benutzername oder E-Mail bereits existiert
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = :username OR email = :email");
        $stmt->execute([':username' => $username, ':email' => $email]);
        if ($stmt->fetch()) {
            $errors[] = "Benutzername oder E-Mail existiert bereits.";
        } else {
            // Passwort sicher hashen (C11)
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            // Benutzer in der DB speichern
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
<?php include 'header.php'; ?>
<h1>Registrieren</h1>
<?php if (!empty($errors)): ?>
    <div style="color:red;">
        <?php foreach ($errors as $error) echo "<p>" . escape($error) . "</p>"; ?>
    </div>
<?php endif; ?>
<form action="register.php" method="post">
    <label for="username">Benutzername:</label>
    <input type="text" name="username" id="username" required value="<?php echo isset($username) ? escape($username) : ''; ?>"><br>

    <label for="email">E-Mail:</label>
    <input type="email" name="email" id="email" required value="<?php echo isset($email) ? escape($email) : ''; ?>"><br>

    <label for="password">Passwort:</label>
    <input type="password" name="password" id="password" required><br>

    <label for="confirm_password">Passwort wiederholen:</label>
    <input type="password" name="confirm_password" id="confirm_password" required><br>

    <button type="submit">Registrieren</button>
</form>
<?php include 'footer.php'; ?>
