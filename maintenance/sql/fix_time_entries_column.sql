-- Fix time_entries table - Add entry_date column or rename work_date to entry_date
-- Run this in phpMyAdmin or MySQL command line

USE hr3systemdb;

-- Check current table structure
DESCRIBE time_entries;

-- Option 1: If work_date column exists, rename it to entry_date
ALTER TABLE time_entries CHANGE COLUMN work_date entry_date DATE NOT NULL;

-- Option 2: If no date column exists, add entry_date column
-- ALTER TABLE time_entries ADD COLUMN entry_date DATE NOT NULL DEFAULT (CURDATE());

-- Verify the fix worked
DESCRIBE time_entries;

-- Test the query that was failing
SELECT COUNT(*) as aggregate FROM time_entries WHERE DATE(entry_date) = CURDATE() AND clock_in IS NOT NULL;
