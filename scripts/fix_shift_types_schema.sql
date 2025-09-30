-- Fix shift_types table schema to ensure all required columns exist
-- This script will add missing columns if they don't exist

USE hr3systemdb;

-- Add missing columns to shift_types table if they don't exist
ALTER TABLE shift_types 
ADD COLUMN IF NOT EXISTS break_duration INT DEFAULT 30,
ADD COLUMN IF NOT EXISTS hourly_rate DECIMAL(10,2) DEFAULT 0.00,
ADD COLUMN IF NOT EXISTS description TEXT,
ADD COLUMN IF NOT EXISTS color_code VARCHAR(7) DEFAULT '#007bff',
ADD COLUMN IF NOT EXISTS is_active TINYINT(1) DEFAULT 1;

-- Update existing records to have default values for new columns
UPDATE shift_types 
SET 
    break_duration = COALESCE(break_duration, 30),
    hourly_rate = COALESCE(hourly_rate, 0.00),
    description = COALESCE(description, ''),
    color_code = COALESCE(color_code, '#007bff'),
    is_active = COALESCE(is_active, 1)
WHERE break_duration IS NULL 
   OR hourly_rate IS NULL 
   OR description IS NULL 
   OR color_code IS NULL 
   OR is_active IS NULL;

-- Ensure we have some sample data if table is empty
INSERT IGNORE INTO shift_types (id, name, type, default_start_time, default_end_time, break_duration, hourly_rate, description, color_code, is_active, created_at, updated_at) VALUES
(1, 'Morning Shift', 'day', '08:00:00', '16:00:00', 30, 15.00, 'Standard morning shift', '#28a745', 1, NOW(), NOW()),
(2, 'Evening Shift', 'evening', '16:00:00', '00:00:00', 30, 17.00, 'Evening shift with night differential', '#ffc107', 1, NOW(), NOW()),
(3, 'Night Shift', 'night', '00:00:00', '08:00:00', 30, 20.00, 'Night shift with premium pay', '#6f42c1', 1, NOW(), NOW()),
(4, 'Weekend Day', 'weekend', '09:00:00', '17:00:00', 60, 18.00, 'Weekend day shift', '#17a2b8', 1, NOW(), NOW()),
(5, 'Weekend Night', 'weekend', '22:00:00', '06:00:00', 30, 22.00, 'Weekend night shift', '#fd7e14', 1, NOW(), NOW());

SELECT 'Shift types schema fixed and sample data added' as status;
