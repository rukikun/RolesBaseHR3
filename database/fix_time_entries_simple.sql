-- Fix time_entries table - Add missing status column (simplified)
-- First check the table structure, then add the status column

USE hr3systemdb;

-- Check current table structure
DESCRIBE time_entries;

-- Add status column without referencing total_hours
ALTER TABLE time_entries 
ADD COLUMN status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending';

-- Add other missing columns
ALTER TABLE time_entries 
ADD COLUMN notes TEXT;

ALTER TABLE time_entries 
ADD COLUMN approved_by INT;

ALTER TABLE time_entries 
ADD COLUMN approved_at TIMESTAMP NULL;

-- Update existing records to have pending status
UPDATE time_entries SET status = 'pending' WHERE status IS NULL;

-- Verify the fix worked
DESCRIBE time_entries;

-- Test the original failing query
SELECT COUNT(*) as aggregate FROM time_entries WHERE status = 'pending';
