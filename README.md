# PHP-Projekt mit Bildupload-Funktionalität

Dieses Projekt ist eine PHP-basierte Webanwendung, die eine sichere Benutzeranmeldung, Profilbild-Upload und eine Verwaltung von Einträgen mit Bildergalerie ermöglicht.

## Funktionen

- **Benutzerregistrierung und Anmeldung**
  - Sichere Passwort-Speicherung mit Hashing und Salting
  - Profilbilder für Benutzer
  - Passwortänderung für angemeldete Benutzer

- **Eintrags-Verwaltung**
  - Erstellen, Bearbeiten und Löschen von Einträgen
  - Nur Eigentümer können ihre Einträge bearbeiten oder löschen
  - Alle Benutzer können alle Einträge ansehen

- **Bildergalerie**
  - Upload mehrerer Bilder pro Eintrag
  - Automatische Thumbnail-Generierung mit dem ersten Bild
  - Bildanzeige in Vollansicht
  - Manuelles Setzen eines Thumbnails

- **Sicherheitsfeatures**
  - Schutz vor SQL-Injection durch Prepared Statements
  - Schutz vor XSS durch Eingabebereinigung
  - Validierung von Formulareingaben (client- und serverseitig)
  - Geschützte Bereiche nur für angemeldete Benutzer

## Installation

### Voraussetzungen

- PHP 7.4 oder höher
- MySQL 5.7 oder höher
- Webserver (Apache oder Nginx)

### Datenbankeinrichtung

1. Erstellen Sie eine neue MySQL-Datenbank:
   ```sql
   CREATE DATABASE mein_projekt CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

2. Erstellen Sie einen Datenbankbenutzer mit eingeschränkten Rechten:
   ```sql
   CREATE USER 'projekt_user'@'localhost' IDENTIFIED BY 'IhrSicheresPasswort';
   GRANT SELECT, INSERT, UPDATE, DELETE ON mein_projekt.* TO 'projekt_user'@'localhost';
   FLUSH PRIVILEGES;
   ```

3. Importieren Sie die Datenbanktabellen:
   ```sql
   USE mein_projekt;
   
   -- Benutzer-Tabelle
   CREATE TABLE benutzer (
       id INT AUTO_INCREMENT PRIMARY KEY,
       vorname VARCHAR(50) NOT NULL,
       nachname VARCHAR(50) NOT NULL,
       email VARCHAR(100) NOT NULL UNIQUE,
       passwort VARCHAR(255) NOT NULL,
       profilbild VARCHAR(255) DEFAULT NULL,
       erstellt_am DATETIME NOT NULL,
       aktualisiert_am DATETIME DEFAULT NULL
   );

   -- Einträge-Tabelle
   CREATE TABLE eintraege (
       id INT AUTO_INCREMENT PRIMARY KEY,
       benutzer_id INT NOT NULL,
       titel VARCHAR(100) NOT NULL,
       beschreibung TEXT NOT NULL,
       erstellt_am DATETIME NOT NULL,
       aktualisiert_am DATETIME DEFAULT NULL,
       FOREIGN KEY (benutzer_id) REFERENCES benutzer(id) ON DELETE CASCADE
   );

   -- Tabelle für Eintragsbilder
   CREATE TABLE eintrag_bilder (
       id INT AUTO_INCREMENT PRIMARY KEY,
       eintrag_id INT NOT NULL,
       dateiname VARCHAR(255) NOT NULL,
       ist_thumbnail TINYINT(1) DEFAULT 0,
       hochgeladen_am DATETIME NOT NULL,
       FOREIGN KEY (eintrag_id) REFERENCES eintraege(id) ON DELETE CASCADE
   );
   ```

### Projekteinrichtung

1. Laden Sie den Projektcode in ein Verzeichnis auf Ihrem Webserver hoch.

2. Erstellen Sie diese Verzeichnisstruktur, falls sie nicht bereits vorhanden ist:
   ```
   /
   ├── config/
   ├── includes/
   └── public/
       └── uploads/
   ```

3. Erstellen Sie die Datei `config/database.php` und tragen Sie Ihre Datenbankinformationen ein:
   ```php
   <?php
   $db_host = 'localhost';
   $db_name = 'mein_projekt';
   $db_user = 'projekt_user';
   $db_pass = 'IhrSicheresPasswort';
   
   try {
       $pdo = new PDO(
           "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4",
           $db_user,
           $db_pass,
           [
               PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
               PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
               PDO::ATTR_EMULATE_PREPARES => false
           ]
       );
   } catch (PDOException $e) {
       die('Datenbankfehler: ' . $e->getMessage());
   }
   ?>
   ```

4. Stellen Sie sicher, dass das Verzeichnis `public/uploads/` für den Webserver beschreibbar ist:
   ```bash
   chmod 755 public/uploads/
   ```

5. Platzieren Sie eine Datei mit dem Namen `default_profile.png` im Verzeichnis `public/uploads/` als Standardprofilbild.

### Webserver-Konfiguration

Konfigurieren Sie Ihren Webserver so, dass das `public`-Verzeichnis als Document Root dient. Beispiel für Apache:

```apache
<VirtualHost *:80>
    ServerName meinprojekt.local
    DocumentRoot /pfad/zum/projekt/public
    
    <Directory /pfad/zum/projekt/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

