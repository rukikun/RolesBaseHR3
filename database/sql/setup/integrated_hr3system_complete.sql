-- INTEGRATED HR3 SYSTEM DATABASE - COMPLETE SETUP
-- Combines Admin Dashboard + Employee ESS Systems
-- Run this script in phpMyAdmin to replace your current database

-- Create database if it doesn't exist
CREATE DATABASE IF NOT EXISTS hr3systemdb CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE hr3systemdb;

-- Drop existing tables in correct order (foreign key dependencies)
DROP TABLE IF EXISTS employee_trainings;
DROP TABLE IF EXISTS competency_assessments;
DROP TABLE IF EXISTS employee_requests;
DROP TABLE IF EXISTS payslips;
DROP TABLE IF EXISTS employee_notifications;
DROP TABLE IF EXISTS leave_balances;
DROP TABLE IF EXISTS shift_requests;
DROP TABLE IF EXISTS shifts;
DROP TABLE IF EXISTS shift_types;
DROP TABLE IF EXISTS claims;
DROP TABLE IF EXISTS claim_types;
DROP TABLE IF EXISTS leave_requests;
DROP TABLE IF EXISTS leave_types;
DROP TABLE IF EXISTS time_entries;
DROP TABLE IF EXISTS training_programs;
DROP TABLE IF EXISTS employees;
DROP TABLE IF EXISTS users;

-- =============================================
-- CORE TABLES
-- =============================================

-- Create employees table (unified for both admin and ESS)
CREATE TABLE employees (
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
    remember_token VARCHAR(100),
    profile_picture VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_department_status (department, status),
    INDEX idx_hire_date (hire_date),
    INDEX idx_status (status),
    INDEX idx_online_status (online_status)
) ENGINE=InnoDB;

-- Create users table (for admin authentication)
CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    email_verified_at TIMESTAMP NULL,
    password VARCHAR(255) NOT NULL,
    remember_token VARCHAR(100),
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
) ENGINE=InnoDB;

-- =============================================
-- TIME AND ATTENDANCE TABLES
-- =============================================

-- Create time_entries table
CREATE TABLE time_entries (
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

-- =============================================
-- SHIFT MANAGEMENT TABLES
-- =============================================

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
    employee_id BIGINT UNSIGNED NOT NULL,
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
    employee_id BIGINT UNSIGNED NOT NULL,
    shift_type_id INT NOT NULL,
    requested_date DATE NOT NULL,
    preferred_start_time TIME,
    preferred_end_time TIME,
    reason TEXT,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    approved_by BIGINT UNSIGNED NULL,
    approved_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    FOREIGN KEY (shift_type_id) REFERENCES shift_types(id) ON DELETE CASCADE,
    FOREIGN KEY (approved_by) REFERENCES employees(id) ON DELETE SET NULL,
    INDEX idx_employee_status (employee_id, status),
    INDEX idx_date_status (requested_date, status)
) ENGINE=InnoDB;

-- =============================================
-- LEAVE MANAGEMENT TABLES
-- =============================================

-- Create leave_types table
CREATE TABLE leave_types (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    code VARCHAR(10),
    description TEXT,
    days_allowed INT DEFAULT 0,
    max_days_per_year INT DEFAULT 0,
    carry_forward BOOLEAN DEFAULT FALSE,
    requires_approval BOOLEAN DEFAULT TRUE,
    status ENUM('active', 'inactive') DEFAULT 'active',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_status (status)
) ENGINE=InnoDB;

