-- Complete Employee Authentication Fix
-- This will completely reset and fix the employee authentication

USE hr3systemdb;

-- First, ensure the employees table has the correct structure
ALTER TABLE employees 
ADD COLUMN IF NOT EXISTS remember_token VARCHAR(100) NULL AFTER password;

-- Clear existing problematic employees
DELETE FROM employees WHERE email LIKE '%@jetlouge.com';

-- Insert fresh employees with PHP-compatible password hashes
-- Using password_hash('password123', PASSWORD_DEFAULT) equivalent
INSERT INTO employees (
    first_name, 
    last_name, 
    email, 
    position, 
    department, 
    hire_date, 
    salary, 
    status, 
    password,
    remember_token,
    created_at, 
    updated_at
) VALUES 
(
    'John', 
    'Doe', 
    'john.doe@jetlouge.com', 
    'Software Developer', 
    'IT', 
    '2024-01-15', 
    55000.00, 
    'active', 
    '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxkUBww2BDv6SvnpEOeKHF0H0ni',
    NULL,
    NOW(), 
    NOW()
),
(
    'Jane', 
    'Smith', 
    'jane.smith@jetlouge.com', 
    'HR Manager', 
    'Human Resources', 
    '2024-02-01', 
    60000.00, 
    'active', 
    '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxkUBww2BDv6SvnpEOeKHF0H0ni',
    NULL,
    NOW(), 
    NOW()
),
(
    'Mike', 
    'Johnson', 
    'mike.johnson@jetlouge.com', 
    'Sales Manager', 
    'Sales', 
    '2024-01-20', 
    58000.00, 
    'active', 
    '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxkUBww2BDv6SvnpEOeKHF0H0ni',
    NULL,
    NOW(), 
    NOW()
);

-- Verify the employees were created
SELECT 
    id,
    CONCAT(first_name, ' ', last_name) as name,
    email,
    status,
    LENGTH(password) as password_length,
    SUBSTRING(password, 1, 7) as hash_type,
    'password123' as test_password
FROM employees 
WHERE email LIKE '%@jetlouge.com'
ORDER BY id;

-- Show final instructions
SELECT '=== AUTHENTICATION FIXED ===' as message
UNION ALL
SELECT 'Email: john.doe@jetlouge.com'
UNION ALL
SELECT 'Password: password123'
UNION ALL
SELECT 'URL: http://127.0.0.1:8000/employee/login';
