<?php
// download.php – Download einer Datei
//
// Diese Datei ermöglicht es, eine Datei aus dem Upload-Ordner herunterzuladen.
// Dabei wird der Dateiname über den GET-Parameter "file" übergeben.

// Binde die Datei mit gemeinsamen Hilfsfunktionen ein, z.B. für sichere Sessions und Escape-Funktion.
require_once 'includes/functions.php';

// Starte eine sichere Session, um sicherzustellen, dass Session-Daten verfügbar sind,
// auch wenn diese Seite keinen direkten Login-Check durchführt.
secure_session_start();

// Überprüfe, ob der GET-Parameter "file" gesetzt ist.
// Wenn "file" nicht übergeben wird, beende das Skript mit einer Fehlermeldung.
if (!isset($_GET['file'])) {
  die("Keine Datei angegeben.");
}

// Hole den Dateinamen aus dem GET-Parameter und wende die Funktion basename() an,
// um eventuelle Pfadangaben zu entfernen. Das erhöht die Sicherheit.
$filename = basename($_GET['file']);

// Setze den Pfad zur Datei im Upload-Verzeichnis.
// Hier wird angenommen, dass sich der Ordner "uploads" im Root-Verzeichnis befindet.
$filepath = 'uploads/' . $filename;

// Überprüfe, ob die Datei existiert.
// Falls die Datei nicht gefunden wird, beende das Skript mit einer Fehlermeldung.
if (!file_exists($filepath)) {
  die("Datei nicht gefunden.");
}

// Sende die notwendigen HTTP-Header, um den Download der Datei zu initiieren.

// Beschreibt die Art der Übertragung der Datei.
header('Content-Description: File Transfer');
// Setzt den Content-Type als "application/octet-stream", um den Browser anzuweisen,
// dass es sich um eine Binärdatei handelt, die heruntergeladen werden soll.
header('Content-Type: application/octet-stream');
// Erzwinge, dass die Datei als Attachment (Download) behandelt wird und setze den Dateinamen.
header('Content-Disposition: attachment; filename="' . $filename . '"');
// Setze den Ablauf der Datei auf 0, sodass der Browser nicht cached.
header('Expires: 0');
// Erzwinge, dass der Browser die Datei neu validiert, falls ein Cache existiert.
header('Cache-Control: must-revalidate');
// Setzt den Pragma-Header, um den Cache zu kontrollieren.
header('Pragma: public');
// Setzt die Content-Length, damit der Browser weiß, wie groß die Datei ist.
header('Content-Length: ' . filesize($filepath));

// Lese die Datei und sende ihren Inhalt an den Browser.
// Dadurch wird der Download der Datei gestartet.
readfile($filepath);

// Beende das Skript.
exit;
?>
