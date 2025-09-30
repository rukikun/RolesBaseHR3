-- Complete HR3 System Database Setup - FULL RESTORATION
-- Run this script in phpMyAdmin or MySQL client

-- Create database if it doesn't exist
CREATE DATABASE IF NOT EXISTS hr3systemdb CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE hr3systemdb;

-- Drop existing tables if they exist (in correct order due to foreign keys)
DROP TABLE IF EXISTS shift_requests;
DROP TABLE IF EXISTS shifts;
DROP TABLE IF EXISTS shift_types;

-- Create shift_types table
CREATE TABLE shift_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    default_start_time TIME NOT NULL,
    default_end_time TIME NOT NULL,
    color_code VARCHAR(7) DEFAULT '#007bff',
    type ENUM('morning', 'afternoon', 'evening', 'night', 'weekend') DEFAULT 'morning',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_name (name)
) ENGINE=InnoDB;

-- Create shifts table
CREATE TABLE shifts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    shift_type_id INT NOT NULL,
    date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    status ENUM('scheduled', 'in_progress', 'completed', 'cancelled') DEFAULT 'scheduled',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    FOREIGN KEY (shift_type_id) REFERENCES shift_types(id) ON DELETE CASCADE,
    INDEX idx_employee_date (employee_id, date),
    INDEX idx_date (date),
    INDEX idx_status (status)
) ENGINE=InnoDB;

-- Create shift_requests table
CREATE TABLE shift_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    shift_type_id INT NOT NULL,
    requested_date DATE NOT NULL,
    preferred_start_time TIME,
    preferred_end_time TIME,
    reason TEXT,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    approved_by INT NULL,
    approved_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    FOREIGN KEY (shift_type_id) REFERENCES shift_types(id) ON DELETE CASCADE,
    FOREIGN KEY (approved_by) REFERENCES employees(id) ON DELETE SET NULL,
    INDEX idx_employee_status (employee_id, status),
    INDEX idx_date_status (requested_date, status)
) ENGINE=InnoDB;

-- Insert default shift types
INSERT INTO shift_types (name, description, default_start_time, default_end_time, color_code, type) VALUES
('Morning Shift', 'Standard morning work shift', '08:00:00', '16:00:00', '#28a745', 'morning'),
('Evening Shift', 'Standard evening work shift', '16:00:00', '00:00:00', '#fd7e14', 'evening'),
('Night Shift', 'Overnight work shift', '00:00:00', '08:00:00', '#6f42c1', 'night'),
('Weekend Day', 'Weekend daytime shift', '09:00:00', '17:00:00', '#20c997', 'weekend'),
('Weekend Night', 'Weekend overnight shift', '22:00:00', '06:00:00', '#e83e8c', 'weekend');

-- Create employees table if it doesn't exist
CREATE TABLE IF NOT EXISTS employees (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    phone VARCHAR(20),
    position VARCHAR(100) NOT NULL,
    department VARCHAR(100) NOT NULL,
    hire_date DATE NOT NULL,
    salary DECIMAL(10,2) DEFAULT 0.00,
    status ENUM('active', 'inactive', 'terminated') DEFAULT 'active',
    online_status ENUM('online', 'offline') DEFAULT 'offline',
    last_activity TIMESTAMP NULL,
    password VARCHAR(255),
    profile_picture VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_department_status (department, status),
    INDEX idx_hire_date (hire_date),
    INDEX idx_status (status),
    INDEX idx_online_status (online_status)
) ENGINE=InnoDB;

-- Create time_entries table
CREATE TABLE IF NOT EXISTS time_entries (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    employee_id BIGINT UNSIGNED NOT NULL,
    work_date DATE NOT NULL,
    clock_in_time TIME,
    clock_out_time TIME,
    hours_worked DECIMAL(4,2) DEFAULT 0.00,
    overtime_hours DECIMAL(4,2) DEFAULT 0.00,
    break_duration DECIMAL(4,2) DEFAULT 0.00,
    description TEXT,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    approved_by BIGINT UNSIGNED NULL,
    approved_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    FOREIGN KEY (approved_by) REFERENCES employees(id) ON DELETE SET NULL,
    INDEX idx_employee_date (employee_id, work_date),
    INDEX idx_work_date (work_date),
    INDEX idx_status (status)
) ENGINE=InnoDB;

