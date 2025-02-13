<?php
// login.php
require_once 'includes/db.php';
require_once 'includes/functions.php';
secure_session_start();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Benutzereingaben
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    
    if (empty($username) || empty($password)) {
        $errors[] = "Bitte alle Felder ausfüllen.";
    }
    
    if (empty($errors)) {
        // Benutzer aus der Datenbank abfragen
        $stmt = $pdo->prepare("SELECT id, username, password, is_admin FROM users WHERE username = :username");
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            // Session regenerieren und Daten speichern
            session_regenerate_id(true);
            $_SESSION['user_id']  = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['is_admin'] = ($user['is_admin'] == 1); // Admin-Status als boolscher Wert
            
            header("Location: index.php");
            exit;
        } else {
            $errors[] = "Ungültiger Benutzername oder Passwort.";
        }
    }
}
?>
<?php include 'includes/header.php'; ?>
<div class="container mt-5">
  <h1>Anmelden</h1>
  <?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
      <?php foreach ($errors as $error): ?>
        <p><?php echo escape($error); ?></p>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
  <form action="login.php" method="post">
    <div class="form-group">
      <label for="username">Benutzername:</label>
      <input type="text" name="username" id="username" class="form-control" required>
    </div>
    <div class="form-group">
      <label for="password">Passwort:</label>
      <input type="password" name="password" id="password" class="form-control" required>
    </div>
    <button type="submit" class="btn btn-primary">Anmelden</button>
  </form>
</div>
<?php include 'includes/footer.php'; ?>