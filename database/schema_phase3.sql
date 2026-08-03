-- ============================================================
-- TANTRA GROUP OF INDUSTRIES
-- Phase 3: Subsidiary Website Builder & Creation Platform
-- Database Enhancements
-- ============================================================

USE tantra_corporate;

-- ============================================================
-- Table: websites
-- Stores website configuration for each company
-- ============================================================
CREATE TABLE IF NOT EXISTS websites (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id INT UNSIGNED NOT NULL,
    template VARCHAR(50) NOT NULL DEFAULT 'corporate',
    status ENUM('Draft', 'Preview', 'Published', 'Archived') NOT NULL DEFAULT 'Draft',
    theme_settings JSON DEFAULT NULL,
    navigation JSON DEFAULT NULL,
    domain VARCHAR(255) DEFAULT NULL,
    subdomain VARCHAR(100) DEFAULT NULL,
    analytics JSON DEFAULT NULL,
    published_at TIMESTAMP NULL DEFAULT NULL,
    archived_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_websites_company (company_id),
    INDEX idx_websites_status (status),
    INDEX idx_websites_subdomain (subdomain),
    UNIQUE KEY unique_company_website (company_id),
    UNIQUE KEY unique_subdomain (subdomain),
    CONSTRAINT fk_websites_company FOREIGN KEY (company_id) 
        REFERENCES companies(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Table: pages
-- Stores individual pages for each website
-- ============================================================
CREATE TABLE IF NOT EXISTS pages (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    website_id INT UNSIGNED NOT NULL,
    company_id INT UNSIGNED NOT NULL,
    page_name VARCHAR(100) NOT NULL,
    page_slug VARCHAR(100) NOT NULL,
    page_content LONGTEXT DEFAULT NULL,
    page_status ENUM('Draft', 'Published', 'Hidden') NOT NULL DEFAULT 'Draft',
    meta_title VARCHAR(255) DEFAULT NULL,
    meta_description TEXT DEFAULT NULL,
    sort_order INT DEFAULT 0,
    is_required TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_pages_website (website_id),
    INDEX idx_pages_company (company_id),
    INDEX idx_pages_slug (page_slug),
    UNIQUE KEY unique_page_per_website (website_id, page_slug),
    CONSTRAINT fk_pages_website FOREIGN KEY (website_id) 
        REFERENCES websites(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_pages_company FOREIGN KEY (company_id) 
        REFERENCES companies(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Table: media_library
-- Centralized media management
-- ============================================================
CREATE TABLE IF NOT EXISTS media_library (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id INT UNSIGNED NOT NULL,
    uploaded_by INT UNSIGNED NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_url VARCHAR(500) NOT NULL,
    file_type VARCHAR(50) NOT NULL,
    file_size INT UNSIGNED DEFAULT NULL,
    mime_type VARCHAR(100) DEFAULT NULL,
    width INT DEFAULT NULL,
    height INT DEFAULT NULL,
    alt_text VARCHAR(255) DEFAULT NULL,
    caption TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_media_company (company_id),
    INDEX idx_media_type (file_type),
    CONSTRAINT fk_media_company FOREIGN KEY (company_id) 
        REFERENCES companies(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_media_user FOREIGN KEY (uploaded_by) 
        REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Table: website_analytics
-- Basic analytics storage
-- ============================================================
CREATE TABLE IF NOT EXISTS website_analytics (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    website_id INT UNSIGNED NOT NULL,
    page_slug VARCHAR(100) DEFAULT NULL,
    visitor_ip VARCHAR(45) NOT NULL,
    user_agent TEXT DEFAULT NULL,
    referrer TEXT DEFAULT NULL,
    session_duration INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_analytics_website (website_id),
    INDEX idx_analytics_date (created_at),
    CONSTRAINT fk_analytics_website FOREIGN KEY (website_id) 
        REFERENCES websites(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Table: contact_submissions
-- Stores contact form submissions from subsidiary websites
-- ============================================================
CREATE TABLE IF NOT EXISTS contact_submissions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    website_id INT UNSIGNED NOT NULL,
    company_id INT UNSIGNED NOT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    subject VARCHAR(255) DEFAULT NULL,
    message TEXT NOT NULL,
    status ENUM('New', 'Read', 'Replied', 'Closed') NOT NULL DEFAULT 'New',
    ip_address VARCHAR(45) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_contact_website (website_id),
    INDEX idx_contact_company (company_id),
    INDEX idx_contact_status (status),
    CONSTRAINT fk_contact_website FOREIGN KEY (website_id) 
        REFERENCES websites(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_contact_company FOREIGN KEY (company_id) 
        REFERENCES companies(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Insert existing companies website records
-- ============================================================
INSERT INTO websites (company_id, template, status, subdomain, published_at)
SELECT id, 'corporate', 'Published', slug, NOW()
FROM companies
WHERE status = 'Live'
AND id NOT IN (SELECT company_id FROM websites)
ON DUPLICATE KEY UPDATE updated_at = CURRENT_TIMESTAMP;

-- ============================================================
-- Create default pages for existing websites
-- ============================================================
INSERT INTO pages (website_id, company_id, page_name, page_slug, page_status, sort_order, is_required)
SELECT 
    w.id,
    w.company_id,
    'Home',
    'home',
    'Published',
    1,
    1
FROM websites w
WHERE NOT EXISTS (SELECT 1 FROM pages p WHERE p.website_id = w.id AND p.page_slug = 'home');

INSERT INTO pages (website_id, company_id, page_name, page_slug, page_status, sort_order, is_required)
SELECT 
    w.id,
    w.company_id,
    'About',
    'about',
    'Published',
    2,
    1
FROM websites w
WHERE NOT EXISTS (SELECT 1 FROM pages p WHERE p.website_id = w.id AND p.page_slug = 'about');

INSERT INTO pages (website_id, company_id, page_name, page_slug, page_status, sort_order, is_required)
SELECT 
    w.id,
    w.company_id,
    'Services',
    'services',
    'Published',
    3,
    1
FROM websites w
WHERE NOT EXISTS (SELECT 1 FROM pages p WHERE p.website_id = w.id AND p.page_slug = 'services');

INSERT INTO pages (website_id, company_id, page_name, page_slug, page_status, sort_order, is_required)
SELECT 
    w.id,
    w.company_id,
    'Contact',
    'contact',
    'Published',
    4,
    1
FROM websites w
WHERE NOT EXISTS (SELECT 1 FROM pages p WHERE p.website_id = w.id AND p.page_slug = 'contact');