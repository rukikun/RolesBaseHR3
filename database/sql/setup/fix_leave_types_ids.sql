-- Fix Leave Types ID Issue
-- This script fixes the ID = 0 problem and ensures proper auto-increment

-- First, let's see the current state
SELECT 'Current leave_types table structure:' as info;
DESCRIBE leave_types;

SELECT 'Current records with ID issues:' as info;
SELECT id, name, code FROM leave_types ORDER BY id;

-- Step 1: Create a backup table
DROP TABLE IF EXISTS leave_types_backup;
CREATE TABLE leave_types_backup AS SELECT * FROM leave_types;
SELECT 'Backup created with', COUNT(*), 'records' FROM leave_types_backup;

-- Step 2: Delete all records with ID = 0 (these are problematic)
DELETE FROM leave_types WHERE id = 0;
SELECT 'Deleted records with ID = 0' as info;

-- Step 3: Reset the auto-increment counter
ALTER TABLE leave_types AUTO_INCREMENT = 1;
SELECT 'Auto-increment reset to 1' as info;

-- Step 4: Ensure the table has proper structure
ALTER TABLE leave_types MODIFY COLUMN id INT AUTO_INCREMENT PRIMARY KEY;
SELECT 'Table structure fixed' as info;

-- Step 5: Clean up any remaining duplicates by name
DELETE t1 FROM leave_types t1
INNER JOIN leave_types t2 
WHERE t1.id > t2.id AND t1.name = t2.name;
SELECT 'Duplicates removed' as info;

-- Step 6: Insert clean standard leave types if table is empty or has issues
INSERT IGNORE INTO leave_types (name, code, description, max_days_per_year, carry_forward, requires_approval, is_active) VALUES
('Annual Leave', 'AL', 'Annual vacation leave', 21, 1, 1, 1),
('Sick Leave', 'SL', 'Medical sick leave', 10, 0, 0, 1),
('Emergency Leave', 'EL', 'Emergency family leave', 5, 0, 1, 1),
('Maternity Leave', 'ML', 'Maternity leave', 90, 0, 1, 1),
('Paternity Leave', 'PL', 'Paternity leave', 7, 0, 1, 1);

-- Step 7: Verify the fix
SELECT 'Final verification - Leave types with proper IDs:' as info;
SELECT id, name, code, max_days_per_year, carry_forward, requires_approval, is_active 
FROM leave_types 
ORDER BY id;

-- Step 8: Check auto-increment status
SELECT 'Auto-increment status:' as info;
SELECT AUTO_INCREMENT 
FROM information_schema.TABLES 
WHERE TABLE_SCHEMA = 'hr3systemdb' 
AND TABLE_NAME = 'leave_types';

-- Step 9: Add unique constraint to prevent future issues
ALTER TABLE leave_types ADD CONSTRAINT unique_leave_name_code UNIQUE (name, code);
SELECT 'Unique constraint added to prevent duplicates' as info;
