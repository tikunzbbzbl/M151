<!DOCTYPE html>
<html lang="<?php echo $language; ?>">
  <head>
    <meta charset="UTF-8">
    <title>Auträge M151</title>
  </head>
  <body>
    "Übung macht den Meister" <br>
    "es ist noch kein Meister vom Himmel gefallen" <br>
  </body>
</html>
<?php
// Vorname und Name als String gespeichert werden, muss man sie in Anführungszeichen schreiben.
// Der Jahrgang wird ohne Anführungszeichen gespeichert, da es eine Zahl ist

//How-To String
// 1. Mit dem Punkt (.) Operator
// 2. Mit dem Zuweisungsoperator (.=), um einen bestehenden String zu erweitern

$text = "Hallo";
$text .= " Welt!"; // Ergebnis: "Hallo Welt!"

$vorname = "Tim"; // String
$name = "Kunz"; // String
$jahrgang = 1998; // Zahl 

// Ausgabe der Variablen
echo "Vorname: $vorname\n";
echo "Name: $name\n";
echo "Jahrgang: $jahrgang\n <br>";
echo "Mein Name ist $vorname $name und ich bin $jahrgang geboren.<br>";
echo $text;
?>