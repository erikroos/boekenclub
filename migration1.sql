-- Migration 1: admin-authenticatie en recensies
-- Voer uit met: mysql -u root boekenclub < migration1.sql

USE boekenclub;

-- Admin-gebruikers. Maak nieuwe admins aan met het CLI-script
-- `create_admin.php` — sla nooit plaintext wachtwoorden op.
CREATE TABLE IF NOT EXISTS admins (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    username      VARCHAR(50)  NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at    TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Recensies (bijeenkomsten). De 'plain' velden worden bij output geescaped,
-- full_html bevat rijke HTML en wordt NIET geescaped — die wordt alleen
-- door de (vertrouwde) admin ingevoerd.
CREATE TABLE IF NOT EXISTS reviews (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    sequence_number INT          NOT NULL UNIQUE,
    book_title      VARCHAR(255) NOT NULL,
    book_author     VARCHAR(255) NOT NULL,
    meeting_date    DATE         NOT NULL,
    host_name       VARCHAR(100) NOT NULL,
    host_location   VARCHAR(100) NOT NULL,
    attendees       VARCHAR(500) DEFAULT NULL,
    verdict         VARCHAR(100) DEFAULT NULL,
    preview         TEXT,
    full_html       LONGTEXT,
    created_at      TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_sequence (sequence_number)
) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
