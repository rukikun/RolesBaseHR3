-- =====================================================
-- HR3 System - Comprehensive Table Test Queries
-- =====================================================
-- This file contains test queries for all tables to verify
-- the database structure and data integrity after migration
-- separation and controller updates.
--
-- Based on reference data from hr3_hr3systemdb.sql
-- Generated: October 4, 2025
-- =====================================================

-- Set SQL mode for consistent behavior
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- =====================================================
-- 1. USERS TABLE TESTS
-- =====================================================

-- Test 1.1: Basic user data retrieval
SELECT 'TEST 1.1: Users Table - Basic Data' as test_name;
SELECT 
    id,
    name,
    email,
    role,
    phone,
    created_at
FROM users 
ORDER BY id;

-- Test 1.2: User role distribution
SELECT 'TEST 1.2: Users Table - Role Distribution' as test_name;
SELECT 
    role,
    COUNT(*) as user_count
FROM users 
GROUP BY role;

-- =====================================================
-- 2. EMPLOYEES TABLE TESTS
-- =====================================================

-- Test 2.1: Active employees with full details
SELECT 'TEST 2.1: Employees Table - Active Employees' as test_name;
SELECT 
    id,
    CONCAT(first_name, ' ', last_name) as full_name,
    email,
    position,
    department,
    hire_date,
    salary,
    status,
    online_status
FROM employees 
WHERE status = 'active'
ORDER BY department, last_name;

-- Test 2.2: Department statistics
SELECT 'TEST 2.2: Employees Table - Department Statistics' as test_name;
SELECT 
    department,
    COUNT(*) as employee_count,
    AVG(salary) as avg_salary,
    MIN(hire_date) as earliest_hire,
    MAX(hire_date) as latest_hire
FROM employees 
WHERE status = 'active'
GROUP BY department
ORDER BY employee_count DESC;

-- Test 2.3: Online status check
SELECT 'TEST 2.3: Employees Table - Online Status' as test_name;
SELECT 
    online_status,
    COUNT(*) as count
FROM employees 
WHERE status = 'active'
GROUP BY online_status;

-- =====================================================
-- 3. TIME ENTRIES TABLE TESTS
-- =====================================================

-- Test 3.1: Recent time entries with employee info
SELECT 'TEST 3.1: Time Entries - Recent Entries with Employee Info' as test_name;
SELECT 
    te.id,
    CONCAT(e.first_name, ' ', e.last_name) as employee_name,
    te.work_date,
    te.clock_in_time,
    te.clock_out_time,
    te.hours_worked,
    te.overtime_hours,
    te.status
FROM time_entries te
JOIN employees e ON te.employee_id = e.id
ORDER BY te.work_date DESC, te.created_at DESC;

-- Test 3.2: Time entries status summary
SELECT 'TEST 3.2: Time Entries - Status Summary' as test_name;
SELECT 
    status,
    COUNT(*) as entry_count,
    SUM(hours_worked) as total_hours,
    SUM(overtime_hours) as total_overtime
FROM time_entries
GROUP BY status;

-- =====================================================
-- 4. ATTENDANCES TABLE TESTS
-- =====================================================

-- Test 4.1: Recent attendance records with employee details
SELECT 'TEST 4.1: Attendances - Recent Records with Employee Details' as test_name;
SELECT 
    a.id,
    CONCAT(e.first_name, ' ', e.last_name) as employee_name,
    a.date as attendance_date,
    a.clock_in_time,
    a.clock_out_time,
    a.total_hours,
    a.overtime_hours,
    a.status,
    a.location,
    a.ip_address
FROM attendances a
JOIN employees e ON a.employee_id = e.id
ORDER BY a.date DESC, a.clock_in_time DESC;

-- Test 4.2: Attendance status distribution
SELECT 'TEST 4.2: Attendances - Status Distribution' as test_name;
SELECT 
    status,
    COUNT(*) as count,
    AVG(total_hours) as avg_hours
FROM attendances
GROUP BY status;

-- Test 4.3: Daily attendance summary
SELECT 'TEST 4.3: Attendances - Daily Summary' as test_name;
SELECT 
    date as attendance_date,
    COUNT(*) as employees_present,
    SUM(total_hours) as total_hours_worked,
    AVG(total_hours) as avg_hours_per_employee
FROM attendances
WHERE status IN ('present', 'clocked_out')
GROUP BY date
ORDER BY date DESC;

