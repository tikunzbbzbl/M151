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
$text .= " Welt"; // Ergebnis: "Hallo Welt!"

$vorname = "Tim"; // String
$name = "Kunz"; // String
$jahrgang = 1998; // Zahl 

// Ausgabe der Variablen
echo "Vorname: $vorname\n";
echo "Name: $name\n";
echo "Jahrgang: $jahrgang\n <br>";
echo "Mein Name ist $vorname $name und ich bin $jahrgang geboren.<br>";
echo $text . "<br>";

$monate = [
  "Januar", "Februar", "März", "April", "Mai", "Juni",
  "Juli", "August", "September", "Oktober", "November", "Dezember"
];

print_r($monate);


$monate2 = [
  1 => "Januar", 2 => "Februar", 3 => "März", 4 => "April",
  5 => "Mai", 6 => "Juni", 7 => "Juli", 8 => "August",
  9 => "September", 10 => "Oktober", 11 => "November", 12 => "Dezember"
];

$monat2 = date("F"); // Holt den Monat

//echo date("n");  // 1-12 (Monat ohne führende Null)
//echo date("m");  // 01-12 (Monat mit führender Null)
//echo date("F");  // Vollständiger Monatsname (z. B. "Januar")
//echo date("M");  // Abgekürzter Monatsname (z. B. "Jan")

echo "<br> Monat ist: " . $monat2;
?>