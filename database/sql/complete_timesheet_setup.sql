-- Complete Timesheet Setup SQL
-- Run this entire script in phpMyAdmin to set up everything

-- Drop existing tables if they exist (optional - remove these lines if you want to keep existing data)
-- DROP TABLE IF EXISTS timesheets;
-- DROP TABLE IF EXISTS employees;

-- Create employees table
CREATE TABLE IF NOT EXISTS `employees` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `email` varchar(255) UNIQUE,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create timesheets table
CREATE TABLE IF NOT EXISTS `timesheets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) NOT NULL,
  `work_date` date NOT NULL,
  `hours_worked` decimal(4,2) NOT NULL DEFAULT 0.00,
  `overtime_hours` decimal(4,2) NOT NULL DEFAULT 0.00,
  `description` text,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `employee_id` (`employee_id`),
  KEY `work_date` (`work_date`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Clear existing data safely
SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE timesheets;
TRUNCATE TABLE employees;
SET FOREIGN_KEY_CHECKS = 1;

-- Insert sample employees using INSERT IGNORE to avoid duplicates
INSERT IGNORE INTO `employees` (`id`, `first_name`, `last_name`, `email`, `status`) VALUES
(1, 'John', 'Doe', 'john.doe@company.com', 'active'),
(2, 'Jane', 'Smith', 'jane.smith@company.com', 'active'),
(3, 'Mike', 'Johnson', 'mike.johnson@company.com', 'active'),
(4, 'Sarah', 'Wilson', 'sarah.wilson@company.com', 'active'),
(5, 'Tom', 'Brown', 'tom.brown@company.com', 'active');

-- Insert sample timesheet data with current dates
INSERT INTO `timesheets` (`employee_id`, `work_date`, `hours_worked`, `overtime_hours`, `description`, `status`) VALUES
(1, '2025-08-24', 8.00, 0.00, 'Regular work day - Monday', 'approved'),
(1, '2025-08-25', 8.50, 0.50, 'Stayed late for meeting', 'approved'),
(1, '2025-08-26', 9.00, 1.00, 'Extra hour for project completion', 'pending'),
(2, '2025-08-24', 8.00, 0.00, 'Customer support duties', 'approved'),
(2, '2025-08-25', 7.50, 0.00, 'Half day - medical appointment', 'approved'),
(2, '2025-08-26', 8.00, 0.00, 'Regular customer support', 'pending'),
(3, '2025-08-24', 8.00, 2.00, 'Overtime for urgent client request', 'approved'),
(3, '2025-08-25', 8.00, 0.00, 'Regular development work', 'approved'),
(3, '2025-08-26', 8.00, 0.00, 'Code review and testing', 'pending'),
(4, '2025-08-24', 8.00, 0.00, 'Marketing campaign work', 'approved'),
(4, '2025-08-25', 8.00, 0.00, 'Social media management', 'approved'),
(5, '2025-08-24', 8.00, 1.00, 'Sales calls and follow-ups', 'approved'),
(5, '2025-08-25', 8.00, 0.00, 'Client presentations', 'approved');

-- Verify data was inserted
SELECT 'Employees inserted:' as info, COUNT(*) as count FROM employees;
SELECT 'Timesheets inserted:' as info, COUNT(*) as count FROM timesheets;

-- Show sample data
SELECT 'Sample timesheet data:' as info;
SELECT t.id, CONCAT(e.first_name, ' ', e.last_name) as employee_name, 
       t.work_date, t.hours_worked, t.overtime_hours, t.status 
FROM timesheets t 
LEFT JOIN employees e ON t.employee_id = e.id 
ORDER BY t.work_date DESC, t.id DESC 
LIMIT 5;
