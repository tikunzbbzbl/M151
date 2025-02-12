<?php
// login.php – Ermöglicht die Anmeldung (C14)
require_once 'includes/db.php';
require_once 'includes/functions.php';
secure_session_start();

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $errors[] = "Benutzername und Passwort müssen ausgefüllt sein.";
    } else {
        // Hole Benutzerinformationen
        $stmt = $pdo->prepare("SELECT id, password FROM users WHERE username = :username");
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Login erfolgreich, Session-Daten setzen (C8, C10)
            session_regenerate_id(true);
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['username']  = $username;
            header("Location: index.php");
            exit;
        } else {
            $errors[] = "Ungültiger Benutzername oder Passwort.";
        }
    }
}
?>
<?php include 'includes/header.php'; ?>
<h1>Anmelden</h1>
<?php if (!empty($errors)): ?>
    <div style="color:red;">
        <?php foreach ($errors as $error) echo "<p>" . escape($error) . "</p>"; ?>
    </div>
<?php endif; ?>
<form action="login.php" method="post">
    <label for="username">Benutzername:</label>
    <input type="text" name="username" id="username" required value="<?php echo isset($username) ? escape($username) : ''; ?>"><br>

    <label for="password">Passwort:</label>
    <input type="password" name="password" id="password" required><br>

    <button type="submit">Anmelden</button>
</form>
<?php include 'includes/footer.php'; ?>
