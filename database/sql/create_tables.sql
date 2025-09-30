-- Create leave_types table
CREATE TABLE IF NOT EXISTS `leave_types` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `code` varchar(10) NOT NULL,
  `description` text,
  `max_days_per_year` int(11) NOT NULL DEFAULT '0',
  `carry_forward` tinyint(1) NOT NULL DEFAULT '0',
  `requires_approval` tinyint(1) NOT NULL DEFAULT '1',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `leave_types_code_unique` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample leave types
INSERT IGNORE INTO `leave_types` (`name`, `code`, `description`, `max_days_per_year`, `carry_forward`, `requires_approval`, `is_active`, `created_at`, `updated_at`) VALUES
('Annual Leave', 'AL', 'Yearly vacation leave for rest and recreation', 21, 1, 1, 1, NOW(), NOW()),
('Sick Leave', 'SL', 'Medical leave for illness or medical appointments', 10, 0, 0, 1, NOW(), NOW()),
('Personal Leave', 'PL', 'Personal time off for family matters or personal business', 5, 0, 1, 1, NOW(), NOW()),
('Maternity Leave', 'ML', 'Leave for new mothers after childbirth', 90, 0, 1, 1, NOW(), NOW()),
('Paternity Leave', 'PTL', 'Leave for new fathers after childbirth', 14, 0, 1, 1, NOW(), NOW()),
('Compassionate Leave', 'CL', 'Leave for bereavement or family emergencies', 7, 0, 1, 1, NOW(), NOW());

-- Create claim_types table
CREATE TABLE IF NOT EXISTS `claim_types` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `code` varchar(10) NOT NULL,
  `description` text,
  `max_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `requires_attachment` tinyint(1) NOT NULL DEFAULT '1',
  `auto_approve` tinyint(1) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `claim_types_code_unique` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample claim types
INSERT IGNORE INTO `claim_types` (`name`, `code`, `description`, `max_amount`, `requires_attachment`, `auto_approve`, `is_active`, `created_at`, `updated_at`) VALUES
('Travel Expenses', 'TRAVEL', 'Business travel expenses including flights, hotels, and transportation', 2000.00, 1, 0, 1, NOW(), NOW()),
('Office Supplies', 'OFFICE', 'Office equipment and supplies for work purposes', 500.00, 1, 1, 1, NOW(), NOW()),
('Meal Allowance', 'MEAL', 'Business meal expenses and client entertainment', 200.00, 1, 1, 1, NOW(), NOW()),
('Transportation', 'TRANSPORT', 'Local transportation and parking expenses', 100.00, 1, 1, 1, NOW(), NOW()),
('Training & Development', 'TRAINING', 'Professional development courses and training materials', 1500.00, 1, 0, 1, NOW(), NOW()),
('Medical Expenses', 'MEDICAL', 'Work-related medical expenses and health checkups', 1000.00, 1, 0, 1, NOW(), NOW()),
('Telecommunications', 'TELECOM', 'Business phone and internet expenses', 150.00, 1, 1, 1, NOW(), NOW());

-- Create employees table
CREATE TABLE IF NOT EXISTS `employees` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `employee_number` varchar(50) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `position` varchar(100) DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `hire_date` date DEFAULT NULL,
  `salary` decimal(10,2) DEFAULT NULL,
  `status` enum('active','inactive','terminated') NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `employees_employee_number_unique` (`employee_number`),
  UNIQUE KEY `employees_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample employees
