-- SYT (Share Your Triumph) Datenbank Setup
-- Dieses Script erstellt einen Datenbankbenutzer, alle erforderlichen Tabellen und Testbenutzer

-- 1. Datenbank erstellen
CREATE DATABASE IF NOT EXISTS syt_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE syt_db;

-- 2. Datenbankbenutzer erstellen
-- WICHTIG: Ersetze 'sicheres_passwort' durch ein eigenes sicheres Passwort
CREATE USER IF NOT EXISTS 'syt_user'@'localhost' IDENTIFIED BY 'sicheres_passwort';

-- 3. Berechtigungen für den Benutzer festlegen (eingeschränkte Rechte)
GRANT SELECT, INSERT, UPDATE, DELETE ON syt_db.* TO 'syt_user'@'localhost';
FLUSH PRIVILEGES;

-- 4. Tabellen erstellen

-- Benutzer-Tabelle
CREATE TABLE IF NOT EXISTS benutzer (
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
CREATE TABLE IF NOT EXISTS eintraege (
    id INT AUTO_INCREMENT PRIMARY KEY,
    benutzer_id INT NOT NULL,
    titel VARCHAR(100) NOT NULL,
    beschreibung TEXT NOT NULL,
    erstellt_am DATETIME NOT NULL,
    aktualisiert_am DATETIME DEFAULT NULL,
    FOREIGN KEY (benutzer_id) REFERENCES benutzer(id) ON DELETE CASCADE
);

-- Tabelle für Eintragsbilder
CREATE TABLE IF NOT EXISTS eintrag_bilder (
    id INT AUTO_INCREMENT PRIMARY KEY,
    eintrag_id INT NOT NULL,
    dateiname VARCHAR(255) NOT NULL,
    ist_thumbnail TINYINT(1) DEFAULT 0,
    hochgeladen_am DATETIME NOT NULL,
    FOREIGN KEY (eintrag_id) REFERENCES eintraege(id) ON DELETE CASCADE
);

-- 5. Testbenutzer erstellen
-- Normaler Benutzer (Passwort: 'benutzer123')
INSERT INTO benutzer (vorname, nachname, email, passwort, is_admin, erstellt_am) 
VALUES ('Test', 'Benutzer', 'test@test.ch', '$2y$10$cKxHJr1Vr7atGO/J.lX54.MafqJlcuZDkz2GpVv8QiXwMTfR9NZHi', 0, NOW());

-- Admin-Benutzer (Passwort: 'admin123')
INSERT INTO benutzer (vorname, nachname, email, passwort, is_admin, erstellt_am) 
VALUES ('Admin', 'User', 'admin@test.ch', '$2y$10$YWZhwGKGAtHwavdN8ypzRuhyQQzC/z8SYL6Vj6.Mb.JtQ0SgUHHuO', 1, NOW());

-- 6. Beispieleintrag für den Admin erstellen
INSERT INTO eintraege (benutzer_id, titel, beschreibung, erstellt_am)
VALUES (
    2, -- Benutzer-ID des Admin-Benutzers
    'Willkommen bei Share Your Triumph!', 
    'Dies ist ein Beispieleintrag, der zeigt, wie die Plattform funktioniert. Hier können Triumph-Besitzer ihre Motorräder präsentieren und Erfahrungen teilen.\n\nFühlt euch frei, eure eigenen Einträge zu erstellen und Bilder hochzuladen!', 
    NOW()
);
