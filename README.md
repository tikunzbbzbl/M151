# M151 PHP-Projekt: Share Your Triumph (SYT)

Diese PHP-basierte Webanwendung ermöglicht Triumph-Motorradfahrern, ihre Motorräder zu präsentieren und Einträge mit anderen Enthusiasten zu teilen. Das System bietet Benutzerregistrierung, Bildupload, Eintrags-Verwaltung und ein Admin-Dashboard.

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

- **Administration**
  - Admin-Dashboard mit Benutzer- und Eintragsverwaltung
  - Systemstatistiken
  - Erweiterte Benutzer- und Inhaltsadministration

- **Sicherheitsfeatures**
  - Schutz vor SQL-Injection durch Prepared Statements
  - Schutz vor XSS durch Eingabebereinigung
  - Validierung von Formulareingaben (client- und serverseitig)
  - Geschützte Bereiche nur für angemeldete Benutzer und Administratoren

## Detaillierte Installationsanleitung für XAMPP/MAMP

Diese Anleitung führt Sie durch alle notwendigen Schritte, um das SYT-Projekt mit XAMPP (Windows/Linux) oder MAMP (macOS) einzurichten.

### 1. Voraussetzungen

- **XAMPP** (Windows/Linux): Version 7.4 oder höher [Download XAMPP](https://www.apachefriends.org/de/index.html)
- **MAMP** (macOS): Version 6.0 oder höher [Download MAMP](https://www.mamp.info/de/downloads/)
- **Git**: Zum Klonen des Repositories (optional)

### 2. Projektdateien herunterladen

**Option A**: Klonen Sie das Repository mit Git:
```bash
# Navigieren Sie zum htdocs-Verzeichnis von XAMPP/MAMP
# Für XAMPP unter Windows:
cd C:\xampp\htdocs

# Für XAMPP unter Linux:
cd /opt/lampp/htdocs

# Für MAMP unter macOS:
cd /Applications/MAMP/htdocs

# Klonen des Repositories
git clone https://github.com/tikunzbbzbl/M151.git
```

**Option B**: Laden Sie das Projekt als ZIP-Datei herunter und extrahieren Sie es in das htdocs-Verzeichnis Ihrer XAMPP/MAMP-Installation.

### 3. XAMPP/MAMP starten

#### Mit XAMPP (Windows/Linux):
1. Starten Sie das XAMPP Control Panel
2. Klicken Sie auf die "Start"-Buttons neben Apache und MySQL
3. Vergewissern Sie sich, dass beide Dienste laufen (grüner Status)

#### Mit MAMP (macOS):
1. Öffnen Sie die MAMP-Anwendung
2. Klicken Sie auf den "Start Server"-Button
3. Warten Sie, bis die Server starten (der Status-Indikator wird grün)

### 4. Datenbank einrichten

#### 4.1 Zugriff auf phpMyAdmin

**XAMPP**:
1. Öffnen Sie Ihren Browser
2. Geben Sie `http://localhost/phpmyadmin` in die Adressleiste ein

**MAMP**:
1. Öffnen Sie Ihren Browser
2. Klicken Sie im MAMP-Fenster auf "Open WebStart page" 
3. Klicken Sie auf den phpMyAdmin-Tab oder den phpMyAdmin-Link

#### 4.2 Datenbank erstellen

1. Wählen Sie im linken Menü "Neu"
2. Geben Sie als Datenbankname `syt_db` ein
3. Wählen Sie als Zeichenkodierung `utf8mb4_unicode_ci`
4. Klicken Sie auf "Erstellen"

#### 4.3 Datenbank-Benutzer erstellen (Optional bei lokalem Setup)

Bei einer lokalen Entwicklungsumgebung können Sie für einfaches Testing den Standard-Benutzer "root" ohne Passwort verwenden (XAMPP) oder "root" mit Passwort "root" (MAMP).

Für eine sicherere Setup (empfohlen für die Produktion) erstellen Sie einen neuen Benutzer:

1. Gehen Sie zum Tab "Benutzerkonten"
2. Klicken Sie auf "Benutzer hinzufügen"
3. Geben Sie als Benutzername `syt_user` ein
4. Wählen Sie "Lokal" als Host
5. Setzen Sie ein sicheres Passwort
6. Im Abschnitt "Globale Rechte" wählen Sie "Nur Daten" aus
7. Klicken Sie auf "OK"

#### 4.4 Datenbank-Tabellen anlegen

1. Wählen Sie die neu erstellte Datenbank `syt_db` im linken Menü aus
2. Wählen Sie den Tab "SQL"
3. Fügen Sie folgenden SQL-Code ein und klicken Sie auf "OK":

```sql
-- Benutzer-Tabelle
CREATE TABLE benutzer (
    id INT AUTO_INCREMENT PRIMARY KEY,
    vorname VARCHAR(50) NOT NULL,
    nachname VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    passwort VARCHAR(255) NOT NULL,
    profilbild VARCHAR(255) DEFAULT NULL,
    is_admin TINYINT(1) DEFAULT 0,
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

### 5. Projektstruktur einrichten

Stellen Sie sicher, dass die Projektstruktur wie folgt aussieht:

```
/M151/
├── config/
│   └── database.php        (Erstellen Sie diese Datei)
├── includes/
│   ├── functions.php
│   ├── header.php
│   ├── footer.php
│   ├── session.php
│   └── validation.php
└── public/
    ├── css/
    │   └── style.css
    ├── uploads/            (Stellen Sie sicher, dass dieses Verzeichnis existiert)
    │   └── placeholder.png  (Standard-Profilbild)
    ├── index.php
    ├── register.php
    ├── login.php
    └── ... (weitere PHP-Dateien)
```

### 6. Uploads-Verzeichnis erstellen und Standardbild hinzufügen

1. Erstellen Sie im Verzeichnis `public` einen Ordner namens `uploads`, falls dieser nicht existiert
2. Platzieren Sie eine Datei mit dem Namen `placeholder.png` im Verzeichnis `uploads` als Standardbild für Profile
3. Stellen Sie sicher, dass das Verzeichnis `uploads` für den Webserver beschreibbar ist:
   - Bei XAMPP unter Windows ist dies normalerweise bereits der Fall
   - Bei MAMP unter macOS / XAMPP unter Linux führen Sie ggf. folgende Befehle aus:
     ```bash
     chmod 755 public/uploads
     ```

### 7. Datenbankverbindung konfigurieren

Erstellen Sie die Datei `config/database.php` mit folgenden Inhalten:

Für **XAMPP** (Windows/Linux - typischerweise ohne Passwort):
```php
<?php
// Datenbankverbindungsinformationen
$db_host = 'localhost';
$db_name = 'syt_db';
$db_user = 'root';     // Standardbenutzer von XAMPP
$db_pass = '';         // Kein Passwort bei Standardinstallation

try {
    // Erstelle PDO-Verbindung
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
    die('Verbindungsfehler: ' . $e->getMessage());
}
?>
```

Für **MAMP** (macOS - Standard-Passwort ist "root"):
```php
<?php
// Datenbankverbindungsinformationen
$db_host = 'localhost';
$db_name = 'syt_db';
$db_user = 'root';     // Standardbenutzer von MAMP
$db_pass = 'root';     // Standardpasswort von MAMP

try {
    // Erstelle PDO-Verbindung
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
    die('Verbindungsfehler: ' . $e->getMessage());
}
?>
```

Wenn Sie in Schritt 4.3 einen eigenen Datenbankbenutzer erstellt haben, passen Sie `$db_user` und `$db_pass` entsprechend an.

### 8. Ersten Administrator anlegen

Nach der Installation müssen Sie einen Administrator-Account erstellen:

1. Öffnen Sie die Website in Ihrem Browser:
   - XAMPP: `http://localhost/M151/public/`
   - MAMP: Entsprechend Ihrer Port-Konfiguration, typischerweise `http://localhost:8888/M151/public/`
2. Registrieren Sie einen normalen Benutzer über die Weboberfläche
3. Öffnen Sie phpMyAdmin und führen Sie folgenden SQL-Befehl aus:
   ```sql
   UPDATE benutzer SET is_admin = 1 WHERE email = 'ihre-email@example.com';
   ```
   Ersetzen Sie `ihre-email@example.com` durch die E-Mail-Adresse, mit der Sie sich registriert haben.

### 9. Testen der Installation

1. Öffnen Sie die Website in Ihrem Browser:
   - XAMPP: `http://localhost/M151/public/`
   - MAMP: Je nach Konfiguration, typischerweise `http://localhost:8888/M151/public/`

2. Testen Sie folgende Funktionen:
   - Registrierung eines neuen Benutzers
   - Anmeldung mit Ihrem Admin-Account
   - Zugriff auf das Admin-Dashboard
   - Erstellen eines neuen Eintrags mit Bildern
   - Hochladen eines Profilbilds

## Fehlerbehebung

### Probleme mit Datei-Uploads

1. **Upload-Verzeichnis nicht beschreibbar**:
   - XAMPP (Windows): Öffnen Sie die Eigenschaften des Verzeichnisses `public/uploads`, gehen Sie zum Reiter "Sicherheit" und stellen Sie sicher, dass der Benutzer "Everyone" Schreibrechte hat.
   - MAMP (macOS): Führen Sie im Terminal folgende Befehle aus:
     ```bash
     chmod 755 /Applications/MAMP/htdocs/M151/public/uploads
     ```

2. **Dateigrößenbeschränkung**:
   - XAMPP: Bearbeiten Sie die Datei `C:\xampp\php\php.ini`
   - MAMP: Bearbeiten Sie die Datei `/Applications/MAMP/bin/php/phpX.X.X/conf/php.ini` (X.X.X ist Ihre PHP-Version)
   - Suchen und erhöhen Sie folgende Werte:
     ```
     upload_max_filesize = 10M
     post_max_size = 10M
     ```
   - Neustarten Sie den Webserver nach den Änderungen

### Datenbank-Verbindungsprobleme

1. **Verbindung fehlgeschlagen**:
   - Überprüfen Sie, ob der MySQL-Server läuft
   - Prüfen Sie, ob die Datenbankverbindungsdetails in `config/database.php` korrekt sind
   - Vergewissern Sie sich, dass die Datenbank `syt_db` existiert

2. **Falsche Anmeldedaten**:
   - XAMPP: Standardmäßig ist der Benutzername "root" ohne Passwort
   - MAMP: Standardmäßig ist der Benutzername "root" mit Passwort "root"

### Dateipfad-Probleme

Bei Dateipfad-Problemen stellen Sie sicher, dass Sie die richtigen Pfade verwenden:

- XAMPP (Windows): Pfade mit Backslashes (`\`) oder Vorwärtsschrägstrichen (`/`)
- XAMPP (Linux) / MAMP (macOS): Pfade mit Vorwärtsschrägstrichen (`/`)

## Projektstruktur

```
/M151/
├── config/                 # Konfigurationsdateien
│   └── database.php        # Datenbankverbindungskonfiguration
├── includes/               # Wiederverwendbare PHP-Komponenten
│   ├── functions.php       # Allgemeine Hilfsfunktionen
│   ├── header.php          # HTML-Header für alle Seiten
│   ├── footer.php          # HTML-Footer für alle Seiten
│   ├── session.php         # Session-Handling und Zugriffsrechte
│   └── validation.php      # Funktionen zur Formularvalidierung
└── public/                 # Öffentlich zugängliche Dateien
    ├── css/
    │   └── style.css       # CSS-Stylesheet
    ├── uploads/            # Verzeichnis für hochgeladene Bilder
    │   └── placeholder.png # Standard-Profilbild
    ├── index.php           # Startseite mit Übersicht aller Einträge
    ├── register.php        # Registrierungsseite
    ├── login.php           # Anmeldeseite
    ├── logout.php          # Abmeldeseite
    ├── dashboard.php       # Übersicht der eigenen Einträge
    ├── admin_dashboard.php # Admin-Bereich
    ├── profile_edit.php    # Seite zum Hochladen des Profilbilds
    ├── create_entry.php    # Seite zum Erstellen eines Eintrags
    ├── edit_entry.php      # Seite zum Bearbeiten eines Eintrags
    ├── view_entry.php      # Detailansicht eines Eintrags
    ├── view_image.php      # Großansicht eines Bildes
    └── ... (weitere Dateien)
```

## Lizenz

Dieses Projekt wurde für Bildungszwecke im Rahmen des Moduls 151 erstellt und steht unter der MIT-Lizenz.