-- Create leave_types table
CREATE TABLE IF NOT EXISTS leave_types (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    days_allowed INT DEFAULT 0,
    carry_forward BOOLEAN DEFAULT FALSE,
    requires_approval BOOLEAN DEFAULT TRUE,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_status (status)
) ENGINE=InnoDB;

-- Create leave_requests table
CREATE TABLE IF NOT EXISTS leave_requests (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    employee_id BIGINT UNSIGNED NOT NULL,
    leave_type_id BIGINT UNSIGNED NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    days_requested INT NOT NULL,
    reason TEXT,
    status ENUM('pending', 'approved', 'rejected', 'cancelled') DEFAULT 'pending',
    approved_by BIGINT UNSIGNED NULL,
    approved_at TIMESTAMP NULL,
    rejection_reason TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    FOREIGN KEY (leave_type_id) REFERENCES leave_types(id) ON DELETE CASCADE,
    FOREIGN KEY (approved_by) REFERENCES employees(id) ON DELETE SET NULL,
    INDEX idx_employee_status (employee_id, status),
    INDEX idx_leave_type (leave_type_id),
    INDEX idx_dates (start_date, end_date)
) ENGINE=InnoDB;

-- Create claim_types table
CREATE TABLE IF NOT EXISTS claim_types (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    max_amount DECIMAL(10,2),
    requires_receipt BOOLEAN DEFAULT TRUE,
    approval_required BOOLEAN DEFAULT TRUE,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_status (status)
) ENGINE=InnoDB;

-- Create claims table
CREATE TABLE IF NOT EXISTS claims (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    employee_id BIGINT UNSIGNED NOT NULL,
    claim_type_id BIGINT UNSIGNED NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    claim_date DATE NOT NULL,
    description TEXT,
    receipt_path VARCHAR(255),
    attachment_path VARCHAR(255),
    status ENUM('pending', 'approved', 'rejected', 'paid') DEFAULT 'pending',
    approved_by BIGINT UNSIGNED NULL,
    approved_at TIMESTAMP NULL,
    paid_at TIMESTAMP NULL,
    rejection_reason TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    FOREIGN KEY (claim_type_id) REFERENCES claim_types(id) ON DELETE CASCADE,
    FOREIGN KEY (approved_by) REFERENCES employees(id) ON DELETE SET NULL,
    INDEX idx_employee_status (employee_id, status),
    INDEX idx_claim_type (claim_type_id),
    INDEX idx_claim_date (claim_date)
) ENGINE=InnoDB;

-- Clear existing employees and related data safely
DELETE FROM shift_requests WHERE employee_id BETWEEN 1 AND 8;
DELETE FROM shifts WHERE employee_id BETWEEN 1 AND 8;
DELETE FROM employees WHERE email IN (
    'john.doe@jetlouge.com', 'jane.smith@jetlouge.com', 'mike.johnson@jetlouge.com', 
    'sarah.wilson@jetlouge.com', 'david.brown@jetlouge.com', 'lisa.davis@jetlouge.com',
    'tom.miller@jetlouge.com', 'emma.garcia@jetlouge.com'
);

-- Reset auto increment to ensure we get IDs 1-8
ALTER TABLE employees AUTO_INCREMENT = 1;

-- Insert sample employees (will get auto-assigned IDs 1-8)
INSERT INTO employees (first_name, last_name, email, position, department, hire_date, status, salary) VALUES
('John', 'Doe', 'john.doe@jetlouge.com', 'Customer Service Representative', 'Operations', '2024-01-15', 'active', 35000.00),
('Jane', 'Smith', 'jane.smith@jetlouge.com', 'Travel Consultant', 'Sales', '2024-02-01', 'active', 40000.00),
('Mike', 'Johnson', 'mike.johnson@jetlouge.com', 'Operations Manager', 'Operations', '2023-11-10', 'active', 55000.00),
('Sarah', 'Wilson', 'sarah.wilson@jetlouge.com', 'Marketing Specialist', 'Marketing', '2024-03-05', 'active', 42000.00),
('David', 'Brown', 'david.brown@jetlouge.com', 'IT Support', 'IT', '2024-01-20', 'active', 45000.00),
('Lisa', 'Davis', 'lisa.davis@jetlouge.com', 'HR Coordinator', 'Human Resources', '2023-12-01', 'active', 38000.00),
('Tom', 'Miller', 'tom.miller@jetlouge.com', 'Finance Analyst', 'Finance', '2024-02-15', 'active', 48000.00),
('Emma', 'Garcia', 'emma.garcia@jetlouge.com', 'Customer Service Rep', 'Operations', '2024-03-01', 'active', 36000.00);

