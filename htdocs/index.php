<?php

// Initialisierung
$error = '';
$message = '';
$firstname = $lastname = $email = $username = '';

// Wurden Daten mit "POST" gesendet?
if($_SERVER['REQUEST_METHOD'] == "POST"){
  // Ausgabe des gesamten $_POST Arrays zum debuggen
  echo "<pre>";
  print_r($_POST);
  echo "</pre>";


if (empty($_POST["firstname"]) || empty($_POST["lastname"]) || empty($_POST["username"])){
    $error = "Vor- und Nachname und Benutzername erforderlich!";
} elseif (strlen($_POST["firstname"]) > 30 || strlen($_POST["lastname"]) > 30 || strlen($_POST["username"]) > 30) {
    $error = "Maximal 30 Zeichen erlaubt in Vor- und Nachname und Benutzername!";
} else {
    $firstname = htmlspecialchars($_POST["firstname"]);
    $lastname = htmlspecialchars($_POST["lastname"]);
    $username = htmlspecialchars($_POST["username"]);
}
if (!filter_var($_POST["email"], FILTER_VALIDATE_EMAIL)) {
  $error .= "Ungültige E-Mail-Adresse!<br>";
} else {
  $email = htmlspecialchars($_POST["email"]);
}
  /** TODO 
   * alle Benutzereingaben gemäss Auftrag validieren
   * Variable vorhanden
   * nicht leer
   * minimale Länge
   * maximale Länge
   * 
   * sonst Fehlermeldung an Variable $error anhängen.
   * $error .= "Geben Sie bitte einen korrekten Vornamen ein";
   */

  // keine Fehler vorhanden
  if(empty($error)){
    $message = "Keine Fehler vorhanden";
  }
}



?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registrierung</title>

    <!-- Bootstrap -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
  </head>
  <body>

    <div class="container">
      <h1>Registrierung</h1>
      <p>
        Bitte registrieren Sie sich, damit Sie diesen Dienst benutzen können.
      </p>
      <?php
        // Ausgabe der Fehlermeldungen
        if(strlen($error)){
          echo "<div class=\"alert alert-danger\" role=\"alert\">" . $error . "</div>";
        } elseif (strlen($message)){
          echo "<div class=\"alert alert-success\" role=\"alert\">" . $message . "</div>";
        }
      ?>
      <form action="" method="post">
        <!-- TODO: Clientseitige Validierung: vorname -->
        <div class="form-group">
          <label for="firstname">Vorname *</label>
          <input type="text" name="firstname" maxlength="35" class="form-control" id="firstname"
                  value="<?php echo htmlspecialchars($firstname) ?>"
                  required placeholder="Geben Sie Ihren Vornamen an.">
        </div>
        <!-- TODO: Clientseitige Validierung: nachname -->
        <div class="form-group">
          <label for="lastname">Nachname *</label>
          <input type="text" name="lastname" maxlength="35" class="form-control" id="lastname"
                  value="<?php echo htmlspecialchars($lastname) ?>"
                  required placeholder="Geben Sie Ihren Nachnamen an">
        </div>
        <!-- TODO: Clientseitige Validierung: email -->
        <div class="form-group">
          <label for="email">Email *</label>
          <input type="email" name="email" maxlength="100" class="form-control" id="email"
                  value="<?php echo $email ?>"
                  required placeholder="Geben Sie Ihre Email-Adresse an.">
        </div>
        <!-- TODO: Clientseitige Validierung: benutzername -->
        <div class="form-group">
          <label for="username">Benutzername *</label>
          <input type="text" name="username" maxlength="30" class="form-control" id="username"
                  value="<?php echo htmlspecialchars($username) ?>"
                  required placeholder="Gross- und Keinbuchstaben, min 6 Zeichen.">
        </div>
        <!-- TODO: Clientseitige Validierung: password -->
        <div class="form-group">
          <label for="password">Password *</label>
          <input type="password" name="password" maxlength="255" class="form-control" id="password"
                  required placeholder="Gross- und Kleinbuchstaben, Zahlen, Sonderzeichen, min. 8 Zeichen, keine Umlaute">
        </div>
        <button type="submit" name="button" value="submit" class="btn btn-info">Senden</button>
        <button type="reset" name="button" value="reset" class="btn btn-warning">Löschen</button>
      </form>
    </div>

    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
  </body>
</html>
