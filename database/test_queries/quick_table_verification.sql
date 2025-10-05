-- =====================================================
-- HR3 System - Quick Table Verification Tests
-- =====================================================
-- Quick verification queries to test if all tables are
-- working correctly with sample data
-- =====================================================

-- Test all main tables with basic data
SELECT '=== QUICK TABLE VERIFICATION TESTS ===' as test_header;

-- 1. Users Table
SELECT 'USERS TABLE' as table_name, COUNT(*) as record_count FROM users;
SELECT id, name, email, role FROM users LIMIT 3;

-- 2. Employees Table  
SELECT 'EMPLOYEES TABLE' as table_name, COUNT(*) as record_count FROM employees;
SELECT id, CONCAT(first_name, ' ', last_name) as name, department, status FROM employees LIMIT 3;

-- 3. Time Entries Table
SELECT 'TIME_ENTRIES TABLE' as table_name, COUNT(*) as record_count FROM time_entries;
SELECT te.id, CONCAT(e.first_name, ' ', e.last_name) as employee, te.work_date, te.status 
FROM time_entries te 
JOIN employees e ON te.employee_id = e.id 
LIMIT 3;

-- 4. Attendances Table
SELECT 'ATTENDANCES TABLE' as table_name, COUNT(*) as record_count FROM attendances;
SELECT a.id, CONCAT(e.first_name, ' ', e.last_name) as employee, a.date, a.status 
FROM attendances a 
JOIN employees e ON a.employee_id = e.id 
LIMIT 3;

-- 5. Shift Types Table
SELECT 'SHIFT_TYPES TABLE' as table_name, COUNT(*) as record_count FROM shift_types;
SELECT id, name, code, default_start_time, default_end_time FROM shift_types WHERE is_active = 1 LIMIT 3;

-- 6. Shifts Table
SELECT 'SHIFTS TABLE' as table_name, COUNT(*) as record_count FROM shifts;
SELECT s.id, CONCAT(e.first_name, ' ', e.last_name) as employee, st.name as shift_type, s.shift_date 
FROM shifts s 
JOIN employees e ON s.employee_id = e.id 
LEFT JOIN shift_types st ON s.shift_type_id = st.id 
LIMIT 3;

-- 7. Shift Requests Table
SELECT 'SHIFT_REQUESTS TABLE' as table_name, COUNT(*) as record_count FROM shift_requests;
SELECT sr.id, CONCAT(e.first_name, ' ', e.last_name) as employee, sr.shift_date, sr.status 
FROM shift_requests sr 
JOIN employees e ON sr.employee_id = e.id 
LIMIT 3;

-- 8. Leave Types Table
SELECT 'LEAVE_TYPES TABLE' as table_name, COUNT(*) as record_count FROM leave_types;
SELECT id, name, code, max_days_per_year FROM leave_types WHERE is_active = 1 LIMIT 3;

-- 9. Leave Requests Table
SELECT 'LEAVE_REQUESTS TABLE' as table_name, COUNT(*) as record_count FROM leave_requests;
SELECT lr.id, CONCAT(e.first_name, ' ', e.last_name) as employee, lt.name as leave_type, lr.status 
FROM leave_requests lr 
JOIN employees e ON lr.employee_id = e.id 
JOIN leave_types lt ON lr.leave_type_id = lt.id 
LIMIT 3;

-- 10. Claim Types Table
SELECT 'CLAIM_TYPES TABLE' as table_name, COUNT(*) as record_count FROM claim_types;
SELECT id, name, code, max_amount FROM claim_types WHERE is_active = 1 LIMIT 3;

-- 11. Claims Table
SELECT 'CLAIMS TABLE' as table_name, COUNT(*) as record_count FROM claims;
SELECT c.id, CONCAT(e.first_name, ' ', e.last_name) as employee, ct.name as claim_type, c.amount, c.status 
FROM claims c 
JOIN employees e ON c.employee_id = e.id 
JOIN claim_types ct ON c.claim_type_id = ct.id 
LIMIT 3;

-- 12. AI Generated Timesheets Table
SELECT 'AI_GENERATED_TIMESHEETS TABLE' as table_name, COUNT(*) as record_count FROM ai_generated_timesheets;
SELECT id, employee_name, department, week_start_date, total_hours, status FROM ai_generated_timesheets LIMIT 3;

-- Summary Statistics
SELECT '=== SUMMARY STATISTICS ===' as summary_header;
SELECT 
    'Active Employees' as metric, 
    COUNT(*) as count 
FROM employees WHERE status = 'active'
UNION ALL
SELECT 
    'Pending Time Entries' as metric, 
    COUNT(*) as count 
FROM time_entries WHERE status = 'pending'
UNION ALL
SELECT 
    'Recent Attendances (Today)' as metric, 
    COUNT(*) as count 
FROM attendances WHERE date = CURDATE()
UNION ALL
SELECT 
    'Active Shift Types' as metric, 
    COUNT(*) as count 
FROM shift_types WHERE is_active = 1
UNION ALL
SELECT 
    'Pending Leave Requests' as metric, 
    COUNT(*) as count 
FROM leave_requests WHERE status = 'pending'
UNION ALL
SELECT 
    'Pending Claims' as metric, 
    COUNT(*) as count 
FROM claims WHERE status = 'pending';

SELECT '=== QUICK VERIFICATION COMPLETED ===' as completion_message;
