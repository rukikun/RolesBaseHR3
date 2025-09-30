-- Fix Claims Table Structure
-- This script will properly recreate the claims table with AUTO_INCREMENT

USE hr3systemdb;

-- Drop existing claims table if it exists
DROP TABLE IF EXISTS claims;

-- Drop existing claim_types table if it exists  
DROP TABLE IF EXISTS claim_types;

-- Create claim_types table first
CREATE TABLE claim_types (
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

-- Create claims table with proper AUTO_INCREMENT
CREATE TABLE claims (
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
    INDEX idx_status (status),
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    FOREIGN KEY (claim_type_id) REFERENCES claim_types(id) ON DELETE CASCADE,
    FOREIGN KEY (approved_by) REFERENCES employees(id) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=1;

-- Insert sample claim types
INSERT INTO claim_types (name, code, description, max_amount, requires_attachment) VALUES
('Travel Expenses', 'TRAVEL', 'Business travel related expenses', 5000.00, true),
('Office Supplies', 'OFFICE', 'Office supplies and equipment', 1000.00, true),
('Meal Allowance', 'MEAL', 'Business meal expenses', 500.00, true),
('Training Costs', 'TRAINING', 'Professional development and training', 2000.00, true),
('Medical Expenses', 'MEDICAL', 'Medical and health related expenses', 3000.00, true);

-- Insert sample claims (using first available employee)
INSERT INTO claims (employee_id, claim_type_id, amount, claim_date, description, status) 
SELECT 
    (SELECT id FROM employees ORDER BY id LIMIT 1) as employee_id,
    ct.id as claim_type_id,
    CASE ct.code
        WHEN 'TRAVEL' THEN 250.00
        WHEN 'OFFICE' THEN 150.00
        WHEN 'MEAL' THEN 75.00
        ELSE 100.00
    END as amount,
    CASE ct.code
        WHEN 'TRAVEL' THEN CURDATE()
        WHEN 'OFFICE' THEN DATE_SUB(CURDATE(), INTERVAL 1 DAY)
        WHEN 'MEAL' THEN DATE_SUB(CURDATE(), INTERVAL 2 DAY)
        ELSE DATE_SUB(CURDATE(), INTERVAL 3 DAY)
    END as claim_date,
    CONCAT('Sample ', ct.name, ' claim') as description,
    CASE ct.code
        WHEN 'TRAVEL' THEN 'pending'
        WHEN 'OFFICE' THEN 'approved'
        WHEN 'MEAL' THEN 'paid'
        ELSE 'pending'
    END as status
FROM claim_types ct
WHERE ct.code IN ('TRAVEL', 'OFFICE', 'MEAL')
AND EXISTS (SELECT 1 FROM employees LIMIT 1);

-- Verify table structure
DESCRIBE claims;
DESCRIBE claim_types;

-- Show sample data
SELECT 'Claims Table Data:' as info;
SELECT * FROM claims;

SELECT 'Claim Types Table Data:' as info;
SELECT * FROM claim_types;
