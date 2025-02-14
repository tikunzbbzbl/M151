<?php
// register.php
require_once 'includes/db.php';
require_once 'includes/functions.php';
secure_session_start();

$errors = [];
$username = $email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Benutzereingaben holen
    $username         = trim($_POST['username'] ?? '');
    $email            = trim($_POST['email'] ?? '');
    $password         = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Serverseitige Validierung
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $errors[] = "Alle Felder müssen ausgefüllt werden.";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Ungültige E-Mail-Adresse.";
    }
    if ($password !== $confirm_password) {
        $errors[] = "Die Passwörter stimmen nicht überein.";
    }

    // Weitere Prüfungen können hier hinzugefügt werden...

    if (empty($errors)) {
        // Überprüfen, ob Benutzername oder E-Mail bereits existiert
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = :username OR email = :email");
        $stmt->execute([':username' => $username, ':email' => $email]);
        if ($stmt->fetch()) {
            $errors[] = "Benutzername oder E-Mail existiert bereits.";
        } else {
            // Passwort hashen
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            // Benutzer in der Datenbank speichern
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (:username, :email, :password)");
            if ($stmt->execute([':username' => $username, ':email' => $email, ':password' => $password_hash])) {
                header("Location: login.php");
                exit;
            } else {
                $errors[] = "Fehler beim Registrieren. Bitte erneut versuchen.";
            }
        }
    }
}
?>
<?php include 'includes/header.php'; ?>
<div class="container mt-5">
    <h1>Registrieren</h1>
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <?php foreach ($errors as $error): ?>
                <p><?php echo escape($error); ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <form action="register.php" method="post">
        <div class="form-group">
            <label for="username">Benutzername:</label>
            <input type="text" name="username" id="username" class="form-control" required value="<?php echo escape($username); ?>">
        </div>
        <div class="form-group">
            <label for="email">E-Mail:</label>
            <input type="email" name="email" id="email" class="form-control" required value="<?php echo escape($email); ?>">
        </div>
        <div class="form-group">
            <label for="password">Passwort:</label>
            <input type="password" name="password" id="password" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="confirm_password">Passwort wiederholen:</label>
            <input type="password" name="confirm_password" id="confirm_password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Registrieren</button>
    </form>
</div>
<?php include 'includes/footer.php'; ?>
