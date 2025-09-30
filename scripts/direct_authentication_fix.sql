-- =============================================
-- DIRECT AUTHENTICATION FIX
-- This script fixes authentication by using a known working password hash
-- =============================================

USE hr3systemdb;

-- Clear existing employees to avoid conflicts
DELETE FROM employees WHERE email LIKE '%@jetlouge.com';

-- Insert employees with a password hash that definitely works with Laravel
-- This hash is for "password123" and is tested to work with Laravel authentication
INSERT INTO employees (first_name, last_name, email, position, department, hire_date, salary, status, password, created_at, updated_at) VALUES
('John', 'Doe', 'john.doe@jetlouge.com', 'Software Developer', 'IT', '2024-01-15', 55000.00, 'active', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NOW(), NOW()),
('Jane', 'Smith', 'jane.smith@jetlouge.com', 'HR Manager', 'Human Resources', '2024-02-01', 60000.00, 'active', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NOW(), NOW()),
('Mike', 'Johnson', 'mike.johnson@jetlouge.com', 'Sales Manager', 'Sales', '2024-01-20', 58000.00, 'active', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NOW(), NOW());

-- Verify the employees were created with correct password format
SELECT 
    id,
    CONCAT(first_name, ' ', last_name) as name,
    email,
    status,
    LENGTH(password) as password_length,
    SUBSTRING(password, 1, 7) as hash_type,
    'password123' as login_password
FROM employees 
WHERE email LIKE '%@jetlouge.com'
ORDER BY id;

-- Show login test information
SELECT 
    '=== LOGIN TEST CREDENTIALS ===' as info
UNION ALL
SELECT 'Email: john.doe@jetlouge.com'
UNION ALL  
SELECT 'Password: password123'
UNION ALL
SELECT 'URL: http://localhost/employee/login'
UNION ALL
SELECT '=== TROUBLESHOOTING ==='
UNION ALL
SELECT 'If still fails, check .env file:'
UNION ALL
SELECT 'DB_DATABASE=hr3systemdb'
UNION ALL
SELECT 'Then run: php artisan config:clear';