-- Create leave_requests table
CREATE TABLE leave_requests (
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

-- Create leave_balances table
CREATE TABLE leave_balances (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    employee_id BIGINT UNSIGNED NOT NULL,
    leave_type_id BIGINT UNSIGNED NOT NULL,
    year INT NOT NULL,
    days_allocated INT NOT NULL DEFAULT 0,
    days_used INT NOT NULL DEFAULT 0,
    days_remaining INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE KEY unique_employee_leave_year (employee_id, leave_type_id, year),
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    FOREIGN KEY (leave_type_id) REFERENCES leave_types(id) ON DELETE CASCADE,
    INDEX idx_employee_id (employee_id),
    INDEX idx_leave_type_id (leave_type_id),
    INDEX idx_year (year)
) ENGINE=InnoDB;

-- =============================================
-- CLAIMS AND REIMBURSEMENT TABLES
-- =============================================

-- Create claim_types table
CREATE TABLE claim_types (
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
CREATE TABLE claims (
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

-- =============================================
-- EMPLOYEE ESS TABLES
-- =============================================

-- Create training_programs table
CREATE TABLE training_programs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    type ENUM('mandatory', 'optional') NOT NULL DEFAULT 'optional',
    duration_hours INT NOT NULL DEFAULT 0,
    delivery_mode ENUM('online', 'classroom', 'hybrid') NOT NULL DEFAULT 'online',
    cost DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    provider VARCHAR(255),
    is_active BOOLEAN NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Create employee_trainings table
CREATE TABLE employee_trainings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    employee_id BIGINT UNSIGNED NOT NULL,
    training_id BIGINT UNSIGNED NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE,
    status ENUM('assigned', 'in_progress', 'completed', 'cancelled') NOT NULL DEFAULT 'assigned',
    progress_percentage DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    assigned_by BIGINT UNSIGNED,
    completed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    FOREIGN KEY (training_id) REFERENCES training_programs(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_by) REFERENCES employees(id) ON DELETE SET NULL,
    INDEX idx_employee_id (employee_id),
    INDEX idx_training_id (training_id),
    INDEX idx_status (status)
) ENGINE=InnoDB;

-- Create competency_assessments table
CREATE TABLE competency_assessments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    employee_id BIGINT UNSIGNED NOT NULL,
    competency_name VARCHAR(255) NOT NULL,
    description TEXT,
    target_score DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    current_score DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    score DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    assessment_date DATE NOT NULL,
    status ENUM('pending', 'in_progress', 'completed', 'cancelled') NOT NULL DEFAULT 'pending',
    assessed_by BIGINT UNSIGNED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    FOREIGN KEY (assessed_by) REFERENCES employees(id) ON DELETE SET NULL,
    INDEX idx_employee_id (employee_id),
    INDEX idx_status (status),
    INDEX idx_assessment_date (assessment_date)
) ENGINE=InnoDB;

-- Create employee_requests table
CREATE TABLE employee_requests (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    employee_id BIGINT UNSIGNED NOT NULL,
    request_type VARCHAR(255) NOT NULL,
    reason TEXT NOT NULL,
    requested_date DATE NOT NULL,
    status ENUM('pending', 'approved', 'rejected', 'cancelled') NOT NULL DEFAULT 'pending',
    approved_by BIGINT UNSIGNED,
    approved_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    FOREIGN KEY (approved_by) REFERENCES employees(id) ON DELETE SET NULL,
    INDEX idx_employee_id (employee_id),
    INDEX idx_status (status),
    INDEX idx_requested_date (requested_date)
) ENGINE=InnoDB;

-- Create payslips table
CREATE TABLE payslips (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    employee_id BIGINT UNSIGNED NOT NULL,
    pay_period_start DATE NOT NULL,
    pay_period_end DATE NOT NULL,
    basic_salary DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    overtime_pay DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    allowances DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    bonuses DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    gross_pay DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    tax_deduction DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    sss_deduction DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    philhealth_deduction DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    pagibig_deduction DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    other_deductions DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    total_deductions DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    net_pay DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    status ENUM('draft', 'finalized', 'sent') NOT NULL DEFAULT 'draft',
    generated_at TIMESTAMP NULL,
    generated_by BIGINT UNSIGNED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    FOREIGN KEY (generated_by) REFERENCES employees(id) ON DELETE SET NULL,
    INDEX idx_employee_id (employee_id),
    INDEX idx_pay_period (pay_period_start, pay_period_end),
    INDEX idx_status (status)
) ENGINE=InnoDB;

-- Create employee_notifications table
CREATE TABLE employee_notifications (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    employee_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('info', 'success', 'warning', 'error') NOT NULL DEFAULT 'info',
    is_read BOOLEAN NOT NULL DEFAULT 0,
    sent_at TIMESTAMP NULL,
    read_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    INDEX idx_employee_id (employee_id),
    INDEX idx_is_read (is_read),
    INDEX idx_sent_at (sent_at)
) ENGINE=InnoDB;

-- =============================================
-- SAMPLE DATA INSERTION
-- =============================================

-- Insert admin user
INSERT INTO users (name, email, password, created_at, updated_at) VALUES
('Admin User', 'admin@jetlouge.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NOW(), NOW());

-- Insert sample employees (unified for both admin and ESS)
INSERT INTO employees (first_name, last_name, email, phone, position, department, hire_date, salary, status, password, created_at, updated_at) VALUES
('John', 'Doe', 'john.doe@jetlouge.com', '+63-912-345-6789', 'Customer Service Representative', 'Operations', '2024-01-15', 35000.00, 'active', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NOW(), NOW()),
('Jane', 'Smith', 'jane.smith@jetlouge.com', '+63-912-345-6790', 'Travel Consultant', 'Sales', '2024-02-01', 40000.00, 'active', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NOW(), NOW()),
('Mike', 'Johnson', 'mike.johnson@jetlouge.com', '+63-912-345-6791', 'Operations Manager', 'Operations', '2023-11-10', 55000.00, 'active', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NOW(), NOW()),
('Sarah', 'Wilson', 'sarah.wilson@jetlouge.com', '+63-912-345-6792', 'Marketing Specialist', 'Marketing', '2024-03-05', 42000.00, 'active', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NOW(), NOW()),
('David', 'Brown', 'david.brown@jetlouge.com', '+63-912-345-6793', 'IT Support', 'IT', '2024-01-20', 45000.00, 'active', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NOW(), NOW()),
('Lisa', 'Davis', 'lisa.davis@jetlouge.com', '+63-912-345-6794', 'HR Coordinator', 'Human Resources', '2023-12-01', 38000.00, 'active', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NOW(), NOW()),
('Tom', 'Miller', 'tom.miller@jetlouge.com', '+63-912-345-6795', 'Finance Analyst', 'Finance', '2024-02-15', 48000.00, 'active', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NOW(), NOW()),
('Emma', 'Garcia', 'emma.garcia@jetlouge.com', '+63-912-345-6796', 'Customer Service Rep', 'Operations', '2024-03-01', 36000.00, 'active', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NOW(), NOW());

-- Insert shift types
INSERT INTO shift_types (name, description, default_start_time, default_end_time, color_code, type) VALUES
('Morning Shift', 'Standard morning work shift', '08:00:00', '16:00:00', '#28a745', 'morning'),
('Evening Shift', 'Standard evening work shift', '16:00:00', '00:00:00', '#fd7e14', 'evening'),
('Night Shift', 'Overnight work shift', '00:00:00', '08:00:00', '#6f42c1', 'night'),
('Weekend Day', 'Weekend daytime shift', '09:00:00', '17:00:00', '#20c997', 'weekend'),
('Weekend Night', 'Weekend overnight shift', '22:00:00', '06:00:00', '#e83e8c', 'weekend');

-- Insert leave types
INSERT INTO leave_types (name, code, description, days_allowed, max_days_per_year, carry_forward, requires_approval) VALUES
('Annual Leave', 'VL', 'Yearly vacation leave for rest and recreation', 15, 15, TRUE, TRUE),
('Sick Leave', 'SL', 'Medical leave for illness or health issues', 10, 10, TRUE, FALSE),
('Emergency Leave', 'EL', 'Emergency leave for urgent personal matters', 5, 5, FALSE, TRUE),
('Maternity Leave', 'ML', 'Maternity leave for new mothers', 90, 90, FALSE, TRUE),
('Paternity Leave', 'PL', 'Paternity leave for new fathers', 7, 7, FALSE, TRUE);

-- Insert claim types
INSERT INTO claim_types (name, description, max_amount, requires_receipt, approval_required) VALUES
('Travel Expenses', 'Business travel related expenses', 5000.00, TRUE, TRUE),
('Meal Allowance', 'Meal expenses during business hours', 500.00, TRUE, FALSE),
('Office Supplies', 'Office equipment and supplies', 1000.00, TRUE, TRUE),
('Training Fees', 'Professional development and training', 10000.00, TRUE, TRUE),
('Communication', 'Phone and internet expenses', 2000.00, TRUE, FALSE);

-- Insert training programs
INSERT INTO training_programs (title, description, type, duration_hours, delivery_mode, cost, provider, is_active, created_at, updated_at) VALUES
('Data Privacy and Security', 'Comprehensive training on data protection and cybersecurity best practices', 'mandatory', 8, 'online', 0.00, 'Internal HR', 1, NOW(), NOW()),
('Customer Service Excellence', 'Advanced customer service techniques and communication skills', 'optional', 16, 'hybrid', 5000.00, 'External Provider', 1, NOW(), NOW()),
('Leadership Development', 'Management and leadership skills for supervisory roles', 'optional', 24, 'classroom', 15000.00, 'Leadership Institute', 1, NOW(), NOW()),
('Travel Industry Regulations', 'Understanding travel industry compliance and regulations', 'mandatory', 12, 'online', 0.00, 'Industry Association', 1, NOW(), NOW()),
('Digital Marketing Fundamentals', 'Modern digital marketing strategies and tools', 'optional', 20, 'online', 8000.00, 'Marketing Academy', 1, NOW(), NOW());

-- Insert comprehensive sample data for September 2025
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
(3, 4, '2025-09-07', '09:00:00', '17:00:00', 'scheduled');

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

-- Insert sample employee notifications
INSERT INTO employee_notifications (employee_id, title, message, type, sent_at, created_at, updated_at) VALUES
(1, 'Training Assignment', 'You have been assigned to complete "Data Privacy and Security" training by end of month.', 'info', NOW(), NOW(), NOW()),
(1, 'Leave Request Update', 'Your vacation leave request for next week has been approved.', 'success', NOW(), NOW(), NOW()),
(2, 'System Maintenance', 'HR system will be under maintenance this weekend. Please complete pending tasks.', 'warning', NOW(), NOW(), NOW()),
(3, 'Performance Review', 'Your quarterly performance review is scheduled for next Friday.', 'info', NOW(), NOW(), NOW()),
(4, 'Payroll Update', 'Your latest payslip is now available in the system.', 'success', NOW(), NOW(), NOW());

-- Insert sample employee trainings
INSERT INTO employee_trainings (employee_id, training_id, start_date, end_date, status, progress_percentage, assigned_by, created_at, updated_at) VALUES
(1, 1, '2024-09-01', '2024-09-15', 'in_progress', 60.00, 1, NOW(), NOW()),
(1, 2, '2024-10-01', '2024-10-31', 'assigned', 0.00, 1, NOW(), NOW()),
(2, 1, '2024-09-01', '2024-09-15', 'completed', 100.00, 1, NOW(), NOW()),
(2, 3, '2024-11-01', '2024-11-30', 'assigned', 0.00, 1, NOW(), NOW()),
(3, 4, '2024-09-15', '2024-09-30', 'in_progress', 75.00, 1, NOW(), NOW());

-- Insert sample leave balances
INSERT INTO leave_balances (employee_id, leave_type_id, year, days_allocated, days_used, days_remaining, created_at, updated_at) VALUES
(1, 1, 2024, 15, 3, 12, NOW(), NOW()),
(1, 2, 2024, 10, 0, 10, NOW(), NOW()),
(2, 1, 2024, 15, 0, 15, NOW(), NOW()),
(2, 2, 2024, 10, 2, 8, NOW(), NOW()),
(3, 1, 2024, 15, 0, 15, NOW(), NOW()),
(3, 2, 2024, 10, 0, 10, NOW(), NOW()),
(4, 1, 2024, 15, 0, 15, NOW(), NOW()),
(4, 2, 2024, 10, 0, 10, NOW(), NOW());

-- Insert sample payslips with Philippine tax structure
INSERT INTO payslips (employee_id, pay_period_start, pay_period_end, basic_salary, overtime_pay, allowances, bonuses, gross_pay, tax_deduction, sss_deduction, philhealth_deduction, pagibig_deduction, other_deductions, total_deductions, net_pay, status, generated_at, generated_by, created_at, updated_at) VALUES
(1, '2024-08-01', '2024-08-31', 35000.00, 2500.00, 3000.00, 0.00, 40500.00, 6075.00, 1575.00, 1012.50, 200.00, 0.00, 8862.50, 31637.50, 'finalized', NOW(), 1, NOW(), NOW()),
(2, '2024-08-01', '2024-08-31', 40000.00, 1800.00, 2500.00, 1000.00, 45300.00, 6795.00, 2040.00, 1132.50, 200.00, 0.00, 10167.50, 35132.50, 'finalized', NOW(), 1, NOW(), NOW()),
(3, '2024-08-01', '2024-08-31', 55000.00, 0.00, 4000.00, 2000.00, 61000.00, 9150.00, 2750.00, 1525.00, 200.00, 0.00, 13625.00, 47375.00, 'finalized', NOW(), 1, NOW(), NOW());

-- =============================================
-- VERIFICATION QUERIES
-- =============================================

-- Display setup completion status
SELECT 'INTEGRATED HR3 SYSTEM SETUP COMPLETE!' as status;

-- Show table counts
SELECT 'Table Counts:' as info;
SELECT 
    'Users' as table_name, COUNT(*) as records FROM users
UNION ALL
SELECT 'Employees' as table_name, COUNT(*) as records FROM employees
UNION ALL
SELECT 'Shift Types' as table_name, COUNT(*) as records FROM shift_types
UNION ALL
SELECT 'Shifts' as table_name, COUNT(*) as records FROM shifts
UNION ALL
SELECT 'Time Entries' as table_name, COUNT(*) as records FROM time_entries
UNION ALL
SELECT 'Leave Types' as table_name, COUNT(*) as records FROM leave_types
UNION ALL
SELECT 'Leave Requests' as table_name, COUNT(*) as records FROM leave_requests
UNION ALL
SELECT 'Claim Types' as table_name, COUNT(*) as records FROM claim_types
UNION ALL
SELECT 'Claims' as table_name, COUNT(*) as records FROM claims
UNION ALL
SELECT 'Training Programs' as table_name, COUNT(*) as records FROM training_programs
UNION ALL
SELECT 'Employee Trainings' as table_name, COUNT(*) as records FROM employee_trainings
UNION ALL
SELECT 'Employee Notifications' as table_name, COUNT(*) as records FROM employee_notifications
UNION ALL
SELECT 'Leave Balances' as table_name, COUNT(*) as records FROM leave_balances
UNION ALL
SELECT 'Payslips' as table_name, COUNT(*) as records FROM payslips;

-- Show login credentials
SELECT 'LOGIN CREDENTIALS:' as info;
SELECT 'ADMIN LOGIN:' as type, 'admin@jetlouge.com' as email, 'password123' as password
UNION ALL
SELECT 'EMPLOYEE ESS LOGIN:', 'john.doe@jetlouge.com', 'password123'
UNION ALL
SELECT '', 'jane.smith@jetlouge.com', 'password123'
UNION ALL
SELECT '', 'mike.johnson@jetlouge.com', 'password123'
UNION ALL
SELECT '', 'All other employees', 'password123';
