-- Setup employees table for HR3 system
-- Run this in phpMyAdmin to ensure the table exists with proper structure

USE hr3systemdb;

-- Create employees table if it doesn't exist
CREATE TABLE IF NOT EXISTS `employees` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `first_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `position` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `department` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `hire_date` date NOT NULL,
  `salary` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` enum('active','inactive','terminated') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `online_status` enum('online','offline') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'offline',
  `last_activity` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `profile_picture` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `employees_email_unique` (`email`),
  KEY `employees_status_index` (`status`),
  KEY `employees_department_index` (`department`),
  KEY `employees_online_status_index` (`online_status`),
  KEY `employees_hire_date_index` (`hire_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample employees if table is empty
INSERT IGNORE INTO `employees` (`id`, `first_name`, `last_name`, `email`, `phone`, `position`, `department`, `hire_date`, `salary`, `status`, `online_status`, `password`, `created_at`, `updated_at`) VALUES
(1, 'John', 'Doe', 'john.doe@jetlouge.com', '+63 912 345 6789', 'Software Developer', 'IT', '2023-01-15', 50000.00, 'active', 'offline', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NOW(), NOW()),
(2, 'Jane', 'Smith', 'jane.smith@jetlouge.com', '+63 917 234 5678', 'HR Manager', 'Human Resources', '2022-06-10', 60000.00, 'active', 'online', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NOW(), NOW()),
(3, 'Mike', 'Johnson', 'mike.johnson@jetlouge.com', '+63 918 345 6789', 'Accountant', 'Finance', '2023-03-20', 45000.00, 'active', 'offline', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NOW(), NOW()),
(4, 'Sarah', 'Wilson', 'sarah.wilson@jetlouge.com', '+63 919 456 7890', 'Marketing Specialist', 'Marketing', '2023-08-05', 42000.00, 'active', 'online', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NOW(), NOW()),
(5, 'David', 'Brown', 'david.brown@jetlouge.com', '+63 920 567 8901', 'Sales Representative', 'Sales', '2022-11-12', 40000.00, 'inactive', 'offline', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NOW(), NOW());

-- Reset AUTO_INCREMENT to ensure proper ID assignment for new records
ALTER TABLE `employees` AUTO_INCREMENT = 6;

SELECT 'Employees table setup completed successfully!' as message;
