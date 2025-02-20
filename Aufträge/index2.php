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


$monat2 = date("F"); // Holt den Monat

//echo date("n");  // 1-12 (Monat ohne führende Null)
//echo date("m");  // 01-12 (Monat mit führender Null)
//echo date("F");  // Vollständiger Monatsname (z. B. "Januar")
//echo date("M");  // Abgekürzter Monatsname (z. B. "Jan")

echo "<br> Monat ist: " . $monat2;
sort($monate);
foreach($monate as $monat){
  echo "<br>". $monat ."<br>";
}

$a = 10;
$b = 3;

echo "<br>Grundlegende mathematische Operatoren:<br>";
echo "Addition: $a + $b = " . ($a + $b) . "<br>";  // Addition
echo "Subtraktion: $a - $b = " . ($a - $b) . "<br>";  // Subtraktion
echo "Multiplikation: $a * $b = " . ($a * $b) . "<br>";  // Multiplikation
echo "Division: $a / $b = " . ($a / $b) . "<br>";  // Division
echo "Modulus (Rest der Division): $a % $b = " . ($a % $b) . "<br>";  // Modulo

echo "<br>Erweiterte mathematische Operatoren:<br>";
echo "Potenzierung: $a ** $b = " . ($a ** $b) . "<br>";  // Potenzierung (10^3 = 1000)
echo "Ganzzahlige Division: intdiv($a, $b) = " . intdiv($a, $b) . "<br>";  // Ganzzahlige Division

echo "<br>Inkrement und Dekrement:<br>";
$a++;
echo "Post-Inkrement (\$a++): $a<br>";  // Erhöht um 1
$a--;
echo "Post-Dekrement (\$a--): $a<br>";  // Verringert um 1
++$a;
echo "Pre-Inkrement (++\$a): $a<br>";  // Erhöht um 1 vor Verwendung
--$a;
echo "Pre-Dekrement (--\$a): $a<br>";  // Verringert um 1 vor Verwendung

/*
+ Addition
- Subtraktion
* Multiplikation
/ Division
% Modulo (Rest einer Division)
** Potenzierung (a ** b → a hoch b)
intdiv(a, b) Ganzzahlige Division (z. B. 10 / 3 = 3, ohne Nachkommastellen)
++ Erhöht eine Zahl um 1 ($a++, ++$a)
-- Verringert eine Zahl um 1 ($a--, --$a)
*/


?>