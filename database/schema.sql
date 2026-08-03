-- ============================================================
-- TANTRA GROUP OF INDUSTRIES
-- Corporate Management Portal - Phase 1 Database Schema
-- Production-Ready | No Dummy Data | Fresh 2026 Structure
-- ============================================================

CREATE DATABASE IF NOT EXISTS tantra_corporate;
USE tantra_corporate;

-- ============================================================
-- TABLE: users
-- Secure authentication and role-based access control
-- ============================================================
CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('Founder', 'Chairman', 'CEO') NOT NULL,
    status ENUM('Active', 'Inactive') NOT NULL DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_users_role (role),
    INDEX idx_users_status (status),
    INDEX idx_users_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: companies
-- Company management with status workflow (Draft / Live)
-- ============================================================
CREATE TABLE companies (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_name VARCHAR(200) NOT NULL,
    company_code VARCHAR(20) NOT NULL UNIQUE,
    company_description TEXT,
    company_logo VARCHAR(500) DEFAULT NULL,
    website_url VARCHAR(500) DEFAULT NULL,
    status ENUM('Draft', 'Live') NOT NULL DEFAULT 'Draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_companies_status (status),
    INDEX idx_companies_code (company_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: audit_logs
-- Immutable audit trail for all corporate actions
-- ============================================================
CREATE TABLE audit_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    action VARCHAR(100) NOT NULL,
    module VARCHAR(50) NOT NULL,
    description TEXT DEFAULT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    user_agent VARCHAR(500) DEFAULT NULL,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_audit_user (user_id),
    INDEX idx_audit_module (module),
    INDEX idx_audit_action (action),
    INDEX idx_audit_timestamp (timestamp),
    CONSTRAINT fk_audit_user FOREIGN KEY (user_id) 
        REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: sessions
-- Server-side session management for enhanced security
-- ============================================================
CREATE TABLE sessions (
    id VARCHAR(128) PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    user_agent VARCHAR(500) DEFAULT NULL,
    payload TEXT,
    last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_sessions_user (user_id),
    INDEX idx_sessions_last_activity (last_activity),
    CONSTRAINT fk_sessions_user FOREIGN KEY (user_id) 
        REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Insert initial corporate users (Founder, Chairman, CEO)
-- Passwords must be hashed using PHP password_hash() with PASSWORD_BCRYPT
-- These are placeholder hashes - replace via seed script
-- ============================================================
-- INSERT INTO users (username, email, password_hash, role) VALUES
-- ('founder', 'founder@tantragroup.com', '$2y$10$...', 'Founder'),
-- ('chairman', 'chairman@tantragroup.com', '$2y$10$...', 'Chairman'),
-- ('ceo', 'ceo@tantragroup.com', '$2y$10$...', 'CEO');

-- ============================================================
-- Initial Companies (Brands under TANTRA GROUP OF INDUSTRIES)
-- ============================================================
INSERT INTO companies (company_name, company_code, company_description, website_url, status) VALUES
('ShopTantra', 'SHOPTANTRA', 'ShopTantra is a multi-category e-commerce platform offering a wide range of products from fashion to electronics, delivering quality and value to customers across India.', 'https://shoptantra.com', 'Live'),
('HireTantra', 'HIRETANTRA', 'HireTantra is a comprehensive recruitment and staffing solutions provider, connecting talented professionals with leading organizations worldwide.', 'https://hiretantra.com', 'Live');