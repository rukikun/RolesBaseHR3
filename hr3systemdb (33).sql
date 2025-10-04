-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 04, 2025 at 08:38 AM
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
-- Table structure for table `ai_generated_timesheets`
--

CREATE TABLE `ai_generated_timesheets` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `employee_id` bigint(20) UNSIGNED NOT NULL,
  `employee_name` varchar(255) NOT NULL,
  `department` varchar(255) DEFAULT NULL,
  `week_start_date` date NOT NULL,
  `weekly_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`weekly_data`)),
  `total_hours` decimal(8,2) NOT NULL DEFAULT 0.00,
  `overtime_hours` decimal(8,2) NOT NULL DEFAULT 0.00,
  `ai_insights` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`ai_insights`)),
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `generated_at` timestamp NULL DEFAULT NULL,
  `approved_by` bigint(20) UNSIGNED DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `rejected_by` bigint(20) UNSIGNED DEFAULT NULL,
  `rejected_at` timestamp NULL DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ai_generated_timesheets`
--

INSERT INTO `ai_generated_timesheets` (`id`, `employee_id`, `employee_name`, `department`, `week_start_date`, `weekly_data`, `total_hours`, `overtime_hours`, `ai_insights`, `status`, `generated_at`, `approved_by`, `approved_at`, `rejected_by`, `rejected_at`, `rejection_reason`, `notes`, `created_at`, `updated_at`) VALUES
(2, 5, 'David Brown', 'Sales', '2025-09-29', '{\"monday\":{\"date\":\"09\\/29\\/25\",\"clock_in\":\"--\",\"break\":\"--\",\"clock_out\":\"--\",\"total_hours\":0,\"overtime\":0},\"tuesday\":{\"date\":\"09\\/30\\/25\",\"clock_in\":\"--\",\"break\":\"--\",\"clock_out\":\"--\",\"total_hours\":0,\"overtime\":0},\"wednesday\":{\"date\":\"10\\/01\\/25\",\"clock_in\":\"10:33 PM\",\"break\":\"12:00 PM - 1:00 PM\",\"clock_out\":\"10:33 PM\",\"total_hours\":0,\"overtime\":0},\"thursday\":{\"date\":\"10\\/02\\/25\",\"clock_in\":\"--\",\"break\":\"--\",\"clock_out\":\"--\",\"total_hours\":0,\"overtime\":0},\"friday\":{\"date\":\"10\\/03\\/25\",\"clock_in\":\"1:41 PM\",\"break\":\"12:00 PM - 1:00 PM\",\"clock_out\":\"1:42 PM\",\"total_hours\":0,\"overtime\":0},\"saturday\":{\"date\":\"10\\/04\\/25\",\"clock_in\":\"--\",\"break\":\"--\",\"clock_out\":\"--\",\"total_hours\":0,\"overtime\":0},\"sunday\":{\"date\":\"10\\/05\\/25\",\"clock_in\":\"--\",\"break\":\"--\",\"clock_out\":\"--\",\"total_hours\":0,\"overtime\":0}}', 0.00, 0.00, '[]', 'approved', '2025-10-03 14:19:27', 1, '2025-10-03 14:19:31', 1, '2025-10-03 07:24:09', 'None', NULL, '2025-10-03 07:24:00', '2025-10-03 14:19:31'),
(3, 2, 'Jane Smith', 'Human Resources', '2025-09-29', '{\"monday\":{\"date\":\"09\\/29\\/25\",\"clock_in\":\"--\",\"break\":\"--\",\"clock_out\":\"--\",\"total_hours\":0,\"overtime\":0},\"tuesday\":{\"date\":\"09\\/30\\/25\",\"clock_in\":\"--\",\"break\":\"--\",\"clock_out\":\"--\",\"total_hours\":0,\"overtime\":0},\"wednesday\":{\"date\":\"10\\/01\\/25\",\"clock_in\":\"--\",\"break\":\"--\",\"clock_out\":\"--\",\"total_hours\":0,\"overtime\":0},\"thursday\":{\"date\":\"10\\/02\\/25\",\"clock_in\":\"--\",\"break\":\"--\",\"clock_out\":\"--\",\"total_hours\":0,\"overtime\":0},\"friday\":{\"date\":\"10\\/03\\/25\",\"clock_in\":\"12:09 PM\",\"break\":\"12:00 PM - 1:00 PM\",\"clock_out\":\"12:09 PM\",\"total_hours\":0,\"overtime\":0},\"saturday\":{\"date\":\"10\\/04\\/25\",\"clock_in\":\"--\",\"break\":\"--\",\"clock_out\":\"--\",\"total_hours\":0,\"overtime\":0},\"sunday\":{\"date\":\"10\\/05\\/25\",\"clock_in\":\"--\",\"break\":\"--\",\"clock_out\":\"--\",\"total_hours\":0,\"overtime\":0}}', 0.00, 0.00, '[]', 'pending', '2025-10-04 00:14:33', 1, '2025-10-03 15:01:51', 1, '2025-10-03 07:33:20', NULL, NULL, '2025-10-03 07:25:06', '2025-10-04 00:14:33'),
(4, 4, 'Sarah Wilson', 'Marketing', '2025-09-29', '{\"monday\":{\"date\":\"09\\/29\\/25\",\"clock_in\":\"--\",\"break\":\"--\",\"clock_out\":\"--\",\"total_hours\":0,\"overtime\":0},\"tuesday\":{\"date\":\"09\\/30\\/25\",\"clock_in\":\"--\",\"break\":\"--\",\"clock_out\":\"--\",\"total_hours\":0,\"overtime\":0},\"wednesday\":{\"date\":\"10\\/01\\/25\",\"clock_in\":\"10:28 PM\",\"break\":\"12:00 PM - 1:00 PM\",\"clock_out\":\"10:28 PM\",\"total_hours\":0,\"overtime\":0},\"thursday\":{\"date\":\"10\\/02\\/25\",\"clock_in\":\"--\",\"break\":\"--\",\"clock_out\":\"--\",\"total_hours\":0,\"overtime\":0},\"friday\":{\"date\":\"10\\/03\\/25\",\"clock_in\":\"--\",\"break\":\"--\",\"clock_out\":\"--\",\"total_hours\":0,\"overtime\":0},\"saturday\":{\"date\":\"10\\/04\\/25\",\"clock_in\":\"--\",\"break\":\"--\",\"clock_out\":\"--\",\"total_hours\":0,\"overtime\":0},\"sunday\":{\"date\":\"10\\/05\\/25\",\"clock_in\":\"--\",\"break\":\"--\",\"clock_out\":\"--\",\"total_hours\":0,\"overtime\":0}}', 0.00, 0.00, '[]', 'rejected', '2025-10-03 07:33:34', NULL, NULL, 1, '2025-10-03 14:19:38', NULL, NULL, '2025-10-03 07:33:34', '2025-10-03 14:19:38'),
(5, 1, 'John Doe', 'IT', '2025-09-29', '{\"monday\":{\"date\":\"09\\/29\\/25\",\"clock_in\":\"--\",\"break\":\"--\",\"clock_out\":\"--\",\"total_hours\":0,\"overtime\":0},\"tuesday\":{\"date\":\"09\\/30\\/25\",\"clock_in\":\"--\",\"break\":\"--\",\"clock_out\":\"--\",\"total_hours\":0,\"overtime\":0},\"wednesday\":{\"date\":\"10\\/01\\/25\",\"clock_in\":\"--\",\"break\":\"--\",\"clock_out\":\"--\",\"total_hours\":0,\"overtime\":0},\"thursday\":{\"date\":\"10\\/02\\/25\",\"clock_in\":\"--\",\"break\":\"--\",\"clock_out\":\"--\",\"total_hours\":0,\"overtime\":0},\"friday\":{\"date\":\"10\\/03\\/25\",\"clock_in\":\"--\",\"break\":\"--\",\"clock_out\":\"--\",\"total_hours\":0,\"overtime\":0},\"saturday\":{\"date\":\"10\\/04\\/25\",\"clock_in\":\"--\",\"break\":\"--\",\"clock_out\":\"--\",\"total_hours\":0,\"overtime\":0},\"sunday\":{\"date\":\"10\\/05\\/25\",\"clock_in\":\"--\",\"break\":\"--\",\"clock_out\":\"--\",\"total_hours\":0,\"overtime\":0}}', 0.00, 0.00, '[]', 'pending', '2025-10-03 14:46:50', NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-03 14:46:50', '2025-10-03 14:46:50');

-- --------------------------------------------------------

--
-- Table structure for table `attendances`
--

CREATE TABLE `attendances` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `employee_id` bigint(20) UNSIGNED NOT NULL,
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
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `attendances`
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
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `claim_type_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `claim_date` date NOT NULL,
  `description` text DEFAULT NULL,
  `receipt_path` varchar(255) DEFAULT NULL,
  `attachment_path` varchar(255) DEFAULT NULL,
  `status` enum('pending','approved','rejected','paid') DEFAULT 'pending',
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
--
-- --------------------------------------------------------

--
-- Table structure for table `claim_types`
--

CREATE TABLE `claim_types` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `code` varchar(10) NOT NULL,
  `description` text DEFAULT NULL,
  `max_amount` decimal(10,2) DEFAULT NULL,
  `requires_attachment` tinyint(1) DEFAULT 0,
  `auto_approve` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `claim_types`
--

INSERT INTO `claim_types` (`id`, `name`, `code`, `description`, `max_amount`, `requires_attachment`, `auto_approve`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Travel Expenses', 'TRAVEL', 'Business travel related expenses', 5000.00, 1, 0, 1, '2025-09-22 01:20:48', '2025-09-22 01:20:48'),
(2, 'Office Supplies', 'OFFICE', 'Office supplies and equipment', 1000.00, 1, 0, 1, '2025-09-22 01:20:48', '2025-09-22 01:20:48'),
(3, 'Meal Allowance', 'MEAL', 'Business meal expenses', 500.00, 1, 0, 1, '2025-09-22 01:20:48', '2025-09-25 06:11:52'),
(4, 'Training Costs', 'TRAINING', 'Professional development and training', 2000.00, 1, 0, 1, '2025-09-22 01:20:48', '2025-09-22 01:20:48'),
(5, 'Medical Expenses', 'MEDICAL', 'Medical and health related expenses', 3000.00, 1, 0, 1, '2025-09-22 01:20:48', '2025-09-22 01:20:48');

-- --------------------------------------------------------

--
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
  `salary` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` enum('active','inactive','terminated') NOT NULL DEFAULT 'active',
  `online_status` enum('online','offline') NOT NULL DEFAULT 'offline',
  `last_activity` timestamp NULL DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`id`, `first_name`, `last_name`, `email`, `phone`, `position`, `department`, `hire_date`, `salary`, `status`, `online_status`, `last_activity`, `password`, `profile_picture`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'John', 'Doe', 'john.doe@jetlouge.com', '+63 912 345 6789', 'Software Developer', 'Information Technology', '2025-04-03', 75000.00, 'active', 'offline', '2025-10-04 00:39:33', '$2y$12$faRZ6.zZfI9TOd1XnYTgXOGZ08NlUotyk9E5fFMpfEGRmC2NbDTzS', NULL, NULL, '2025-10-03 14:48:48', '2025-10-03 16:35:17'),
