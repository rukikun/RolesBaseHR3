-- Fix for shift_types table structure issue
-- This script ensures the table exists with correct structure before inserting data

-- Drop and recreate shift_types table with correct structure
DROP TABLE IF EXISTS `shift_types`;

CREATE TABLE `shift_types` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `code` varchar(20) NOT NULL,
  `description` text DEFAULT NULL,
  `default_start_time` time NOT NULL,
  `default_end_time` time NOT NULL,
  `break_duration` int(11) DEFAULT 0,
  `hourly_rate` decimal(8,2) DEFAULT NULL,
  `color_code` varchar(7) DEFAULT '#007bff',
  `type` enum('day','night','swing','split','rotating') DEFAULT 'day',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `shift_types_code_unique` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert the data
INSERT INTO `shift_types` (`id`, `name`, `code`, `description`, `default_start_time`, `default_end_time`, `break_duration`, `hourly_rate`, `color_code`, `type`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Morning Shift', 'MORNING', 'Standard morning shift for regular operations', '08:00:00', '16:00:00', 60, 25.00, '#28a745', 'day', 1, '2025-09-10 16:39:11', '2025-09-10 16:39:11'),
(2, 'Afternoon Shift', 'AFTERNOON', 'Afternoon to evening coverage shift', '14:00:00', '22:00:00', 45, 27.50, '#ffc107', 'swing', 1, '2025-09-10 16:39:11', '2025-09-10 16:39:11'),
(3, 'Night Shift', 'NIGHT', 'Overnight shift with premium pay', '22:00:00', '06:00:00', 60, 32.00, '#6f42c1', 'night', 1, '2025-09-10 16:39:11', '2025-09-10 16:39:11'),
(4, 'Split Shift', 'SPLIT', 'Split shift with extended break period', '09:00:00', '17:00:00', 120, 24.00, '#17a2b8', 'split', 1, '2025-09-10 16:39:11', '2025-09-10 16:39:11'),
(5, 'Weekend Shift', 'WEEKEND', 'Weekend coverage with rotating schedule', '10:00:00', '18:00:00', 45, 30.00, '#fd7e14', 'rotating', 1, '2025-09-10 16:39:11', '2025-09-10 16:39:11');