-- =====================================================
-- 5. SHIFT TYPES TABLE TESTS
-- =====================================================

-- Test 5.1: All shift types with details
SELECT 'TEST 5.1: Shift Types - All Active Types' as test_name;
SELECT 
    id,
    name,
    code,
    description,
    default_start_time,
    default_end_time,
    break_duration,
    hourly_rate,
    color_code,
    type,
    is_active
FROM shift_types
WHERE is_active = 1
ORDER BY default_start_time;

-- Test 5.2: Shift type statistics
SELECT 'TEST 5.2: Shift Types - Statistics' as test_name;
SELECT 
    type,
    COUNT(*) as shift_type_count,
    AVG(hourly_rate) as avg_hourly_rate,
    AVG(break_duration) as avg_break_minutes
FROM shift_types
WHERE is_active = 1
GROUP BY type;

-- =====================================================
-- 6. SHIFTS TABLE TESTS
-- =====================================================

-- Test 6.1: Current shifts with employee and shift type info
SELECT 'TEST 6.1: Shifts - Current Assignments with Details' as test_name;
SELECT 
    s.id,
    CONCAT(e.first_name, ' ', e.last_name) as employee_name,
    st.name as shift_type_name,
    s.shift_date,
    s.start_time,
    s.end_time,
    s.location,
    s.status,
    s.notes
FROM shifts s
JOIN employees e ON s.employee_id = e.id
LEFT JOIN shift_types st ON s.shift_type_id = st.id
ORDER BY s.shift_date DESC, s.start_time;

-- Test 6.2: Shift status distribution
SELECT 'TEST 6.2: Shifts - Status Distribution' as test_name;
SELECT 
    status,
    COUNT(*) as shift_count
FROM shifts
GROUP BY status;

-- Test 6.3: Upcoming shifts by shift type
SELECT 'TEST 6.3: Shifts - Upcoming by Shift Type' as test_name;
SELECT 
    st.name as shift_type_name,
    COUNT(*) as upcoming_shifts,
    MIN(s.shift_date) as earliest_date,
    MAX(s.shift_date) as latest_date
FROM shifts s
JOIN shift_types st ON s.shift_type_id = st.id
WHERE s.shift_date >= CURDATE()
GROUP BY st.id, st.name
ORDER BY st.name;

-- =====================================================
-- 7. SHIFT REQUESTS TABLE TESTS
-- =====================================================

-- Test 7.1: Shift requests with employee details
SELECT 'TEST 7.1: Shift Requests - All Requests with Employee Details' as test_name;
SELECT 
    sr.id,
    CONCAT(e.first_name, ' ', e.last_name) as employee_name,
    sr.requested_date,
    sr.shift_date,
    sr.start_time,
    sr.end_time,
    sr.location,
    sr.hours,
    sr.request_type,
    sr.status,
    sr.reason,
    sr.admin_notes
FROM shift_requests sr
JOIN employees e ON sr.employee_id = e.id
ORDER BY sr.created_at DESC;

-- Test 7.2: Shift request status summary
SELECT 'TEST 7.2: Shift Requests - Status Summary' as test_name;
SELECT 
    status,
    request_type,
    COUNT(*) as request_count,
    AVG(hours) as avg_hours
FROM shift_requests
GROUP BY status, request_type
ORDER BY status, request_type;

-- =====================================================
-- 8. LEAVE TYPES TABLE TESTS
-- =====================================================

-- Test 8.1: All leave types with policies
SELECT 'TEST 8.1: Leave Types - All Active Types with Policies' as test_name;
SELECT 
    id,
    name,
    code,
    description,
    days_allowed,
    max_days_per_year,
    carry_forward,
    requires_approval,
    status,
    is_active
FROM leave_types
WHERE is_active = 1
ORDER BY name;

-- Test 8.2: Leave type policy summary
SELECT 'TEST 8.2: Leave Types - Policy Summary' as test_name;
SELECT 
    COUNT(*) as total_leave_types,
    SUM(max_days_per_year) as total_annual_days,
    AVG(max_days_per_year) as avg_days_per_type,
    SUM(CASE WHEN carry_forward = 1 THEN 1 ELSE 0 END) as carry_forward_types,
    SUM(CASE WHEN requires_approval = 1 THEN 1 ELSE 0 END) as approval_required_types
FROM leave_types
WHERE is_active = 1;

-- =====================================================
-- 9. LEAVE REQUESTS TABLE TESTS
-- =====================================================

