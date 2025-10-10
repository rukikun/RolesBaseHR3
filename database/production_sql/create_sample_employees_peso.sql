-- Create sample employees with Philippine Peso salaries for testing data consistency
INSERT INTO employees (first_name, last_name, email, phone, position, department, hire_date, salary, status, created_at, updated_at) VALUES
('John', 'Doe', 'john.doe@jetlouge.com', '+63 912 345 6789', 'Software Developer', 'IT', '2024-01-15', 95000.00, 'active', NOW(), NOW()),
('Jane', 'Smith', 'jane.smith@jetlouge.com', '+63 917 234 5678', 'HR Manager', 'Human Resources', '2023-06-10', 85000.00, 'active', NOW(), NOW()),
('Mike', 'Johnson', 'mike.johnson@jetlouge.com', '+63 918 345 6789', 'Marketing Specialist', 'Marketing', '2024-03-20', 75000.00, 'active', NOW(), NOW()),
('Sarah', 'Wilson', 'sarah.wilson@jetlouge.com', '+63 919 456 7890', 'Accountant', 'Finance', '2023-11-05', 80000.00, 'active', NOW(), NOW()),
('David', 'Brown', 'david.brown@jetlouge.com', '+63 920 567 8901', 'Operations Manager', 'Operations', '2024-02-01', 90000.00, 'active', NOW(), NOW()),
('Super', 'Admin', 'admin@jetlouge.com', '+63 912 345 6789', 'System Administrator', 'IT', '2023-01-15', 120000.00, 'active', NOW(), NOW()),
('Maria', 'Garcia', 'maria.garcia@jetlouge.com', '+63 921 678 9012', 'Travel Consultant', 'Sales', '2024-04-10', 65000.00, 'active', NOW(), NOW()),
('Carlos', 'Rodriguez', 'carlos.rodriguez@jetlouge.com', '+63 922 789 0123', 'Finance Manager', 'Finance', '2023-08-15', 95000.00, 'active', NOW(), NOW()),
('Ana', 'Santos', 'ana.santos@jetlouge.com', '+63 923 890 1234', 'HR Scheduler', 'Human Resources', '2024-05-20', 65000.00, 'active', NOW(), NOW()),
('Roberto', 'Cruz', 'roberto.cruz@jetlouge.com', '+63 924 901 2345', 'Customer Service Representative', 'Operations', '2024-06-01', 45000.00, 'active', NOW(), NOW());

-- Create some sample timesheet entries to test "With Timesheets" count
INSERT INTO time_entries (employee_id, work_date, hours_worked, overtime_hours, status, description, created_at, updated_at) VALUES
(1, CURDATE(), 8.0, 0.0, 'approved', 'Regular work day', NOW(), NOW()),
(2, CURDATE(), 8.0, 1.5, 'pending', 'Overtime for project deadline', NOW(), NOW()),
(3, CURDATE(), 7.5, 0.0, 'approved', 'Marketing campaign work', NOW(), NOW()),
(4, CURDATE(), 8.0, 0.5, 'approved', 'Finance reporting tasks', NOW(), NOW()),
(5, CURDATE(), 8.0, 2.0, 'pending', 'Operations management overtime', NOW(), NOW());

-- Create some sample claims with Philippine Peso amounts
INSERT INTO claims (employee_id, claim_type_id, amount, claim_date, description, status, created_at, updated_at) VALUES
(1, 1, 12500.00, CURDATE() - INTERVAL 5 DAY, 'Business trip to client meeting in Cebu - flight and hotel expenses', 'pending', NOW(), NOW()),
(2, 2, 3200.00, CURDATE() - INTERVAL 3 DAY, 'Office supplies: printer paper, pens, and folders', 'approved', NOW(), NOW()),
(3, 3, 1800.00, CURDATE() - INTERVAL 2 DAY, 'Lunch meeting with potential client', 'approved', NOW(), NOW()),
(4, 4, 18000.00, CURDATE() - INTERVAL 7 DAY, 'AWS Cloud Certification training course', 'pending', NOW(), NOW()),
(5, 5, 4500.00, CURDATE() - INTERVAL 4 DAY, 'Annual health checkup as required by company policy', 'approved', NOW(), NOW());
