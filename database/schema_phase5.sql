-- ============================================================
-- TANTRA GROUP OF INDUSTRIES
-- Phase 5: Global Corporate Headquarters Platform
-- Enterprise Database Schema (Multi-Company Architecture)
-- ============================================================

USE tantra_corporate;

-- ============================================================
-- FINANCE MODULE
-- ============================================================
CREATE TABLE IF NOT EXISTS finance_records (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id INT UNSIGNED NOT NULL,
    record_type ENUM('Income', 'Expense', 'Transfer') NOT NULL,
    category VARCHAR(100) NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    description TEXT DEFAULT NULL,
    transaction_date DATE NOT NULL,
    created_by INT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS budgets (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id INT UNSIGNED NOT NULL,
    department_id INT UNSIGNED DEFAULT NULL,
    fiscal_year YEAR NOT NULL,
    allocated DECIMAL(15,2) NOT NULL DEFAULT 0,
    spent DECIMAL(15,2) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS invoices (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id INT UNSIGNED NOT NULL,
    invoice_number VARCHAR(50) NOT NULL UNIQUE,
    vendor VARCHAR(200) NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    status ENUM('Pending', 'Paid', 'Overdue', 'Cancelled') NOT NULL DEFAULT 'Pending',
    due_date DATE DEFAULT NULL,
    paid_date DATE DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ============================================================
-- ASSET MANAGEMENT
-- ============================================================
CREATE TABLE IF NOT EXISTS assets (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id INT UNSIGNED NOT NULL,
    asset_name VARCHAR(200) NOT NULL,
    asset_type ENUM('Laptop', 'Server', 'Domain', 'Software', 'Equipment', 'Office', 'Other') NOT NULL,
    serial_number VARCHAR(100) DEFAULT NULL,
    value DECIMAL(15,2) DEFAULT 0,
    assigned_to INT UNSIGNED DEFAULT NULL,
    status ENUM('Available', 'Assigned', 'Maintenance', 'Retired') NOT NULL DEFAULT 'Available',
    purchase_date DATE DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ============================================================
-- ERP MODULE
-- ============================================================
CREATE TABLE IF NOT EXISTS projects (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id INT UNSIGNED NOT NULL,
    project_name VARCHAR(200) NOT NULL,
    description TEXT DEFAULT NULL,
    status ENUM('Planning', 'Active', 'Completed', 'On Hold') NOT NULL DEFAULT 'Planning',
    start_date DATE DEFAULT NULL,
    end_date DATE DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS tasks (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    project_id INT UNSIGNED DEFAULT NULL,
    company_id INT UNSIGNED NOT NULL,
    task_name VARCHAR(200) NOT NULL,
    assigned_to INT UNSIGNED DEFAULT NULL,
    status ENUM('Pending', 'In Progress', 'Completed', 'Cancelled') NOT NULL DEFAULT 'Pending',
    priority ENUM('Low', 'Medium', 'High', 'Critical') NOT NULL DEFAULT 'Medium',
    due_date DATE DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS vendors (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id INT UNSIGNED NOT NULL,
    vendor_name VARCHAR(200) NOT NULL,
    contact_person VARCHAR(100) DEFAULT NULL,
    email VARCHAR(255) DEFAULT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    status ENUM('Active', 'Inactive') NOT NULL DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ============================================================
-- DOCUMENT & COMPLIANCE
-- ============================================================
CREATE TABLE IF NOT EXISTS documents (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id INT UNSIGNED DEFAULT NULL,
    document_name VARCHAR(255) NOT NULL,
    document_type ENUM('Policy', 'Agreement', 'Contract', 'SOP', 'Legal', 'Corporate') NOT NULL,
    file_url VARCHAR(500) DEFAULT NULL,
    description TEXT DEFAULT NULL,
    expiry_date DATE DEFAULT NULL,
    created_by INT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ============================================================
-- AI INSIGHTS
-- ============================================================
CREATE TABLE IF NOT EXISTS ai_insights (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id INT UNSIGNED DEFAULT NULL,
    insight_type ENUM('Growth', 'Performance', 'Risk', 'Recommendation', 'Forecast') NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    confidence_score DECIMAL(5,2) DEFAULT 0,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- COMPLIANCE
-- ============================================================
CREATE TABLE IF NOT EXISTS compliance_records (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id INT UNSIGNED NOT NULL,
    compliance_type ENUM('License', 'Registration', 'Filing', 'Legal', 'Certificate') NOT NULL,
    title VARCHAR(255) NOT NULL,
    issuing_authority VARCHAR(255) DEFAULT NULL,
    issue_date DATE DEFAULT NULL,
    expiry_date DATE NOT NULL,
    status ENUM('Active', 'Expiring', 'Expired') NOT NULL DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ============================================================
-- GLOBAL SEARCH INDEX
-- ============================================================
CREATE TABLE IF NOT EXISTS search_index (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    entity_type ENUM('Company', 'Employee', 'Department', 'Document', 'Website', 'Asset', 'Report') NOT NULL,
    entity_id INT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT DEFAULT NULL,
    keywords TEXT DEFAULT NULL,
    url VARCHAR(500) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_search_type (entity_type),
    INDEX idx_search_title (title)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;