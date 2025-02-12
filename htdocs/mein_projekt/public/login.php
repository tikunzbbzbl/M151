<?php
require_once __DIR__ . '/../src/auth.php';

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["login"])) {
    loginUser($_POST["username"], $_POST["password"]);
}
?>

<h2>Anmelden</h2>
<form method="post">
    <label>Benutzername:</label>
    <input type="text" name="username" required>
    
    <label>Passwort:</label>
    <input type="password" name="password" required>
    
    <button type="submit" name="login">Anmelden</button>
</form>