-- Timesheets Database Schema
-- Run this SQL in phpMyAdmin to create the timesheets table

-- First, let's create the table without foreign key constraint
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

-- Create employees table if it doesn't exist (for testing purposes)
CREATE TABLE IF NOT EXISTS `employees` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `email` varchar(255) UNIQUE,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample employees for testing
INSERT IGNORE INTO `employees` (`id`, `first_name`, `last_name`, `email`) VALUES
(1, 'John', 'Doe', 'john.doe@company.com'),
(2, 'Jane', 'Smith', 'jane.smith@company.com'),
(3, 'Mike', 'Johnson', 'mike.johnson@company.com');

-- Optional: Add foreign key constraint
-- ALTER TABLE `timesheets` ADD CONSTRAINT `fk_timesheets_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE;

-- Insert sample timesheet data for testing (using current dates)
INSERT INTO `timesheets` (`employee_id`, `work_date`, `hours_worked`, `overtime_hours`, `description`, `status`) VALUES
(1, '2025-08-25', 8.00, 0.00, 'Regular work day', 'approved'),
(1, '2025-08-26', 9.00, 1.00, 'Extra hour for project completion', 'pending'),
(2, '2025-08-25', 8.00, 0.00, 'Customer support duties', 'approved'),
(2, '2025-08-26', 7.50, 0.00, 'Half day due to appointment', 'pending'),
(3, '2025-08-25', 8.00, 2.00, 'Overtime for urgent client request', 'approved');
