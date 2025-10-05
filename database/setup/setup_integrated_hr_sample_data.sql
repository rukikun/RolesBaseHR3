-- Setup sample data for integrated HR system
-- This script works with existing database schema

-- Clear existing data (if any)
SET FOREIGN_KEY_CHECKS = 0;
DELETE FROM claims WHERE id > 0;
DELETE FROM leave_requests WHERE id > 0;
DELETE FROM shifts WHERE id > 0;
DELETE FROM time_entries WHERE id > 0;
DELETE FROM employees WHERE id > 0;
SET FOREIGN_KEY_CHECKS = 1;

-- Insert sample employees
INSERT INTO employees (id, first_name, last_name, email, phone, position, department, hire_date, salary, status, created_at, updated_at) VALUES
(1, 'John', 'Doe', 'john.doe@jetlouge.com', '+1234567890', 'Software Developer', 'IT', '2023-01-15', 75000.00, 'active', NOW(), NOW()),
(2, 'Jane', 'Smith', 'jane.smith@jetlouge.com', '+1234567891', 'HR Manager', 'Human Resources', '2022-03-20', 65000.00, 'active', NOW(), NOW()),
(3, 'Mike', 'Johnson', 'mike.johnson@jetlouge.com', '+1234567892', 'Travel Consultant', 'Sales', '2023-06-10', 55000.00, 'active', NOW(), NOW()),
(4, 'Sarah', 'Wilson', 'sarah.wilson@jetlouge.com', '+1234567893', 'Marketing Specialist', 'Marketing', '2023-02-28', 60000.00, 'active', NOW(), NOW()),
(5, 'David', 'Brown', 'david.brown@jetlouge.com', '+1234567894', 'Finance Manager', 'Finance', '2022-11-15', 70000.00, 'active', NOW(), NOW());

-- Insert sample time entries
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

-- Insert sample shifts (if shifts table exists)
INSERT IGNORE INTO shifts (employee_id, shift_date, start_time, end_time, status, notes, created_at, updated_at) VALUES
(1, CURDATE(), '08:00:00', '16:00:00', 'scheduled', 'Regular morning shift', NOW(), NOW()),
(2, CURDATE(), '09:00:00', '17:00:00', 'scheduled', 'HR office hours', NOW(), NOW()),
(3, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '10:00:00', '18:00:00', 'scheduled', 'Client meeting day', NOW(), NOW()),
(4, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '08:30:00', '16:30:00', 'scheduled', 'Marketing campaign launch', NOW(), NOW()),
(5, CURDATE(), '08:00:00', '16:00:00', 'scheduled', 'Financial review day', NOW(), NOW());

-- Insert sample leave requests (if leave_requests table exists)
INSERT IGNORE INTO leave_requests (employee_id, start_date, end_date, reason, status, created_at, updated_at) VALUES
(1, DATE_ADD(CURDATE(), INTERVAL 7 DAY), DATE_ADD(CURDATE(), INTERVAL 9 DAY), 'Personal vacation', 'pending', NOW(), NOW()),
(2, DATE_ADD(CURDATE(), INTERVAL 14 DAY), DATE_ADD(CURDATE(), INTERVAL 16 DAY), 'Family time', 'approved', NOW(), NOW()),
(3, DATE_ADD(CURDATE(), INTERVAL 21 DAY), DATE_ADD(CURDATE(), INTERVAL 23 DAY), 'Medical appointment', 'pending', NOW(), NOW()),
(4, DATE_ADD(CURDATE(), INTERVAL 10 DAY), DATE_ADD(CURDATE(), INTERVAL 12 DAY), 'Conference attendance', 'approved', NOW(), NOW());

-- Insert sample claims (if claims table exists)
INSERT IGNORE INTO claims (employee_id, amount, description, status, created_at, updated_at) VALUES
(1, 150.50, 'Software development books', 'pending', NOW(), NOW()),
(2, 75.25, 'HR conference materials', 'approved', NOW(), NOW()),
(3, 200.00, 'Client meeting expenses', 'pending', NOW(), NOW()),
(4, 125.75, 'Marketing materials', 'approved', NOW(), NOW()),
(5, 89.99, 'Financial software subscription', 'pending', NOW(), NOW());

-- Update employee online status for demo
UPDATE employees SET online_status = 'online', last_activity = NOW() WHERE id IN (1, 3, 5);
UPDATE employees SET online_status = 'offline', last_activity = DATE_SUB(NOW(), INTERVAL 2 HOUR) WHERE id IN (2, 4);

SELECT 'Sample data inserted successfully!' as message;
