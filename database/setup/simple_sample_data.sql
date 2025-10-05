-- Simple sample data for existing HR system tables
-- Works with current database schema

-- Clear existing data safely
SET FOREIGN_KEY_CHECKS = 0;
DELETE FROM time_entries WHERE id > 0;
DELETE FROM employees WHERE id > 0;
SET FOREIGN_KEY_CHECKS = 1;

-- Insert sample employees (matching existing schema)
INSERT INTO employees (id, first_name, last_name, email, phone, position, department, hire_date, salary, status, created_at, updated_at) VALUES
(1, 'John', 'Doe', 'john.doe@jetlouge.com', '+1234567890', 'Software Developer', 'IT', '2023-01-15', 75000.00, 'active', NOW(), NOW()),
(2, 'Jane', 'Smith', 'jane.smith@jetlouge.com', '+1234567891', 'HR Manager', 'Human Resources', '2022-03-20', 65000.00, 'active', NOW(), NOW()),
(3, 'Mike', 'Johnson', 'mike.johnson@jetlouge.com', '+1234567892', 'Travel Consultant', 'Sales', '2023-06-10', 55000.00, 'active', NOW(), NOW()),
(4, 'Sarah', 'Wilson', 'sarah.wilson@jetlouge.com', '+1234567893', 'Marketing Specialist', 'Marketing', '2023-02-28', 60000.00, 'active', NOW(), NOW()),
(5, 'David', 'Brown', 'david.brown@jetlouge.com', '+1234567894', 'Finance Manager', 'Finance', '2022-11-15', 70000.00, 'active', NOW(), NOW());

-- Insert sample time entries (matching existing schema)
INSERT INTO time_entries (employee_id, work_date, hours_worked, overtime_hours, description, status, created_at, updated_at) VALUES
(1, CURDATE(), 8.0, 0.0, 'Daily development work', 'approved', NOW(), NOW()),
(1, DATE_SUB(CURDATE(), INTERVAL 1 DAY), 8.5, 0.5, 'Bug fixes and testing', 'approved', NOW(), NOW()),
(2, CURDATE(), 7.5, 0.0, 'HR meetings and interviews', 'pending', NOW(), NOW()),
(2, DATE_SUB(CURDATE(), INTERVAL 1 DAY), 8.0, 0.0, 'Employee onboarding', 'approved', NOW(), NOW()),
(3, CURDATE(), 8.0, 1.0, 'Client consultations', 'pending', NOW(), NOW()),
(3, DATE_SUB(CURDATE(), INTERVAL 2 DAY), 7.0, 0.0, 'Travel planning', 'approved', NOW(), NOW()),
(4, CURDATE(), 8.0, 0.0, 'Marketing campaign work', 'approved', NOW(), NOW()),
(4, DATE_SUB(CURDATE(), INTERVAL 1 DAY), 6.5, 0.0, 'Social media management', 'pending', NOW(), NOW()),
(5, CURDATE(), 8.0, 0.0, 'Financial reporting', 'approved', NOW(), NOW()),
(5, DATE_SUB(CURDATE(), INTERVAL 1 DAY), 8.5, 0.5, 'Budget analysis', 'pending', NOW(), NOW());

-- Update employee online status if column exists
UPDATE employees SET online_status = 'online', last_activity = NOW() WHERE id IN (1, 3, 5) AND EXISTS (SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'employees' AND COLUMN_NAME = 'online_status');
UPDATE employees SET online_status = 'offline', last_activity = DATE_SUB(NOW(), INTERVAL 2 HOUR) WHERE id IN (2, 4) AND EXISTS (SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'employees' AND COLUMN_NAME = 'online_status');

SELECT 'Sample data inserted successfully!' as message;
