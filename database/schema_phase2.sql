-- ============================================================
-- TANTRA GROUP OF INDUSTRIES
-- Phase 2: Company Publishing & Live Brand Management
-- Database Enhancements
-- ============================================================

USE tantra_corporate;

-- ============================================================
-- Enhance companies table with Phase 2 fields
-- ============================================================
ALTER TABLE companies ADD COLUMN IF NOT EXISTS slug VARCHAR(200) UNIQUE AFTER company_code;
ALTER TABLE companies ADD COLUMN IF NOT EXISTS industry VARCHAR(100) DEFAULT NULL AFTER company_description;
ALTER TABLE companies ADD COLUMN IF NOT EXISTS short_description TEXT DEFAULT NULL AFTER industry;
ALTER TABLE companies ADD COLUMN IF NOT EXISTS banner VARCHAR(500) DEFAULT NULL AFTER company_logo;
ALTER TABLE companies ADD COLUMN IF NOT EXISTS launch_year INT DEFAULT NULL AFTER banner;
ALTER TABLE companies ADD COLUMN IF NOT EXISTS headquarters VARCHAR(200) DEFAULT NULL AFTER launch_year;
ALTER TABLE companies ADD COLUMN IF NOT EXISTS contact_email VARCHAR(255) DEFAULT NULL AFTER headquarters;
ALTER TABLE companies ADD COLUMN IF NOT EXISTS contact_number VARCHAR(20) DEFAULT NULL AFTER contact_email;
ALTER TABLE companies ADD COLUMN IF NOT EXISTS published_at TIMESTAMP NULL DEFAULT NULL AFTER contact_number;
ALTER TABLE companies ADD COLUMN IF NOT EXISTS archived_at TIMESTAMP NULL DEFAULT NULL AFTER published_at;

-- Modify status enum to include new values
ALTER TABLE companies MODIFY COLUMN status ENUM('Draft', 'Review', 'Live', 'Archived') NOT NULL DEFAULT 'Draft';

-- Add indexes for performance
CREATE INDEX IF NOT EXISTS idx_companies_slug ON companies(slug);
CREATE INDEX IF NOT EXISTS idx_companies_industry ON companies(industry);
CREATE INDEX IF NOT EXISTS idx_companies_status_published ON companies(status, published_at DESC);

-- ============================================================
-- Table: company_media
-- Stores media assets (logos, banners)
-- ============================================================
CREATE TABLE IF NOT EXISTS company_media (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id INT UNSIGNED NOT NULL,
    media_type ENUM('logo', 'banner') NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_size INT UNSIGNED DEFAULT NULL,
    mime_type VARCHAR(100) DEFAULT NULL,
    width INT DEFAULT NULL,
    height INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_company_media_company (company_id),
    INDEX idx_company_media_type (media_type),
    CONSTRAINT fk_company_media_company FOREIGN KEY (company_id) 
        REFERENCES companies(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Table: company_history
-- Tracks all status changes and major events
-- ============================================================
CREATE TABLE IF NOT EXISTS company_history (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    action VARCHAR(100) NOT NULL,
    old_value VARCHAR(100) DEFAULT NULL,
    new_value VARCHAR(100) DEFAULT NULL,
    description TEXT DEFAULT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    user_agent VARCHAR(500) DEFAULT NULL,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_company_history_company (company_id),
    INDEX idx_company_history_user (user_id),
    INDEX idx_company_history_timestamp (timestamp),
    CONSTRAINT fk_company_history_company FOREIGN KEY (company_id) 
        REFERENCES companies(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_company_history_user FOREIGN KEY (user_id) 
        REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Insert initial companies with Phase 2 fields
-- ============================================================
INSERT INTO companies 
    (company_name, company_code, slug, industry, description, short_description, website_url, status, launch_year, headquarters, contact_email, contact_number, published_at) 
VALUES 
    ('ShopTantra', 'SHOPTANTRA', 'shoptantra', 'E-Commerce', 
     'ShopTantra is a multi-category e-commerce platform offering a wide range of products from fashion to electronics, delivering quality and value to customers across India.',
     'Multi-category e-commerce platform for quality products.',
     'https://shoptantra.com', 'Live', 2026, 'India', 'contact@shoptantra.com', '+91-000-000-0000', NOW()),
    ('HireTantra', 'HIRETANTRA', 'hiretantra', 'Recruitment & Staffing',
     'HireTantra is a comprehensive recruitment and staffing solutions provider, connecting talented professionals with leading organizations worldwide.',
     'Comprehensive recruitment and staffing solutions provider.',
     'https://hiretantra.com', 'Live', 2026, 'India', 'contact@hiretantra.com', '+91-000-000-0000', NOW())
ON DUPLICATE KEY UPDATE 
    slug = VALUES(slug),
    industry = VALUES(industry),
    short_description = VALUES(short_description),
    launch_year = VALUES(launch_year),
    headquarters = VALUES(headquarters),
    contact_email = VALUES(contact_email),
    contact_number = VALUES(contact_number);