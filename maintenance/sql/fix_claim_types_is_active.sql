-- Fix claim_types table - Add missing is_active column
-- Copy and paste this into phpMyAdmin SQL tab

USE hr3systemdb;

-- Check current table structure
DESCRIBE claim_types;

-- Add is_active column
ALTER TABLE claim_types 
ADD COLUMN is_active BOOLEAN DEFAULT TRUE AFTER requires_receipt;

-- Update existing records to be active
UPDATE claim_types SET is_active = TRUE WHERE is_active IS NULL;

-- Verify the fix worked
DESCRIBE claim_types;

-- Test the original failing query
SELECT * FROM claim_types WHERE is_active = 1;
