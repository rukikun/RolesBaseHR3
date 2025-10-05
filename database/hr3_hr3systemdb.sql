-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 04, 2025 at 01:56 PM
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
-- Database: `hr3_hr3systemdb`
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
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ai_generated_timesheets`
--

INSERT INTO `ai_generated_timesheets` (`id`, `employee_id`, `employee_name`, `department`, `week_start_date`, `weekly_data`, `total_hours`, `overtime_hours`, `ai_insights`, `status`, `generated_at`, `approved_by`, `approved_at`, `notes`, `created_at`, `updated_at`) VALUES
(1, 5, 'David Brown', 'Sales', '2025-09-29', '{\"monday\":{\"date\":\"09\\/29\\/25\",\"clock_in\":\"--\",\"break\":\"--\",\"clock_out\":\"--\",\"total_hours\":0,\"overtime\":0},\"tuesday\":{\"date\":\"09\\/30\\/25\",\"clock_in\":\"--\",\"break\":\"--\",\"clock_out\":\"--\",\"total_hours\":0,\"overtime\":0},\"wednesday\":{\"date\":\"10\\/01\\/25\",\"clock_in\":\"--\",\"break\":\"--\",\"clock_out\":\"--\",\"total_hours\":0,\"overtime\":0},\"thursday\":{\"date\":\"10\\/02\\/25\",\"clock_in\":\"--\",\"break\":\"--\",\"clock_out\":\"--\",\"total_hours\":0,\"overtime\":0},\"friday\":{\"date\":\"10\\/03\\/25\",\"clock_in\":\"--\",\"break\":\"--\",\"clock_out\":\"--\",\"total_hours\":0,\"overtime\":0},\"saturday\":{\"date\":\"10\\/04\\/25\",\"clock_in\":\"--\",\"break\":\"--\",\"clock_out\":\"--\",\"total_hours\":0,\"overtime\":0},\"sunday\":{\"date\":\"10\\/05\\/25\",\"clock_in\":\"--\",\"break\":\"--\",\"clock_out\":\"--\",\"total_hours\":0,\"overtime\":0}}', 0.00, 0.00, '[]', 'approved', '2025-10-04 08:50:33', 1, '2025-10-04 08:50:38', NULL, '2025-10-04 08:50:33', '2025-10-04 08:50:38'),
(2, 2, 'Jane Smith', 'Human Resources', '2025-09-29', '{\"monday\":{\"date\":\"09\\/29\\/25\",\"clock_in\":\"--\",\"break\":\"--\",\"clock_out\":\"--\",\"total_hours\":0,\"overtime\":0},\"tuesday\":{\"date\":\"09\\/30\\/25\",\"clock_in\":\"--\",\"break\":\"--\",\"clock_out\":\"--\",\"total_hours\":0,\"overtime\":0},\"wednesday\":{\"date\":\"10\\/01\\/25\",\"clock_in\":\"--\",\"break\":\"--\",\"clock_out\":\"--\",\"total_hours\":0,\"overtime\":0},\"thursday\":{\"date\":\"10\\/02\\/25\",\"clock_in\":\"--\",\"break\":\"--\",\"clock_out\":\"--\",\"total_hours\":0,\"overtime\":0},\"friday\":{\"date\":\"10\\/03\\/25\",\"clock_in\":\"--\",\"break\":\"--\",\"clock_out\":\"--\",\"total_hours\":0,\"overtime\":0},\"saturday\":{\"date\":\"10\\/04\\/25\",\"clock_in\":\"6:13 PM\",\"break\":\"12:00 PM - 1:00 PM\",\"clock_out\":\"6:13 PM\",\"total_hours\":0,\"overtime\":0},\"sunday\":{\"date\":\"10\\/05\\/25\",\"clock_in\":\"--\",\"break\":\"--\",\"clock_out\":\"--\",\"total_hours\":0,\"overtime\":0}}', 0.00, 0.00, '[]', 'pending', '2025-10-04 11:21:34', NULL, NULL, NULL, '2025-10-04 11:21:34', '2025-10-04 11:21:34');

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
  `total_hours` decimal(5,2) NOT NULL DEFAULT 0.00,
  `overtime_hours` decimal(5,2) NOT NULL DEFAULT 0.00,
  `status` enum('present','absent','late','on_break','clocked_out') NOT NULL DEFAULT 'present',
  `location` varchar(255) DEFAULT NULL,
  `ip_address` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `attendances`
--

INSERT INTO `attendances` (`id`, `employee_id`, `date`, `clock_in_time`, `clock_out_time`, `break_start_time`, `break_end_time`, `total_hours`, `overtime_hours`, `status`, `location`, `ip_address`, `notes`, `created_at`, `updated_at`) VALUES
(1, 1, '2025-10-04', '2025-10-04 16:51:20', '2025-10-04 16:51:37', NULL, NULL, 0.00, 0.00, 'clocked_out', 'ESS Portal', '127.0.0.1', NULL, '2025-10-04 08:51:20', '2025-10-04 08:51:37');

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
  `description` text NOT NULL,
  `receipt_path` varchar(255) DEFAULT NULL,
  `status` enum('pending','approved','rejected','paid') NOT NULL DEFAULT 'pending',
  `approved_by` bigint(20) UNSIGNED DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `admin_notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `claims`
