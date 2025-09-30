-- Fix leave_types table - Add missing is_active column
-- Copy and paste this into phpMyAdmin SQL tab

USE hr3systemdb;

-- Check current table structure
DESCRIBE leave_types;

-- Add is_active column
ALTER TABLE leave_types 
ADD COLUMN is_active BOOLEAN DEFAULT TRUE AFTER carry_forward;

-- Update existing records to be active
UPDATE leave_types SET is_active = TRUE WHERE is_active IS NULL;

-- Verify the fix worked
DESCRIBE leave_types;

-- Test the original failing query
SELECT * FROM leave_types WHERE is_active = 1;
