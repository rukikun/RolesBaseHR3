-- Simple Timesheet Setup - Run this step by step in phpMyAdmin

-- Step 1: Create employees table
CREATE TABLE IF NOT EXISTS `employees` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Step 2: Create timesheets table
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

-- Step 3: Clear any existing data (run only if needed)
-- DELETE FROM timesheets;
-- DELETE FROM employees;

-- Step 4: Insert employees one by one
INSERT INTO `employees` (`first_name`, `last_name`, `email`, `status`) VALUES ('John', 'Doe', 'john.doe@company.com', 'active');
INSERT INTO `employees` (`first_name`, `last_name`, `email`, `status`) VALUES ('Jane', 'Smith', 'jane.smith@company.com', 'active');
INSERT INTO `employees` (`first_name`, `last_name`, `email`, `status`) VALUES ('Mike', 'Johnson', 'mike.johnson@company.com', 'active');

-- Step 5: Insert timesheet data one by one
INSERT INTO `timesheets` (`employee_id`, `work_date`, `hours_worked`, `overtime_hours`, `description`, `status`) VALUES (1, '2025-08-26', 8.00, 0.00, 'Regular work day', 'approved');
INSERT INTO `timesheets` (`employee_id`, `work_date`, `hours_worked`, `overtime_hours`, `description`, `status`) VALUES (2, '2025-08-26', 8.50, 0.50, 'Extra work for project', 'pending');
INSERT INTO `timesheets` (`employee_id`, `work_date`, `hours_worked`, `overtime_hours`, `description`, `status`) VALUES (3, '2025-08-26', 9.00, 1.00, 'Overtime for client request', 'approved');

-- Step 6: Verify data
SELECT 'Employees:' as table_name, COUNT(*) as count FROM employees
UNION ALL
SELECT 'Timesheets:', COUNT(*) FROM timesheets;