-- Test 9.1: Leave requests with employee and leave type info
SELECT 'TEST 9.1: Leave Requests - All Requests with Details' as test_name;
SELECT 
    lr.id,
    CONCAT(e.first_name, ' ', e.last_name) as employee_name,
    lt.name as leave_type_name,
    lr.start_date,
    lr.end_date,
    lr.days_requested,
    lr.reason,
    lr.status,
    lr.approved_at,
    lr.admin_notes
FROM leave_requests lr
JOIN employees e ON lr.employee_id = e.id
JOIN leave_types lt ON lr.leave_type_id = lt.id
ORDER BY lr.created_at DESC;

-- Test 9.2: Leave request statistics
SELECT 'TEST 9.2: Leave Requests - Statistics' as test_name;
SELECT 
    lt.name as leave_type,
    lr.status,
    COUNT(*) as request_count,
    SUM(lr.days_requested) as total_days_requested,
    AVG(lr.days_requested) as avg_days_per_request
FROM leave_requests lr
JOIN leave_types lt ON lr.leave_type_id = lt.id
GROUP BY lt.name, lr.status
ORDER BY lt.name, lr.status;

-- =====================================================
-- 10. CLAIM TYPES TABLE TESTS
-- =====================================================

-- Test 10.1: All claim types with policies
SELECT 'TEST 10.1: Claim Types - All Active Types with Policies' as test_name;
SELECT 
    id,
    name,
    code,
    description,
    max_amount,
    requires_attachment,
    auto_approve,
    is_active
FROM claim_types
WHERE is_active = 1
ORDER BY name;

-- Test 10.2: Claim type policy analysis
SELECT 'TEST 10.2: Claim Types - Policy Analysis' as test_name;
SELECT 
    COUNT(*) as total_claim_types,
    SUM(max_amount) as total_max_amount,
    AVG(max_amount) as avg_max_amount,
    SUM(CASE WHEN requires_attachment = 1 THEN 1 ELSE 0 END) as attachment_required_types,
    SUM(CASE WHEN auto_approve = 1 THEN 1 ELSE 0 END) as auto_approve_types
FROM claim_types
WHERE is_active = 1;

-- =====================================================
-- 11. CLAIMS TABLE TESTS
-- =====================================================

-- Test 11.1: Claims with employee and claim type details
SELECT 'TEST 11.1: Claims - All Claims with Details' as test_name;
SELECT 
    c.id,
    CONCAT(e.first_name, ' ', e.last_name) as employee_name,
    ct.name as claim_type_name,
    c.amount,
    c.claim_date,
    c.description,
    c.status,
    c.approved_at,
    c.admin_notes
FROM claims c
JOIN employees e ON c.employee_id = e.id
JOIN claim_types ct ON c.claim_type_id = ct.id
ORDER BY c.claim_date DESC;

-- Test 11.2: Claims financial summary
SELECT 'TEST 11.2: Claims - Financial Summary' as test_name;
SELECT 
    ct.name as claim_type,
    c.status,
    COUNT(*) as claim_count,
    SUM(c.amount) as total_amount,
    AVG(c.amount) as avg_amount,
    MIN(c.amount) as min_amount,
    MAX(c.amount) as max_amount
FROM claims c
JOIN claim_types ct ON c.claim_type_id = ct.id
GROUP BY ct.name, c.status
ORDER BY ct.name, c.status;

-- =====================================================
-- 12. AI GENERATED TIMESHEETS TABLE TESTS
-- =====================================================

-- Test 12.1: AI timesheets with employee details
SELECT 'TEST 12.1: AI Timesheets - All Generated Timesheets' as test_name;
SELECT 
    ait.id,
    ait.employee_name,
    ait.department,
    ait.week_start_date,
    ait.total_hours,
    ait.overtime_hours,
    ait.status,
    ait.generated_at,
    ait.approved_at,
    CONCAT(e.first_name, ' ', e.last_name) as actual_employee_name
FROM ai_generated_timesheets ait
LEFT JOIN employees e ON ait.employee_id = e.id
ORDER BY ait.week_start_date DESC, ait.created_at DESC;

-- Test 12.2: AI timesheet status and hours summary
SELECT 'TEST 12.2: AI Timesheets - Status and Hours Summary' as test_name;
SELECT 
    status,
    COUNT(*) as timesheet_count,
    SUM(total_hours) as total_hours_sum,
    SUM(overtime_hours) as total_overtime_sum,
    AVG(total_hours) as avg_hours_per_timesheet,
    AVG(overtime_hours) as avg_overtime_per_timesheet
