-- Direct Employee Creation for Login Testing
USE hr3systemdb;

-- Delete existing test employee
DELETE FROM employees WHERE email = 'john.doe@jetlouge.com';

-- Create employee with verified working password hash
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
) VALUES (
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
);

-- Verify employee was created
SELECT 
    id,
    CONCAT(first_name, ' ', last_name) as name,
    email,
    status,
    'password123' as password_to_use
FROM employees 
WHERE email = 'john.doe@jetlouge.com';
