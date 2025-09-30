-- Fix time_entries table - Add missing status column
-- Copy and paste this into phpMyAdmin or MySQL command line

USE hr3systemdb;

-- Add status column to time_entries table if it doesn't exist
ALTER TABLE time_entries 
ADD COLUMN IF NOT EXISTS status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending' AFTER total_hours;

-- Add other missing columns that might be needed
ALTER TABLE time_entries 
ADD COLUMN IF NOT EXISTS notes TEXT AFTER status;

ALTER TABLE time_entries 
ADD COLUMN IF NOT EXISTS approved_by INT AFTER notes;

ALTER TABLE time_entries 
ADD COLUMN IF NOT EXISTS approved_at TIMESTAMP NULL AFTER approved_by;

-- Update existing records to have pending status if null
UPDATE time_entries SET status = 'pending' WHERE status IS NULL;

-- Verify the table structure
DESCRIBE time_entries;

-- Test the query that was failing
SELECT COUNT(*) as aggregate FROM time_entries WHERE status = 'pending';