FROM ai_generated_timesheets
GROUP BY status;

-- =====================================================
-- 13. CROSS-TABLE RELATIONSHIP TESTS
-- =====================================================

-- Test 13.1: Employee activity summary (across multiple tables)
SELECT 'TEST 13.1: Cross-Table - Employee Activity Summary' as test_name;
SELECT 
    e.id,
    CONCAT(e.first_name, ' ', e.last_name) as employee_name,
    e.department,
    e.status as employee_status,
    COUNT(DISTINCT te.id) as time_entries_count,
    COUNT(DISTINCT a.id) as attendance_records_count,
    COUNT(DISTINCT s.id) as shift_assignments_count,
    COUNT(DISTINCT lr.id) as leave_requests_count,
    COUNT(DISTINCT c.id) as claims_count,
    COUNT(DISTINCT ait.id) as ai_timesheets_count
FROM employees e
LEFT JOIN time_entries te ON e.id = te.employee_id
LEFT JOIN attendances a ON e.id = a.employee_id
LEFT JOIN shifts s ON e.id = s.employee_id
LEFT JOIN leave_requests lr ON e.id = lr.employee_id
LEFT JOIN claims c ON e.id = c.employee_id
LEFT JOIN ai_generated_timesheets ait ON e.id = ait.employee_id
WHERE e.status = 'active'
GROUP BY e.id, e.first_name, e.last_name, e.department, e.status
ORDER BY e.last_name, e.first_name;

-- Test 13.2: Department workload analysis
SELECT 'TEST 13.2: Cross-Table - Department Workload Analysis' as test_name;
SELECT 
    e.department,
    COUNT(DISTINCT e.id) as employee_count,
    COUNT(DISTINCT s.id) as total_shifts,
    COUNT(DISTINCT lr.id) as leave_requests,
    COUNT(DISTINCT c.id) as claims_submitted,
    SUM(COALESCE(te.hours_worked, 0)) as total_hours_worked,
    SUM(COALESCE(a.total_hours, 0)) as total_attendance_hours
FROM employees e
LEFT JOIN shifts s ON e.id = s.employee_id
LEFT JOIN leave_requests lr ON e.id = lr.employee_id
LEFT JOIN claims c ON e.id = c.employee_id
LEFT JOIN time_entries te ON e.id = te.employee_id
LEFT JOIN attendances a ON e.id = a.employee_id
WHERE e.status = 'active'
GROUP BY e.department
ORDER BY employee_count DESC;

-- Test 13.3: Recent activity across all tables (last 7 days)
SELECT 'TEST 13.3: Cross-Table - Recent Activity (Last 7 Days)' as test_name;
SELECT 
    'Time Entries' as activity_type,
    COUNT(*) as count,
    MAX(created_at) as latest_activity
FROM time_entries 
WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
UNION ALL
SELECT 
    'Attendances' as activity_type,
    COUNT(*) as count,
    MAX(created_at) as latest_activity
FROM attendances 
WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
UNION ALL
SELECT 
    'Shifts' as activity_type,
    COUNT(*) as count,
    MAX(created_at) as latest_activity
FROM shifts 
WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
UNION ALL
SELECT 
    'Leave Requests' as activity_type,
    COUNT(*) as count,
    MAX(created_at) as latest_activity
FROM leave_requests 
WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
UNION ALL
SELECT 
    'Claims' as activity_type,
    COUNT(*) as count,
    MAX(created_at) as latest_activity
FROM claims 
WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
UNION ALL
SELECT 
    'AI Timesheets' as activity_type,
    COUNT(*) as count,
    MAX(created_at) as latest_activity
FROM ai_generated_timesheets 
WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
ORDER BY count DESC;

-- =====================================================
-- 14. DATA INTEGRITY TESTS
-- =====================================================

-- Test 14.1: Foreign key integrity check
SELECT 'TEST 14.1: Data Integrity - Foreign Key Relationships' as test_name;

