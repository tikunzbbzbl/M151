-- Erstelle die Datenbank
CREATE DATABASE thingiverse_simplified CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE thingiverse_simplified;

-- Erstelle die Tabelle für Benutzer
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    profile_picture VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Erstelle die Tabelle für Kreationen
CREATE TABLE kreationen (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    image VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Lege den eingeschränkten Benutzer an und weise Rechte zu
CREATE USER 'user1'@'localhost' IDENTIFIED BY 'pass';
GRANT SELECT, INSERT, UPDATE, DELETE ON thingiverse_simplified.* TO 'user1'@'localhost';
FLUSH PRIVILEGES;
