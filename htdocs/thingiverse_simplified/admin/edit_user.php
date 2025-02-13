<?php
// admin/edit_user.php
require_once '../includes/db.php';
require_once '../includes/functions.php';
secure_session_start();

// Prüfen, ob der aktuelle Benutzer Admin ist
if (!is_logged_in() || empty($_SESSION['is_admin'])) {
    header("Location: ../login.php");
    exit;
}

// Prüfen, ob eine ID übergeben wurde
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Ungültige Anfrage. Keine Benutzer-ID übergeben.");
}

$user_id = (int)$_GET['id'];

// Benutzerdaten abrufen
$stmt = $pdo->prepare("SELECT id, username, email, is_admin FROM users WHERE id = :id");
$stmt->execute([':id' => $user_id]);
$user = $stmt->fetch();

if (!$user) {
    die("Benutzer nicht gefunden.");
}

$errors = [];
$success = '';

// Wenn das Formular abgeschickt wurde
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username    = trim($_POST['username'] ?? '');
    $email       = trim($_POST['email'] ?? '');
    $is_admin    = isset($_POST['is_admin']) ? 1 : 0; 
    $newPassword = trim($_POST['password'] ?? '');
    
    if (empty($username)) {
        $errors[] = "Der Benutzername darf nicht leer sein.";
    }
    if (empty($email)) {
        $errors[] = "Die E-Mail darf nicht leer sein.";
    }
    
    // Falls keine Fehler vorliegen, aktualisieren wir den Datensatz
    if (empty($errors)) {
        // Wenn ein neues Passwort übergeben wurde, muss es gehasht werden
        if (!empty($newPassword)) {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmtUpdate = $pdo->prepare("
                UPDATE users
                SET username = :username,
                    email    = :email,
                    is_admin = :is_admin,
                    password = :password
                WHERE id = :id
            ");
            $stmtUpdate->execute([
                ':username' => $username,
                ':email'    => $email,
                ':is_admin' => $is_admin,
                ':password' => $hashedPassword,
                ':id'       => $user_id
            ]);
        } else {
            // Passwort bleibt unverändert
            $stmtUpdate = $pdo->prepare("
                UPDATE users
                SET username = :username,
                    email    = :email,
                    is_admin = :is_admin
                WHERE id = :id
            ");
            $stmtUpdate->execute([
                ':username' => $username,
                ':email'    => $email,
                ':is_admin' => $is_admin,
                ':id'       => $user_id
            ]);
        }
        
        $success = "Benutzerdaten erfolgreich aktualisiert.";
        
        // Aktualisierte Daten neu laden
        $stmt = $pdo->prepare("SELECT id, username, email, is_admin FROM users WHERE id = :id");
        $stmt->execute([':id' => $user_id]);
        $user = $stmt->fetch();
    }
}
?>
<?php include '../includes/header.php'; ?>
<div class="container mt-5">
    <h1>Benutzer bearbeiten</h1>
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <?php foreach ($errors as $error): ?>
                <p><?php echo escape($error); ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
        <div class="alert alert-success">
            <p><?php echo escape($success); ?></p>
        </div>
    <?php endif; ?>
    
    <form action="edit_user.php?id=<?php echo $user_id; ?>" method="post">
        <div class="form-group">
            <label for="username">Benutzername:</label>
            <input type="text" name="username" id="username" class="form-control"
                   value="<?php echo escape($user['username']); ?>" required>
        </div>
        <div class="form-group">
            <label for="email">E-Mail:</label>
            <input type="email" name="email" id="email" class="form-control"
                   value="<?php echo escape($user['email']); ?>" required>
        </div>
        <div class="form-group form-check">
            <input type="checkbox" name="is_admin" id="is_admin" class="form-check-input"
                   <?php echo $user['is_admin'] ? 'checked' : ''; ?>>
            <label for="is_admin" class="form-check-label">Admin?</label>
        </div>
        <div class="form-group">
            <label for="password">Neues Passwort (optional):</label>
            <input type="password" name="password" id="password" class="form-control"
                   placeholder="Leer lassen, um das Passwort nicht zu ändern">
        </div>
        <button type="submit" class="btn btn-primary">Speichern</button>
        <a href="manage_users.php" class="btn btn-secondary">Abbrechen</a>
    </form>
</div>
<?php include '../includes/footer.php'; ?>