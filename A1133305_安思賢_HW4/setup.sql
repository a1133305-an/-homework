-- ============================================
-- MAILSYS Database Setup
-- Run this once to initialize the database
-- ============================================

CREATE DATABASE IF NOT EXISTS mailsys_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE mailsys_db;

CREATE TABLE IF NOT EXISTS recipients (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    email       VARCHAR(255) NOT NULL UNIQUE,
    label       VARCHAR(100) DEFAULT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Sample test data (optional, remove in production)
-- INSERT INTO recipients (email, label) VALUES ('test@example.com', 'Test User');
