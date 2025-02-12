CREATE DATABASE mein_projekt;
USE mein_projekt;

-- Tabelle für Benutzer
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Beispiel-Admin-Benutzer (Passwort: admin123, Hash sollte geändert werden)
INSERT INTO users (username, password) VALUES 
('admin', '$2y$10$ABCDEFGHIJKLMNOPQRSTUV12345678901234567890'); -- Passwort-Hash ersetzen

-- Tabelle für Benutzerinhalte
CREATE TABLE user_content (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);