## Verwendung

1. **Registrierung und Anmeldung**:
   - Besuchen Sie die Startseite und klicken Sie auf "Registrieren"
   - Füllen Sie das Formular aus und erstellen Sie ein Konto
   - Melden Sie sich mit Ihrer E-Mail und Ihrem Passwort an

2. **Profilbild hochladen**:
   - Nach der Anmeldung klicken Sie auf das Profilbild-Symbol in der Navigationsleiste
   - Wählen Sie ein neues Profilbild aus und laden Sie es hoch

3. **Einträge verwalten**:
   - Auf dem Dashboard können Sie neue Einträge erstellen
   - Sie können Bilder zu Ihren Einträgen hinzufügen
   - Das erste hochgeladene Bild wird automatisch als Thumbnail verwendet
   - Sie können Ihre eigenen Einträge bearbeiten oder löschen

4. **Einträge ansehen**:
   - Auf der Startseite werden alle Einträge angezeigt
   - Klicken Sie auf "Ansehen", um die Details eines Eintrags zu sehen
   - Klicken Sie auf ein Bild, um es in voller Größe anzuzeigen

## Projektstruktur

```
/
├── config/
│   └── database.php         - Datenbankverbindungskonfiguration
├── includes/
│   ├── functions.php        - Allgemeine Hilfsfunktionen
│   ├── header.php           - HTML-Header für alle Seiten
│   ├── footer.php           - HTML-Footer für alle Seiten
│   ├── session.php          - Session-Handling und Zugriffsrechte
│   └── validation.php       - Funktionen zur Formularvalidierung
└── public/
    ├── css/
    │   └── style.css        - CSS-Stylesheet
    ├── uploads/             - Verzeichnis für hochgeladene Bilder
    │   └── default_profile.png  - Standard-Profilbild
    ├── index.php            - Startseite mit Übersicht aller Einträge
    ├── register.php         - Registrierungsseite
    ├── login.php            - Anmeldeseite
    ├── logout.php           - Abmeldeseite
    ├── dashboard.php        - Übersicht der eigenen Einträge
    ├── profile_edit.php     - Seite zum Hochladen des Profilbilds
    ├── create_entry.php     - Seite zum Erstellen eines Eintrags
    ├── edit_entry.php       - Seite zum Bearbeiten eines Eintrags
    ├── view_entry.php       - Detailansicht eines Eintrags
    ├── view_image.php       - Großansicht eines Bildes
    └── delete_entry.php     - Skript zum Löschen eines Eintrags
```

## Sicherheit

- Alle Benutzereingaben werden bereinigt, um XSS-Angriffe zu verhindern
- Passwörter werden mit modernen Hashing-Algorithmen gespeichert
- SQL-Injection wird durch Prepared Statements vermieden
- Dateitypen und -größen werden beim Upload überprüft
- Sitzungen sind vor Session-Fixation und Session-Hijacking geschützt

## Erweiterungsmöglichkeiten

- Kommentarfunktion für Einträge
- Unterstützung für Tags oder Kategorien
- Suchfunktion für Einträge
- Fortgeschrittenere Benutzerprofile
- Admin-Bereich zur Verwaltung aller Inhalte

## Lizenz

Dieses Projekt ist für Bildungszwecke erstellt worden und steht unter der MIT-Lizenz.
