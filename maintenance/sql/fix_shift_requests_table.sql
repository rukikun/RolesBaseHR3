-- Fix shift_requests table by adding missing columns
-- Run this SQL script in your MySQL database

USE hr_system;

-- Check if columns exist and add them if missing
SET @sql = (SELECT IF(
    (SELECT COUNT(*)
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE table_name = 'shift_requests'
        AND table_schema = 'hr_system'
        AND column_name = 'approved_by') = 0,
    'ALTER TABLE shift_requests ADD COLUMN approved_by BIGINT UNSIGNED NULL AFTER status',
    'SELECT "approved_by column already exists"'));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
    (SELECT COUNT(*)
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE table_name = 'shift_requests'
        AND table_schema = 'hr_system'
        AND column_name = 'approved_at') = 0,
    'ALTER TABLE shift_requests ADD COLUMN approved_at TIMESTAMP NULL AFTER approved_by',
    'SELECT "approved_at column already exists"'));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add foreign key constraint for approved_by if it doesn't exist
SET @sql = (SELECT IF(
    (SELECT COUNT(*)
        FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
        WHERE table_name = 'shift_requests'
        AND table_schema = 'hr_system'
        AND constraint_name = 'shift_requests_approved_by_foreign') = 0,
    'ALTER TABLE shift_requests ADD CONSTRAINT shift_requests_approved_by_foreign FOREIGN KEY (approved_by) REFERENCES employees(id) ON DELETE SET NULL',
    'SELECT "Foreign key constraint already exists"'));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Verify the table structure
DESCRIBE shift_requests;
