-- =============================================
-- DIAGNOSE LOGIN ISSUE
-- This script checks why login is failing despite database setup
-- =============================================

USE hr3systemdb;

-- Check if we're using the right database
SELECT DATABASE() as current_database;

-- Check employees table structure
DESCRIBE employees;

-- Check specific employee you're trying to log in with
SELECT 
    id,
    first_name,
    last_name,
    email,
    status,
    password IS NOT NULL as has_password,
    LENGTH(password) as password_length,
    LEFT(password, 7) as password_start,
    created_at,
    updated_at
FROM employees 
WHERE email = 'john.doe@jetlouge.com';

-- Check all employees with passwords
SELECT 
    id,
    CONCAT(first_name, ' ', last_name) as name,
    email,
    status,
    CASE 
        WHEN password IS NULL THEN 'NO PASSWORD'
        WHEN LENGTH(password) < 10 THEN 'WEAK PASSWORD'
        WHEN LENGTH(password) = 60 THEN 'BCRYPT HASH (GOOD)'
        ELSE CONCAT('LENGTH: ', LENGTH(password))
    END as password_status
FROM employees 
WHERE status = 'active'
ORDER BY id;

-- Test password verification (this will show if our hash is correct)
-- The hash $2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi should equal "password123"
SELECT 
    'Password Hash Test' as test_type,
    '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' as hash_used,
    'password123' as plain_password,
    'This hash should work with Laravel bcrypt verification' as note;

-- Show database connection info
SHOW VARIABLES LIKE 'character_set_database';
SHOW VARIABLES LIKE 'collation_database';
