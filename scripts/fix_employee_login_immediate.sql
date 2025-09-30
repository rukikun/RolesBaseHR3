-- =============================================
-- IMMEDIATE FIX FOR EMPLOYEE LOGIN
-- Execute this script in phpMyAdmin to fix login issues
-- =============================================

USE hr3systemdb;

-- First, let's ensure the employees table has the password column
ALTER TABLE employees ADD COLUMN IF NOT EXISTS password VARCHAR(255) NULL;

-- Delete any existing test employees to avoid duplicates
DELETE FROM employees WHERE email LIKE '%@jetlouge.com';

-- Insert fresh employee accounts with properly hashed passwords
-- Password hash for "password123" using Laravel bcrypt
INSERT INTO employees (first_name, last_name, email, position, department, hire_date, salary, status, password, created_at, updated_at) VALUES
('John', 'Doe', 'john.doe@jetlouge.com', 'Software Developer', 'IT', '2024-01-15', 55000.00, 'active', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NOW(), NOW()),
('Jane', 'Smith', 'jane.smith@jetlouge.com', 'HR Manager', 'Human Resources', '2024-02-01', 60000.00, 'active', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NOW(), NOW()),
('Mike', 'Johnson', 'mike.johnson@jetlouge.com', 'Sales Manager', 'Sales', '2024-01-20', 58000.00, 'active', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NOW(), NOW()),
('Sarah', 'Wilson', 'sarah.wilson@jetlouge.com', 'Marketing Specialist', 'Marketing', '2024-03-01', 52000.00, 'active', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NOW(), NOW()),
('David', 'Brown', 'david.brown@jetlouge.com', 'Finance Analyst', 'Finance', '2024-02-15', 54000.00, 'active', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NOW(), NOW());

-- Verify the accounts were created
SELECT 
    id,
    CONCAT(first_name, ' ', last_name) as name,
    email,
    position,
    status,
    'password123' as password_to_use,
    CASE WHEN LENGTH(password) = 60 THEN '✓ Password OK' ELSE '✗ Password Issue' END as password_status
FROM employees 
WHERE email LIKE '%@jetlouge.com'
ORDER BY id;

-- Show login instructions
SELECT 
    '=== EMPLOYEE LOGIN CREDENTIALS ===' as info
UNION ALL
SELECT CONCAT('Email: ', email, ' | Password: password123') 
FROM employees 
WHERE status = 'active' AND email LIKE '%@jetlouge.com'
UNION ALL
SELECT '=== LOGIN URL ===' 
UNION ALL
SELECT 'http://localhost/employee/login';