-- Insert comprehensive sample shift data for September 2025
INSERT INTO shifts (employee_id, shift_type_id, date, start_time, end_time, status) VALUES
-- Week 1 (Sep 1-7, 2025)
(1, 1, '2025-09-01', '08:00:00', '16:00:00', 'scheduled'),
(2, 1, '2025-09-01', '08:00:00', '16:00:00', 'scheduled'),
(3, 2, '2025-09-01', '16:00:00', '00:00:00', 'scheduled'),
(4, 1, '2025-09-02', '08:00:00', '16:00:00', 'scheduled'),
(5, 1, '2025-09-02', '08:00:00', '16:00:00', 'scheduled'),
(6, 2, '2025-09-02', '16:00:00', '00:00:00', 'scheduled'),
(7, 1, '2025-09-03', '08:00:00', '16:00:00', 'scheduled'),
(8, 1, '2025-09-03', '08:00:00', '16:00:00', 'scheduled'),
(1, 2, '2025-09-03', '16:00:00', '00:00:00', 'scheduled'),
(2, 1, '2025-09-04', '08:00:00', '16:00:00', 'scheduled'),
(3, 1, '2025-09-04', '08:00:00', '16:00:00', 'scheduled'),
(4, 2, '2025-09-04', '16:00:00', '00:00:00', 'scheduled'),
(5, 1, '2025-09-05', '08:00:00', '16:00:00', 'scheduled'),
(6, 1, '2025-09-05', '08:00:00', '16:00:00', 'scheduled'),
(7, 2, '2025-09-05', '16:00:00', '00:00:00', 'scheduled'),
(8, 4, '2025-09-06', '09:00:00', '17:00:00', 'scheduled'),
(1, 4, '2025-09-06', '09:00:00', '17:00:00', 'scheduled'),
(2, 4, '2025-09-07', '09:00:00', '17:00:00', 'scheduled'),
(3, 4, '2025-09-07', '09:00:00', '17:00:00', 'scheduled'),

-- Week 2 (Sep 8-14, 2025)
(4, 1, '2025-09-08', '08:00:00', '16:00:00', 'scheduled'),
(5, 1, '2025-09-08', '08:00:00', '16:00:00', 'scheduled'),
(6, 2, '2025-09-08', '16:00:00', '00:00:00', 'scheduled'),
(7, 1, '2025-09-09', '08:00:00', '16:00:00', 'scheduled'),
(8, 1, '2025-09-09', '08:00:00', '16:00:00', 'scheduled'),
(1, 2, '2025-09-09', '16:00:00', '00:00:00', 'scheduled'),
(2, 1, '2025-09-10', '08:00:00', '16:00:00', 'scheduled'),
(3, 1, '2025-09-10', '08:00:00', '16:00:00', 'scheduled'),
(4, 2, '2025-09-10', '16:00:00', '00:00:00', 'scheduled'),
(5, 1, '2025-09-11', '08:00:00', '16:00:00', 'scheduled'),
(6, 1, '2025-09-11', '08:00:00', '16:00:00', 'scheduled'),
(7, 2, '2025-09-11', '16:00:00', '00:00:00', 'scheduled'),
(8, 1, '2025-09-12', '08:00:00', '16:00:00', 'scheduled'),
(1, 1, '2025-09-12', '08:00:00', '16:00:00', 'scheduled'),
(2, 2, '2025-09-12', '16:00:00', '00:00:00', 'scheduled'),
(3, 4, '2025-09-13', '09:00:00', '17:00:00', 'scheduled'),
(4, 4, '2025-09-13', '09:00:00', '17:00:00', 'scheduled'),
(5, 4, '2025-09-14', '09:00:00', '17:00:00', 'scheduled'),
(6, 4, '2025-09-14', '09:00:00', '17:00:00', 'scheduled'),

