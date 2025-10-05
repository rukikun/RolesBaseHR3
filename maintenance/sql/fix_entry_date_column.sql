-- Fix time_entries table - Add missing entry_date column
-- Copy and paste this into phpMyAdmin SQL tab

USE hr3systemdb;

-- Check current table structure
DESCRIBE time_entries;

-- Add entry_date column (same as date column for compatibility)
ALTER TABLE time_entries 
ADD COLUMN entry_date DATE AFTER date;

-- Update entry_date with values from date column
UPDATE time_entries SET entry_date = date WHERE entry_date IS NULL;

-- Verify the fix worked
DESCRIBE time_entries;

-- Test the original failing query
SELECT * FROM time_entries ORDER BY entry_date DESC, created_at DESC LIMIT 10;
