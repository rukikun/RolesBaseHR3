-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 19, 2025 at 07:21 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `hr3systemdb`
--

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `claims`
--

CREATE TABLE `claims` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `employee_id` bigint(20) UNSIGNED NOT NULL,
  `claim_type_id` bigint(20) UNSIGNED NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `claim_date` date NOT NULL,
  `description` text DEFAULT NULL,
  `receipt_path` varchar(255) DEFAULT NULL,
  `attachment_path` varchar(255) DEFAULT NULL,
  `status` enum('pending','approved','rejected','paid') DEFAULT 'pending',
  `approved_by` bigint(20) UNSIGNED DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `paid_at` timestamp NULL DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `claims`
--

INSERT INTO `claims` (`id`, `employee_id`, `claim_type_id`, `amount`, `claim_date`, `description`, `receipt_path`, `attachment_path`, `status`, `approved_by`, `approved_at`, `paid_at`, `rejection_reason`, `created_at`, `updated_at`) VALUES
(5, 42, 5, 12.00, '2025-09-10', 'Meal', NULL, NULL, 'pending', NULL, NULL, NULL, NULL, '2025-09-09 09:49:25', '2025-09-09 09:49:25'),
(6, 42, 5, 12.00, '2025-09-10', 'Break', NULL, NULL, 'paid', 42, '2025-09-19 08:33:20', '2025-09-19 08:33:27', NULL, '2025-09-09 10:21:51', '2025-09-19 08:33:27');

-- --------------------------------------------------------

--
-- Table structure for table `claim_types`
--

CREATE TABLE `claim_types` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `max_amount` decimal(10,2) DEFAULT NULL,
  `requires_receipt` tinyint(1) DEFAULT 1,
  `approval_required` tinyint(1) DEFAULT 1,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `claim_types`
--

