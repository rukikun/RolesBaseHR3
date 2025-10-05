-- Complete ESS Setup for hr3systemdb
USE hr3systemdb;

-- Setup Employee Self-Service (ESS) Sample Data (Safe Version)
-- This script handles existing employees and only adds missing data

-- Add password column to employees table if it doesn't exist
ALTER TABLE employees ADD COLUMN IF NOT EXISTS password VARCHAR(255);
ALTER TABLE employees ADD COLUMN IF NOT EXISTS remember_token VARCHAR(100);
ALTER TABLE employees ADD COLUMN IF NOT EXISTS profile_picture VARCHAR(255);

-- Update existing employees with passwords if they don't have them
-- Password for all test employees: 'password123'
UPDATE employees 
SET password = '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'
WHERE password IS NULL OR password = '';

-- Insert sample employees only if they don't exist
INSERT IGNORE INTO employees (first_name, last_name, email, phone, position, department, hire_date, salary, status, password, created_at, updated_at) VALUES
('John', 'Doe', 'john.doe@jetlouge.com', '+63-912-345-6789', 'Software Developer', 'IT', '2023-01-15', 50000.00, 'active', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NOW(), NOW()),
('Jane', 'Smith', 'jane.smith@jetlouge.com', '+63-912-345-6790', 'HR Specialist', 'Human Resources', '2023-02-01', 45000.00, 'active', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NOW(), NOW()),
('Mike', 'Johnson', 'mike.johnson@jetlouge.com', '+63-912-345-6791', 'Marketing Manager', 'Marketing', '2023-03-10', 55000.00, 'active', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NOW(), NOW()),
('Sarah', 'Wilson', 'sarah.wilson@jetlouge.com', '+63-912-345-6792', 'Accountant', 'Finance', '2023-04-05', 48000.00, 'active', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NOW(), NOW()),
('David', 'Brown', 'david.brown@jetlouge.com', '+63-912-345-6793', 'Travel Consultant', 'Operations', '2023-05-20', 42000.00, 'active', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NOW(), NOW());

-- Create leave types if they don't exist
INSERT IGNORE INTO leave_types (name, days_allowed, created_at, updated_at) VALUES
('Vacation Leave', 15, NOW(), NOW()),
('Sick Leave', 10, NOW(), NOW()),
('Emergency Leave', 5, NOW(), NOW()),
('Maternity Leave', 60, NOW(), NOW()),
('Paternity Leave', 7, NOW(), NOW());

-- Create training programs if they don't exist
INSERT IGNORE INTO training_programs (title, description, type, duration_hours, delivery_mode, cost, provider, is_active, created_at, updated_at) VALUES
('Data Privacy and Security', 'Comprehensive training on data protection and cybersecurity best practices', 'mandatory', 8, 'online', 0.00, 'Internal HR', 1, NOW(), NOW()),
('Customer Service Excellence', 'Advanced customer service techniques and communication skills', 'optional', 16, 'hybrid', 5000.00, 'External Provider', 1, NOW(), NOW()),
('Leadership Development', 'Management and leadership skills for supervisory roles', 'optional', 24, 'classroom', 15000.00, 'Leadership Institute', 1, NOW(), NOW()),
('Travel Industry Regulations', 'Understanding travel industry compliance and regulations', 'mandatory', 12, 'online', 0.00, 'Industry Association', 1, NOW(), NOW()),
('Digital Marketing Fundamentals', 'Modern digital marketing strategies and tools', 'optional', 20, 'online', 8000.00, 'Marketing Academy', 1, NOW(), NOW());

-- Get employee IDs for reference
SET @john_id = (SELECT id FROM employees WHERE email = 'john.doe@jetlouge.com' LIMIT 1);
SET @jane_id = (SELECT id FROM employees WHERE email = 'jane.smith@jetlouge.com' LIMIT 1);
SET @mike_id = (SELECT id FROM employees WHERE email = 'mike.johnson@jetlouge.com' LIMIT 1);
SET @sarah_id = (SELECT id FROM employees WHERE email = 'sarah.wilson@jetlouge.com' LIMIT 1);
SET @david_id = (SELECT id FROM employees WHERE email = 'david.brown@jetlouge.com' LIMIT 1);

