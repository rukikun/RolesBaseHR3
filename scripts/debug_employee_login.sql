-- =============================================
-- DEBUG EMPLOYEE LOGIN SCRIPT
-- This script checks the current state of employee accounts
-- =============================================

USE hr3systemdb;

-- Check if employees table exists and show structure
SHOW TABLES LIKE 'employees';
DESCRIBE employees;

-- Check current employee records
SELECT 
    id,
    first_name,
    last_name,
    email,
    status,
    CASE 
        WHEN password IS NULL THEN 'NULL'
        WHEN password = '' THEN 'EMPTY'
        WHEN LENGTH(password) < 10 THEN 'TOO SHORT'
        WHEN LENGTH(password) >= 60 THEN 'HASHED (OK)'
        ELSE 'UNKNOWN'
    END as password_status,
    LENGTH(password) as password_length,
    created_at,
    updated_at
FROM employees 
ORDER BY id;

-- Count employees by status
SELECT 
    status,
    COUNT(*) as count,
    COUNT(CASE WHEN password IS NOT NULL AND LENGTH(password) >= 60 THEN 1 END) as with_password
FROM employees 
GROUP BY status;

-- Check specific employee that was tested
SELECT 
    id,
    first_name,
    last_name,
    email,
    status,
    password IS NOT NULL as has_password,
    LENGTH(password) as pwd_length,
    LEFT(password, 10) as pwd_start
FROM employees 
WHERE email = 'jane.smith@jetlouge.com';

-- Show all active employees for testing
SELECT 
    CONCAT('Email: ', email, ' | Password: password123') as login_credentials
FROM employees 
WHERE status = 'active'
ORDER BY id;