--

INSERT INTO `claims` (`id`, `employee_id`, `claim_type_id`, `amount`, `claim_date`, `description`, `receipt_path`, `status`, `approved_by`, `approved_at`, `admin_notes`, `created_at`, `updated_at`) VALUES
(1, 2, 3, 12.00, '2025-10-09', 'break', NULL, 'approved', NULL, NULL, NULL, '2025-10-04 08:53:30', '2025-10-04 09:11:02'),
(2, 6, 3, 12.00, '2025-10-05', 'Meal', NULL, 'rejected', NULL, NULL, NULL, '2025-10-04 08:54:52', '2025-10-04 09:10:57'),
(3, 4, 1, 250.00, '2025-10-05', 'Claims', NULL, 'pending', NULL, NULL, NULL, '2025-10-04 09:11:38', '2025-10-04 09:11:38');

-- --------------------------------------------------------

--
-- Table structure for table `claim_types`
--

CREATE TABLE `claim_types` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `code` varchar(10) NOT NULL,
  `description` text DEFAULT NULL,
  `max_amount` decimal(10,2) DEFAULT NULL,
  `requires_attachment` tinyint(1) NOT NULL DEFAULT 0,
  `auto_approve` tinyint(1) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `claim_types`
--

INSERT INTO `claim_types` (`id`, `name`, `code`, `description`, `max_amount`, `requires_attachment`, `auto_approve`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Travel Expenses', 'TRAVEL', 'Business travel related expenses', 5000.00, 1, 0, 1, '2025-10-04 08:45:30', '2025-10-04 08:45:30'),
(2, 'Office Supplies', 'OFFICE', 'Office supplies and equipment', 1000.00, 1, 0, 1, '2025-10-04 08:45:30', '2025-10-04 08:45:30'),
(3, 'Meal Allowance', 'MEAL', 'Business meal expenses', 500.00, 1, 0, 1, '2025-10-04 08:45:30', '2025-10-04 08:45:30'),
(4, 'Training Costs', 'TRAINING', 'Professional development and training', 2000.00, 1, 0, 1, '2025-10-04 08:45:30', '2025-10-04 08:45:30'),
(5, 'Medical Expenses', 'MEDICAL', 'Medical and health related expenses', 3000.00, 1, 0, 1, '2025-10-04 08:45:30', '2025-10-04 08:45:30');

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `employee_number` varchar(255) DEFAULT NULL,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `position` varchar(255) NOT NULL,
  `department` varchar(255) NOT NULL,
  `hire_date` date NOT NULL,
  `salary` decimal(10,2) DEFAULT NULL,
  `status` enum('active','inactive','terminated') NOT NULL DEFAULT 'active',
  `online_status` enum('online','offline','away') NOT NULL DEFAULT 'offline',
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

INSERT INTO `employees` (`id`, `employee_number`, `first_name`, `last_name`, `email`, `phone`, `position`, `department`, `hire_date`, `salary`, `status`, `online_status`, `last_activity`, `password`, `profile_picture`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, NULL, 'John', 'Doe', 'john.doe@jetlouge.com', '+63 912 345 6789', 'Software Developer', 'IT', '2023-01-15', 50000.00, 'active', 'online', '2025-10-04 08:51:18', '$2y$12$iEnnofzvZPbVNys16/V4V.MbDWuhSTu/IreFU7L/9gA9GLgbjrUmq', NULL, NULL, '2025-10-04 08:48:29', '2025-10-04 08:48:29'),
(2, NULL, 'Jane', 'Smith', 'jane.smith@jetlouge.com', '+63 917 234 5678', 'HR Manager', 'Human Resources', '2022-06-10', 60000.00, 'active', 'online', NULL, '$2y$12$3Dlt15iRkN.g5whemoZM4uRcK73lXIEqrHPNeGObWf6CYg.MAeYZi', NULL, NULL, '2025-10-04 08:48:30', '2025-10-04 08:48:30'),
(3, NULL, 'Mike', 'Johnson', 'mike.johnson@jetlouge.com', '+63 918 345 6789', 'Accountant', 'Human Resources', '2023-03-20', 45000.00, 'active', 'offline', NULL, '$2y$12$f8wVSon8wHZubJcgj1f3HuiupPITY3C5Zh4XJvUBSLLsKtE1qCT3.', NULL, NULL, '2025-10-04 08:48:30', '2025-10-04 11:25:33'),
(4, NULL, 'Sarah', 'Wilson', 'sarah.wilson@jetlouge.com', '+63 919 456 7890', 'Marketing Specialist', 'Marketing', '2023-08-05', 42000.00, 'active', 'online', NULL, '$2y$12$gk2awDRw6KNK9oYMp/zkQusOWVzIlsrDJ335dRIB9Z.hcPFCjLrS2', NULL, NULL, '2025-10-04 08:48:30', '2025-10-04 11:25:15'),
(5, NULL, 'David', 'Brown', 'david.brown@jetlouge.com', '+63 920 567 8901', 'Sales Representative', 'Sales', '2022-11-12', 40000.00, 'active', 'offline', NULL, '$2y$12$unzkZhZb2MAyNDFDAq2mCebHhseJUJNunZPHJmT6/Vh9FgjmkJjf6', NULL, NULL, '2025-10-04 08:48:31', '2025-10-04 08:53:54'),
(6, NULL, 'Alex', 'Mcqueen', 'alex.mcqueen@gmai.com', '+639162504316', 'Scheduler', 'Human Resources', '2025-10-04', 12.00, 'active', 'offline', NULL, NULL, NULL, NULL, '2025-10-04 08:54:10', '2025-10-04 08:54:10');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
  `reason` text NOT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `approved_by` bigint(20) UNSIGNED DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `admin_notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `leave_requests`
--

INSERT INTO `leave_requests` (`id`, `employee_id`, `leave_type_id`, `start_date`, `end_date`, `days_requested`, `reason`, `status`, `approved_by`, `approved_at`, `admin_notes`, `created_at`, `updated_at`) VALUES
(1, 2, 1, '2025-10-04', '2025-10-06', 3, 'Break', 'approved', NULL, '2025-10-04 09:08:51', NULL, '2025-10-04 08:53:00', '2025-10-04 09:08:51'),
(2, 1, 1, '2025-10-04', '2025-10-05', 2, 'Break', 'rejected', NULL, NULL, NULL, '2025-10-04 09:09:15', '2025-10-04 09:10:49');

-- --------------------------------------------------------

--
-- Table structure for table `leave_types`
--

CREATE TABLE `leave_types` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `code` varchar(10) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `days_allowed` int(11) NOT NULL DEFAULT 0,
  `max_days_per_year` int(11) NOT NULL DEFAULT 0,
  `carry_forward` tinyint(1) NOT NULL DEFAULT 0,
  `requires_approval` tinyint(1) NOT NULL DEFAULT 1,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `leave_types`
--

INSERT INTO `leave_types` (`id`, `name`, `code`, `description`, `days_allowed`, `max_days_per_year`, `carry_forward`, `requires_approval`, `status`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Annual Leave', 'AL', 'Annual vacation leave', 0, 21, 1, 1, 'active', 1, '2025-10-04 08:45:30', '2025-10-04 08:45:30'),
(2, 'Sick Leave', 'SL', 'Medical sick leave', 0, 10, 0, 0, 'active', 1, '2025-10-04 08:45:30', '2025-10-04 08:45:30'),
(3, 'Emergency Leave', 'EL', 'Emergency family leave', 0, 5, 0, 1, 'active', 1, '2025-10-04 08:45:30', '2025-10-04 08:45:30'),
(4, 'Maternity Leave', 'ML', 'Maternity leave', 0, 90, 0, 1, 'active', 1, '2025-10-04 08:45:30', '2025-10-04 08:45:30'),
(5, 'Paternity Leave', 'PL', 'Paternity leave', 0, 7, 0, 1, 'active', 1, '2025-10-04 08:45:30', '2025-10-04 08:45:30');

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
(1, '0001_01_01_000001_create_cache_table', 1),
(2, '0001_01_01_000002_create_jobs_table', 1),
(3, '2025_08_15_112816_create_personal_access_tokens_table', 1),
(4, '2025_08_27_043945_create_sessions_table', 1),
(5, '2025_10_04_143640_create_hr3_authoritative_schema', 1),
(6, '2025_10_04_174200_update_shift_requests_table_for_modal_fields', 2);

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` text NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `shifts`
--

CREATE TABLE `shifts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `employee_id` bigint(20) UNSIGNED NOT NULL,
  `shift_type_id` bigint(20) UNSIGNED DEFAULT NULL,
  `shift_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `break_duration` int(11) NOT NULL DEFAULT 0,
  `status` enum('scheduled','in_progress','completed','cancelled') NOT NULL DEFAULT 'scheduled',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `shifts`
--

INSERT INTO `shifts` (`id`, `employee_id`, `shift_type_id`, `shift_date`, `start_time`, `end_time`, `location`, `break_duration`, `status`, `notes`, `created_at`, `updated_at`) VALUES
(1, 2, 2, '2025-10-09', '14:00:00', '22:00:00', 'Main Office', 0, 'scheduled', NULL, '2025-10-04 08:52:17', '2025-10-04 08:52:17'),
(2, 6, 3, '2025-10-15', '22:00:00', '06:00:00', 'Main Office', 0, 'scheduled', NULL, '2025-10-04 09:07:40', '2025-10-04 09:07:40'),
(3, 5, 1, '2025-10-05', '08:00:00', '16:00:00', 'Main Office', 0, 'scheduled', NULL, '2025-10-04 09:08:01', '2025-10-04 09:08:01');

-- --------------------------------------------------------

--
-- Table structure for table `shift_requests`
--

CREATE TABLE `shift_requests` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `employee_id` bigint(20) UNSIGNED NOT NULL,
  `shift_type_id` bigint(20) UNSIGNED DEFAULT NULL,
  `current_shift_id` bigint(20) UNSIGNED DEFAULT NULL,
  `requested_shift_type_id` bigint(20) UNSIGNED DEFAULT NULL,
  `requested_date` date NOT NULL,
  `shift_date` date DEFAULT NULL,
  `requested_start_time` time DEFAULT NULL,
  `requested_end_time` time DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `hours` decimal(5,2) DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `request_type` enum('swap','change','cancel') NOT NULL DEFAULT 'change',
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `approved_by` bigint(20) UNSIGNED DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `admin_notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `shift_requests`
--

INSERT INTO `shift_requests` (`id`, `employee_id`, `shift_type_id`, `current_shift_id`, `requested_shift_type_id`, `requested_date`, `shift_date`, `requested_start_time`, `requested_end_time`, `start_time`, `end_time`, `location`, `notes`, `hours`, `reason`, `request_type`, `status`, `approved_by`, `approved_at`, `admin_notes`, `created_at`, `updated_at`) VALUES
(1, 1, 1, NULL, NULL, '0000-00-00', '2024-10-05', NULL, NULL, '09:00:00', '17:00:00', 'Main Office', 'Regular morning shift request', 8.00, NULL, 'change', 'rejected', 1, '2025-10-04 09:44:43', NULL, '2025-10-04 09:44:37', '2025-10-04 09:44:43'),
(2, 2, 2, NULL, NULL, '0000-00-00', '2024-10-06', NULL, NULL, '14:00:00', '22:00:00', 'Branch Office', 'Evening shift request', 8.00, NULL, 'change', 'pending', NULL, NULL, NULL, '2025-10-04 09:44:37', '2025-10-04 09:44:37'),
(3, 3, 3, NULL, NULL, '0000-00-00', '2024-10-07', NULL, NULL, '22:00:00', '06:00:00', 'Main Office', 'Night shift request', 8.00, NULL, 'change', 'approved', NULL, NULL, NULL, '2025-10-04 09:44:37', '2025-10-04 09:44:37'),
(4, 1, 1, NULL, NULL, '0000-00-00', '2024-10-08', NULL, NULL, '08:00:00', '16:00:00', 'Remote', 'Work from home request', 8.00, NULL, 'change', 'rejected', NULL, NULL, NULL, '2025-10-04 09:44:37', '2025-10-04 09:44:37'),
(5, 2, 2, NULL, NULL, '0000-00-00', '2024-10-09', NULL, NULL, '10:00:00', '18:00:00', 'Main Office', 'Flexible hours request', 8.00, NULL, 'change', 'pending', NULL, NULL, NULL, '2025-10-04 09:44:37', '2025-10-04 09:44:37');

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
  `break_duration` int(11) NOT NULL DEFAULT 0,
  `hourly_rate` decimal(8,2) DEFAULT NULL,
  `color_code` varchar(7) NOT NULL DEFAULT '#007bff',
  `type` enum('day','night','swing','split','rotating') NOT NULL DEFAULT 'day',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `shift_types`
--

INSERT INTO `shift_types` (`id`, `name`, `code`, `description`, `default_start_time`, `default_end_time`, `break_duration`, `hourly_rate`, `color_code`, `type`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Morning Shift', 'MORNING', 'Standard morning shift for regular operations', '08:00:00', '16:00:00', 60, 25.00, '#28a745', 'day', 1, '2025-10-04 08:45:30', '2025-10-04 08:45:30'),
(2, 'Afternoon Shift', 'AFTERNOON', 'Afternoon to evening coverage shift', '14:00:00', '22:00:00', 45, 27.50, '#ffc107', 'swing', 1, '2025-10-04 08:45:30', '2025-10-04 08:45:30'),
(3, 'Night Shift', 'NIGHT', 'Overnight shift with premium pay', '22:00:00', '06:00:00', 60, 32.00, '#6f42c1', 'night', 1, '2025-10-04 08:45:30', '2025-10-04 08:45:30'),
(4, 'Split Shift', 'SPLIT', 'Split shift with extended break period', '09:00:00', '17:00:00', 120, 24.00, '#17a2b8', 'split', 1, '2025-10-04 08:45:30', '2025-10-04 08:45:30'),
(5, 'Weekend Shift', 'WEEKEND', 'Weekend coverage with rotating schedule', '10:00:00', '18:00:00', 45, 30.00, '#fd7e14', 'rotating', 1, '2025-10-04 08:45:30', '2025-10-04 08:45:30');

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
  `hours_worked` decimal(5,2) DEFAULT NULL,
  `overtime_hours` decimal(5,2) NOT NULL DEFAULT 0.00,
  `break_duration` int(11) NOT NULL DEFAULT 0,
  `description` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `approved_by` bigint(20) UNSIGNED DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `time_entries`
--

INSERT INTO `time_entries` (`id`, `employee_id`, `work_date`, `clock_in_time`, `clock_out_time`, `hours_worked`, `overtime_hours`, `break_duration`, `description`, `notes`, `status`, `approved_by`, `approved_at`, `created_at`, `updated_at`) VALUES
(1, 1, '2025-10-04', '16:51:20', '16:51:37', 0.00, 0.00, 0, NULL, NULL, 'pending', NULL, NULL, '2025-10-04 08:51:20', '2025-10-04 08:51:37');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `role` enum('admin','hr','employee') NOT NULL DEFAULT 'employee',
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `phone`, `profile_picture`, `role`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Jonnylito Duyanon', 'johnkaizer19.jh@gmail.com', NULL, '$2y$12$xJwZTn5SB.8JGPr2PA6MMuQTYESxztXxyU9Qrg3Wpaifaynq1z8PS', '09225523129', NULL, 'employee', NULL, '2025-10-04 08:49:48', '2025-10-04 08:49:48'),
(2, 'Brylle Cillo', 'Brylle.Cil@gmai.com', NULL, '$2y$12$Zvyj9dAj6CnN0EjiaM9mRuZHE6MW6G5myHq5rRSEGWjvCiI3IUdbu', '09129384723', NULL, 'employee', NULL, '2025-10-04 10:11:55', '2025-10-04 10:11:55');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `ai_generated_timesheets`
--
ALTER TABLE `ai_generated_timesheets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ai_generated_timesheets_employee_id_week_start_date_unique` (`employee_id`,`week_start_date`),
  ADD KEY `ai_generated_timesheets_approved_by_foreign` (`approved_by`),
  ADD KEY `ai_generated_timesheets_employee_id_week_start_date_index` (`employee_id`,`week_start_date`),
  ADD KEY `ai_generated_timesheets_status_generated_at_index` (`status`,`generated_at`);

--
-- Indexes for table `attendances`
--
ALTER TABLE `attendances`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `attendances_employee_id_date_unique` (`employee_id`,`date`),
  ADD KEY `attendances_employee_id_date_index` (`employee_id`,`date`),
  ADD KEY `attendances_date_index` (`date`),
  ADD KEY `attendances_status_index` (`status`);

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
  ADD KEY `claims_claim_type_id_foreign` (`claim_type_id`),
  ADD KEY `claims_approved_by_foreign` (`approved_by`),
  ADD KEY `claims_employee_id_status_index` (`employee_id`,`status`),
  ADD KEY `claims_status_index` (`status`);

--
-- Indexes for table `claim_types`
--
ALTER TABLE `claim_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `claim_types_code_unique` (`code`),
  ADD KEY `claim_types_is_active_index` (`is_active`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `employees_email_unique` (`email`),
  ADD UNIQUE KEY `employees_employee_number_unique` (`employee_number`),
  ADD KEY `employees_email_index` (`email`),
  ADD KEY `employees_status_index` (`status`),
  ADD KEY `employees_department_index` (`department`),
  ADD KEY `employees_online_status_index` (`online_status`);

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
-- Indexes for table `leave_requests`
--
ALTER TABLE `leave_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `leave_requests_leave_type_id_foreign` (`leave_type_id`),
  ADD KEY `leave_requests_approved_by_foreign` (`approved_by`),
  ADD KEY `leave_requests_employee_id_status_index` (`employee_id`,`status`),
  ADD KEY `leave_requests_status_index` (`status`);

--
-- Indexes for table `leave_types`
--
ALTER TABLE `leave_types`
  ADD PRIMARY KEY (`id`),
  ADD KEY `leave_types_is_active_index` (`is_active`),
  ADD KEY `leave_types_status_index` (`status`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  ADD KEY `personal_access_tokens_expires_at_index` (`expires_at`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `shifts`
--
ALTER TABLE `shifts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `shifts_shift_type_id_foreign` (`shift_type_id`),
  ADD KEY `shifts_employee_id_shift_date_index` (`employee_id`,`shift_date`),
  ADD KEY `shifts_shift_date_index` (`shift_date`),
  ADD KEY `shifts_status_index` (`status`);

--
-- Indexes for table `shift_requests`
--
ALTER TABLE `shift_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `shift_requests_current_shift_id_foreign` (`current_shift_id`),
  ADD KEY `shift_requests_requested_shift_type_id_foreign` (`requested_shift_type_id`),
  ADD KEY `shift_requests_approved_by_foreign` (`approved_by`),
  ADD KEY `shift_requests_employee_id_status_index` (`employee_id`,`status`),
  ADD KEY `shift_requests_status_index` (`status`),
  ADD KEY `shift_requests_requested_date_index` (`requested_date`);

--
-- Indexes for table `shift_types`
--
ALTER TABLE `shift_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `shift_types_code_unique` (`code`),
  ADD KEY `shift_types_is_active_index` (`is_active`),
  ADD KEY `shift_types_type_index` (`type`);

--
-- Indexes for table `time_entries`
--
ALTER TABLE `time_entries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `time_entries_approved_by_foreign` (`approved_by`),
  ADD KEY `time_entries_employee_id_work_date_index` (`employee_id`,`work_date`),
  ADD KEY `time_entries_status_index` (`status`),
  ADD KEY `time_entries_work_date_index` (`work_date`);

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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `attendances`
--
ALTER TABLE `attendances`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `claims`
--
ALTER TABLE `claims`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `claim_types`
--
ALTER TABLE `claim_types`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `leave_requests`
--
ALTER TABLE `leave_requests`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `leave_types`
--
ALTER TABLE `leave_types`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sessions`
--
ALTER TABLE `sessions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `shifts`
--
ALTER TABLE `shifts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `shift_requests`
--
ALTER TABLE `shift_requests`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `shift_types`
--
ALTER TABLE `shift_types`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `time_entries`
--
ALTER TABLE `time_entries`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `ai_generated_timesheets`
--
ALTER TABLE `ai_generated_timesheets`
  ADD CONSTRAINT `ai_generated_timesheets_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `employees` (`id`),
  ADD CONSTRAINT `ai_generated_timesheets_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `attendances`
--
ALTER TABLE `attendances`
  ADD CONSTRAINT `attendances_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `claims`
--
ALTER TABLE `claims`
  ADD CONSTRAINT `claims_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `employees` (`id`),
  ADD CONSTRAINT `claims_claim_type_id_foreign` FOREIGN KEY (`claim_type_id`) REFERENCES `claim_types` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `claims_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `leave_requests`
--
ALTER TABLE `leave_requests`
  ADD CONSTRAINT `leave_requests_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `employees` (`id`),
  ADD CONSTRAINT `leave_requests_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `leave_requests_leave_type_id_foreign` FOREIGN KEY (`leave_type_id`) REFERENCES `leave_types` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `shifts`
--
ALTER TABLE `shifts`
  ADD CONSTRAINT `shifts_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `shifts_shift_type_id_foreign` FOREIGN KEY (`shift_type_id`) REFERENCES `shift_types` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `shift_requests`
--
ALTER TABLE `shift_requests`
  ADD CONSTRAINT `shift_requests_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `employees` (`id`),
  ADD CONSTRAINT `shift_requests_current_shift_id_foreign` FOREIGN KEY (`current_shift_id`) REFERENCES `shifts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `shift_requests_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `shift_requests_requested_shift_type_id_foreign` FOREIGN KEY (`requested_shift_type_id`) REFERENCES `shift_types` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `time_entries`
--
ALTER TABLE `time_entries`
  ADD CONSTRAINT `time_entries_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `employees` (`id`),
  ADD CONSTRAINT `time_entries_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
