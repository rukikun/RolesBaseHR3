-- =============================================
-- SET DEFAULT EMPLOYEE PASSWORDS SCRIPT
-- This script sets the default password "password123" for all employees
-- =============================================

USE hr3systemdb;

-- Update all existing employees with hashed password for "password123"
-- Using Laravel's bcrypt equivalent hash
UPDATE employees 
SET password = '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    updated_at = NOW()
WHERE password IS NULL OR password = '' OR LENGTH(password) < 10;

-- Insert sample employees if the table is empty
INSERT IGNORE INTO employees (first_name, last_name, email, position, department, hire_date, salary, status, password, created_at, updated_at)
VALUES 
('John', 'Doe', 'john.doe@jetlouge.com', 'Software Developer', 'IT', '2024-01-15', 55000.00, 'active', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NOW(), NOW()),
('Jane', 'Smith', 'jane.smith@jetlouge.com', 'HR Manager', 'Human Resources', '2024-02-01', 60000.00, 'active', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NOW(), NOW()),
('Mike', 'Johnson', 'mike.johnson@jetlouge.com', 'Sales Manager', 'Sales', '2024-01-20', 58000.00, 'active', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NOW(), NOW()),
('Sarah', 'Wilson', 'sarah.wilson@jetlouge.com', 'Marketing Specialist', 'Marketing', '2024-03-01', 52000.00, 'active', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NOW(), NOW()),
('David', 'Brown', 'david.brown@jetlouge.com', 'Finance Analyst', 'Finance', '2024-02-15', 54000.00, 'active', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NOW(), NOW()),
('Lisa', 'Garcia', 'lisa.garcia@jetlouge.com', 'Project Manager', 'Operations', '2024-01-10', 62000.00, 'active', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NOW(), NOW()),
('Robert', 'Martinez', 'robert.martinez@jetlouge.com', 'Customer Support', 'Support', '2024-03-15', 45000.00, 'active', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NOW(), NOW()),
('Emily', 'Davis', 'emily.davis@jetlouge.com', 'Quality Assurance', 'IT', '2024-02-20', 51000.00, 'active', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NOW(), NOW());

-- Display all active employees for verification
SELECT 
    id,
    CONCAT(first_name, ' ', last_name) as full_name,
    email,
    position,
    department,
    status,
    CASE 
        WHEN password IS NOT NULL AND LENGTH(password) > 10 THEN 'Password Set ✓'
        ELSE 'No Password ✗'
    END as password_status
FROM employees 
WHERE status = 'active'
ORDER BY id;

-- Show summary
SELECT 
    COUNT(*) as total_employees,
    SUM(CASE WHEN password IS NOT NULL AND LENGTH(password) > 10 THEN 1 ELSE 0 END) as employees_with_password,
    SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_employees
FROM employees;
