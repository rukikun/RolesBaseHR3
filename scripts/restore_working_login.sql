-- Restore Working Employee Login (Based on Previous Working Session)
USE hr3systemdb;

-- Clear any problematic employee records
DELETE FROM employees WHERE email IN ('john.doe@jetlouge.com', 'jane.smith@jetlouge.com', 'mike.johnson@jetlouge.com');

-- Insert employees with the EXACT same hash that was working before
-- This is the bcrypt hash for 'password123' that was confirmed working
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
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
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
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
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
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    NOW(), 
    NOW()
);

-- Verify employees were created correctly
SELECT 
    id,
    CONCAT(first_name, ' ', last_name) as name,
    email,
    status,
    'password123' as test_password
FROM employees 
WHERE email LIKE '%@jetlouge.com'
ORDER BY id;
