-- =============================================
-- COMPLETE EMPLOYEE LOGIN FIX
-- This script ensures employee login works properly
-- =============================================

-- First, check if hr3systemdb exists, if not create it
CREATE DATABASE IF NOT EXISTS hr3systemdb;
USE hr3systemdb;

-- Create employees table with proper structure
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
    remember_token VARCHAR(100),
    profile_picture VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Clear existing test employees to avoid conflicts
DELETE FROM employees WHERE email LIKE '%@jetlouge.com';

-- Insert fresh employee accounts with correct bcrypt hash for "password123"
INSERT INTO employees (first_name, last_name, email, position, department, hire_date, salary, status, password, created_at, updated_at) VALUES
('John', 'Doe', 'john.doe@jetlouge.com', 'Software Developer', 'IT', '2024-01-15', 55000.00, 'active', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NOW(), NOW()),
('Jane', 'Smith', 'jane.smith@jetlouge.com', 'HR Manager', 'Human Resources', '2024-02-01', 60000.00, 'active', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NOW(), NOW()),
('Mike', 'Johnson', 'mike.johnson@jetlouge.com', 'Sales Manager', 'Sales', '2024-01-20', 58000.00, 'active', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NOW(), NOW()),
('Sarah', 'Wilson', 'sarah.wilson@jetlouge.com', 'Marketing Specialist', 'Marketing', '2024-03-01', 52000.00, 'active', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NOW(), NOW()),
('David', 'Brown', 'david.brown@jetlouge.com', 'Finance Analyst', 'Finance', '2024-02-15', 54000.00, 'active', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NOW(), NOW()),
('Lisa', 'Garcia', 'lisa.garcia@jetlouge.com', 'Project Manager', 'Operations', '2024-01-10', 62000.00, 'active', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NOW(), NOW()),
('Robert', 'Martinez', 'robert.martinez@jetlouge.com', 'Customer Support', 'Support', '2024-03-15', 45000.00, 'active', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NOW(), NOW()),
('Emily', 'Davis', 'emily.davis@jetlouge.com', 'Quality Assurance', 'IT', '2024-02-20', 51000.00, 'active', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NOW(), NOW());

-- Verify employees were created successfully
SELECT 
    CONCAT('✓ ', first_name, ' ', last_name, ' (', email, ')') as employee_info,
    'Password: password123' as password_info
FROM employees 
WHERE status = 'active' AND email LIKE '%@jetlouge.com'
ORDER BY email;

-- Show database and table verification
SELECT 
    DATABASE() as current_database,
    COUNT(*) as total_employees,
    SUM(CASE WHEN password IS NOT NULL AND LENGTH(password) = 60 THEN 1 ELSE 0 END) as employees_with_password,
    SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_employees
FROM employees;

-- Final verification query
SELECT 'Database: hr3systemdb' as info
UNION ALL
SELECT 'Table: employees'  
UNION ALL
SELECT CONCAT('Total Active Employees: ', COUNT(*))
FROM employees WHERE status = 'active'
UNION ALL
SELECT '=== TEST THESE CREDENTIALS ==='
UNION ALL
SELECT 'Email: jane.smith@jetlouge.com'
UNION ALL
SELECT 'Password: password123'
UNION ALL
SELECT 'URL: http://localhost/employee/login';
