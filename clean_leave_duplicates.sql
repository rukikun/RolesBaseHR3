-- Clean up duplicate leave types
-- This script removes duplicate leave types and keeps only unique entries

-- First, let's see what duplicates we have
SELECT name, code, COUNT(*) as duplicate_count 
FROM leave_types 
GROUP BY name, code 
HAVING COUNT(*) > 1;

-- Create a temporary table with unique records (keeping the lowest ID for each duplicate)
CREATE TEMPORARY TABLE temp_unique_leave_types AS
SELECT MIN(id) as keep_id, name, code, description, max_days_per_year, carry_forward, requires_approval, is_active, created_at, updated_at
FROM leave_types 
GROUP BY name, code;

-- Delete all records from the original table
DELETE FROM leave_requests WHERE leave_type_id NOT IN (SELECT keep_id FROM temp_unique_leave_types);
DELETE FROM leave_types;

-- Reset auto increment
ALTER TABLE leave_types AUTO_INCREMENT = 1;

-- Insert the unique records back with new sequential IDs
INSERT INTO leave_types (name, code, description, max_days_per_year, carry_forward, requires_approval, is_active, created_at, updated_at)
SELECT name, code, description, max_days_per_year, carry_forward, requires_approval, is_active, created_at, updated_at
FROM temp_unique_leave_types
ORDER BY name;

-- Ensure we have the standard leave types with proper setup
INSERT IGNORE INTO leave_types (name, code, description, max_days_per_year, carry_forward, requires_approval, is_active) VALUES
('Annual Leave', 'AL', 'Annual vacation leave', 21, TRUE, TRUE, TRUE),
('Emergency Leave', 'EL', 'Emergency family leave', 5, FALSE, TRUE, TRUE),
('Maternity Leave', 'ML', 'Maternity leave', 90, FALSE, TRUE, TRUE),
('Paternity Leave', 'PL', 'Paternity leave', 7, FALSE, TRUE, TRUE),
('Sick Leave', 'SL', 'Medical sick leave', 10, FALSE, FALSE, TRUE);

-- Add unique constraint to prevent future duplicates
ALTER TABLE leave_types ADD CONSTRAINT unique_leave_name_code UNIQUE (name, code);

-- Show final results
SELECT 'Cleanup completed. Final leave types:' as message;
SELECT id, name, code, max_days_per_year, carry_forward, requires_approval, is_active 
FROM leave_types 
ORDER BY name;
