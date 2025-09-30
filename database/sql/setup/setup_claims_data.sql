-- Setup Claims Data for HR3 System
-- Run this in phpMyAdmin to ensure proper data structure

USE hr3systemdb;

-- Create claim_types table if it doesn't exist
CREATE TABLE IF NOT EXISTS claim_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    code VARCHAR(10) NOT NULL UNIQUE,
    description TEXT,
    max_amount DECIMAL(10,2) DEFAULT NULL,
    requires_attachment BOOLEAN DEFAULT FALSE,
    auto_approve BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Create claims table if it doesn't exist
CREATE TABLE IF NOT EXISTS claims (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    claim_type_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    claim_date DATE NOT NULL,
    description TEXT,
    receipt_path VARCHAR(255),
    attachment_path VARCHAR(255),
    status ENUM('pending', 'approved', 'rejected', 'paid') DEFAULT 'pending',
    approved_by INT NULL,
    approved_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_employee_id (employee_id),
    INDEX idx_claim_type_id (claim_type_id),
    INDEX idx_status (status)
) ENGINE=InnoDB AUTO_INCREMENT=1;

-- Insert sample claim types if table is empty
INSERT IGNORE INTO claim_types (name, code, description, max_amount, requires_attachment, is_active) VALUES
('Travel Expenses', 'TRAVEL', 'Business travel related expenses', 5000.00, 1, 1),
('Office Supplies', 'OFFICE', 'Office supplies and equipment', 1000.00, 1, 1),
('Meal Allowance', 'MEAL', 'Business meal expenses', 500.00, 1, 1),
('Training Costs', 'TRAINING', 'Professional development and training', 2000.00, 1, 1),
('Medical Expenses', 'MEDICAL', 'Medical and health related expenses', 3000.00, 1, 1);

-- Insert sample claims if we have employees (adjust employee_id as needed)
INSERT IGNORE INTO claims (employee_id, claim_type_id, amount, claim_date, description, status) 
SELECT 
    e.id as employee_id,
    ct.id as claim_type_id,
    250.00 as amount,
    CURDATE() as claim_date,
    'Sample travel expense claim' as description,
    'pending' as status
FROM employees e 
CROSS JOIN claim_types ct 
WHERE e.id = (SELECT MIN(id) FROM employees LIMIT 1)
AND ct.code = 'TRAVEL'
AND NOT EXISTS (SELECT 1 FROM claims WHERE employee_id = e.id AND claim_type_id = ct.id)
LIMIT 1;

INSERT IGNORE INTO claims (employee_id, claim_type_id, amount, claim_date, description, status) 
SELECT 
    e.id as employee_id,
    ct.id as claim_type_id,
    150.00 as amount,
    DATE_SUB(CURDATE(), INTERVAL 1 DAY) as claim_date,
    'Office supplies purchase' as description,
    'approved' as status
FROM employees e 
CROSS JOIN claim_types ct 
WHERE e.id = (SELECT MIN(id) FROM employees LIMIT 1)
AND ct.code = 'OFFICE'
AND NOT EXISTS (SELECT 1 FROM claims WHERE employee_id = e.id AND claim_type_id = ct.id AND amount = 150.00)
LIMIT 1;

INSERT IGNORE INTO claims (employee_id, claim_type_id, amount, claim_date, description, status) 
SELECT 
    e.id as employee_id,
    ct.id as claim_type_id,
    75.00 as amount,
    DATE_SUB(CURDATE(), INTERVAL 2 DAY) as claim_date,
    'Business lunch meeting' as description,
    'paid' as status
FROM employees e 
CROSS JOIN claim_types ct 
WHERE e.id = (SELECT MIN(id) FROM employees LIMIT 1)
AND ct.code = 'MEAL'
AND NOT EXISTS (SELECT 1 FROM claims WHERE employee_id = e.id AND claim_type_id = ct.id AND amount = 75.00)
LIMIT 1;

-- Show results
SELECT 'Claim Types' as table_name, COUNT(*) as record_count FROM claim_types
UNION ALL
SELECT 'Claims' as table_name, COUNT(*) as record_count FROM claims
UNION ALL
SELECT 'Employees' as table_name, COUNT(*) as record_count FROM employees;

-- Show sample data
SELECT 'Sample Claim Types:' as info;
SELECT id, name, code, max_amount, is_active FROM claim_types ORDER BY name;

SELECT 'Sample Claims:' as info;
SELECT 
    c.id,
    CONCAT(e.first_name, ' ', e.last_name) as employee_name,
    ct.name as claim_type,
    c.amount,
    c.status,
    c.claim_date
FROM claims c
LEFT JOIN employees e ON c.employee_id = e.id
LEFT JOIN claim_types ct ON c.claim_type_id = ct.id
ORDER BY c.created_at DESC
LIMIT 10;