-- Only proceed if we have employee IDs
-- Create sample employee notifications
INSERT IGNORE INTO employee_notifications (employee_id, title, message, type, sent_at, created_at, updated_at) 
SELECT * FROM (
    SELECT @john_id, 'Training Assignment', 'You have been assigned to complete "Data Privacy and Security" training by end of month.', 'info', NOW(), NOW(), NOW()
    UNION ALL
    SELECT @john_id, 'Leave Request Update', 'Your vacation leave request for next week has been approved.', 'success', NOW(), NOW(), NOW()
    UNION ALL
    SELECT @jane_id, 'System Maintenance', 'HR system will be under maintenance this weekend. Please complete pending tasks.', 'warning', NOW(), NOW(), NOW()
    UNION ALL
    SELECT @mike_id, 'Performance Review', 'Your quarterly performance review is scheduled for next Friday.', 'info', NOW(), NOW(), NOW()
    UNION ALL
    SELECT @sarah_id, 'Payroll Update', 'Your latest payslip is now available in the system.', 'success', NOW(), NOW(), NOW()
) AS tmp WHERE @john_id IS NOT NULL;

-- Create sample employee trainings
INSERT IGNORE INTO employee_trainings (employee_id, training_id, start_date, end_date, status, progress_percentage, assigned_by, created_at, updated_at) 
SELECT * FROM (
    SELECT @john_id, 1, '2024-09-01', '2024-09-15', 'in_progress', 60.00, 1, NOW(), NOW()
    UNION ALL
    SELECT @john_id, 2, '2024-10-01', '2024-10-31', 'assigned', 0.00, 1, NOW(), NOW()
    UNION ALL
    SELECT @jane_id, 1, '2024-09-01', '2024-09-15', 'completed', 100.00, 1, NOW(), NOW()
    UNION ALL
    SELECT @jane_id, 3, '2024-11-01', '2024-11-30', 'assigned', 0.00, 1, NOW(), NOW()
    UNION ALL
    SELECT @mike_id, 4, '2024-09-15', '2024-09-30', 'in_progress', 75.00, 1, NOW(), NOW()
    UNION ALL
    SELECT @sarah_id, 1, '2024-09-01', '2024-09-15', 'assigned', 0.00, 1, NOW(), NOW()
    UNION ALL
    SELECT @david_id, 2, '2024-10-15', '2024-11-15', 'assigned', 0.00, 1, NOW(), NOW()
) AS tmp WHERE @john_id IS NOT NULL;

-- Create sample competency assessments
INSERT IGNORE INTO competency_assessments (employee_id, competency_name, description, target_score, current_score, score, assessment_date, status, created_at, updated_at) 
SELECT * FROM (
    SELECT @john_id, 'Technical Skills', 'Programming and software development capabilities', 85.00, 78.00, 78.00, '2024-08-15', 'completed', NOW(), NOW()
    UNION ALL
    SELECT @john_id, 'Communication', 'Verbal and written communication effectiveness', 80.00, 85.00, 85.00, '2024-08-15', 'completed', NOW(), NOW()
    UNION ALL
    SELECT @jane_id, 'HR Knowledge', 'Human resources policies and procedures', 90.00, 88.00, 88.00, '2024-08-20', 'completed', NOW(), NOW()
    UNION ALL
    SELECT @jane_id, 'Leadership', 'Team management and leadership skills', 75.00, 70.00, 70.00, '2024-08-20', 'completed', NOW(), NOW()
    UNION ALL
    SELECT @mike_id, 'Marketing Strategy', 'Strategic marketing planning and execution', 85.00, 82.00, 82.00, '2024-08-25', 'completed', NOW(), NOW()
    UNION ALL
    SELECT @sarah_id, 'Financial Analysis', 'Accounting and financial reporting skills', 90.00, 92.00, 92.00, '2024-08-30', 'completed', NOW(), NOW()
    UNION ALL
    SELECT @david_id, 'Customer Service', 'Client interaction and service delivery', 80.00, 75.00, 75.00, '2024-09-01', 'completed', NOW(), NOW()
) AS tmp WHERE @john_id IS NOT NULL;

