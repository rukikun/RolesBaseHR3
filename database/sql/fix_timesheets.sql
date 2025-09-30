-- Clear existing data and recreate with current dates
DELETE FROM timesheets;
DELETE FROM employees;

-- Reset auto increment
ALTER TABLE timesheets AUTO_INCREMENT = 1;
ALTER TABLE employees AUTO_INCREMENT = 1;

-- Insert fresh employee data
INSERT INTO `employees` (`id`, `first_name`, `last_name`, `email`, `status`) VALUES
(1, 'John', 'Doe', 'john.doe@company.com', 'active'),
(2, 'Jane', 'Smith', 'jane.smith@company.com', 'active'),
(3, 'Mike', 'Johnson', 'mike.johnson@company.com', 'active');

-- Insert current timesheet data
INSERT INTO `timesheets` (`employee_id`, `work_date`, `hours_worked`, `overtime_hours`, `description`, `status`) VALUES
(1, '2025-08-25', 8.00, 0.00, 'Regular work day', 'approved'),
(1, '2025-08-26', 9.00, 1.00, 'Extra hour for project completion', 'pending'),
(2, '2025-08-25', 8.00, 0.00, 'Customer support duties', 'approved'),
(2, '2025-08-26', 7.50, 0.00, 'Half day due to appointment', 'pending'),
(3, '2025-08-25', 8.00, 2.00, 'Overtime for urgent client request', 'approved');