(2, 'Jane', 'Smith', 'jane.smith@jetlouge.com', '+63 912 345 6790', 'HR Manager', 'Human Resources', '2024-10-03', 85000.00, 'active', 'online', '2025-10-04 00:40:17', '$2y$12$nm.9c1TFL1dHnHbw.Mx5huOn9Jkp.4FYH0fdKPbN/1Csujadc0Oya', NULL, NULL, '2025-10-03 14:48:48', '2025-10-03 16:35:18'),
(3, 'Mike', 'Johnson', 'mike.johnson@jetlouge.com', '+63 912 345 6791', 'Accountant', 'Finance', '2025-02-03', 65000.00, 'active', 'offline', NULL, '$2y$12$MAvWg72ECxBmYJ30l03WQOb6VWEqVy2dZO.I6ffdGCWiEUmpgUGYe', NULL, NULL, '2025-10-03 14:48:48', '2025-10-03 16:35:18'),
(7, 'Alex', 'Mcqueen', 'alex.mcqueen@gmai.com', '+639162504316', 'Scheduler', 'Human Resources', '2025-10-03', 12.00, 'active', 'offline', NULL, '$2y$12$r4i/P57o.uWfPGgFMZQMW.JJWc0GAmI64pN9kRXtIhi6MpJJORAJe', NULL, NULL, '2025-10-03 14:51:19', '2025-10-03 16:35:19');

