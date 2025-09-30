-- Setup Employee Self-Service (ESS) Sample Data (Safe Version)
-- This script handles existing employees and only adds missing data

-- Create all required tables if they don't exist

-- Create employee_trainings table
CREATE TABLE IF NOT EXISTS employee_trainings (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    employee_id BIGINT UNSIGNED NOT NULL,
    training_id BIGINT UNSIGNED NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE,
    status ENUM('assigned', 'in_progress', 'completed', 'cancelled') NOT NULL DEFAULT 'assigned',
    progress_percentage DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    assigned_by BIGINT UNSIGNED,
    completed_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (id),
    INDEX idx_employee_id (employee_id),
    INDEX idx_training_id (training_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create competency_assessments table
CREATE TABLE IF NOT EXISTS competency_assessments (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    employee_id BIGINT UNSIGNED NOT NULL,
    competency_name VARCHAR(255) NOT NULL,
    description TEXT,
    target_score DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    current_score DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    score DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    assessment_date DATE NOT NULL,
    status ENUM('pending', 'in_progress', 'completed', 'cancelled') NOT NULL DEFAULT 'pending',
    assessed_by BIGINT UNSIGNED,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (id),
    INDEX idx_employee_id (employee_id),
    INDEX idx_status (status),
    INDEX idx_assessment_date (assessment_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create employee_requests table
CREATE TABLE IF NOT EXISTS employee_requests (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    employee_id BIGINT UNSIGNED NOT NULL,
    request_type VARCHAR(255) NOT NULL,
    reason TEXT NOT NULL,
    requested_date DATE NOT NULL,
    status ENUM('pending', 'approved', 'rejected', 'cancelled') NOT NULL DEFAULT 'pending',
    approved_by BIGINT UNSIGNED,
    approved_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (id),
    INDEX idx_employee_id (employee_id),
    INDEX idx_status (status),
    INDEX idx_requested_date (requested_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create payslips table
CREATE TABLE IF NOT EXISTS payslips (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    employee_id BIGINT UNSIGNED NOT NULL,
    pay_period_start DATE NOT NULL,
    pay_period_end DATE NOT NULL,
    basic_salary DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    overtime_pay DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    allowances DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    bonuses DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    gross_pay DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    tax_deduction DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    sss_deduction DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    philhealth_deduction DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    pagibig_deduction DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    other_deductions DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    total_deductions DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    net_pay DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    status ENUM('draft', 'finalized', 'sent') NOT NULL DEFAULT 'draft',
    generated_at TIMESTAMP NULL DEFAULT NULL,
    generated_by BIGINT UNSIGNED,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (id),
    INDEX idx_employee_id (employee_id),
    INDEX idx_pay_period (pay_period_start, pay_period_end),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create time_entries table if it doesn't exist
CREATE TABLE IF NOT EXISTS time_entries (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    employee_id BIGINT UNSIGNED NOT NULL,
    work_date DATE NOT NULL,
    clock_in TIME,
    clock_out TIME,
    hours_worked DECIMAL(4,2) NOT NULL DEFAULT 0.00,
    overtime_hours DECIMAL(4,2) NOT NULL DEFAULT 0.00,
    description TEXT,
    status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
    approved_by BIGINT UNSIGNED,
    approved_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (id),
    INDEX idx_employee_id (employee_id),
    INDEX idx_work_date (work_date),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create leave_balances table if it doesn't exist
CREATE TABLE IF NOT EXISTS leave_balances (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    employee_id BIGINT UNSIGNED NOT NULL,
    leave_type_id BIGINT UNSIGNED NOT NULL,
    year INT NOT NULL,
    days_allocated INT NOT NULL DEFAULT 0,
    days_used INT NOT NULL DEFAULT 0,
    days_remaining INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY unique_employee_leave_year (employee_id, leave_type_id, year),
    INDEX idx_employee_id (employee_id),
    INDEX idx_leave_type_id (leave_type_id),
    INDEX idx_year (year)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create employee_notifications table if it doesn't exist
CREATE TABLE IF NOT EXISTS employee_notifications (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    employee_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('info', 'success', 'warning', 'error') NOT NULL DEFAULT 'info',
    is_read BOOLEAN NOT NULL DEFAULT 0,
    sent_at TIMESTAMP NULL DEFAULT NULL,
    read_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (id),
    INDEX idx_employee_id (employee_id),
    INDEX idx_is_read (is_read),
    INDEX idx_sent_at (sent_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
INSERT IGNORE INTO leave_types (name, code, description, max_days_per_year, carry_forward, requires_approval, is_active, created_at, updated_at) VALUES
('Vacation Leave', 'VL', 'Annual vacation leave for rest and recreation', 15, true, true, true, NOW(), NOW()),
('Sick Leave', 'SL', 'Medical leave for illness or health issues', 10, true, false, true, NOW(), NOW()),
('Emergency Leave', 'EL', 'Emergency leave for urgent personal matters', 5, false, true, true, NOW(), NOW()),
('Maternity Leave', 'ML', 'Maternity leave for new mothers', 60, false, true, true, NOW(), NOW()),
('Paternity Leave', 'PL', 'Paternity leave for new fathers', 7, false, true, true, NOW(), NOW());

-- Create training_programs table if it doesn't exist
CREATE TABLE IF NOT EXISTS training_programs (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    type ENUM('mandatory', 'optional') NOT NULL DEFAULT 'optional',
    duration_hours INT NOT NULL DEFAULT 0,
    delivery_mode ENUM('online', 'classroom', 'hybrid') NOT NULL DEFAULT 'online',
    cost DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    provider VARCHAR(255),
    is_active BOOLEAN NOT NULL DEFAULT 1,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
    SELECT @john_id AS employee_id, 'Training Assignment' AS title, 'You have been assigned to complete "Data Privacy and Security" training by end of month.' AS message, 'info' AS type, NOW() AS sent_at, NOW() AS created_at, NOW() AS updated_at
    UNION ALL
    SELECT @john_id AS employee_id, 'Leave Request Update' AS title, 'Your vacation leave request for next week has been approved.' AS message, 'success' AS type, NOW() AS sent_at, NOW() AS created_at, NOW() AS updated_at
    UNION ALL
    SELECT @jane_id AS employee_id, 'System Maintenance' AS title, 'HR system will be under maintenance this weekend. Please complete pending tasks.' AS message, 'warning' AS type, NOW() AS sent_at, NOW() AS created_at, NOW() AS updated_at
    UNION ALL
    SELECT @mike_id AS employee_id, 'Performance Review' AS title, 'Your quarterly performance review is scheduled for next Friday.' AS message, 'info' AS type, NOW() AS sent_at, NOW() AS created_at, NOW() AS updated_at
    UNION ALL
    SELECT @sarah_id AS employee_id, 'Payroll Update' AS title, 'Your latest payslip is now available in the system.' AS message, 'success' AS type, NOW() AS sent_at, NOW() AS created_at, NOW() AS updated_at
) AS tmp WHERE @john_id IS NOT NULL;

-- Create sample employee trainings
INSERT IGNORE INTO employee_trainings (employee_id, training_id, start_date, end_date, status, progress_percentage, assigned_by, created_at, updated_at) 
SELECT * FROM (
    SELECT @john_id AS employee_id, 1 AS training_id, '2024-09-01' AS start_date, '2024-09-15' AS end_date, 'in_progress' AS status, 60.00 AS progress_percentage, 1 AS assigned_by, NOW() AS created_at, NOW() AS updated_at
    UNION ALL
    SELECT @john_id AS employee_id, 2 AS training_id, '2024-10-01' AS start_date, '2024-10-31' AS end_date, 'assigned' AS status, 0.00 AS progress_percentage, 1 AS assigned_by, NOW() AS created_at, NOW() AS updated_at
    UNION ALL
    SELECT @jane_id AS employee_id, 1 AS training_id, '2024-09-01' AS start_date, '2024-09-15' AS end_date, 'completed' AS status, 100.00 AS progress_percentage, 1 AS assigned_by, NOW() AS created_at, NOW() AS updated_at
    UNION ALL
    SELECT @jane_id AS employee_id, 3 AS training_id, '2024-11-01' AS start_date, '2024-11-30' AS end_date, 'assigned' AS status, 0.00 AS progress_percentage, 1 AS assigned_by, NOW() AS created_at, NOW() AS updated_at
    UNION ALL
    SELECT @mike_id AS employee_id, 4 AS training_id, '2024-09-15' AS start_date, '2024-09-30' AS end_date, 'in_progress' AS status, 75.00 AS progress_percentage, 1 AS assigned_by, NOW() AS created_at, NOW() AS updated_at
    UNION ALL
    SELECT @sarah_id AS employee_id, 1 AS training_id, '2024-09-01' AS start_date, '2024-09-15' AS end_date, 'assigned' AS status, 0.00 AS progress_percentage, 1 AS assigned_by, NOW() AS created_at, NOW() AS updated_at
    UNION ALL
    SELECT @david_id AS employee_id, 2 AS training_id, '2024-10-15' AS start_date, '2024-11-15' AS end_date, 'assigned' AS status, 0.00 AS progress_percentage, 1 AS assigned_by, NOW() AS created_at, NOW() AS updated_at
) AS tmp WHERE @john_id IS NOT NULL;

-- Create sample competency assessments
INSERT IGNORE INTO competency_assessments (employee_id, competency_name, description, target_score, current_score, score, assessment_date, status, created_at, updated_at) 
SELECT * FROM (
    SELECT @john_id AS employee_id, 'Technical Skills' AS competency_name, 'Programming and software development capabilities' AS description, 85.00 AS target_score, 78.00 AS current_score, 78.00 AS score, '2024-08-15' AS assessment_date, 'completed' AS status, NOW() AS created_at, NOW() AS updated_at
    UNION ALL
    SELECT @john_id AS employee_id, 'Communication' AS competency_name, 'Verbal and written communication effectiveness' AS description, 80.00 AS target_score, 85.00 AS current_score, 85.00 AS score, '2024-08-15' AS assessment_date, 'completed' AS status, NOW() AS created_at, NOW() AS updated_at
    UNION ALL
    SELECT @jane_id AS employee_id, 'HR Knowledge' AS competency_name, 'Human resources policies and procedures' AS description, 90.00 AS target_score, 88.00 AS current_score, 88.00 AS score, '2024-08-20' AS assessment_date, 'completed' AS status, NOW() AS created_at, NOW() AS updated_at
    UNION ALL
    SELECT @mike_id AS employee_id, 'Marketing Skills' AS competency_name, 'Digital marketing and campaign management' AS description, 75.00 AS target_score, 82.00 AS current_score, 82.00 AS score, '2024-08-25' AS assessment_date, 'completed' AS status, NOW() AS created_at, NOW() AS updated_at
    UNION ALL
    SELECT @sarah_id AS employee_id, 'Financial Analysis' AS competency_name, 'Accounting and financial reporting skills' AS description, 85.00 AS target_score, 90.00 AS current_score, 90.00 AS score, '2024-08-30' AS assessment_date, 'completed' AS status, NOW() AS created_at, NOW() AS updated_at
) AS tmp WHERE @john_id IS NOT NULL;

-- Create sample leave requests
INSERT IGNORE INTO leave_requests (employee_id, leave_type_id, start_date, end_date, reason, status, created_at, updated_at) 
SELECT * FROM (
    SELECT @john_id AS employee_id, 1 AS leave_type_id, '2024-09-20' AS start_date, '2024-09-22' AS end_date, 'Family vacation' AS reason, 'approved' AS status, NOW() AS created_at, NOW() AS updated_at
    UNION ALL
    SELECT @jane_id AS employee_id, 2 AS leave_type_id, '2024-09-10' AS start_date, '2024-09-12' AS end_date, 'Medical checkup' AS reason, 'approved' AS status, NOW() AS created_at, NOW() AS updated_at
    UNION ALL
    SELECT @mike_id AS employee_id, 1 AS leave_type_id, '2024-10-01' AS start_date, '2024-10-05' AS end_date, 'Annual leave' AS reason, 'pending' AS status, NOW() AS created_at, NOW() AS updated_at
    UNION ALL
    SELECT @sarah_id AS employee_id, 3 AS leave_type_id, '2024-09-15' AS start_date, '2024-09-15' AS end_date, 'Family emergency' AS reason, 'approved' AS status, NOW() AS created_at, NOW() AS updated_at
) AS tmp WHERE @john_id IS NOT NULL;

-- Create sample employee requests
INSERT IGNORE INTO employee_requests (employee_id, request_type, reason, requested_date, status, created_at, updated_at) 
SELECT * FROM (
    SELECT @john_id AS employee_id, 'Equipment Request' AS request_type, 'Need new laptop for development work' AS reason, '2024-09-15' AS requested_date, 'approved' AS status, NOW() AS created_at, NOW() AS updated_at
    UNION ALL
    SELECT @jane_id AS employee_id, 'Training Request' AS request_type, 'Request for advanced HR certification course' AS reason, '2024-09-10' AS requested_date, 'pending' AS status, NOW() AS created_at, NOW() AS updated_at
    UNION ALL
    SELECT @mike_id AS employee_id, 'Schedule Change' AS request_type, 'Request for flexible working hours' AS reason, '2024-09-12' AS requested_date, 'approved' AS status, NOW() AS created_at, NOW() AS updated_at
    UNION ALL
    SELECT @sarah_id AS employee_id, 'Office Supplies' AS request_type, 'Need additional office supplies for accounting' AS reason, '2024-09-08' AS requested_date, 'approved' AS status, NOW() AS created_at, NOW() AS updated_at
) AS tmp WHERE @john_id IS NOT NULL;

-- Create sample payslips
INSERT IGNORE INTO payslips (employee_id, pay_period_start, pay_period_end, basic_salary, overtime_pay, allowances, bonuses, gross_pay, tax_deduction, sss_deduction, philhealth_deduction, pagibig_deduction, other_deductions, total_deductions, net_pay, status, generated_at, generated_by, created_at, updated_at) 
SELECT * FROM (
    SELECT @john_id AS employee_id, '2024-08-01' AS pay_period_start, '2024-08-31' AS pay_period_end, 50000.00 AS basic_salary, 2500.00 AS overtime_pay, 3000.00 AS allowances, 0.00 AS bonuses, 55500.00 AS gross_pay, 8325.00 AS tax_deduction, 2250.00 AS sss_deduction, 1375.00 AS philhealth_deduction, 200.00 AS pagibig_deduction, 0.00 AS other_deductions, 12150.00 AS total_deductions, 43350.00 AS net_pay, 'finalized' AS status, NOW() AS generated_at, 1 AS generated_by, NOW() AS created_at, NOW() AS updated_at
    UNION ALL
    SELECT @jane_id AS employee_id, '2024-08-01' AS pay_period_start, '2024-08-31' AS pay_period_end, 45000.00 AS basic_salary, 1800.00 AS overtime_pay, 2500.00 AS allowances, 1000.00 AS bonuses, 50300.00 AS gross_pay, 7545.00 AS tax_deduction, 2265.00 AS sss_deduction, 1257.50 AS philhealth_deduction, 200.00 AS pagibig_deduction, 0.00 AS other_deductions, 11267.50 AS total_deductions, 39032.50 AS net_pay, 'finalized' AS status, NOW() AS generated_at, 1 AS generated_by, NOW() AS created_at, NOW() AS updated_at
    UNION ALL
    SELECT @mike_id AS employee_id, '2024-08-01' AS pay_period_start, '2024-08-31' AS pay_period_end, 55000.00 AS basic_salary, 0.00 AS overtime_pay, 4000.00 AS allowances, 2000.00 AS bonuses, 61000.00 AS gross_pay, 9150.00 AS tax_deduction, 2750.00 AS sss_deduction, 1525.00 AS philhealth_deduction, 200.00 AS pagibig_deduction, 0.00 AS other_deductions, 13625.00 AS total_deductions, 47375.00 AS net_pay, 'finalized' AS status, NOW() AS generated_at, 1 AS generated_by, NOW() AS created_at, NOW() AS updated_at
    UNION ALL
    SELECT @sarah_id AS employee_id, '2024-08-01' AS pay_period_start, '2024-08-31' AS pay_period_end, 48000.00 AS basic_salary, 1200.00 AS overtime_pay, 2800.00 AS allowances, 0.00 AS bonuses, 52000.00 AS gross_pay, 7800.00 AS tax_deduction, 2340.00 AS sss_deduction, 1300.00 AS philhealth_deduction, 200.00 AS pagibig_deduction, 0.00 AS other_deductions, 11640.00 AS total_deductions, 40360.00 AS net_pay, 'finalized' AS status, NOW() AS generated_at, 1 AS generated_by, NOW() AS created_at, NOW() AS updated_at
    UNION ALL
    SELECT @david_id AS employee_id, '2024-08-01' AS pay_period_start, '2024-08-31' AS pay_period_end, 42000.00 AS basic_salary, 2100.00 AS overtime_pay, 2200.00 AS allowances, 500.00 AS bonuses, 46800.00 AS gross_pay, 7020.00 AS tax_deduction, 2106.00 AS sss_deduction, 1170.00 AS philhealth_deduction, 200.00 AS pagibig_deduction, 0.00 AS other_deductions, 10496.00 AS total_deductions, 36304.00 AS net_pay, 'finalized' AS status, NOW() AS generated_at, 1 AS generated_by, NOW() AS created_at, NOW() AS updated_at
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
SELECT 'ESS Test Accounts Ready:' as message;
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