-- Create sample leave requests
INSERT IGNORE INTO leave_requests (employee_id, leave_type_id, start_date, end_date, reason, status, created_at, updated_at) 
SELECT * FROM (
    SELECT @john_id, 1, '2024-09-20', '2024-09-22', 'Family vacation', 'approved', NOW(), NOW()
    UNION ALL
    SELECT @jane_id, 2, '2024-09-10', '2024-09-11', 'Medical appointment', 'approved', NOW(), NOW()
    UNION ALL
    SELECT @mike_id, 1, '2024-10-01', '2024-10-05', 'Personal travel', 'pending', NOW(), NOW()
    UNION ALL
    SELECT @sarah_id, 3, '2024-09-15', '2024-09-15', 'Family emergency', 'approved', NOW(), NOW()
    UNION ALL
    SELECT @david_id, 1, '2024-11-01', '2024-11-03', 'Rest and relaxation', 'pending', NOW(), NOW()
) AS tmp WHERE @john_id IS NOT NULL;

-- Create sample employee requests
INSERT IGNORE INTO employee_requests (employee_id, request_type, reason, requested_date, status, created_at, updated_at) 
SELECT * FROM (
    SELECT @john_id, 'Equipment Request', 'Need new laptop for development work', '2024-09-15', 'approved', NOW(), NOW()
    UNION ALL
    SELECT @jane_id, 'Training Request', 'Request to attend HR conference', '2024-10-01', 'pending', NOW(), NOW()
    UNION ALL
    SELECT @mike_id, 'Certificate Request', 'Employment certificate for bank loan', NULL, 'processing', NOW(), NOW()
    UNION ALL
    SELECT @sarah_id, 'Attendance Adjustment', 'Adjust time in for September 5th due to traffic', '2024-09-05', 'approved', NOW(), NOW()
    UNION ALL
    SELECT @david_id, 'Other', 'Request for flexible work arrangement', NULL, 'pending', NOW(), NOW()
) AS tmp WHERE @john_id IS NOT NULL;

-- Create sample payslips
INSERT IGNORE INTO payslips (employee_id, pay_period_start, pay_period_end, basic_salary, overtime_pay, allowances, bonuses, gross_pay, tax_deduction, sss_deduction, philhealth_deduction, pagibig_deduction, other_deductions, total_deductions, net_pay, status, generated_at, generated_by, created_at, updated_at) 
SELECT * FROM (
    SELECT @john_id, '2024-08-01', '2024-08-31', 50000.00, 2500.00, 3000.00, 0.00, 55500.00, 8325.00, 2250.00, 1375.00, 200.00, 0.00, 12150.00, 43350.00, 'finalized', NOW(), 1, NOW(), NOW()
    UNION ALL
    SELECT @jane_id, '2024-08-01', '2024-08-31', 45000.00, 1800.00, 2500.00, 1000.00, 50300.00, 7545.00, 2265.00, 1257.50, 200.00, 0.00, 11267.50, 39032.50, 'finalized', NOW(), 1, NOW(), NOW()
    UNION ALL
    SELECT @mike_id, '2024-08-01', '2024-08-31', 55000.00, 0.00, 4000.00, 2000.00, 61000.00, 9150.00, 2750.00, 1525.00, 200.00, 0.00, 13625.00, 47375.00, 'finalized', NOW(), 1, NOW(), NOW()
    UNION ALL
    SELECT @sarah_id, '2024-08-01', '2024-08-31', 48000.00, 1200.00, 2800.00, 0.00, 52000.00, 7800.00, 2340.00, 1300.00, 200.00, 0.00, 11640.00, 40360.00, 'finalized', NOW(), 1, NOW(), NOW()
    UNION ALL
    SELECT @david_id, '2024-08-01', '2024-08-31', 42000.00, 2100.00, 2200.00, 500.00, 46800.00, 7020.00, 2106.00, 1170.00, 200.00, 0.00, 10496.00, 36304.00, 'finalized', NOW(), 1, NOW(), NOW()
) AS tmp WHERE @john_id IS NOT NULL;

