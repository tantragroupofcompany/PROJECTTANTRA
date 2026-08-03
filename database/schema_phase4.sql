-- ============================================================
-- TANTRA GROUP OF INDUSTRIES
-- Phase 4: Enterprise Operations, Governance & Analytics
-- Database Enhancements
-- ============================================================

USE tantra_corporate;

-- ============================================================
-- Table: departments
-- ============================================================
CREATE TABLE IF NOT EXISTS departments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id INT UNSIGNED NOT NULL,
    department_name VARCHAR(100) NOT NULL,
    department_code VARCHAR(20) NOT NULL,
    description TEXT DEFAULT NULL,
    head_id INT UNSIGNED DEFAULT NULL,
    status ENUM('Active', 'Inactive') NOT NULL DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_dept_company (company_id),
    INDEX idx_dept_head (head_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Table: employees
-- ============================================================
CREATE TABLE IF NOT EXISTS employees (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    employee_id VARCHAR(50) NOT NULL UNIQUE,
    company_id INT UNSIGNED DEFAULT NULL,
    department_id INT UNSIGNED DEFAULT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    designation VARCHAR(100) NOT NULL,
    manager_id INT UNSIGNED DEFAULT NULL,
    role ENUM('Founder', 'Chairman', 'CEO', 'Director', 'Manager', 'Employee', 'Viewer') NOT NULL DEFAULT 'Employee',
    status ENUM('Active', 'On Leave', 'Suspended', 'Deactivated') NOT NULL DEFAULT 'Active',
    joining_date DATE DEFAULT NULL,
    avatar VARCHAR(500) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_emp_company (company_id),
    INDEX idx_emp_department (department_id),
    INDEX idx_emp_role (role),
    INDEX idx_emp_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Table: permissions
-- ============================================================
CREATE TABLE IF NOT EXISTS permissions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    role VARCHAR(50) NOT NULL,
    module VARCHAR(100) NOT NULL,
    can_create TINYINT(1) DEFAULT 0,
    can_read TINYINT(1) DEFAULT 1,
    can_update TINYINT(1) DEFAULT 0,
    can_delete TINYINT(1) DEFAULT 0,
    can_approve TINYINT(1) DEFAULT 0,
    UNIQUE KEY unique_role_module (role, module)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Table: approvals
-- ============================================================
CREATE TABLE IF NOT EXISTS approvals (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id INT UNSIGNED DEFAULT NULL,
    employee_id INT UNSIGNED DEFAULT NULL,
    approval_type ENUM('Company', 'Website', 'Employee', 'Department', 'Policy') NOT NULL,
    status ENUM('Pending', 'Under Review', 'Approved', 'Rejected') NOT NULL DEFAULT 'Pending',
    requested_by INT UNSIGNED NOT NULL,
    reviewed_by INT UNSIGNED DEFAULT NULL,
    review_notes TEXT DEFAULT NULL,
    reviewed_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_approval_company (company_id),
    INDEX idx_approval_employee (employee_id),
    INDEX idx_approval_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Table: notifications
-- ============================================================
CREATE TABLE IF NOT EXISTS notifications (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED DEFAULT NULL,
    company_id INT UNSIGNED DEFAULT NULL,
    type ENUM('System', 'Approval', 'Security', 'Employee', 'Company') NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    channel ENUM('Dashboard', 'Email', 'In-App') NOT NULL DEFAULT 'Dashboard',
    is_read TINYINT(1) DEFAULT 0,
    read_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_notif_user (user_id),
    INDEX idx_notif_company (company_id),
    INDEX idx_notif_read (is_read)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Table: reports
-- ============================================================
CREATE TABLE IF NOT EXISTS reports (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id INT UNSIGNED DEFAULT NULL,
    report_type ENUM('Company', 'Employee', 'Department', 'Security', 'Activity') NOT NULL,
    report_name VARCHAR(255) NOT NULL,
    report_format ENUM('PDF', 'Excel', 'CSV') NOT NULL DEFAULT 'PDF',
    filters JSON DEFAULT NULL,
    generated_by INT UNSIGNED NOT NULL,
    file_path VARCHAR(500) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_report_company (company_id),
    INDEX idx_report_type (report_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Insert default permissions
-- ============================================================
INSERT INTO permissions (role, module, can_create, can_read, can_update, can_delete, can_approve) VALUES
('Founder', 'all', 1, 1, 1, 1, 1),
('Chairman', 'all', 1, 1, 1, 1, 1),
('CEO', 'all', 1, 1, 1, 1, 1),
('Director', 'department', 1, 1, 1, 0, 1),
('Manager', 'team', 1, 1, 1, 0, 0),
('Employee', 'own', 0, 1, 0, 0, 0),
('Viewer', 'readonly', 0, 1, 0, 0, 0)
ON DUPLICATE KEY UPDATE can_read = VALUES(can_read);