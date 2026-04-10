-- Schema voor de HBO-ICT Boekenclub database
-- Voer uit met: mysql -u root -p < schema.sql

CREATE DATABASE IF NOT EXISTS boekenclub
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE boekenclub;

CREATE TABLE IF NOT EXISTS book_suggestions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    submitter_name VARCHAR(100) NOT NULL,
    title VARCHAR(255) NOT NULL,
    author VARCHAR(255) NOT NULL,
    url VARCHAR(2048) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
