-- Complete Leave Management Database Setup
-- This script creates all necessary tables and sample data for the leave management module

USE hr3systemdb;

-- Create leave_types table if it doesn't exist
CREATE TABLE IF NOT EXISTS leave_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    code VARCHAR(10) UNIQUE NOT NULL,
    description TEXT NULL,
    max_days_per_year INT DEFAULT 0,
    carry_forward BOOLEAN DEFAULT FALSE,
    requires_approval BOOLEAN DEFAULT TRUE,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create leave_requests table if it doesn't exist
CREATE TABLE IF NOT EXISTS leave_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    leave_type_id INT NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    days_requested INT NOT NULL,
    reason TEXT NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    approved_by INT NULL,
    approved_at TIMESTAMP NULL,
    rejection_reason TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    FOREIGN KEY (leave_type_id) REFERENCES leave_types(id) ON DELETE CASCADE,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Create leave_balances table if it doesn't exist
CREATE TABLE IF NOT EXISTS leave_balances (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    leave_type_id INT NOT NULL,
    year YEAR NOT NULL,
    allocated_days INT DEFAULT 0,
    used_days INT DEFAULT 0,
    remaining_days INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_employee_leave_year (employee_id, leave_type_id, year),
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    FOREIGN KEY (leave_type_id) REFERENCES leave_types(id) ON DELETE CASCADE
);

-- Clear existing data to avoid duplicates
DELETE FROM leave_requests;
DELETE FROM leave_balances;
DELETE FROM leave_types;

-- Insert sample leave types
INSERT INTO leave_types (name, code, description, max_days_per_year, carry_forward, requires_approval, is_active) VALUES
('Annual Leave', 'ANNUAL', 'Annual vacation leave for employees', 21, 1, 1, 1),
('Sick Leave', 'SICK', 'Medical leave for illness or medical appointments', 14, 0, 0, 1),
('Personal Leave', 'PERSONAL', 'Personal time off for personal matters', 5, 0, 1, 1),
('Maternity Leave', 'MATERNITY', 'Maternity leave for new mothers', 90, 0, 1, 1),
('Paternity Leave', 'PATERNITY', 'Paternity leave for new fathers', 14, 0, 1, 1),
('Emergency Leave', 'EMERGENCY', 'Emergency leave for urgent family matters', 3, 0, 1, 1),
('Study Leave', 'STUDY', 'Educational leave for professional development', 10, 1, 1, 1);

-- Insert sample leave requests (using existing employee IDs: 6-11 and leave_type_ids: 15-21)
INSERT INTO leave_requests (employee_id, leave_type_id, start_date, end_date, days_requested, reason, status, approved_by, approved_at) VALUES
(6, 15, DATE_ADD(CURDATE(), INTERVAL 7 DAY), DATE_ADD(CURDATE(), INTERVAL 11 DAY), 5, 'Family vacation to Bali', 'pending', NULL, NULL),
(7, 16, DATE_SUB(CURDATE(), INTERVAL 2 DAY), DATE_SUB(CURDATE(), INTERVAL 1 DAY), 2, 'Flu symptoms and doctor appointment', 'approved', 1, DATE_SUB(NOW(), INTERVAL 1 DAY)),
(8, 17, DATE_ADD(CURDATE(), INTERVAL 3 DAY), DATE_ADD(CURDATE(), INTERVAL 3 DAY), 1, 'Personal appointment', 'approved', 1, DATE_SUB(NOW(), INTERVAL 2 HOUR)),
(6, 15, DATE_ADD(CURDATE(), INTERVAL 14 DAY), DATE_ADD(CURDATE(), INTERVAL 21 DAY), 8, 'Annual leave - visiting family overseas', 'pending', NULL, NULL),
(7, 20, DATE_SUB(CURDATE(), INTERVAL 1 DAY), DATE_SUB(CURDATE(), INTERVAL 1 DAY), 1, 'Family emergency', 'approved', 1, DATE_SUB(NOW(), INTERVAL 6 HOUR)),
(8, 21, DATE_ADD(CURDATE(), INTERVAL 30 DAY), DATE_ADD(CURDATE(), INTERVAL 34 DAY), 5, 'Professional certification course', 'rejected', 1, DATE_SUB(NOW(), INTERVAL 3 DAY)),
(9, 15, DATE_ADD(CURDATE(), INTERVAL 45 DAY), DATE_ADD(CURDATE(), INTERVAL 52 DAY), 8, 'Christmas holiday', 'pending', NULL, NULL),
(10, 18, DATE_ADD(CURDATE(), INTERVAL 60 DAY), DATE_ADD(CURDATE(), INTERVAL 150 DAY), 90, 'Maternity leave', 'approved', 1, DATE_SUB(NOW(), INTERVAL 1 DAY)),
(11, 19, DATE_ADD(CURDATE(), INTERVAL 5 DAY), DATE_ADD(CURDATE(), INTERVAL 19 DAY), 14, 'Paternity leave for new baby', 'pending', NULL, NULL);

-- Create leave balances for all employees and leave types for current year
INSERT INTO leave_balances (employee_id, leave_type_id, year, allocated_days, used_days, remaining_days)
SELECT 
    e.id as employee_id,
    lt.id as leave_type_id,
    YEAR(CURDATE()) as year,
    lt.max_days_per_year as allocated_days,
    COALESCE(used.days_used, 0) as used_days,
    (lt.max_days_per_year - COALESCE(used.days_used, 0)) as remaining_days
FROM employees e
CROSS JOIN leave_types lt
LEFT JOIN (
    SELECT 
        employee_id, 
        leave_type_id, 
        SUM(days_requested) as days_used
    FROM leave_requests 
    WHERE status = 'approved' 
    AND YEAR(start_date) = YEAR(CURDATE())
    GROUP BY employee_id, leave_type_id
) used ON e.id = used.employee_id AND lt.id = used.leave_type_id
WHERE lt.is_active = 1;

-- Verify the setup
SELECT 'Leave Types Created:' as Info, COUNT(*) as Count FROM leave_types WHERE is_active = 1;
SELECT 'Leave Requests Created:' as Info, COUNT(*) as Count FROM leave_requests;
SELECT 'Leave Balances Created:' as Info, COUNT(*) as Count FROM leave_balances;

-- Show sample data
SELECT 'Sample Leave Types:' as Info;
SELECT id, name, code, max_days_per_year, carry_forward, requires_approval FROM leave_types WHERE is_active = 1 LIMIT 5;

SELECT 'Sample Leave Requests:' as Info;
SELECT lr.id, 
       CONCAT(e.first_name, ' ', e.last_name) as employee_name,
       lt.name as leave_type, 
       lr.days_requested, 
       lr.status, 
       lr.start_date, 
       lr.end_date
FROM leave_requests lr 
LEFT JOIN employees e ON lr.employee_id = e.id 
LEFT JOIN leave_types lt ON lr.leave_type_id = lt.id 
LIMIT 5;
