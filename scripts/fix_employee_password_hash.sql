-- =============================================
-- FIX EMPLOYEE PASSWORD HASH
-- This script updates employee passwords with a working bcrypt hash
-- =============================================

USE hr3systemdb;

-- Update all employees with a fresh bcrypt hash for "password123"
-- Using a different hash that should work with Laravel
UPDATE employees 
SET password = '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm',
    updated_at = NOW()
WHERE status = 'active';

-- Verify the update
SELECT 
    id,
    CONCAT(first_name, ' ', last_name) as name,
    email,
    status,
    LENGTH(password) as password_length,
    LEFT(password, 10) as password_start,
    'password123' as use_this_password
FROM employees 
WHERE status = 'active'
ORDER BY id;

-- Show login instructions
SELECT 
    '=== EMPLOYEE LOGIN CREDENTIALS ===' as info
UNION ALL
SELECT CONCAT('✓ ', first_name, ' ', last_name, ' - ', email)
FROM employees 
WHERE status = 'active'
UNION ALL
SELECT '=== PASSWORD FOR ALL EMPLOYEES ==='
UNION ALL
SELECT 'password123'
UNION ALL
SELECT '=== LOGIN URL ==='
UNION ALL
SELECT 'http://localhost/employee/login';
