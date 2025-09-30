# INTEGRATED HR3 SYSTEM DEPLOYMENT INSTRUCTIONS

## Overview
This guide will help you deploy the integrated HR3 system database that combines your Admin Dashboard and Employee ESS systems into one unified database.

## What's Included
The `integrated_hr3system_complete.sql` script contains:

### **Core Tables**
- `employees` - Unified employee records (works for both admin and ESS)
- `users` - Admin authentication
- `time_entries` - Time tracking and attendance
- `shifts` & `shift_types` - Shift scheduling system
- `leave_types` & `leave_requests` & `leave_balances` - Leave management
- `claim_types` & `claims` - Claims and reimbursement
- `training_programs` & `employee_trainings` - Training management
- `competency_assessments` - Employee assessments
- `employee_requests` - General employee requests
- `payslips` - Payroll management
- `employee_notifications` - ESS notifications

### **Sample Data**
- 1 Admin user + 8 Employee accounts
- 5 Shift types with September 2025 schedule
- 5 Leave types with sample requests
- 5 Claim types with sample claims
- 5 Training programs with assignments
- Sample time entries, payslips, and notifications

## Deployment Steps

### Step 1: Backup Current Database (IMPORTANT!)
Before proceeding, backup your current database:
1. Open phpMyAdmin
2. Select your current `hr3systemdb` database
3. Click "Export" tab
4. Click "Go" to download backup
5. Save the backup file safely

### Step 2: Deploy New Database
1. Open phpMyAdmin in your browser
2. Click "SQL" tab at the top
3. Copy the entire contents of `integrated_hr3system_complete.sql`
4. Paste into the SQL query box
5. Click "Go" to execute

**The script will:**
- Drop existing tables safely (in correct order)
- Create all new tables with proper relationships
- Insert all sample data
- Show verification results

### Step 3: Verify Installation
After running the script, you should see:
- "INTEGRATED HR3 SYSTEM SETUP COMPLETE!" message
- Table counts showing all data was inserted
- Login credentials display

### Step 4: Test Login Access

#### Admin Dashboard Login
- URL: `http://localhost/hr3system/admin/login`
- Email: `admin@jetlouge.com`
- Password: `password123`

#### Employee ESS Login
- URL: `http://localhost/hr3system/employee/login`
- Email: `john.doe@jetlouge.com` (or any employee email)
- Password: `password123`

### Step 5: Update Environment Configuration
Your `.env` file should already be configured correctly:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hr3systemdb
DB_USERNAME=root
DB_PASSWORD=
```

## What's Changed

### **Unified Employee System**
- Single `employees` table serves both admin and ESS
- All employees can log into ESS portal
- Admin manages all employees from one interface

### **Complete Integration**
- All HR modules share the same employee data
- Cross-module navigation and data consistency
- Unified reporting and analytics

### **Enhanced Features**
- Employee online status tracking
- Comprehensive payroll with Philippine tax structure
- Training and competency management
- Employee notifications system
- Leave balance tracking

## Login Credentials

### Admin Access
- **Email:** admin@jetlouge.com
- **Password:** password123
- **Access:** Full admin dashboard with all HR modules

### Employee ESS Access
All employees use **password123**:
- john.doe@jetlouge.com
- jane.smith@jetlouge.com
- mike.johnson@jetlouge.com
- sarah.wilson@jetlouge.com
- david.brown@jetlouge.com
- lisa.davis@jetlouge.com
- tom.miller@jetlouge.com
- emma.garcia@jetlouge.com

## Troubleshooting

### If Script Fails
1. Check that XAMPP MySQL is running
2. Ensure you have sufficient privileges
3. Check for any foreign key constraint errors
4. Try running the script in smaller sections

### If Login Doesn't Work
1. Clear browser cache and cookies
2. Check that Laravel routes are properly configured
3. Verify `.env` database settings
4. Run `php artisan config:clear` if needed

### If Data Doesn't Display
1. Check database connection in Laravel
2. Verify table names match controller queries
3. Check for any PHP errors in Laravel logs

## Benefits of Integration

### **For Administrators**
- Single database to manage
- Unified employee records
- Cross-module reporting
- Simplified maintenance

### **For Employees**
- Single login for all ESS features
- Consistent data across modules
- Real-time notifications
- Complete self-service portal

### **For System**
- Reduced data duplication
- Better performance
- Easier backups
- Simplified deployment

## Next Steps
1. Deploy the integrated database
2. Test both admin and employee logins
3. Verify all modules are working
4. Remove old database files if everything works
5. Update any documentation or training materials

## Support
If you encounter any issues during deployment, check:
1. XAMPP MySQL service is running
2. Database permissions are correct
3. Laravel configuration is updated
4. All file paths are accessible

The integrated system is now ready for production use with full functionality for both admin dashboard and employee self-service portal!
