<?php
// profile.php
require_once 'includes/db.php';
require_once 'includes/functions.php';
secure_session_start();

if (!is_logged_in()) {
    header("Location: login.php");
    exit;
}

// Benutzerdaten aus der Datenbank abrufen
$stmt = $pdo->prepare("SELECT id, username, email, profile_picture FROM users WHERE id = :id");
$stmt->execute([':id' => $_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user) {
    die("Benutzer nicht gefunden.");
}

// Profilbild ermitteln: Falls keines gesetzt ist, verwende einen Platzhalter
if (!empty($user['profile_picture'])) {
    $profilePic = 'uploads/' . $user['profile_picture'];
} else {
    $profilePic = 'uploads/placeholder.png';
}
?>
<?php include 'includes/header.php'; ?>
<div class="container mt-5">
    <h1>Mein Profil</h1>
    <div class="row">
        <div class="col-md-3 text-center">
            <img src="<?php echo escape($profilePic); ?>" alt="Profilbild" class="img-fluid rounded-circle" style="max-width:200px;">
        </div>
        <div class="col-md-9">
            <h3><?php echo escape($user['username']); ?></h3>
            <p>Email: <?php echo escape($user['email']); ?></p>
            <!-- Weitere Profileinstellungen oder Links können hier ergänzt werden -->
            <a href="profile/edit_profile.php" class="btn btn-primary mb-2">Profil bearbeiten</a>
            <a href="profile/profile_posts.php" class="btn btn-secondary mb-2">Meine Kreationen verwalten</a>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>