-- Week 3 (Sep 15-21, 2025)
(7, 1, '2025-09-15', '08:00:00', '16:00:00', 'scheduled'),
(8, 1, '2025-09-15', '08:00:00', '16:00:00', 'scheduled'),
(1, 2, '2025-09-15', '16:00:00', '00:00:00', 'scheduled'),
(2, 1, '2025-09-16', '08:00:00', '16:00:00', 'scheduled'),
(3, 1, '2025-09-16', '08:00:00', '16:00:00', 'scheduled'),
(4, 2, '2025-09-16', '16:00:00', '00:00:00', 'scheduled'),
(5, 1, '2025-09-17', '08:00:00', '16:00:00', 'scheduled'),
(6, 1, '2025-09-17', '08:00:00', '16:00:00', 'scheduled'),
(7, 2, '2025-09-17', '16:00:00', '00:00:00', 'scheduled'),
(8, 1, '2025-09-18', '08:00:00', '16:00:00', 'scheduled'),
(1, 1, '2025-09-18', '08:00:00', '16:00:00', 'scheduled'),
(2, 2, '2025-09-18', '16:00:00', '00:00:00', 'scheduled'),
(3, 1, '2025-09-19', '08:00:00', '16:00:00', 'scheduled'),
(4, 1, '2025-09-19', '08:00:00', '16:00:00', 'scheduled'),
(5, 2, '2025-09-19', '16:00:00', '00:00:00', 'scheduled'),
(6, 4, '2025-09-20', '09:00:00', '17:00:00', 'scheduled'),
(7, 4, '2025-09-20', '09:00:00', '17:00:00', 'scheduled'),
(8, 4, '2025-09-21', '09:00:00', '17:00:00', 'scheduled'),
(1, 4, '2025-09-21', '09:00:00', '17:00:00', 'scheduled'),

-- Week 4 (Sep 22-28, 2025)
(2, 1, '2025-09-22', '08:00:00', '16:00:00', 'scheduled'),
(3, 1, '2025-09-22', '08:00:00', '16:00:00', 'scheduled'),
(4, 2, '2025-09-22', '16:00:00', '00:00:00', 'scheduled'),
(5, 1, '2025-09-23', '08:00:00', '16:00:00', 'scheduled'),
(6, 1, '2025-09-23', '08:00:00', '16:00:00', 'scheduled'),
(7, 2, '2025-09-23', '16:00:00', '00:00:00', 'scheduled'),
(8, 1, '2025-09-24', '08:00:00', '16:00:00', 'scheduled'),
(1, 1, '2025-09-24', '08:00:00', '16:00:00', 'scheduled'),
(2, 2, '2025-09-24', '16:00:00', '00:00:00', 'scheduled'),
(3, 1, '2025-09-25', '08:00:00', '16:00:00', 'scheduled'),
(4, 1, '2025-09-25', '08:00:00', '16:00:00', 'scheduled'),
(5, 2, '2025-09-25', '16:00:00', '00:00:00', 'scheduled'),
(6, 1, '2025-09-26', '08:00:00', '16:00:00', 'scheduled'),
(7, 1, '2025-09-26', '08:00:00', '16:00:00', 'scheduled'),
(8, 2, '2025-09-26', '16:00:00', '00:00:00', 'scheduled'),
(1, 4, '2025-09-27', '09:00:00', '17:00:00', 'scheduled'),
(2, 4, '2025-09-27', '09:00:00', '17:00:00', 'scheduled'),
(3, 4, '2025-09-28', '09:00:00', '17:00:00', 'scheduled'),
(4, 4, '2025-09-28', '09:00:00', '17:00:00', 'scheduled'),

-- Week 5 (Sep 29-30, 2025)
(5, 1, '2025-09-29', '08:00:00', '16:00:00', 'scheduled'),
(6, 1, '2025-09-29', '08:00:00', '16:00:00', 'scheduled'),
(7, 2, '2025-09-29', '16:00:00', '00:00:00', 'scheduled'),
(8, 1, '2025-09-30', '08:00:00', '16:00:00', 'scheduled'),
(1, 1, '2025-09-30', '08:00:00', '16:00:00', 'scheduled'),
(2, 2, '2025-09-30', '16:00:00', '00:00:00', 'scheduled');

-- Insert sample leave types
INSERT INTO leave_types (name, description, days_allowed, carry_forward, requires_approval) VALUES
('Annual Leave', 'Yearly vacation leave', 15, TRUE, TRUE),
('Sick Leave', 'Medical leave for illness', 10, FALSE, FALSE),
('Emergency Leave', 'Urgent personal matters', 5, FALSE, TRUE),
('Maternity Leave', 'Maternity leave for mothers', 90, FALSE, TRUE),
('Paternity Leave', 'Paternity leave for fathers', 7, FALSE, TRUE);

