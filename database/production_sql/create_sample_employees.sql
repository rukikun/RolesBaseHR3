-- Create sample employees for testing data consistency
INSERT INTO employees (first_name, last_name, email, phone, position, department, hire_date, salary, status, created_at, updated_at) VALUES
('John', 'Doe', 'john.doe@company.com', '555-0123', 'Software Developer', 'IT', '2024-01-15', 75000.00, 'active', NOW(), NOW()),
('Jane', 'Smith', 'jane.smith@company.com', '555-0124', 'HR Manager', 'Human Resources', '2023-06-10', 68000.00, 'active', NOW(), NOW()),
('Mike', 'Johnson', 'mike.johnson@company.com', '555-0125', 'Marketing Specialist', 'Marketing', '2024-03-20', 55000.00, 'active', NOW(), NOW()),
('Sarah', 'Wilson', 'sarah.wilson@company.com', '555-0126', 'Accountant', 'Finance', '2023-11-05', 62000.00, 'active', NOW(), NOW()),
('David', 'Brown', 'david.brown@company.com', '555-0127', 'Operations Manager', 'Operations', '2024-02-01', 72000.00, 'inactive', NOW(), NOW());

-- Create some sample timesheet entries to test "With Timesheets" count
INSERT INTO time_entries (employee_id, work_date, hours_worked, overtime_hours, status, description, created_at, updated_at) VALUES
(1, CURDATE(), 8.0, 0.0, 'approved', 'Regular work day', NOW(), NOW()),
(2, CURDATE(), 8.0, 1.5, 'pending', 'Overtime for project deadline', NOW(), NOW()),
(3, CURDATE(), 7.5, 0.0, 'approved', 'Marketing campaign work', NOW(), NOW());
