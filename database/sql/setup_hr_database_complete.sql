-- Complete HR Database Setup Script
-- Run this script to create all necessary tables with proper relationships

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
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_department (department),
    INDEX idx_employee_number (employee_number)
);

-- Time entries table (for timesheets)
CREATE TABLE time_entries (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    employee_id BIGINT UNSIGNED NOT NULL,
    work_date DATE NOT NULL,
    clock_in TIME NULL,
    clock_out TIME NULL,
    hours_worked DECIMAL(5,2) DEFAULT 0.00,
    overtime_hours DECIMAL(5,2) DEFAULT 0.00,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    description TEXT NULL,
    notes TEXT NULL,
    approved_by BIGINT UNSIGNED NULL,
    approved_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_employee_work_date (employee_id, work_date),
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
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_type (type),
    INDEX idx_is_active (is_active)
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

-- Leave types table
CREATE TABLE leave_types (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    days_allowed INT DEFAULT 0,
    is_paid BOOLEAN DEFAULT TRUE,
    requires_approval BOOLEAN DEFAULT TRUE,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Leave requests table
CREATE TABLE leave_requests (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    employee_id BIGINT UNSIGNED NOT NULL,
    leave_type_id BIGINT UNSIGNED NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    days_requested INT NOT NULL,
    reason TEXT,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    approved_by BIGINT UNSIGNED NULL,
    approved_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    FOREIGN KEY (leave_type_id) REFERENCES leave_types(id) ON DELETE CASCADE,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_employee_id (employee_id),
    INDEX idx_status (status),
    INDEX idx_dates (start_date, end_date)
);

-- Claim types table
CREATE TABLE claim_types (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    max_amount DECIMAL(10,2) DEFAULT NULL,
    requires_receipt BOOLEAN DEFAULT TRUE,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Claims table
CREATE TABLE claims (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    employee_id BIGINT UNSIGNED NOT NULL,
    claim_type_id BIGINT UNSIGNED NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    claim_date DATE NOT NULL,
    description TEXT,
    receipt_path VARCHAR(255) NULL,
    status ENUM('pending', 'approved', 'rejected', 'paid') DEFAULT 'pending',
    approved_by BIGINT UNSIGNED NULL,
    approved_at TIMESTAMP NULL,
    paid_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    FOREIGN KEY (claim_type_id) REFERENCES claim_types(id) ON DELETE CASCADE,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_employee_id (employee_id),
    INDEX idx_status (status),
    INDEX idx_claim_date (claim_date)
);

-- Insert sample data
INSERT INTO users (name, email, password) VALUES
('Admin User', 'admin@jetlouge.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'); -- password123

INSERT INTO employees (employee_number, first_name, last_name, email, phone, position, department, hire_date, status) VALUES
('EMP001', 'John', 'Doe', 'john.doe@jetlouge.com', '555-0101', 'Developer', 'IT', '2024-01-15', 'active'),
('EMP002', 'Jane', 'Smith', 'jane.smith@jetlouge.com', '555-0102', 'Manager', 'HR', '2024-02-01', 'active'),
('EMP003', 'Mike', 'Johnson', 'mike.johnson@jetlouge.com', '555-0103', 'Analyst', 'Finance', '2024-03-10', 'active'),
('EMP004', 'Sarah', 'Wilson', 'sarah.wilson@jetlouge.com', '555-0104', 'Designer', 'Marketing', '2024-01-20', 'active'),
('EMP005', 'David', 'Brown', 'david.brown@jetlouge.com', '555-0105', 'Support', 'IT', '2024-02-15', 'active');

INSERT INTO shift_types (name, type, start_time, end_time, break_duration, description) VALUES
('Morning Shift', 'day', '08:00:00', '16:00:00', 30, 'Standard morning shift'),
('Evening Shift', 'evening', '16:00:00', '00:00:00', 30, 'Evening shift coverage'),
('Night Shift', 'night', '00:00:00', '08:00:00', 30, 'Overnight shift coverage');

INSERT INTO leave_types (name, description, days_allowed, is_paid) VALUES
('Annual Leave', 'Yearly vacation days', 21, TRUE),
('Sick Leave', 'Medical leave', 10, TRUE),
('Personal Leave', 'Personal time off', 5, FALSE),
('Maternity Leave', 'Maternity/Paternity leave', 90, TRUE);

INSERT INTO claim_types (name, description, max_amount, requires_receipt) VALUES
('Travel Expenses', 'Business travel costs', 1000.00, TRUE),
('Office Supplies', 'Office equipment and supplies', 200.00, TRUE),
('Training Courses', 'Professional development', 500.00, TRUE),
('Client Entertainment', 'Client meeting expenses', 300.00, TRUE);

-- Insert sample time entries
INSERT INTO time_entries (employee_id, work_date, hours_worked, overtime_hours, status, description) VALUES
(1, CURDATE(), 8.0, 0.0, 'approved', 'Regular work day'),
(1, DATE_SUB(CURDATE(), INTERVAL 1 DAY), 8.5, 0.5, 'pending', 'Worked overtime'),
(2, CURDATE(), 7.5, 0.0, 'approved', 'Regular work day'),
(3, CURDATE(), 8.0, 0.0, 'pending', 'Regular work day');

-- Insert sample shifts
INSERT INTO shifts (employee_id, shift_type_id, date, start_time, end_time, status) VALUES
(1, 1, CURDATE(), '08:00:00', '16:00:00', 'scheduled'),
(2, 1, CURDATE(), '08:00:00', '16:00:00', 'scheduled'),
(3, 2, CURDATE(), '16:00:00', '00:00:00', 'scheduled');

SELECT 'Database setup complete!' as status;
SELECT 'Tables created:' as info;
SHOW TABLES;