INSERT IGNORE INTO `employees` (`employee_number`, `first_name`, `last_name`, `email`, `phone`, `position`, `department`, `hire_date`, `salary`, `status`, `created_at`, `updated_at`) VALUES
('EMP001', 'John', 'Anderson', 'john.anderson@jetlouge.com', '+1-555-0101', 'Senior Developer', 'IT', '2022-01-15', 75000.00, 'active', NOW(), NOW()),
('EMP002', 'Jane', 'Smith', 'jane.smith@jetlouge.com', '+1-555-0102', 'HR Manager', 'Human Resources', '2021-03-20', 68000.00, 'active', NOW(), NOW()),
('EMP003', 'Mike', 'Johnson', 'mike.johnson@jetlouge.com', '+1-555-0103', 'Marketing Specialist', 'Marketing', '2022-06-10', 55000.00, 'active', NOW(), NOW()),
('EMP004', 'Sarah', 'Williams', 'sarah.williams@jetlouge.com', '+1-555-0104', 'Accountant', 'Finance', '2021-11-05', 62000.00, 'active', NOW(), NOW()),
('EMP005', 'David', 'Brown', 'david.brown@jetlouge.com', '+1-555-0105', 'Project Manager', 'IT', '2020-09-12', 72000.00, 'active', NOW(), NOW()),
('EMP006', 'Lisa', 'Davis', 'lisa.davis@jetlouge.com', '+1-555-0106', 'Sales Representative', 'Sales', '2023-02-28', 48000.00, 'active', NOW(), NOW()),
('EMP007', 'Robert', 'Miller', 'robert.miller@jetlouge.com', '+1-555-0107', 'Operations Manager', 'Operations', '2021-07-18', 70000.00, 'active', NOW(), NOW()),
('EMP008', 'Emily', 'Wilson', 'emily.wilson@jetlouge.com', '+1-555-0108', 'Designer', 'Creative', '2022-11-03', 58000.00, 'active', NOW(), NOW());

-- Create leave_requests table
CREATE TABLE IF NOT EXISTS `leave_requests` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint(20) unsigned NOT NULL,
  `leave_type_id` bigint(20) unsigned NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `days_requested` int(11) NOT NULL,
  `reason` text NOT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `approved_by` bigint(20) unsigned DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `rejection_reason` text,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `leave_requests_employee_id_index` (`employee_id`),
  KEY `leave_requests_leave_type_id_index` (`leave_type_id`),
  KEY `leave_requests_status_index` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample leave requests
INSERT IGNORE INTO `leave_requests` (`employee_id`, `leave_type_id`, `start_date`, `end_date`, `days_requested`, `reason`, `status`, `approved_by`, `approved_at`, `created_at`, `updated_at`) VALUES
(1, 1, '2025-09-15', '2025-09-19', 5, 'Family vacation to celebrate anniversary', 'pending', NULL, NULL, NOW(), NOW()),
(2, 2, '2025-09-05', '2025-09-05', 1, 'Doctor appointment for annual checkup', 'approved', 1, NOW(), NOW(), NOW()),
(3, 1, '2025-10-01', '2025-10-10', 8, 'Extended vacation to visit family overseas', 'pending', NULL, NULL, NOW(), NOW()),
(4, 3, '2025-09-12', '2025-09-12', 1, 'Personal matter - bank appointment', 'approved', 2, NOW(), NOW(), NOW()),
(5, 1, '2025-11-20', '2025-11-24', 5, 'Thanksgiving week vacation', 'pending', NULL, NULL, NOW(), NOW());

-- Insert rejected leave request with rejection_reason
INSERT IGNORE INTO `leave_requests` (`employee_id`, `leave_type_id`, `start_date`, `end_date`, `days_requested`, `reason`, `status`, `approved_by`, `approved_at`, `rejection_reason`, `created_at`, `updated_at`) VALUES
(6, 2, '2025-09-08', '2025-09-09', 2, 'Flu symptoms and recovery', 'rejected', 2, NOW(), 'Insufficient medical documentation provided', NOW(), NOW());

-- Create claims table
CREATE TABLE IF NOT EXISTS `claims` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint(20) unsigned NOT NULL,
  `claim_type_id` bigint(20) unsigned NOT NULL,
  `claim_date` date NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `description` text NOT NULL,
  `receipt_path` varchar(255) DEFAULT NULL,
  `status` enum('pending','approved','rejected','paid') NOT NULL DEFAULT 'pending',
  `approved_by` bigint(20) unsigned DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `rejection_reason` text,
  `paid_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `claims_employee_id_index` (`employee_id`),
  KEY `claims_claim_type_id_index` (`claim_type_id`),
  KEY `claims_status_index` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