-- Create sample time entries for current month
INSERT IGNORE INTO time_entries (employee_id, work_date, clock_in, clock_out, hours_worked, overtime_hours, description, status, created_at, updated_at) 
SELECT * FROM (
    SELECT @john_id, '2024-09-01', '08:00:00', '17:00:00', 8.00, 0.00, 'Regular work day', 'approved', NOW(), NOW()
    UNION ALL
    SELECT @john_id, '2024-09-02', '08:15:00', '18:30:00', 8.25, 2.00, 'Project deadline work', 'approved', NOW(), NOW()
    UNION ALL
    SELECT @jane_id, '2024-09-01', '09:00:00', '18:00:00', 8.00, 0.00, 'HR meetings and interviews', 'approved', NOW(), NOW()
    UNION ALL
    SELECT @jane_id, '2024-09-02', '08:30:00', '17:30:00', 8.00, 0.00, 'Regular work day', 'approved', NOW(), NOW()
    UNION ALL
    SELECT @mike_id, '2024-09-01', '08:00:00', '17:00:00', 8.00, 0.00, 'Marketing campaign planning', 'approved', NOW(), NOW()
    UNION ALL
    SELECT @sarah_id, '2024-09-01', '08:45:00', '17:45:00', 8.00, 0.00, 'Financial reports preparation', 'approved', NOW(), NOW()
    UNION ALL
    SELECT @david_id, '2024-09-01', '09:00:00', '18:00:00', 8.00, 0.00, 'Client consultations', 'approved', NOW(), NOW()
) AS tmp WHERE @john_id IS NOT NULL;

-- Update leave balances for employees
INSERT IGNORE INTO leave_balances (employee_id, leave_type_id, year, days_allocated, days_used, days_remaining, created_at, updated_at) 
SELECT * FROM (
    SELECT @john_id, 1, 2024, 15, 3, 12, NOW(), NOW()
    UNION ALL
    SELECT @john_id, 2, 2024, 10, 0, 10, NOW(), NOW()
    UNION ALL
    SELECT @jane_id, 1, 2024, 15, 0, 15, NOW(), NOW()
    UNION ALL
    SELECT @jane_id, 2, 2024, 10, 2, 8, NOW(), NOW()
    UNION ALL
    SELECT @mike_id, 1, 2024, 15, 0, 15, NOW(), NOW()
    UNION ALL
    SELECT @mike_id, 2, 2024, 10, 0, 10, NOW(), NOW()
    UNION ALL
    SELECT @sarah_id, 1, 2024, 15, 0, 15, NOW(), NOW()
    UNION ALL
    SELECT @sarah_id, 2, 2024, 10, 0, 10, NOW(), NOW()
    UNION ALL
    SELECT @sarah_id, 3, 2024, 5, 1, 4, NOW(), NOW()
    UNION ALL
    SELECT @david_id, 1, 2024, 15, 0, 15, NOW(), NOW()
    UNION ALL
    SELECT @david_id, 2, 2024, 10, 0, 10, NOW(), NOW()
) AS tmp WHERE @john_id IS NOT NULL;

-- Display created/updated test accounts
SELECT 'ESS Test Accounts Ready for hr3systemdb:' as message;
SELECT 
    CONCAT(first_name, ' ', last_name) as 'Full Name',
    email as 'Email',
    'password123' as 'Password',
    position as 'Position',
    department as 'Department',
    CASE WHEN password IS NOT NULL THEN 'Yes' ELSE 'No' END as 'Password Set'
FROM employees 
WHERE email LIKE '%@jetlouge.com'
ORDER BY id;