-- --------------------------------------------------------

--

--
-- Table structure for table `employee_requests`
--
--
-- Table structure for table `employee_timesheet_details`
--

CREATE TABLE `employee_timesheet_details` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `employee_id` bigint(20) UNSIGNED NOT NULL,
  `week_start_date` date NOT NULL,
  `week_end_date` date NOT NULL,
  `monday_date` date DEFAULT NULL,
  `monday_time_in` varchar(255) DEFAULT NULL,
  `monday_break` varchar(255) DEFAULT NULL,
  `monday_time_out` varchar(255) DEFAULT NULL,
  `monday_total_hours` decimal(5,2) NOT NULL DEFAULT 0.00,
  `monday_actual_time` decimal(5,2) NOT NULL DEFAULT 0.00,
  `tuesday_date` date DEFAULT NULL,
  `tuesday_time_in` varchar(255) DEFAULT NULL,
  `tuesday_break` varchar(255) DEFAULT NULL,
  `tuesday_time_out` varchar(255) DEFAULT NULL,
  `tuesday_total_hours` decimal(5,2) NOT NULL DEFAULT 0.00,
  `tuesday_actual_time` decimal(5,2) NOT NULL DEFAULT 0.00,
  `wednesday_date` date DEFAULT NULL,
  `wednesday_time_in` varchar(255) DEFAULT NULL,
  `wednesday_break` varchar(255) DEFAULT NULL,
  `wednesday_time_out` varchar(255) DEFAULT NULL,
  `wednesday_total_hours` decimal(5,2) NOT NULL DEFAULT 0.00,
  `wednesday_actual_time` decimal(5,2) NOT NULL DEFAULT 0.00,
  `thursday_date` date DEFAULT NULL,
  `thursday_time_in` varchar(255) DEFAULT NULL,
  `thursday_break` varchar(255) DEFAULT NULL,
  `thursday_time_out` varchar(255) DEFAULT NULL,
  `thursday_total_hours` decimal(5,2) NOT NULL DEFAULT 0.00,
  `thursday_actual_time` decimal(5,2) NOT NULL DEFAULT 0.00,
  `friday_date` date DEFAULT NULL,
  `friday_time_in` varchar(255) DEFAULT NULL,
  `friday_break` varchar(255) DEFAULT NULL,
  `friday_time_out` varchar(255) DEFAULT NULL,
  `friday_total_hours` decimal(5,2) NOT NULL DEFAULT 0.00,
  `friday_actual_time` decimal(5,2) NOT NULL DEFAULT 0.00,
  `saturday_date` date DEFAULT NULL,
  `saturday_time_in` varchar(255) DEFAULT NULL,
  `saturday_break` varchar(255) DEFAULT NULL,
  `saturday_time_out` varchar(255) DEFAULT NULL,
  `saturday_total_hours` decimal(5,2) NOT NULL DEFAULT 0.00,
  `saturday_actual_time` decimal(5,2) NOT NULL DEFAULT 0.00,
  `sunday_date` date DEFAULT NULL,
  `sunday_time_in` varchar(255) DEFAULT NULL,
  `sunday_break` varchar(255) DEFAULT NULL,
  `sunday_time_out` varchar(255) DEFAULT NULL,
  `sunday_total_hours` decimal(5,2) NOT NULL DEFAULT 0.00,
  `sunday_actual_time` decimal(5,2) NOT NULL DEFAULT 0.00,
  `total_week_hours` decimal(6,2) NOT NULL DEFAULT 0.00,
  `overtime_hours` decimal(5,2) NOT NULL DEFAULT 0.00,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `supervisor_id` bigint(20) UNSIGNED DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--

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
(2, 2, 1, '2025-09-10', '2025-09-11', 2, 'Personal time off', 'approved', NULL, '2025-09-25 12:24:45', NULL, '2025-09-25 12:22:40', '2025-09-25 12:24:45'),
(3, 3, 1, '2025-09-10', '2025-09-10', 1, 'Doctor appointment', 'rejected', NULL, NULL, NULL, '2025-09-25 12:22:40', '2025-09-25 14:00:51'),
(0, 3, 1, '2025-10-02', '2025-10-03', 2, 'Break', 'pending', NULL, NULL, NULL, '2025-09-30 17:37:49', '2025-09-30 17:37:49'),
(0, 2, 4, '2025-10-02', '2025-10-04', 3, 'Break', 'pending', NULL, NULL, NULL, '2025-09-30 17:51:25', '2025-09-30 17:51:25'),
(0, 4, 3, '2025-10-01', '2025-10-03', 3, 'Leave', 'pending', NULL, NULL, NULL, '2025-09-30 22:40:26', '2025-09-30 22:40:26');

