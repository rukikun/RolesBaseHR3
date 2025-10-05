-- Complete HR System Database Schema
-- This script creates all necessary tables for the HR3 system

-- Create database if not exists
CREATE DATABASE IF NOT EXISTS hr3systemdb;
USE hr3systemdb;

-- Disable foreign key checks to allow dropping tables
SET FOREIGN_KEY_CHECKS = 0;

-- Drop existing tables (order doesn't matter with foreign key checks disabled)
DROP TABLE IF EXISTS shift_requests;
DROP TABLE IF EXISTS shifts;
DROP TABLE IF EXISTS shift_types;
DROP TABLE IF EXISTS leave_requests;
DROP TABLE IF EXISTS leave_balances;
DROP TABLE IF EXISTS leave_types;
DROP TABLE IF EXISTS claims;
DROP TABLE IF EXISTS claim_types;
DROP TABLE IF EXISTS time_entries;
DROP TABLE IF EXISTS employees;
DROP TABLE IF EXISTS users;

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

-- Users table (for authentication)
CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    email_verified_at TIMESTAMP NULL,
    password VARCHAR(255) NOT NULL,
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Employees table
CREATE TABLE employees (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    employee_number VARCHAR(50) UNIQUE,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    phone VARCHAR(20),
    position VARCHAR(100) NOT NULL,
    department VARCHAR(100) NOT NULL,
    hire_date DATE NOT NULL,
    salary DECIMAL(10,2) DEFAULT 0.00,
    status ENUM('active', 'inactive', 'terminated') DEFAULT 'active',
    user_id BIGINT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_email (email),
    INDEX idx_status (status),
    INDEX idx_department (department)
);

-- Time entries table
CREATE TABLE time_entries (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    employee_id BIGINT UNSIGNED NOT NULL,
    work_date DATE NOT NULL,
    clock_in_time TIME,
    clock_out_time TIME,
    hours_worked DECIMAL(4,2) NOT NULL,
    overtime_hours DECIMAL(4,2) DEFAULT 0.00,
    break_duration INT DEFAULT 0, -- in minutes
    description TEXT,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    approved_by BIGINT UNSIGNED NULL,
    approved_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_employee_date (employee_id, work_date),
    INDEX idx_status (status),
    INDEX idx_work_date (work_date)
);

-- Shift types table
CREATE TABLE shift_types (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    type ENUM('day', 'evening', 'night', 'weekend') NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    break_duration INT DEFAULT 30, -- in minutes
    color_code VARCHAR(7) DEFAULT '#007bff',
    description TEXT,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_type (type),
    INDEX idx_status (status)
);

-- Shifts table (shift assignments)
CREATE TABLE shifts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    employee_id BIGINT UNSIGNED NOT NULL,
    shift_type_id BIGINT UNSIGNED NOT NULL,
    date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    status ENUM('scheduled', 'completed', 'cancelled', 'no_show') DEFAULT 'scheduled',
    notes TEXT,
    created_by BIGINT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    FOREIGN KEY (shift_type_id) REFERENCES shift_types(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY unique_employee_date (employee_id, date),
    INDEX idx_date (date),
    INDEX idx_status (status)
);

-- Shift requests table
CREATE TABLE shift_requests (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    employee_id BIGINT UNSIGNED NOT NULL,
    request_type ENUM('change', 'swap', 'overtime', 'time_off') NOT NULL,
    current_shift_id BIGINT UNSIGNED NULL,
    requested_shift_id BIGINT UNSIGNED NULL,
    requested_date DATE NOT NULL,
    reason TEXT NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    approved_by BIGINT UNSIGNED NULL,
    approved_at TIMESTAMP NULL,
    rejection_reason TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    FOREIGN KEY (current_shift_id) REFERENCES shifts(id) ON DELETE SET NULL,
    FOREIGN KEY (requested_shift_id) REFERENCES shifts(id) ON DELETE SET NULL,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_employee (employee_id),
    INDEX idx_status (status),
    INDEX idx_date (requested_date)
);

-- Leave types table
CREATE TABLE leave_types (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    code VARCHAR(20) UNIQUE NOT NULL,
    max_days_per_year INT DEFAULT 0,
    carry_forward BOOLEAN DEFAULT FALSE,
    requires_approval BOOLEAN DEFAULT TRUE,
    is_active BOOLEAN DEFAULT TRUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_code (code),
    INDEX idx_active (is_active)
);

-- Leave balances table
CREATE TABLE leave_balances (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    employee_id BIGINT UNSIGNED NOT NULL,
    leave_type_id BIGINT UNSIGNED NOT NULL,
    year YEAR NOT NULL,
    allocated_days DECIMAL(4,1) NOT NULL,
    used_days DECIMAL(4,1) DEFAULT 0.0,
    remaining_days DECIMAL(4,1) GENERATED ALWAYS AS (allocated_days - used_days) STORED,
    carried_forward DECIMAL(4,1) DEFAULT 0.0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    FOREIGN KEY (leave_type_id) REFERENCES leave_types(id) ON DELETE CASCADE,
    UNIQUE KEY unique_employee_leave_year (employee_id, leave_type_id, year),
    INDEX idx_year (year)
);

-- Leave requests table
CREATE TABLE leave_requests (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    employee_id BIGINT UNSIGNED NOT NULL,
    leave_type_id BIGINT UNSIGNED NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    days_requested DECIMAL(4,1) NOT NULL,
    reason TEXT NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    approved_by BIGINT UNSIGNED NULL,
    approved_at TIMESTAMP NULL,
    rejection_reason TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    FOREIGN KEY (leave_type_id) REFERENCES leave_types(id) ON DELETE CASCADE,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_employee (employee_id),
    INDEX idx_status (status),
    INDEX idx_dates (start_date, end_date)
);

-- Claim types table
CREATE TABLE claim_types (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    code VARCHAR(20) UNIQUE NOT NULL,
    max_amount DECIMAL(10,2) DEFAULT 0.00,
    requires_receipt BOOLEAN DEFAULT TRUE,
    requires_approval BOOLEAN DEFAULT TRUE,
    is_active BOOLEAN DEFAULT TRUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_code (code),
    INDEX idx_active (is_active)
);

-- Claims table
CREATE TABLE claims (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    employee_id BIGINT UNSIGNED NOT NULL,
    claim_type_id BIGINT UNSIGNED NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    submitted_date DATE NOT NULL,
    description TEXT NOT NULL,
    receipt_path VARCHAR(500) NULL,
    status ENUM('pending', 'approved', 'rejected', 'paid') DEFAULT 'pending',
    approved_by BIGINT UNSIGNED NULL,
    approved_at TIMESTAMP NULL,
    paid_at TIMESTAMP NULL,
    rejection_reason TEXT NULL,
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    FOREIGN KEY (claim_type_id) REFERENCES claim_types(id) ON DELETE CASCADE,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_employee (employee_id),
    INDEX idx_status (status),
    INDEX idx_submitted_date (submitted_date)
);

-- Insert default admin user
INSERT INTO users (name, email, password, created_at, updated_at) VALUES 
('Admin User', 'admin@jetlouge.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NOW(), NOW());

-- Insert sample employees
INSERT INTO employees (employee_number, first_name, last_name, email, phone, position, department, hire_date, salary, status, user_id) VALUES
('EMP001', 'John', 'Anderson', 'john.anderson@jetlouge.com', '+1-555-0123', 'HR Administrator', 'Human Resources', '2023-01-15', 65000.00, 'active', 1),
('EMP002', 'Jane', 'Smith', 'jane.smith@jetlouge.com', '+1-555-0124', 'Software Developer', 'IT', '2023-03-20', 75000.00, 'active', NULL),
('EMP003', 'Mike', 'Johnson', 'mike.johnson@jetlouge.com', '+1-555-0125', 'Marketing Manager', 'Marketing', '2023-02-10', 70000.00, 'active', NULL),
('EMP004', 'Sarah', 'Wilson', 'sarah.wilson@jetlouge.com', '+1-555-0126', 'Accountant', 'Finance', '2023-04-05', 60000.00, 'active', NULL),
('EMP005', 'David', 'Brown', 'david.brown@jetlouge.com', '+1-555-0127', 'Sales Representative', 'Sales', '2023-05-12', 55000.00, 'active', NULL);

-- Insert sample shift types
INSERT INTO shift_types (name, type, start_time, end_time, break_duration, color_code, description) VALUES
('Morning Shift', 'day', '08:00:00', '16:00:00', 60, '#28a745', 'Standard morning shift'),
('Evening Shift', 'evening', '16:00:00', '00:00:00', 60, '#ffc107', 'Evening shift coverage'),
('Night Shift', 'night', '00:00:00', '08:00:00', 60, '#6f42c1', 'Overnight shift'),
('Weekend Day', 'weekend', '09:00:00', '17:00:00', 60, '#17a2b8', 'Weekend day shift');

-- Insert sample leave types
INSERT INTO leave_types (name, code, max_days_per_year, carry_forward, requires_approval, is_active, description) VALUES
('Annual Leave', 'AL', 25, TRUE, TRUE, TRUE, 'Annual vacation days'),
('Sick Leave', 'SL', 10, FALSE, FALSE, TRUE, 'Medical leave for illness'),
('Personal Leave', 'PL', 5, FALSE, TRUE, TRUE, 'Personal time off'),
('Maternity Leave', 'ML', 90, FALSE, TRUE, TRUE, 'Maternity leave'),
('Emergency Leave', 'EL', 3, FALSE, TRUE, TRUE, 'Emergency situations');

-- Insert sample claim types
INSERT INTO claim_types (name, code, max_amount, requires_receipt, requires_approval, is_active, description) VALUES
('Travel Expenses', 'TRAVEL', 1000.00, TRUE, TRUE, TRUE, 'Business travel related expenses'),
('Meal Allowance', 'MEAL', 50.00, TRUE, TRUE, TRUE, 'Business meal expenses'),
('Office Supplies', 'OFFICE', 200.00, TRUE, TRUE, TRUE, 'Office supplies and equipment'),
('Training Costs', 'TRAINING', 500.00, TRUE, TRUE, TRUE, 'Professional development and training'),
('Communication', 'COMM', 100.00, TRUE, TRUE, TRUE, 'Phone and internet expenses');

-- Insert sample leave balances for current year
INSERT INTO leave_balances (employee_id, leave_type_id, year, allocated_days, used_days, carried_forward) VALUES
(1, 1, YEAR(CURDATE()), 25.0, 5.0, 0.0),
(1, 2, YEAR(CURDATE()), 10.0, 2.0, 0.0),
(1, 3, YEAR(CURDATE()), 5.0, 1.0, 0.0),
(2, 1, YEAR(CURDATE()), 25.0, 3.0, 0.0),
(2, 2, YEAR(CURDATE()), 10.0, 1.0, 0.0),
(2, 3, YEAR(CURDATE()), 5.0, 0.0, 0.0),
(3, 1, YEAR(CURDATE()), 25.0, 8.0, 2.0),
(3, 2, YEAR(CURDATE()), 10.0, 0.0, 0.0),
(3, 3, YEAR(CURDATE()), 5.0, 2.0, 0.0);

-- Insert sample time entries
INSERT INTO time_entries (employee_id, work_date, clock_in_time, clock_out_time, hours_worked, overtime_hours, description, status) VALUES
(1, CURDATE() - INTERVAL 1 DAY, '08:00:00', '17:00:00', 8.0, 1.0, 'Regular work day with overtime', 'approved'),
(1, CURDATE() - INTERVAL 2 DAY, '08:00:00', '16:00:00', 8.0, 0.0, 'Regular work day', 'approved'),
(2, CURDATE() - INTERVAL 1 DAY, '09:00:00', '17:00:00', 8.0, 0.0, 'Development work', 'pending'),
(2, CURDATE() - INTERVAL 2 DAY, '09:00:00', '18:00:00', 8.0, 1.0, 'Project deadline work', 'approved'),
(3, CURDATE() - INTERVAL 1 DAY, '08:30:00', '16:30:00', 8.0, 0.0, 'Marketing campaigns', 'pending');

-- Insert sample shifts for current week
INSERT INTO shifts (employee_id, shift_type_id, date, start_time, end_time, status, notes) VALUES
(1, 1, CURDATE(), '08:00:00', '16:00:00', 'scheduled', 'Regular morning shift'),
(2, 1, CURDATE(), '08:00:00', '16:00:00', 'scheduled', 'Development work'),
(3, 1, CURDATE() + INTERVAL 1 DAY, '08:00:00', '16:00:00', 'scheduled', 'Marketing tasks'),
(4, 2, CURDATE() + INTERVAL 1 DAY, '16:00:00', '00:00:00', 'scheduled', 'Evening coverage'),
(5, 1, CURDATE() + INTERVAL 2 DAY, '08:00:00', '16:00:00', 'scheduled', 'Sales activities');

-- Insert sample leave requests
INSERT INTO leave_requests (employee_id, leave_type_id, start_date, end_date, days_requested, reason, status) VALUES
(1, 1, CURDATE() + INTERVAL 7 DAY, CURDATE() + INTERVAL 11 DAY, 5.0, 'Family vacation', 'pending'),
(2, 2, CURDATE() + INTERVAL 3 DAY, CURDATE() + INTERVAL 3 DAY, 1.0, 'Medical appointment', 'approved'),
(3, 1, CURDATE() + INTERVAL 14 DAY, CURDATE() + INTERVAL 18 DAY, 5.0, 'Personal travel', 'pending');

-- Insert sample claims
INSERT INTO claims (employee_id, claim_type_id, amount, submitted_date, description, status) VALUES
(1, 1, 250.00, CURDATE() - INTERVAL 2 DAY, 'Business trip to client site', 'approved'),
(2, 2, 35.50, CURDATE() - INTERVAL 1 DAY, 'Client lunch meeting', 'pending'),
(3, 3, 85.00, CURDATE() - INTERVAL 3 DAY, 'Office supplies purchase', 'paid'),
(4, 4, 300.00, CURDATE() - INTERVAL 5 DAY, 'Professional certification course', 'approved'),
(5, 1, 180.00, CURDATE() - INTERVAL 1 DAY, 'Travel expenses for conference', 'pending');

-- Create indexes for better performance
CREATE INDEX idx_employees_full_name ON employees (first_name, last_name);
CREATE INDEX idx_time_entries_employee_status ON time_entries (employee_id, status);
CREATE INDEX idx_shifts_employee_date ON shifts (employee_id, date);
CREATE INDEX idx_leave_requests_employee_status ON leave_requests (employee_id, status);
CREATE INDEX idx_claims_employee_status ON claims (employee_id, status);

-- Create views for reporting
CREATE VIEW employee_summary AS
SELECT 
    e.id,
    e.employee_number,
    CONCAT(e.first_name, ' ', e.last_name) as full_name,
    e.email,
    e.position,
    e.department,
    e.status,
    COUNT(DISTINCT te.id) as total_time_entries,
    COALESCE(SUM(te.hours_worked), 0) as total_hours_worked,
    COUNT(DISTINCT lr.id) as total_leave_requests,
    COUNT(DISTINCT c.id) as total_claims
FROM employees e
LEFT JOIN time_entries te ON e.id = te.employee_id
LEFT JOIN leave_requests lr ON e.id = lr.employee_id
LEFT JOIN claims c ON e.id = c.employee_id
GROUP BY e.id;

COMMIT;