-- Insert sample claim types
INSERT INTO claim_types (name, description, max_amount, requires_receipt, approval_required) VALUES
('Travel Expenses', 'Business travel related expenses', 5000.00, TRUE, TRUE),
('Meal Allowance', 'Meal expenses during business hours', 500.00, TRUE, FALSE),
('Office Supplies', 'Office equipment and supplies', 1000.00, TRUE, TRUE),
('Training Fees', 'Professional development and training', 10000.00, TRUE, TRUE),
('Communication', 'Phone and internet expenses', 2000.00, TRUE, FALSE);

-- Insert sample time entries
INSERT INTO time_entries (employee_id, work_date, clock_in_time, clock_out_time, hours_worked, overtime_hours, description, status) VALUES
(1, CURDATE(), '08:00:00', '17:00:00', 8.00, 0.00, 'Regular work day', 'approved'),
(2, CURDATE(), '08:30:00', '17:30:00', 8.00, 1.00, 'Extended work for project deadline', 'approved'),
(3, CURDATE(), '09:00:00', '18:00:00', 8.00, 1.00, 'Financial reporting', 'pending'),
(4, CURDATE() - INTERVAL 1 DAY, '08:00:00', '17:00:00', 8.00, 0.00, 'Marketing campaign work', 'approved'),
(5, CURDATE() - INTERVAL 1 DAY, '08:00:00', '16:00:00', 7.00, 0.00, 'Sales calls and meetings', 'approved');

-- Insert sample leave requests
INSERT INTO leave_requests (employee_id, leave_type_id, start_date, end_date, days_requested, reason, status) VALUES
(1, 1, CURDATE() + INTERVAL 7 DAY, CURDATE() + INTERVAL 9 DAY, 3, 'Family vacation', 'pending'),
(2, 2, CURDATE() + INTERVAL 2 DAY, CURDATE() + INTERVAL 2 DAY, 1, 'Medical appointment', 'approved'),
(3, 1, CURDATE() + INTERVAL 14 DAY, CURDATE() + INTERVAL 21 DAY, 8, 'Annual vacation', 'pending');

-- Insert sample claims
INSERT INTO claims (employee_id, claim_type_id, amount, claim_date, description, status) VALUES
(1, 1, 1500.00, CURDATE() - INTERVAL 3 DAY, 'Business trip to Manila', 'approved'),
(2, 2, 250.00, CURDATE() - INTERVAL 1 DAY, 'Client lunch meeting', 'pending'),
(3, 3, 800.00, CURDATE() - INTERVAL 5 DAY, 'Office equipment purchase', 'approved'),
(4, 4, 5000.00, CURDATE() - INTERVAL 10 DAY, 'Digital marketing certification', 'pending');

-- Insert sample shift requests
INSERT INTO shift_requests (employee_id, shift_type_id, requested_date, reason, status) VALUES
(1, 2, '2025-09-15', 'Prefer evening shift for personal appointment', 'pending'),
(3, 1, '2025-09-20', 'Request morning shift instead of weekend', 'approved'),
(5, 4, '2025-09-21', 'Available for weekend coverage', 'pending'),
(7, 3, '2025-09-25', 'Can work night shift if needed', 'pending');

-- Verify the setup
SELECT 'Setup Complete!' as status;
SELECT 'Shift Types' as table_name, COUNT(*) as records FROM shift_types
UNION ALL
SELECT 'Employees' as table_name, COUNT(*) as records FROM employees
UNION ALL
SELECT 'Shifts' as table_name, COUNT(*) as records FROM shifts
UNION ALL
SELECT 'Shift Requests' as table_name, COUNT(*) as records FROM shift_requests
UNION ALL
SELECT 'Time Entries' as table_name, COUNT(*) as records FROM time_entries
UNION ALL
SELECT 'Leave Types' as table_name, COUNT(*) as records FROM leave_types
UNION ALL
SELECT 'Leave Requests' as table_name, COUNT(*) as records FROM leave_requests
UNION ALL
SELECT 'Claim Types' as table_name, COUNT(*) as records FROM claim_types
UNION ALL
SELECT 'Claims' as table_name, COUNT(*) as records FROM claims;

-- Show sample data
SELECT 'Sample September 2025 Shifts:' as info;
SELECT 
    s.date,
    e.first_name,
    e.last_name,
    st.name as shift_type,
    s.start_time,
    s.end_time,
    s.status
FROM shifts s
JOIN employees e ON s.employee_id = e.id
JOIN shift_types st ON s.shift_type_id = st.id
WHERE s.date LIKE '2025-09%'
ORDER BY s.date, s.start_time
LIMIT 10;