-- --------------------------------------------------------

--
-- Table structure for table `leave_types`
--

CREATE TABLE `leave_types` (
  `id` int(11) NOT NULL,
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
(1, 'Annual Leave', 'AL', 'Annual vacation leave', 0, 21, 1, 1, 'active', 1, '2025-09-25 13:29:33', '2025-09-25 13:29:33'),
(2, 'Sick Leave', 'SL', 'Medical sick leave', 0, 10, 0, 0, 'active', 1, '2025-09-25 13:29:33', '2025-09-25 13:29:33'),
(3, 'Emergency Leave', 'EL', 'Emergency family leave', 0, 5, 0, 1, 'active', 1, '2025-09-25 13:29:33', '2025-09-25 13:29:33'),
(4, 'Maternity Leave', 'ML', 'Maternity leave', 0, 90, 0, 1, 'active', 1, '2025-09-25 13:29:33', '2025-09-25 13:29:33'),
(5, 'Paternity Leave', 'PL', 'Paternity leave', 0, 7, 0, 1, 'active', 1, '2025-09-25 13:29:33', '2025-09-25 13:29:33');

-- --------------------------------------------------------


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
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2025_10_03_141803_add_missing_columns_to_ai_generated_timesheets_table', 2),
(5, '2025_10_03_152009_create_ai_generated_timesheets_table_final', 3);

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
-----

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
  `id` bigint(20) UNSIGNED NOT NULL,
  `employee_id` int(11) NOT NULL,
  `shift_type_id` int(11) NOT NULL,
  `shift_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `location` varchar(255) DEFAULT 'Main Office',
  `notes` text DEFAULT NULL,
  `status` enum('scheduled','in_progress','completed','cancelled') DEFAULT 'scheduled',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `shifts`
--
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
  `location` varchar(255) NOT NULL DEFAULT 'Main Office',
  `notes` text DEFAULT NULL,
  `hours` decimal(5,2) NOT NULL DEFAULT 0.00,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `shift_requests`
--

INSERT INTO `shift_requests` (`id`, `employee_id`, `shift_type_id`, `shift_date`, `start_time`, `end_time`, `location`, `notes`, `hours`, `status`, `created_at`, `updated_at`, `approved_by`, `approved_at`) VALUES
(1, 1, 1, '2024-10-05', '09:00:00', '17:00:00', 'Main Office', 'Regular morning shift request', 8.00, 'approved', '2025-10-02 05:53:05', '2025-10-03 14:52:56', 1, '2025-10-03 14:52:56'),
(2, 2, 2, '2024-10-06', '14:00:00', '22:00:00', 'Branch Office', 'Evening shift request', 8.00, 'pending', '2025-10-02 05:53:05', '2025-10-02 05:53:05', NULL, NULL),
(3, 3, 3, '2024-10-07', '22:00:00', '06:00:00', 'Main Office', 'Night shift request', 8.00, 'approved', '2025-10-02 05:53:05', '2025-10-02 05:53:05', NULL, NULL),
(4, 1, 1, '2024-10-08', '08:00:00', '16:00:00', 'Remote', 'Work from home request', 8.00, 'rejected', '2025-10-02 05:53:05', '2025-10-02 05:53:05', NULL, NULL),
(5, 2, 2, '2024-10-09', '10:00:00', '18:00:00', 'Main Office', 'Flexible hours request', 8.00, 'pending', '2025-10-02 05:53:05', '2025-10-02 05:53:05', NULL, NULL),
(10, 1, 1, '2025-10-21', '08:00:00', '16:00:00', 'Main Office', NULL, -8.00, 'approved', '2025-10-03 15:03:00', '2025-10-03 15:03:08', 1, '2025-10-03 15:03:08');

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
-- Indexes for table `ai_generated_timesheets`
--
ALTER TABLE `ai_generated_timesheets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ai_generated_timesheets_employee_id_week_start_date_index` (`employee_id`,`week_start_date`),
  ADD KEY `ai_generated_timesheets_status_generated_at_index` (`status`,`generated_at`),
  ADD KEY `ai_generated_timesheets_week_start_date_index` (`week_start_date`);

--
-- Indexes for table `attendances`
--
ALTER TABLE `attendances`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `attendances_employee_id_date_unique` (`employee_id`,`date`),
  ADD KEY `attendances_employee_id_index` (`employee_id`),
  ADD KEY `attendances_date_index` (`date`),
  ADD KEY `attendances_status_index` (`status`),
  ADD KEY `idx_attendances_date` (`date`);

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
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `employees_email_unique` (`email`),
  ADD KEY `employees_status_index` (`status`),
  ADD KEY `employees_department_index` (`department`),
  ADD KEY `employees_online_status_index` (`online_status`),
  ADD KEY `employees_hire_date_index` (`hire_date`),
  ADD KEY `idx_employees_status` (`status`),
  ADD KEY `idx_employees_department` (`department`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

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
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `shifts`
--
ALTER TABLE `shifts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_shifts_shift_date` (`shift_date`);

--
-- Indexes for table `shift_requests`
--
ALTER TABLE `shift_requests`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `time_entries`
--
ALTER TABLE `time_entries`
  ADD KEY `idx_time_entries_work_date` (`work_date`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `ai_generated_timesheets`
--
ALTER TABLE `ai_generated_timesheets`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `attendances`
--
ALTER TABLE `attendances`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;


--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `shifts`
--
ALTER TABLE `shifts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=69;

--
-- AUTO_INCREMENT for table `shift_requests`
--
ALTER TABLE `shift_requests`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
