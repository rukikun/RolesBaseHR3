    -- Create shift_types table if it doesn't exist
CREATE TABLE IF NOT EXISTS `shift_types` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `code` varchar(20) NOT NULL,
  `description` text,
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
  UNIQUE KEY `unique_name` (`name`),
  UNIQUE KEY `unique_code` (`code`),
  KEY `idx_status` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample shift types
INSERT INTO `shift_types` (`name`, `code`, `type`, `default_start_time`, `default_end_time`, `break_duration`, `hourly_rate`, `description`, `is_active`, `color_code`, `created_at`, `updated_at`) VALUES
('Morning Shift', 'MORNING', 'day', '08:00:00', '16:00:00', 60, 25.00, 'Standard morning shift for regular operations', 1, '#28a745', NOW(), NOW()),
('Afternoon Shift', 'AFTERNOON', 'swing', '14:00:00', '22:00:00', 45, 27.50, 'Afternoon to evening coverage shift', 1, '#ffc107', NOW(), NOW()),
('Night Shift', 'NIGHT', 'night', '22:00:00', '06:00:00', 60, 32.00, 'Overnight shift with premium pay', 1, '#6f42c1', NOW(), NOW()),
('Split Shift', 'SPLIT', 'split', '09:00:00', '17:00:00', 120, 24.00, 'Split shift with extended break period', 1, '#17a2b8', NOW(), NOW()),
('Weekend Shift', 'WEEKEND', 'rotating', '10:00:00', '18:00:00', 45, 30.00, 'Weekend coverage with rotating schedule', 1, '#fd7e14', NOW(), NOW())
ON DUPLICATE KEY UPDATE
  `description` = VALUES(`description`),
  `default_start_time` = VALUES(`default_start_time`),
  `default_end_time` = VALUES(`default_end_time`),
  `break_duration` = VALUES(`break_duration`),
  `hourly_rate` = VALUES(`hourly_rate`),
  `color_code` = VALUES(`color_code`),
  `updated_at` = NOW();
