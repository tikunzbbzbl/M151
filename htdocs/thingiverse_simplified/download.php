<?php
// download.php – Download einer Datei
require_once 'includes/functions.php';
secure_session_start();

if (!isset($_GET['file'])) {
  die("Keine Datei angegeben.");
}

$filename = basename($_GET['file']);
$filepath = 'uploads/' . $filename;

if (!file_exists($filepath)) {
  die("Datei nicht gefunden.");
}

header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($filepath));
readfile($filepath);
exit;
?>