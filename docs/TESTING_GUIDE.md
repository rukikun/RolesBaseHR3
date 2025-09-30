# HR System Testing Guide

## Setup Instructions

### 1. Database Setup
1. Make sure XAMPP MySQL service is running
2. Run the database setup script:
   ```bash
   # Option 1: Use the batch file
   setup_database.bat
   
   # Option 2: Manual MySQL command
   mysql -u root hr_system < setup_hr_database.sql
   ```

### 2. Start the Laravel Development Server
```bash
php artisan serve
```

### 3. Access the System
Open your browser and navigate to: `http://localhost:8000/admin/dashboard`

## Testing All Modules

### **Timesheet Management Module**

#### Test Data Saving:
1. Navigate to **Timesheet Management** from the admin dashboard
2. Click **"Add Timesheet"** button
3. Fill in the form:
   - **Employee**: Select any employee from dropdown
   - **Work Date**: Choose any date
   - **Hours Worked**: Enter 8.0
   - **Overtime Hours**: Enter 1.5
   - **Status**: Select "Pending"
   - **Notes**: Enter "Test timesheet entry"
4. Click **"Save"** button
5. **Expected Result**: Success message appears and timesheet appears in the table

#### Test Editing:
1. Click the **Edit** button (pencil icon) on any timesheet row
2. Modify the hours worked to 7.5
3. Click **"Save"**
4. **Expected Result**: Changes are saved and reflected in the table

#### Test Filtering:
1. Use the **Employee Filter** dropdown to filter by specific employee
2. Use the **Status Filter** to show only "Pending" entries
3. Click **"Refresh"** button
4. **Expected Result**: Table updates to show filtered results

### **Employee Management Module**

#### Test Adding Employee:
1. Click on the **"Employees"** tab
2. Click **"Add Employee"** button
3. Fill in the form:
   - **First Name**: John
   - **Last Name**: Doe
   - **Email**: john.doe@test.com
   - **Phone**: +1-555-9999
   - **Position**: Test Position
   - **Department**: IT
   - **Hire Date**: Today's date
   - **Status**: Active
4. Click **"Save Employee"**
5. **Expected Result**: Employee is added and appears in the table

#### Test Employee Editing:
1. Click **Edit** button on any employee
2. Change the position to "Senior Developer"
3. Click **"Save Employee"**
4. **Expected Result**: Changes are saved and displayed

### **Shift Management Module**

#### Test Shift Assignment:
1. Click on the **"Shifts"** tab
2. Click **"Assign Shift"** button
3. Fill in the form:
   - **Employee**: Select an employee
   - **Date**: Tomorrow's date
   - **Shift Type**: Select "Morning Shift"
   - **Start Time**: 08:00
   - **End Time**: 16:00
   - **Notes**: Test shift assignment
4. Click **"Save Shift"**
5. **Expected Result**: Shift is created and appears in the table

### **Leave Management Module**

#### Test Leave Request:
1. Click on the **"Leave Requests"** tab
2. Click **"Request Leave"** button
3. Fill in the form:
   - **Employee**: Select an employee
   - **Leave Type**: Annual Leave
   - **Start Date**: Next week
   - **End Date**: Next week + 2 days
   - **Reason**: Testing leave request functionality
4. Click **"Submit Request"**
5. **Expected Result**: Leave request is created and appears in the table

#### Test Leave Approval:
1. Click the **Approve** button (checkmark icon) on a pending leave request
2. **Expected Result**: Status changes to "Approved"

### **Claims Management Module**

#### Test Claim Submission:
1. Click on the **"Claims"** tab
2. Click **"Submit Claim"** button
3. Fill in the form:
   - **Employee**: Select an employee
   - **Claim Type**: Travel Expenses
   - **Amount**: 150.00
   - **Expense Date**: Yesterday
   - **Description**: Test travel claim
4. Click **"Submit Claim"**
5. **Expected Result**: Claim is created and appears in the table

#### Test Claim Approval:
1. Click the **Approve** button on a pending claim
2. **Expected Result**: Status changes to "Approved"

## Verification Checklist

### ✅ Database Connectivity
- [ ] All tables are created successfully
- [ ] Sample data is inserted
- [ ] No database connection errors

### ✅ CRUD Operations
- [ ] **Create**: All forms can add new records
- [ ] **Read**: All data displays correctly in tables
- [ ] **Update**: Edit functionality works for all modules
- [ ] **Delete**: Delete buttons work (where applicable)

### ✅ User Interface
- [ ] All modals open and close properly
- [ ] Form validation works (required fields)
- [ ] Success/error messages display correctly
- [ ] Tables update after operations
- [ ] Filters and search work properly

### ✅ API Integration
- [ ] All API endpoints respond correctly
- [ ] CSRF tokens are handled properly
- [ ] Error handling works as expected
- [ ] Statistics update correctly

## Common Issues and Solutions

### Issue: "Table doesn't exist" error
**Solution**: Run the database setup script again
```bash
mysql -u root hr_system < setup_hr_database.sql
```

### Issue: CSRF token mismatch
**Solution**: Clear browser cache and refresh the page

### Issue: Modal forms not working
**Solution**: Check browser console for JavaScript errors

### Issue: Data not saving
**Solution**: 
1. Check database connection
2. Verify XAMPP MySQL is running
3. Check Laravel logs: `storage/logs/laravel.log`

## Expected Sample Data

After running the setup script, you should see:
- **5 sample employees** (EMP001-EMP005)
- **4 shift types** (Morning, Evening, Night, Weekend)
- **5 leave types** (Annual, Sick, Personal, Maternity, Emergency)
- **5 claim types** (Travel, Meal, Office, Training, Communication)
- **Sample time entries, shifts, leave requests, and claims**

## Success Criteria

The system is working correctly if:
1. ✅ All forms can save data to the database
2. ✅ Data appears immediately in tables after saving
3. ✅ Edit functionality loads existing data and saves changes
4. ✅ Statistics cards update with correct numbers
5. ✅ All tabs switch properly and load their respective data
6. ✅ No JavaScript errors in browser console
7. ✅ All API endpoints return proper JSON responses

## Support

If you encounter any issues:
1. Check the browser console for JavaScript errors
2. Check Laravel logs in `storage/logs/laravel.log`
3. Verify database connection and table structure
4. Ensure all required fields are filled in forms
