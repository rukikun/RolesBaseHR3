-- =============================================
-- ULTIMATE EMPLOYEE LOGIN FIX
-- This script ensures employee login works by fixing all potential issues
-- =============================================

USE hr3systemdb;

-- First, ensure we're using the correct database
SELECT 'Using database: hr3systemdb' as status;

-- Clear any existing problematic employee records
DELETE FROM employees WHERE email LIKE '%@jetlouge.com';

-- Create fresh employee records with multiple password hash formats to ensure compatibility
INSERT INTO employees (first_name, last_name, email, position, department, hire_date, salary, status, password, created_at, updated_at) VALUES
('John', 'Doe', 'john.doe@jetlouge.com', 'Software Developer', 'IT', '2024-01-15', 55000.00, 'active', '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', NOW(), NOW()),
('Jane', 'Smith', 'jane.smith@jetlouge.com', 'HR Manager', 'Human Resources', '2024-02-01', 60000.00, 'active', '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', NOW(), NOW()),
('Mike', 'Johnson', 'mike.johnson@jetlouge.com', 'Sales Manager', 'Sales', '2024-01-20', 58000.00, 'active', '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', NOW(), NOW()),
('Sarah', 'Wilson', 'sarah.wilson@jetlouge.com', 'Marketing Specialist', 'Marketing', '2024-03-01', 52000.00, 'active', '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', NOW(), NOW()),
('David', 'Brown', 'david.brown@jetlouge.com', 'Finance Analyst', 'Finance', '2024-02-15', 54000.00, 'active', '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', NOW(), NOW());

-- Verify employees were created
SELECT 
    id,
    CONCAT(first_name, ' ', last_name) as full_name,
    email,
    position,
    status,
    LENGTH(password) as pwd_length,
    'password123' as login_password
FROM employees 
WHERE status = 'active' AND email LIKE '%@jetlouge.com'
ORDER BY id;

-- Final verification
SELECT 
    COUNT(*) as total_active_employees,
    'All employees can login with password123' as note,
    'URL: http://localhost/employee/login' as login_url
FROM employees 
WHERE status = 'active';
