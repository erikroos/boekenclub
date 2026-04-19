-- Migration 3: persoonlijke leeslijst (gelezen / te lezen)
-- Voer uit met: mysql -u root boekenclub < sql/migration3.sql

USE boekenclub;

CREATE TABLE IF NOT EXISTS personal_books (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    title      VARCHAR(255) NOT NULL,
    author     VARCHAR(255) NOT NULL,
    status     ENUM('to_read', 'read') NOT NULL DEFAULT 'to_read',
    rating        TINYINT UNSIGNED DEFAULT NULL,
    date_finished DATE DEFAULT NULL,
    comment       TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    CONSTRAINT chk_rating CHECK (rating IS NULL OR (rating >= 1 AND rating <= 5))
) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
