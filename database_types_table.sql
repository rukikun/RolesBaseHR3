-- ========================================
-- HR3 System - Database Types Tables
-- SQL INSERT Queries for Reference Data
-- ========================================

-- --------------------------------------------------------
-- Table: claim_types
-- --------------------------------------------------------

INSERT INTO `claim_types` (`id`, `name`, `code`, `description`, `max_amount`, `requires_attachment`, `auto_approve`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Travel Expenses', 'TRAVEL', 'Business travel related expenses', 5000.00, 1, 0, 1, NOW(), NOW()),
(2, 'Office Supplies', 'OFFICE', 'Office supplies and equipment', 1000.00, 1, 0, 1, NOW(), NOW()),
(3, 'Meal Allowance', 'MEAL', 'Business meal expenses', 500.00, 1, 0, 1, NOW(), NOW()),
(4, 'Training Costs', 'TRAINING', 'Professional development and training', 2000.00, 1, 0, 1, NOW(), NOW()),
(5, 'Medical Expenses', 'MEDICAL', 'Medical and health related expenses', 3000.00, 1, 0, 1, NOW(), NOW());

-- --------------------------------------------------------
-- Table: leave_types
-- --------------------------------------------------------

INSERT INTO `leave_types` (`id`, `name`, `code`, `description`, `days_allowed`, `max_days_per_year`, `carry_forward`, `requires_approval`, `status`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Annual Leave', 'AL', 'Annual vacation leave', 0, 21, 1, 1, 'active', 1, NOW(), NOW()),
(2, 'Sick Leave', 'SL', 'Medical sick leave', 0, 10, 0, 0, 'active', 1, NOW(), NOW()),
(3, 'Emergency Leave', 'EL', 'Emergency family leave', 0, 5, 0, 1, 'active', 1, NOW(), NOW()),
(4, 'Maternity Leave', 'ML', 'Maternity leave', 0, 90, 0, 1, 'active', 1, NOW(), NOW()),
(5, 'Paternity Leave', 'PL', 'Paternity leave', 0, 7, 0, 1, 'active', 1, NOW(), NOW());

-- --------------------------------------------------------
-- Table: shift_types
-- --------------------------------------------------------

INSERT INTO `shift_types` (`id`, `name`, `code`, `description`, `default_start_time`, `default_end_time`, `break_duration`, `hourly_rate`, `color_code`, `type`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Morning Shift', 'MORNING', 'Standard morning shift for regular operations', '08:00:00', '16:00:00', 60, 25.00, '#28a745', 'day', 1, NOW(), NOW()),
(2, 'Afternoon Shift', 'AFTERNOON', 'Afternoon to evening coverage shift', '14:00:00', '22:00:00', 45, 27.50, '#ffc107', 'swing', 1, NOW(), NOW()),
(3, 'Night Shift', 'NIGHT', 'Overnight shift with premium pay', '22:00:00', '06:00:00', 60, 32.00, '#6f42c1', 'night', 1, NOW(), NOW()),
(4, 'Split Shift', 'SPLIT', 'Split shift with extended break period', '09:00:00', '17:00:00', 120, 24.00, '#17a2b8', 'split', 1, NOW(), NOW()),
(5, 'Weekend Shift', 'WEEKEND', 'Weekend coverage with rotating schedule', '10:00:00', '18:00:00', 45, 30.00, '#fd7e14', 'rotating', 1, NOW(), NOW());

-- ========================================
-- End of SQL INSERT Queries
-- ========================================
