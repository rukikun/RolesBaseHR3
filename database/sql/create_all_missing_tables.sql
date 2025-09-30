-- Create all missing HR system tables
-- Copy and paste this into phpMyAdmin SQL tab

USE hr3systemdb;

-- Create leave_types table
CREATE TABLE IF NOT EXISTS leave_types (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    max_days_per_year INT DEFAULT 0,
    carry_forward BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create leave_requests table
CREATE TABLE IF NOT EXISTS leave_requests (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    employee_id BIGINT UNSIGNED NOT NULL,
    leave_type_id BIGINT UNSIGNED NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    days_requested INT NOT NULL,
    reason TEXT,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    approved_by BIGINT UNSIGNED,
    approved_at TIMESTAMP NULL,
    rejection_reason TEXT,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    FOREIGN KEY (leave_type_id) REFERENCES leave_types(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create leave_balances table
CREATE TABLE IF NOT EXISTS leave_balances (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    employee_id BIGINT UNSIGNED NOT NULL,
    leave_type_id BIGINT UNSIGNED NOT NULL,
    year YEAR NOT NULL,
    allocated_days INT DEFAULT 0,
    used_days INT DEFAULT 0,
    remaining_days INT DEFAULT 0,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    FOREIGN KEY (leave_type_id) REFERENCES leave_types(id) ON DELETE CASCADE,
    UNIQUE KEY unique_employee_leave_year (employee_id, leave_type_id, year)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create claim_types table
CREATE TABLE IF NOT EXISTS claim_types (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    max_amount DECIMAL(10,2) DEFAULT 0,
    requires_receipt BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create claims table
CREATE TABLE IF NOT EXISTS claims (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    employee_id BIGINT UNSIGNED NOT NULL,
    claim_type_id BIGINT UNSIGNED NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    description TEXT,
    receipt_path VARCHAR(255),
    claim_date DATE NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    approved_by BIGINT UNSIGNED,
    approved_at TIMESTAMP NULL,
    rejection_reason TEXT,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    FOREIGN KEY (claim_type_id) REFERENCES claim_types(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample leave types
INSERT IGNORE INTO leave_types (name, description, max_days_per_year, carry_forward, created_at, updated_at) VALUES
('Annual Leave', 'Yearly vacation days', 25, TRUE, NOW(), NOW()),
('Sick Leave', 'Medical leave for illness', 10, FALSE, NOW(), NOW()),
('Personal Leave', 'Personal time off', 5, FALSE, NOW(), NOW()),
('Maternity Leave', 'Maternity leave for new mothers', 90, FALSE, NOW(), NOW()),
('Paternity Leave', 'Paternity leave for new fathers', 14, FALSE, NOW(), NOW());

-- Insert sample claim types
INSERT IGNORE INTO claim_types (name, description, max_amount, requires_receipt, created_at, updated_at) VALUES
('Travel Expenses', 'Business travel related expenses', 1000.00, TRUE, NOW(), NOW()),
('Meal Allowance', 'Business meal expenses', 50.00, TRUE, NOW(), NOW()),
('Office Supplies', 'Work-related office supplies', 200.00, TRUE, NOW(), NOW()),
('Training Courses', 'Professional development courses', 2000.00, TRUE, NOW(), NOW()),
('Mobile Phone', 'Business mobile phone expenses', 100.00, TRUE, NOW(), NOW());

-- Insert sample leave requests
INSERT IGNORE INTO leave_requests (employee_id, leave_type_id, start_date, end_date, days_requested, reason, status, created_at, updated_at) VALUES
(1, 1, '2025-09-01', '2025-09-05', 5, 'Family vacation', 'pending', NOW(), NOW()),
(2, 2, '2025-08-28', '2025-08-30', 3, 'Medical appointment', 'approved', NOW(), NOW()),
(3, 1, '2025-09-15', '2025-09-20', 6, 'Wedding anniversary trip', 'pending', NOW(), NOW());

-- Insert sample claims
INSERT IGNORE INTO claims (employee_id, claim_type_id, amount, description, claim_date, status, created_at, updated_at) VALUES
(1, 1, 250.00, 'Client meeting travel expenses', '2025-08-20', 'pending', NOW(), NOW()),
(2, 2, 35.00, 'Business lunch with candidate', '2025-08-22', 'approved', NOW(), NOW()),
(4, 3, 85.00, 'Office supplies for accounting department', '2025-08-21', 'pending', NOW(), NOW());

-- Verify tables were created
SHOW TABLES;

-- Test the queries that were failing
SELECT COUNT(*) as aggregate FROM leave_requests WHERE status = 'pending';
SELECT COUNT(*) as aggregate FROM claims WHERE status = 'pending';
