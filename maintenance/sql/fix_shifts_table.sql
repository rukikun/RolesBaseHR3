-- Fix shifts table - add missing is_active column
USE hr3systemdb;

-- Add is_active column to shifts table if it doesn't exist
ALTER TABLE shifts ADD COLUMN IF NOT EXISTS is_active BOOLEAN DEFAULT TRUE;

-- Update existing records to be active
UPDATE shifts SET is_active = TRUE WHERE is_active IS NULL;

SELECT 'Shifts table fixed - is_active column added!' as Result;