-- Check for orphaned time entries
SELECT 'Orphaned Time Entries' as check_type, COUNT(*) as count
FROM time_entries te
LEFT JOIN employees e ON te.employee_id = e.id
WHERE e.id IS NULL
UNION ALL
-- Check for orphaned attendances
SELECT 'Orphaned Attendances' as check_type, COUNT(*) as count
FROM attendances a
LEFT JOIN employees e ON a.employee_id = e.id
WHERE e.id IS NULL
UNION ALL
-- Check for orphaned shifts
SELECT 'Orphaned Shifts' as check_type, COUNT(*) as count
FROM shifts s
LEFT JOIN employees e ON s.employee_id = e.id
WHERE e.id IS NULL
UNION ALL
-- Check for orphaned leave requests
SELECT 'Orphaned Leave Requests' as check_type, COUNT(*) as count
FROM leave_requests lr
LEFT JOIN employees e ON lr.employee_id = e.id
WHERE e.id IS NULL
UNION ALL
-- Check for orphaned claims
SELECT 'Orphaned Claims' as check_type, COUNT(*) as count
FROM claims c
LEFT JOIN employees e ON c.employee_id = e.id
WHERE e.id IS NULL;

-- Test 14.2: Data consistency checks
SELECT 'TEST 14.2: Data Integrity - Consistency Checks' as test_name;

-- Check for negative hours
SELECT 'Negative Hours in Time Entries' as check_type, COUNT(*) as count
FROM time_entries
WHERE hours_worked < 0 OR overtime_hours < 0
UNION ALL
SELECT 'Negative Hours in Attendances' as check_type, COUNT(*) as count
FROM attendances
WHERE total_hours < 0 OR overtime_hours < 0
UNION ALL
-- Check for invalid date ranges in leave requests
SELECT 'Invalid Leave Date Ranges' as check_type, COUNT(*) as count
FROM leave_requests
WHERE start_date > end_date
UNION ALL
-- Check for invalid shift times
SELECT 'Invalid Shift Times' as check_type, COUNT(*) as count
FROM shifts
WHERE start_time = end_time;

-- =====================================================
-- 15. PERFORMANCE TEST QUERIES
-- =====================================================

-- Test 15.1: Index usage verification (explain plan tests)
SELECT 'TEST 15.1: Performance - Index Usage Tests' as test_name;

-- This would typically use EXPLAIN, but for compatibility we'll show the queries
SELECT 'Employee lookup by email' as query_type, COUNT(*) as result_count
FROM employees WHERE email = 'john.doe@jetlouge.com'
UNION ALL
SELECT 'Attendance by employee and date' as query_type, COUNT(*) as result_count
FROM attendances WHERE employee_id = 1 AND date = '2025-10-04'
UNION ALL
SELECT 'Time entries by status' as query_type, COUNT(*) as result_count
FROM time_entries WHERE status = 'pending'
UNION ALL
SELECT 'Active shift types' as query_type, COUNT(*) as result_count
FROM shift_types WHERE is_active = 1;

-- =====================================================
-- 16. SUMMARY REPORT
-- =====================================================

-- Test 16.1: Overall system health summary
SELECT 'TEST 16.1: System Health - Overall Summary' as test_name;
SELECT 
    'Total Users' as metric,
    COUNT(*) as value,
    'Active system users' as description
FROM users
UNION ALL
SELECT 
    'Active Employees' as metric,
    COUNT(*) as value,
    'Currently active employees' as description
FROM employees WHERE status = 'active'
UNION ALL
SELECT 
    'Total Time Entries' as metric,
    COUNT(*) as value,
    'All time tracking records' as description
FROM time_entries
UNION ALL
SELECT 
    'Total Attendance Records' as metric,
    COUNT(*) as value,
    'All attendance tracking records' as description
FROM attendances
UNION ALL
SELECT 
    'Active Shift Types' as metric,
    COUNT(*) as value,
    'Available shift configurations' as description
FROM shift_types WHERE is_active = 1
UNION ALL
SELECT 
    'Scheduled Shifts' as metric,
    COUNT(*) as value,
    'All shift assignments' as description
FROM shifts
UNION ALL
SELECT 
    'Pending Approvals' as metric,
    (SELECT COUNT(*) FROM leave_requests WHERE status = 'pending') +
    (SELECT COUNT(*) FROM claims WHERE status = 'pending') +
    (SELECT COUNT(*) FROM shift_requests WHERE status = 'pending') as value,
    'Items requiring approval' as description
FROM dual;

-- =====================================================
-- END OF COMPREHENSIVE TABLE TESTS
-- =====================================================

SELECT '=== COMPREHENSIVE TABLE TESTS COMPLETED ===' as completion_message;
SELECT CONCAT('Test execution completed at: ', NOW()) as execution_time;
