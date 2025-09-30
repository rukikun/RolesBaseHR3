-- Safe Timesheet Setup - Handles existing foreign key constraints
-- Run this in phpMyAdmin

-- Step 1: Disable foreign key checks temporarily
SET FOREIGN_KEY_CHECKS = 0;

-- Step 2: Create employees table if it doesn't exist
CREATE TABLE IF NOT EXISTS `employees` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Step 3: Create timesheets table if it doesn't exist
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
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Step 4: Clear only timesheet data (safer approach)
DELETE FROM timesheets WHERE id > 0;

-- Step 5: Insert employees only if they don't exist
INSERT IGNORE INTO `employees` (`id`, `first_name`, `last_name`, `email`, `status`) VALUES
(1, 'John', 'Doe', 'john.doe@company.com', 'active'),
(2, 'Jane', 'Smith', 'jane.smith@company.com', 'active'),
(3, 'Mike', 'Johnson', 'mike.johnson@company.com', 'active'),
(4, 'Sarah', 'Wilson', 'sarah.wilson@company.com', 'active'),
(5, 'Tom', 'Brown', 'tom.brown@company.com', 'active');

-- Step 6: Insert fresh timesheet data
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

-- Step 7: Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

-- Step 8: Verify the data
SELECT 'Setup completed successfully!' as status;
SELECT COUNT(*) as employee_count FROM employees WHERE id IN (1,2,3,4,5);
SELECT COUNT(*) as timesheet_count FROM timesheets;

-- Step 9: Show sample data to verify
SELECT t.id, CONCAT(e.first_name, ' ', e.last_name) as employee_name, 
       t.work_date, t.hours_worked, t.overtime_hours, t.status 
FROM timesheets t 
LEFT JOIN employees e ON t.employee_id = e.id 
ORDER BY t.work_date DESC, t.id DESC 
LIMIT 5;
