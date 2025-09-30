-- Attendance System Database Setup Script
-- This script creates the attendance table and inserts sample data

-- Create attendances table if it doesn't exist
CREATE TABLE IF NOT EXISTS `attendances` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `employee_id` bigint(20) unsigned NOT NULL,
    `date` date NOT NULL,
    `clock_in_time` datetime DEFAULT NULL,
    `clock_out_time` datetime DEFAULT NULL,
    `break_start_time` datetime DEFAULT NULL,
    `break_end_time` datetime DEFAULT NULL,
    `total_hours` decimal(5,2) DEFAULT 0.00,
    `overtime_hours` decimal(5,2) DEFAULT 0.00,
    `status` enum('present','absent','late','on_break','clocked_out') DEFAULT 'present',
    `location` varchar(255) DEFAULT NULL,
    `ip_address` varchar(45) DEFAULT NULL,
    `notes` text DEFAULT NULL,
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `attendances_employee_id_date_unique` (`employee_id`,`date`),
    KEY `attendances_employee_id_index` (`employee_id`),
    KEY `attendances_date_index` (`date`),
    KEY `attendances_status_index` (`status`),
    CONSTRAINT `attendances_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample attendance data
INSERT IGNORE INTO `attendances` (`employee_id`, `date`, `clock_in_time`, `clock_out_time`, `break_start_time`, `break_end_time`, `total_hours`, `overtime_hours`, `status`, `location`, `ip_address`, `notes`, `created_at`, `updated_at`) VALUES
-- Today's attendance (some employees clocked in, some haven't clocked out yet)
(1, CURDATE(), CONCAT(CURDATE(), ' 08:30:00'), CONCAT(CURDATE(), ' 17:30:00'), CONCAT(CURDATE(), ' 12:00:00'), CONCAT(CURDATE(), ' 13:00:00'), 8.00, 0.00, 'clocked_out', 'Main Office', '192.168.1.100', 'Regular workday', NOW(), NOW()),
(2, CURDATE(), CONCAT(CURDATE(), ' 09:15:00'), NULL, NULL, NULL, 0.00, 0.00, 'present', 'Main Office', '192.168.1.101', 'Late arrival', NOW(), NOW()),
(3, CURDATE(), CONCAT(CURDATE(), ' 08:45:00'), NULL, CONCAT(CURDATE(), ' 12:30:00'), NULL, 0.00, 0.00, 'on_break', 'Main Office', '192.168.1.102', 'Currently on lunch break', NOW(), NOW()),
(4, CURDATE(), CONCAT(CURDATE(), ' 08:00:00'), CONCAT(CURDATE(), ' 17:00:00'), CONCAT(CURDATE(), ' 12:00:00'), CONCAT(CURDATE(), ' 12:30:00'), 8.50, 0.50, 'clocked_out', 'Remote', '10.0.0.50', 'Working from home', NOW(), NOW()),
(5, CURDATE(), CONCAT(CURDATE(), ' 09:30:00'), NULL, NULL, NULL, 0.00, 0.00, 'late', 'Main Office', '192.168.1.103', 'Traffic delay', NOW(), NOW()),

-- Yesterday's attendance
(1, DATE_SUB(CURDATE(), INTERVAL 1 DAY), CONCAT(DATE_SUB(CURDATE(), INTERVAL 1 DAY), ' 08:30:00'), CONCAT(DATE_SUB(CURDATE(), INTERVAL 1 DAY), ' 17:30:00'), CONCAT(DATE_SUB(CURDATE(), INTERVAL 1 DAY), ' 12:00:00'), CONCAT(DATE_SUB(CURDATE(), INTERVAL 1 DAY), ' 13:00:00'), 8.00, 0.00, 'clocked_out', 'Main Office', '192.168.1.100', 'Regular workday', DATE_SUB(NOW(), INTERVAL 1 DAY), DATE_SUB(NOW(), INTERVAL 1 DAY)),
(2, DATE_SUB(CURDATE(), INTERVAL 1 DAY), CONCAT(DATE_SUB(CURDATE(), INTERVAL 1 DAY), ' 08:45:00'), CONCAT(DATE_SUB(CURDATE(), INTERVAL 1 DAY), ' 18:00:00'), CONCAT(DATE_SUB(CURDATE(), INTERVAL 1 DAY), ' 12:15:00'), CONCAT(DATE_SUB(CURDATE(), INTERVAL 1 DAY), ' 13:15:00'), 8.25, 0.25, 'clocked_out', 'Main Office', '192.168.1.101', 'Overtime work', DATE_SUB(NOW(), INTERVAL 1 DAY), DATE_SUB(NOW(), INTERVAL 1 DAY)),
(3, DATE_SUB(CURDATE(), INTERVAL 1 DAY), CONCAT(DATE_SUB(CURDATE(), INTERVAL 1 DAY), ' 09:00:00'), CONCAT(DATE_SUB(CURDATE(), INTERVAL 1 DAY), ' 17:00:00'), CONCAT(DATE_SUB(CURDATE(), INTERVAL 1 DAY), ' 12:00:00'), CONCAT(DATE_SUB(CURDATE(), INTERVAL 1 DAY), ' 12:45:00'), 7.25, 0.00, 'clocked_out', 'Main Office', '192.168.1.102', 'Regular workday', DATE_SUB(NOW(), INTERVAL 1 DAY), DATE_SUB(NOW(), INTERVAL 1 DAY)),
(4, DATE_SUB(CURDATE(), INTERVAL 1 DAY), NULL, NULL, NULL, NULL, 0.00, 0.00, 'absent', NULL, NULL, 'Sick leave', DATE_SUB(NOW(), INTERVAL 1 DAY), DATE_SUB(NOW(), INTERVAL 1 DAY)),
(5, DATE_SUB(CURDATE(), INTERVAL 1 DAY), CONCAT(DATE_SUB(CURDATE(), INTERVAL 1 DAY), ' 08:30:00'), CONCAT(DATE_SUB(CURDATE(), INTERVAL 1 DAY), ' 16:30:00'), CONCAT(DATE_SUB(CURDATE(), INTERVAL 1 DAY), ' 12:00:00'), CONCAT(DATE_SUB(CURDATE(), INTERVAL 1 DAY), ' 13:00:00'), 7.00, 0.00, 'clocked_out', 'Client Site', '203.0.113.10', 'Client meeting', DATE_SUB(NOW(), INTERVAL 1 DAY), DATE_SUB(NOW(), INTERVAL 1 DAY)),

-- Day before yesterday
(1, DATE_SUB(CURDATE(), INTERVAL 2 DAY), CONCAT(DATE_SUB(CURDATE(), INTERVAL 2 DAY), ' 08:15:00'), CONCAT(DATE_SUB(CURDATE(), INTERVAL 2 DAY), ' 17:45:00'), CONCAT(DATE_SUB(CURDATE(), INTERVAL 2 DAY), ' 12:00:00'), CONCAT(DATE_SUB(CURDATE(), INTERVAL 2 DAY), ' 13:00:00'), 8.50, 0.50, 'clocked_out', 'Main Office', '192.168.1.100', 'Project deadline work', DATE_SUB(NOW(), INTERVAL 2 DAY), DATE_SUB(NOW(), INTERVAL 2 DAY)),
(2, DATE_SUB(CURDATE(), INTERVAL 2 DAY), CONCAT(DATE_SUB(CURDATE(), INTERVAL 2 DAY), ' 09:30:00'), CONCAT(DATE_SUB(CURDATE(), INTERVAL 2 DAY), ' 17:30:00'), CONCAT(DATE_SUB(CURDATE(), INTERVAL 2 DAY), ' 12:30:00'), CONCAT(DATE_SUB(CURDATE(), INTERVAL 2 DAY), ' 13:30:00'), 7.00, 0.00, 'late', 'Main Office', '192.168.1.101', 'Late due to appointment', DATE_SUB(NOW(), INTERVAL 2 DAY), DATE_SUB(NOW(), INTERVAL 2 DAY)),
(3, DATE_SUB(CURDATE(), INTERVAL 2 DAY), CONCAT(DATE_SUB(CURDATE(), INTERVAL 2 DAY), ' 08:30:00'), CONCAT(DATE_SUB(CURDATE(), INTERVAL 2 DAY), ' 17:30:00'), CONCAT(DATE_SUB(CURDATE(), INTERVAL 2 DAY), ' 12:00:00'), CONCAT(DATE_SUB(CURDATE(), INTERVAL 2 DAY), ' 13:00:00'), 8.00, 0.00, 'clocked_out', 'Main Office', '192.168.1.102', 'Regular workday', DATE_SUB(NOW(), INTERVAL 2 DAY), DATE_SUB(NOW(), INTERVAL 2 DAY)),
(4, DATE_SUB(CURDATE(), INTERVAL 2 DAY), CONCAT(DATE_SUB(CURDATE(), INTERVAL 2 DAY), ' 08:00:00'), CONCAT(DATE_SUB(CURDATE(), INTERVAL 2 DAY), ' 19:00:00'), CONCAT(DATE_SUB(CURDATE(), INTERVAL 2 DAY), ' 12:00:00'), CONCAT(DATE_SUB(CURDATE(), INTERVAL 2 DAY), ' 13:00:00'), 10.00, 2.00, 'clocked_out', 'Remote', '10.0.0.50', 'Extended work session', DATE_SUB(NOW(), INTERVAL 2 DAY), DATE_SUB(NOW(), INTERVAL 2 DAY)),
(5, DATE_SUB(CURDATE(), INTERVAL 2 DAY), CONCAT(DATE_SUB(CURDATE(), INTERVAL 2 DAY), ' 08:45:00'), CONCAT(DATE_SUB(CURDATE(), INTERVAL 2 DAY), ' 17:15:00'), CONCAT(DATE_SUB(CURDATE(), INTERVAL 2 DAY), ' 12:15:00'), CONCAT(DATE_SUB(CURDATE(), INTERVAL 2 DAY), ' 13:15:00'), 7.50, 0.00, 'clocked_out', 'Main Office', '192.168.1.103', 'Regular workday', DATE_SUB(NOW(), INTERVAL 2 DAY), DATE_SUB(NOW(), INTERVAL 2 DAY)),

-- Last week's data (Monday)
(1, DATE_SUB(CURDATE(), INTERVAL 7 DAY), CONCAT(DATE_SUB(CURDATE(), INTERVAL 7 DAY), ' 08:30:00'), CONCAT(DATE_SUB(CURDATE(), INTERVAL 7 DAY), ' 17:30:00'), CONCAT(DATE_SUB(CURDATE(), INTERVAL 7 DAY), ' 12:00:00'), CONCAT(DATE_SUB(CURDATE(), INTERVAL 7 DAY), ' 13:00:00'), 8.00, 0.00, 'clocked_out', 'Main Office', '192.168.1.100', 'Monday start', DATE_SUB(NOW(), INTERVAL 7 DAY), DATE_SUB(NOW(), INTERVAL 7 DAY)),
(2, DATE_SUB(CURDATE(), INTERVAL 7 DAY), CONCAT(DATE_SUB(CURDATE(), INTERVAL 7 DAY), ' 08:45:00'), CONCAT(DATE_SUB(CURDATE(), INTERVAL 7 DAY), ' 17:45:00'), CONCAT(DATE_SUB(CURDATE(), INTERVAL 7 DAY), ' 12:00:00'), CONCAT(DATE_SUB(CURDATE(), INTERVAL 7 DAY), ' 13:00:00'), 8.00, 0.00, 'clocked_out', 'Main Office', '192.168.1.101', 'Regular Monday', DATE_SUB(NOW(), INTERVAL 7 DAY), DATE_SUB(NOW(), INTERVAL 7 DAY)),
(3, DATE_SUB(CURDATE(), INTERVAL 7 DAY), CONCAT(DATE_SUB(CURDATE(), INTERVAL 7 DAY), ' 09:00:00'), CONCAT(DATE_SUB(CURDATE(), INTERVAL 7 DAY), ' 18:00:00'), CONCAT(DATE_SUB(CURDATE(), INTERVAL 7 DAY), ' 12:30:00'), CONCAT(DATE_SUB(CURDATE(), INTERVAL 7 DAY), ' 13:30:00'), 8.00, 0.00, 'clocked_out', 'Main Office', '192.168.1.102', 'Monday work', DATE_SUB(NOW(), INTERVAL 7 DAY), DATE_SUB(NOW(), INTERVAL 7 DAY)),
(4, DATE_SUB(CURDATE(), INTERVAL 7 DAY), CONCAT(DATE_SUB(CURDATE(), INTERVAL 7 DAY), ' 08:00:00'), CONCAT(DATE_SUB(CURDATE(), INTERVAL 7 DAY), ' 17:00:00'), CONCAT(DATE_SUB(CURDATE(), INTERVAL 7 DAY), ' 12:00:00'), CONCAT(DATE_SUB(CURDATE(), INTERVAL 7 DAY), ' 12:30:00'), 8.50, 0.50, 'clocked_out', 'Remote', '10.0.0.50', 'Remote Monday', DATE_SUB(NOW(), INTERVAL 7 DAY), DATE_SUB(NOW(), INTERVAL 7 DAY)),
(5, DATE_SUB(CURDATE(), INTERVAL 7 DAY), CONCAT(DATE_SUB(CURDATE(), INTERVAL 7 DAY), ' 08:30:00'), CONCAT(DATE_SUB(CURDATE(), INTERVAL 7 DAY), ' 17:30:00'), CONCAT(DATE_SUB(CURDATE(), INTERVAL 7 DAY), ' 12:00:00'), CONCAT(DATE_SUB(CURDATE(), INTERVAL 7 DAY), ' 13:00:00'), 8.00, 0.00, 'clocked_out', 'Main Office', '192.168.1.103', 'Monday routine', DATE_SUB(NOW(), INTERVAL 7 DAY), DATE_SUB(NOW(), INTERVAL 7 DAY));

-- Update employees table to ensure we have the necessary employees
INSERT IGNORE INTO `employees` (`id`, `first_name`, `last_name`, `email`, `phone`, `position`, `department`, `hire_date`, `salary`, `status`, `created_at`, `updated_at`) VALUES
(1, 'John', 'Doe', 'john.doe@jetlouge.com', '+63-912-345-6789', 'Software Developer', 'IT', '2023-01-15', 75000.00, 'active', NOW(), NOW()),
(2, 'Jane', 'Smith', 'jane.smith@jetlouge.com', '+63-912-345-6790', 'Project Manager', 'IT', '2023-02-01', 85000.00, 'active', NOW(), NOW()),
(3, 'Mike', 'Johnson', 'mike.johnson@jetlouge.com', '+63-912-345-6791', 'Designer', 'Creative', '2023-03-10', 65000.00, 'active', NOW(), NOW()),
(4, 'Sarah', 'Wilson', 'sarah.wilson@jetlouge.com', '+63-912-345-6792', 'Business Analyst', 'Operations', '2023-04-05', 70000.00, 'active', NOW(), NOW()),
(5, 'David', 'Brown', 'david.brown@jetlouge.com', '+63-912-345-6793', 'Marketing Specialist', 'Marketing', '2023-05-20', 60000.00, 'active', NOW(), NOW());

-- Verify the data
SELECT 
    'Attendance Records Created' as Status,
    COUNT(*) as Total_Records,
    COUNT(DISTINCT employee_id) as Unique_Employees,
    COUNT(DISTINCT date) as Unique_Days
FROM attendances;

SELECT 
    e.first_name,
    e.last_name,
    COUNT(a.id) as attendance_records,
    SUM(a.total_hours) as total_hours,
    AVG(a.total_hours) as avg_hours_per_day
FROM employees e
LEFT JOIN attendances a ON e.id = a.employee_id
WHERE e.id <= 5
GROUP BY e.id, e.first_name, e.last_name
ORDER BY e.id;
