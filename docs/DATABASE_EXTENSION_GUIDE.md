# Database Extension Guide for HR3 System

## Database Connection Details
- **Host**: `127.0.0.1` (localhost)
- **Port**: `3306`
- **Database**: `hr3systemdb`
- **Username**: `root`
- **Password**: (empty - default XAMPP setup)

## Key Tables to Explore

### 1. **employees** - Employee Management
```sql
SELECT * FROM employees;
```
- View all employee records
- Check employee status (active/inactive/terminated)
- Verify employee details and departments

### 2. **time_entries** - Timesheet Data
```sql
SELECT te.*, CONCAT(e.first_name, ' ', e.last_name) as employee_name 
FROM time_entries te 
LEFT JOIN employees e ON te.employee_id = e.id 
ORDER BY te.work_date DESC;
```
- View all timesheet entries with employee names
- Check hours worked and overtime
- Monitor approval status

### 3. **leave_requests** - Leave Management
```sql
SELECT lr.*, CONCAT(e.first_name, ' ', e.last_name) as employee_name, lt.name as leave_type
FROM leave_requests lr
LEFT JOIN employees e ON lr.employee_id = e.id
LEFT JOIN leave_types lt ON lr.leave_type_id = lt.id
ORDER BY lr.created_at DESC;
```
- View leave requests with employee and leave type details
- Check approval status and dates

### 4. **claims** - Claims & Reimbursements
```sql
SELECT c.*, CONCAT(e.first_name, ' ', e.last_name) as employee_name, ct.name as claim_type
FROM claims c
LEFT JOIN employees e ON c.employee_id = e.id
LEFT JOIN claim_types ct ON c.claim_type_id = ct.id
ORDER BY c.submitted_date DESC;
```
- View all claims with employee and claim type details
- Check amounts and approval status

### 5. **shifts** - Shift Scheduling
```sql
SELECT s.*, CONCAT(e.first_name, ' ', e.last_name) as employee_name, st.name as shift_type
FROM shifts s
LEFT JOIN employees e ON s.employee_id = e.id
LEFT JOIN shift_types st ON s.shift_type_id = st.id
ORDER BY s.date DESC;
```
- View scheduled shifts with employee and shift type details
- Monitor shift assignments and status

## Quick Verification Queries

### Check Data Counts
```sql
SELECT 
    (SELECT COUNT(*) FROM employees WHERE status = 'active') as active_employees,
    (SELECT COUNT(*) FROM time_entries) as total_timesheets,
    (SELECT COUNT(*) FROM leave_requests WHERE status = 'pending') as pending_leaves,
    (SELECT COUNT(*) FROM claims WHERE status = 'pending') as pending_claims,
    (SELECT COUNT(*) FROM shifts WHERE date >= CURDATE()) as upcoming_shifts;
```

### Recent Activity
```sql
-- Recent timesheet entries
SELECT 'Timesheet' as type, CONCAT(e.first_name, ' ', e.last_name) as employee, te.created_at as created
FROM time_entries te
LEFT JOIN employees e ON te.employee_id = e.id
WHERE te.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)

UNION ALL

-- Recent leave requests
SELECT 'Leave Request' as type, CONCAT(e.first_name, ' ', e.last_name) as employee, lr.created_at as created
FROM leave_requests lr
LEFT JOIN employees e ON lr.employee_id = e.id
WHERE lr.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)

UNION ALL

-- Recent claims
SELECT 'Claim' as type, CONCAT(e.first_name, ' ', e.last_name) as employee, c.created_at as created
FROM claims c
LEFT JOIN employees e ON c.employee_id = e.id
WHERE c.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)

ORDER BY created DESC;
```

## Testing Database Integration

### 1. **Test Form Submissions**
- Go to any HR module (Timesheets, Employees, etc.)
- Add a new record through the web interface
- Check the database extension to see if the record appears
- Verify all fields are populated correctly

### 2. **Test Updates**
- Edit an existing record through the web interface
- Refresh the database view to see the changes
- Check that `updated_at` timestamp is modified

### 3. **Test Deletions**
- Delete a record through the web interface
- Verify it's removed from the database
- Check that related records handle the deletion properly

### 4. **Test Relationships**
- Add a timesheet entry for an employee
- Verify the `employee_id` foreign key relationship
- Check that employee name displays correctly in the web interface

## Database Extension Tips

1. **Refresh Views**: Always refresh after making changes through the web interface
2. **Use Filters**: Filter by date ranges or status to focus on specific data
3. **Export Data**: Use the extension's export feature for backups
4. **Monitor Performance**: Watch query execution times for optimization
5. **Check Constraints**: Verify foreign key relationships are working

## Troubleshooting

### If No Data Appears:
1. Check database connection settings
2. Ensure XAMPP MySQL service is running
3. Verify database name is `hr3systemdb`
4. Run the setup script: `setup_complete_database.bat`

### If Forms Don't Save:
1. Check browser console for JavaScript errors
2. Verify API endpoints are responding (use `/database-test` page)
3. Check Laravel logs for PHP errors
4. Ensure CSRF tokens are properly configured

## Sample Data Verification

After running the setup, you should see:
- **5 sample employees** in various departments
- **Sample timesheet entries** for recent dates
- **Leave types** (Annual, Sick, Personal, etc.)
- **Claim types** (Travel, Meals, Office Supplies, etc.)
- **Shift types** (Morning, Evening, Night, Weekend)

Use the database extension to explore these tables and verify the complete integration is working properly!
