-- Manual Database Setup for HR System
-- Run this in phpMyAdmin or MySQL Workbench

CREATE DATABASE IF NOT EXISTS hr3systemdb;
USE hr3systemdb;

-- Create employees table
CREATE TABLE IF NOT EXISTS employees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id VARCHAR(20) UNIQUE NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(20),
    position VARCHAR(100),
    department VARCHAR(100),
    hire_date DATE,
    salary DECIMAL(10,2),
    status ENUM('active', 'inactive', 'terminated') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Drop time_entries table if it exists to recreate with correct structure
DROP TABLE IF EXISTS time_entries;

-- Create time_entries table
CREATE TABLE time_entries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    date DATE NOT NULL,
    clock_in TIME,
    clock_out TIME,
    break_duration INT DEFAULT 0,
    total_hours DECIMAL(4,2),
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    notes TEXT,
    approved_by INT,
    approved_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    UNIQUE KEY unique_employee_date (employee_id, date)
);

-- Insert sample employees
INSERT IGNORE INTO employees (employee_id, first_name, last_name, email, phone, position, department, hire_date, salary, status) VALUES
('EMP001', 'John', 'Doe', 'john.doe@company.com', '+1234567890', 'Software Developer', 'IT', '2023-01-15', 75000.00, 'active'),
('EMP002', 'Jane', 'Smith', 'jane.smith@company.com', '+1234567891', 'HR Manager', 'Human Resources', '2022-03-10', 65000.00, 'active'),
('EMP003', 'Mike', 'Johnson', 'mike.johnson@company.com', '+1234567892', 'Marketing Specialist', 'Marketing', '2023-06-01', 55000.00, 'active'),
('EMP004', 'Sarah', 'Wilson', 'sarah.wilson@company.com', '+1234567893', 'Accountant', 'Finance', '2022-11-20', 60000.00, 'active'),
('EMP005', 'Admin', 'User', 'admin@jetlouge.com', '+1234567894', 'System Administrator', 'IT', '2022-01-01', 80000.00, 'active');

-- Insert sample time entries
INSERT IGNORE INTO time_entries (employee_id, date, clock_in, clock_out, status, notes) VALUES
(1, '2025-08-20', '09:00:00', '17:00:00', 'approved', 'Regular workday'),
(1, '2025-08-21', '09:15:00', '17:30:00', 'approved', 'Slightly late start'),
(1, '2025-08-22', '08:45:00', '16:45:00', 'pending', 'Early finish'),
(1, '2025-08-23', '09:00:00', '17:00:00', 'pending', 'Regular workday'),
(1, '2025-08-24', '09:30:00', '17:30:00', 'pending', 'Late start'),
(2, '2025-08-20', '09:00:00', '17:00:00', 'approved', 'HR meetings'),
(2, '2025-08-21', '09:00:00', '18:00:00', 'approved', 'Overtime for recruitment'),
(2, '2025-08-22', '09:00:00', '17:00:00', 'pending', 'Regular workday'),
(3, '2025-08-20', '09:30:00', '17:30:00', 'pending', 'Marketing campaign work'),
(3, '2025-08-21', '09:00:00', '17:00:00', 'approved', 'Client meetings'),
(4, '2025-08-20', '09:00:00', '17:00:00', 'approved', 'Financial reports'),
(4, '2025-08-21', '08:30:00', '16:30:00', 'approved', 'Early shift'),
(5, '2025-08-20', '08:00:00', '16:00:00', 'approved', 'System maintenance'),
(5, '2025-08-21', '08:00:00', '16:00:00', 'approved', 'Server updates');

-- Verify data
SELECT 'Employees created:' as info, COUNT(*) as count FROM employees
UNION ALL
SELECT 'Time entries created:' as info, COUNT(*) as count FROM time_entries;