INSERT INTO `claim_types` (`id`, `name`, `description`, `max_amount`, `requires_receipt`, `approval_required`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Travel Expenses', 'Business travel related expenses', 5000.00, 1, 1, 'active', '2025-09-09 13:39:47', '2025-09-09 13:39:47'),
(2, 'Meal Allowance', 'Meal expenses during business hours', 500.00, 1, 0, 'active', '2025-09-09 13:39:47', '2025-09-09 13:39:47'),
(3, 'Office Supplies', 'Office equipment and supplies', 1000.00, 1, 1, 'active', '2025-09-09 13:39:47', '2025-09-09 13:39:47'),
(4, 'Training Fees', 'Professional development and training', 10000.00, 1, 1, 'active', '2025-09-09 13:39:47', '2025-09-09 13:39:47'),
(5, 'Communication', 'Phone and internet expenses', 2000.00, 1, 0, 'active', '2025-09-09 13:39:47', '2025-09-09 13:39:47');

-- --------------------------------------------------------

--
-- Table structure for table `competency_assessments`
--

CREATE TABLE `competency_assessments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `employee_id` bigint(20) UNSIGNED NOT NULL,
  `competency_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `target_score` decimal(5,2) NOT NULL DEFAULT 0.00,
  `current_score` decimal(5,2) NOT NULL DEFAULT 0.00,
  `score` decimal(5,2) NOT NULL DEFAULT 0.00,
  `assessment_date` date NOT NULL,
  `status` enum('pending','in_progress','completed','cancelled') NOT NULL DEFAULT 'pending',
  `assessed_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `position` varchar(100) NOT NULL,
  `department` varchar(100) NOT NULL,
  `hire_date` date NOT NULL,
  `salary` decimal(10,2) DEFAULT 0.00,
  `status` enum('active','inactive','terminated') DEFAULT 'active',
  `online_status` enum('online','offline') DEFAULT 'offline',
  `last_activity` timestamp NULL DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`id`, `first_name`, `last_name`, `email`, `phone`, `position`, `department`, `hire_date`, `salary`, `status`, `online_status`, `last_activity`, `password`, `remember_token`, `profile_picture`, `created_at`, `updated_at`) VALUES
(42, 'Mike', 'Johnson', 'mike.johnson@jetlouge.com', NULL, 'Sales Manager', 'Sales', '2024-01-20', 58000.00, 'active', 'offline', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, '2025-09-09 15:08:50', '2025-09-09 15:08:50'),
(45, 'Jane', 'Doe', 'john.doe@jetlouge.com', '09225523193', 'HR Manager', 'Human Resources', '2025-09-19', 200.00, 'active', 'online', '2025-09-19 08:07:23', '$2y$12$RTWARWk8r.Wa6mCyXGKKsuQL3R41/.vVDFVDxGrgu/r1HNYGqk98G', NULL, NULL, '2025-09-09 15:41:35', '2025-09-19 17:08:56'),
(46, 'John', 'Kaizer', 'john.kaizer@gmail.com', '09225523193', 'HR Manager', 'Human Resources', '2025-09-09', 1200.00, 'active', 'offline', NULL, NULL, NULL, NULL, '2025-09-09 10:30:19', '2025-09-09 10:30:19');

-- --------------------------------------------------------

--
-- Table structure for table `employee_notifications`
--

CREATE TABLE `employee_notifications` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `employee_id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `type` enum('info','success','warning','error') NOT NULL DEFAULT 'info',
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `sent_at` timestamp NULL DEFAULT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `employee_requests`
--

CREATE TABLE `employee_requests` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `employee_id` bigint(20) UNSIGNED NOT NULL,
  `request_type` varchar(255) NOT NULL,
  `reason` text NOT NULL,
  `requested_date` date NOT NULL,
  `status` enum('pending','approved','rejected','cancelled') NOT NULL DEFAULT 'pending',
  `approved_by` bigint(20) UNSIGNED DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `employee_trainings`
--

CREATE TABLE `employee_trainings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `employee_id` bigint(20) UNSIGNED NOT NULL,
  `training_id` bigint(20) UNSIGNED NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `status` enum('assigned','in_progress','completed','cancelled') NOT NULL DEFAULT 'assigned',
  `progress_percentage` decimal(5,2) NOT NULL DEFAULT 0.00,
  `assigned_by` bigint(20) UNSIGNED DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `leave_balances`
--

CREATE TABLE `leave_balances` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `employee_id` bigint(20) UNSIGNED NOT NULL,
  `leave_type_id` bigint(20) UNSIGNED NOT NULL,
  `year` int(11) NOT NULL,
  `days_allocated` int(11) NOT NULL DEFAULT 0,
  `days_used` int(11) NOT NULL DEFAULT 0,
  `days_remaining` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `leave_requests`
--

CREATE TABLE `leave_requests` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `employee_id` bigint(20) UNSIGNED NOT NULL,
  `leave_type_id` bigint(20) UNSIGNED NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `days_requested` int(11) NOT NULL,
  `reason` text DEFAULT NULL,
  `status` enum('pending','approved','rejected','cancelled') DEFAULT 'pending',
  `approved_by` bigint(20) UNSIGNED DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `leave_requests`
--

INSERT INTO `leave_requests` (`id`, `employee_id`, `leave_type_id`, `start_date`, `end_date`, `days_requested`, `reason`, `status`, `approved_by`, `approved_at`, `rejection_reason`, `created_at`, `updated_at`) VALUES
(4, 42, 1, '2025-09-19', '2025-09-21', 3, 'Family vacation trip', 'rejected', NULL, NULL, NULL, '2025-09-09 15:46:57', '2025-09-11 11:48:17'),
(5, 42, 2, '2025-09-04', '2025-09-06', 3, 'Medical appointment and recovery', 'approved', NULL, NULL, NULL, '2025-09-09 15:46:57', '2025-09-09 15:46:57'),
(6, 45, 1, '2025-09-19', '2025-09-21', 3, 'Family vacation trip', 'pending', NULL, NULL, NULL, '2025-09-09 15:46:57', '2025-09-09 15:46:57'),
(7, 45, 2, '2025-09-04', '2025-09-06', 3, 'Medical appointment and recovery', 'approved', NULL, NULL, NULL, '2025-09-09 15:46:57', '2025-09-09 15:46:57'),
(8, 42, 1, '2025-09-10', '2025-09-11', 2, 'Break', 'pending', NULL, NULL, NULL, '2025-09-09 09:49:46', '2025-09-09 09:49:46'),
(9, 45, 1, '2025-09-10', '2025-09-10', 1, 'Break', 'pending', NULL, NULL, NULL, '2025-09-09 12:45:06', '2025-09-09 12:45:06'),
(10, 45, 1, '2025-09-10', '2025-09-11', 2, 'Break', 'pending', NULL, NULL, NULL, '2025-09-09 12:58:07', '2025-09-09 12:58:07');

-- --------------------------------------------------------

--
-- Table structure for table `leave_types`
--

CREATE TABLE `leave_types` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `code` varchar(10) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `days_allowed` int(11) DEFAULT 0,
  `max_days_per_year` int(11) DEFAULT 0,
  `carry_forward` tinyint(1) DEFAULT 0,
  `requires_approval` tinyint(1) DEFAULT 1,
  `status` enum('active','inactive') DEFAULT 'active',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `leave_types`
--

INSERT INTO `leave_types` (`id`, `name`, `code`, `description`, `days_allowed`, `max_days_per_year`, `carry_forward`, `requires_approval`, `status`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Annual Leave', 'VL', 'Yearly vacation leave for rest and recreation', 15, 15, 1, 1, 'active', 1, '2025-09-09 13:39:47', '2025-09-09 13:39:47'),
(2, 'Sick Leave', 'SL', 'Medical leave for illness or health issues', 10, 10, 1, 0, 'active', 1, '2025-09-09 13:39:47', '2025-09-09 13:39:47'),
(3, 'Emergency Leave', 'EL', 'Emergency leave for urgent personal matters', 5, 5, 0, 1, 'active', 1, '2025-09-09 13:39:47', '2025-09-09 13:39:47'),
(4, 'Maternity Leave', 'ML', 'Maternity leave for new mothers', 90, 90, 0, 1, 'active', 1, '2025-09-09 13:39:47', '2025-09-09 13:39:47'),
(5, 'Paternity Leave', 'PL', 'Paternity leave for new fathers', 7, 7, 0, 1, 'active', 1, '2025-09-09 13:39:47', '2025-09-09 13:39:47');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1);

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payslips`
--

CREATE TABLE `payslips` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `employee_id` bigint(20) UNSIGNED NOT NULL,
  `pay_period_start` date NOT NULL,
  `pay_period_end` date NOT NULL,
  `basic_salary` decimal(10,2) NOT NULL DEFAULT 0.00,
  `overtime_pay` decimal(10,2) NOT NULL DEFAULT 0.00,
  `allowances` decimal(10,2) NOT NULL DEFAULT 0.00,
  `bonuses` decimal(10,2) NOT NULL DEFAULT 0.00,
  `gross_pay` decimal(10,2) NOT NULL DEFAULT 0.00,
  `tax_deduction` decimal(10,2) NOT NULL DEFAULT 0.00,
  `sss_deduction` decimal(10,2) NOT NULL DEFAULT 0.00,
  `philhealth_deduction` decimal(10,2) NOT NULL DEFAULT 0.00,
  `pagibig_deduction` decimal(10,2) NOT NULL DEFAULT 0.00,
  `other_deductions` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_deductions` decimal(10,2) NOT NULL DEFAULT 0.00,
  `net_pay` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` enum('draft','finalized','sent') NOT NULL DEFAULT 'draft',
  `generated_at` timestamp NULL DEFAULT NULL,
  `generated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `shifts`
--

CREATE TABLE `shifts` (
  `id` int(11) NOT NULL,
  `employee_id` bigint(20) UNSIGNED NOT NULL,
  `shift_type_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `status` enum('scheduled','in_progress','completed','cancelled') DEFAULT 'scheduled',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `shift_requests`
--

CREATE TABLE `shift_requests` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `employee_id` bigint(20) UNSIGNED NOT NULL,
  `shift_type_id` bigint(20) UNSIGNED NOT NULL,
  `shift_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `hours` decimal(4,2) NOT NULL,
  `location` varchar(255) NOT NULL DEFAULT 'Main Office',
  `notes` text DEFAULT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `approved_by` bigint(20) UNSIGNED DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `shift_requests`
--

INSERT INTO `shift_requests` (`id`, `employee_id`, `shift_type_id`, `shift_date`, `start_time`, `end_time`, `hours`, `location`, `notes`, `status`, `approved_by`, `approved_at`, `created_at`, `updated_at`) VALUES
(1, 42, 1, '2025-09-20', '08:00:00', '16:00:00', -8.00, 'Main Office', 'I want to swap shifts', 'rejected', 1, '2025-09-19 08:53:57', '2025-09-19 08:40:44', '2025-09-19 08:53:57'),
(2, 42, 1, '2025-09-20', '08:00:00', '16:00:00', -8.00, 'Main Office', 'I want to swap', 'approved', 1, '2025-09-19 08:53:52', '2025-09-19 08:53:48', '2025-09-19 08:53:52'),
(3, 45, 1, '2025-09-20', '08:00:00', '16:00:00', -8.00, 'Main Office', 'I want to swap shifts', 'pending', NULL, NULL, '2025-09-19 08:54:12', '2025-09-19 08:54:12');

-- --------------------------------------------------------

--
-- Table structure for table `shift_types`
--

CREATE TABLE `shift_types` (
  `id` bigint(20) UNSIGNED NOT NULL,
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
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `shift_types`
--

INSERT INTO `shift_types` (`id`, `name`, `code`, `description`, `default_start_time`, `default_end_time`, `break_duration`, `hourly_rate`, `color_code`, `type`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Morning Shift', 'MORNING', 'Standard morning shift for regular operations', '08:00:00', '16:00:00', 60, 25.00, '#28a745', 'day', 1, '2025-09-10 16:39:11', '2025-09-10 16:39:11'),
(2, 'Afternoon Shift', 'AFTERNOON', 'Afternoon to evening coverage shift', '14:00:00', '22:00:00', 45, 27.50, '#ffc107', 'swing', 1, '2025-09-10 16:39:11', '2025-09-10 16:39:11'),
(3, 'Night Shift', 'NIGHT', 'Overnight shift with premium pay', '22:00:00', '06:00:00', 60, 32.00, '#6f42c1', 'night', 1, '2025-09-10 16:39:11', '2025-09-10 16:39:11'),
(4, 'Split Shift', 'SPLIT', 'Split shift with extended break period', '09:00:00', '17:00:00', 120, 24.00, '#17a2b8', 'split', 1, '2025-09-10 16:39:11', '2025-09-10 16:39:11'),
(5, 'Weekend Shift', 'WEEKEND', 'Weekend coverage with rotating schedule', '10:00:00', '18:00:00', 45, 30.00, '#fd7e14', 'rotating', 1, '2025-09-10 16:39:11', '2025-09-10 16:39:11');

-- --------------------------------------------------------

--
-- Table structure for table `timesheets`
--

CREATE TABLE `timesheets` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `clock_in` time DEFAULT NULL,
  `clock_out` time DEFAULT NULL,
  `break_start` time DEFAULT NULL,
  `break_end` time DEFAULT NULL,
  `total_hours` decimal(4,2) DEFAULT 0.00,
  `overtime_hours` decimal(4,2) DEFAULT 0.00,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `time_entries`
--

CREATE TABLE `time_entries` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `employee_id` bigint(20) UNSIGNED NOT NULL,
  `work_date` date NOT NULL,
  `clock_in_time` time DEFAULT NULL,
  `clock_out_time` time DEFAULT NULL,
  `hours_worked` decimal(4,2) DEFAULT 0.00,
  `overtime_hours` decimal(4,2) DEFAULT 0.00,
  `break_duration` decimal(4,2) DEFAULT 0.00,
  `description` text DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `approved_by` bigint(20) UNSIGNED DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `training_programs`
--

CREATE TABLE `training_programs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `type` enum('mandatory','optional') NOT NULL DEFAULT 'optional',
  `duration_hours` int(11) NOT NULL DEFAULT 0,
  `delivery_mode` enum('online','classroom','hybrid') NOT NULL DEFAULT 'online',
  `cost` decimal(10,2) NOT NULL DEFAULT 0.00,
  `provider` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `training_programs`
--

INSERT INTO `training_programs` (`id`, `title`, `description`, `type`, `duration_hours`, `delivery_mode`, `cost`, `provider`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Data Privacy and Security', 'Comprehensive training on data protection and cybersecurity best practices', 'mandatory', 8, 'online', 0.00, 'Internal HR', 1, '2025-09-09 13:39:47', '2025-09-09 13:39:47'),
(2, 'Customer Service Excellence', 'Advanced customer service techniques and communication skills', 'optional', 16, 'hybrid', 5000.00, 'External Provider', 1, '2025-09-09 13:39:47', '2025-09-09 13:39:47'),
(3, 'Leadership Development', 'Management and leadership skills for supervisory roles', 'optional', 24, 'classroom', 15000.00, 'Leadership Institute', 1, '2025-09-09 13:39:47', '2025-09-09 13:39:47'),
(4, 'Travel Industry Regulations', 'Understanding travel industry compliance and regulations', 'mandatory', 12, 'online', 0.00, 'Industry Association', 1, '2025-09-09 13:39:47', '2025-09-09 13:39:47'),
(5, 'Digital Marketing Fundamentals', 'Modern digital marketing strategies and tools', 'optional', 20, 'online', 8000.00, 'Marketing Academy', 1, '2025-09-09 13:39:47', '2025-09-09 13:39:47');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `phone`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Brylle Cillo', 'Brylle.Cil@gmai.com', '09129384723', NULL, '$2y$12$HYTAxMYh1xOrVg.yLeha4ehvKm7xv/UTPQt9.xO2mQRwCyglKMvuC', NULL, '2025-09-10 08:44:58', '2025-09-10 08:44:58');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `claims`
--
ALTER TABLE `claims`
  ADD PRIMARY KEY (`id`),
  ADD KEY `approved_by` (`approved_by`),
  ADD KEY `idx_employee_status` (`employee_id`,`status`),
  ADD KEY `idx_claim_type` (`claim_type_id`),
  ADD KEY `idx_claim_date` (`claim_date`);

--
-- Indexes for table `claim_types`
--
ALTER TABLE `claim_types`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `competency_assessments`
--
ALTER TABLE `competency_assessments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `assessed_by` (`assessed_by`),
  ADD KEY `idx_employee_id` (`employee_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_assessment_date` (`assessment_date`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_department_status` (`department`,`status`),
  ADD KEY `idx_hire_date` (`hire_date`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_online_status` (`online_status`);

--
-- Indexes for table `employee_notifications`
--
ALTER TABLE `employee_notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_employee_id` (`employee_id`),
  ADD KEY `idx_is_read` (`is_read`),
  ADD KEY `idx_sent_at` (`sent_at`);

--
-- Indexes for table `employee_requests`
--
ALTER TABLE `employee_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `approved_by` (`approved_by`),
  ADD KEY `idx_employee_id` (`employee_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_requested_date` (`requested_date`);

--
-- Indexes for table `employee_trainings`
--
ALTER TABLE `employee_trainings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `assigned_by` (`assigned_by`),
  ADD KEY `idx_employee_id` (`employee_id`),
  ADD KEY `idx_training_id` (`training_id`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `shift_requests`
--
ALTER TABLE `shift_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `shift_requests_employee_id_index` (`employee_id`),
  ADD KEY `shift_requests_shift_type_id_index` (`shift_type_id`),
  ADD KEY `shift_requests_status_index` (`status`),
  ADD KEY `shift_requests_shift_date_index` (`shift_date`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `shift_requests`
--
ALTER TABLE `shift_requests`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
