-- database.sql
CREATE DATABASE IF NOT EXISTS thingiverse_simplified CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE thingiverse_simplified;

-- Tabelle für Benutzer
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    profile_picture VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabelle für Kreationen
CREATE TABLE IF NOT EXISTS kreationen (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Tabelle für Dateien, die zu einer Kreation gehören
CREATE TABLE IF NOT EXISTS kreation_files (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kreation_id INT NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_type VARCHAR(50) NOT NULL,
    is_thumbnail TINYINT(1) DEFAULT 0,  -- 1 = Thumbnail, 0 = normale Datei
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (kreation_id) REFERENCES kreationen(id) ON DELETE CASCADE
);
CREATE USER IF NOT EXISTS 'user1'@'localhost' IDENTIFIED BY 'pass';
GRANT SELECT, INSERT, UPDATE, DELETE ON thingiverse_simplified.* TO 'user1'@'localhost';
FLUSH PRIVILEGES;