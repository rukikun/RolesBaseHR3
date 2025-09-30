# Employee Login Setup Guide

## Issue Resolution
The employee accounts cannot be accessed because the employees in the database don't have properly hashed passwords set. This guide will fix that issue.

## Quick Fix Steps

### Step 1: Execute the Password Update Script
1. Open **phpMyAdmin** in your browser (usually `http://localhost/phpmyadmin`)
2. Select the `hr3systemdb` database
3. Click on the **SQL** tab
4. Copy and paste the contents of `scripts/update_employee_passwords.sql`
5. Click **Go** to execute the script

### Step 2: Verify Employee Accounts
After running the script, you should see:
- All employees now have hashed passwords
- 8 sample employee accounts created (if none existed)
- Summary showing total employees with passwords

## Employee Login Credentials

**All employees can now log in with:**
- **Email**: Their respective email addresses (see list below)
- **Password**: `password123`

### Sample Employee Accounts Created:
1. **John Doe** - `john.doe@jetlouge.com`
2. **Jane Smith** - `jane.smith@jetlouge.com`  
3. **Mike Johnson** - `mike.johnson@jetlouge.com`
4. **Sarah Wilson** - `sarah.wilson@jetlouge.com`
5. **David Brown** - `david.brown@jetlouge.com`
6. **Lisa Garcia** - `lisa.garcia@jetlouge.com`
7. **Robert Martinez** - `robert.martinez@jetlouge.com`
8. **Emily Davis** - `emily.davis@jetlouge.com`

## Access URLs

### Employee Portal
- **URL**: `http://localhost/employee/login`
- **Login Process**: Portal Selection → Employee Portal → Login Form → Employee Dashboard

### Admin Portal  
- **URL**: `http://localhost/admin/login`
- **Credentials**: `admin@jetlouge.com` / `password123`

## Authentication System Details

### Employee Authentication Configuration
- **Guard**: `employee` (separate from admin authentication)
- **Model**: `App\Models\Employee`
- **Table**: `employees`
- **Password Hashing**: Laravel bcrypt (secure)

### Employee Model Features
- ✅ Implements `Authenticatable` interface
- ✅ Uses `employee` guard for authentication
- ✅ Connected to `hr3systemdb` database
- ✅ Proper password hashing and verification
- ✅ Online status tracking
- ✅ Relationships with all HR modules

## Troubleshooting

### If Login Still Fails:
1. **Check Database Connection**: Ensure XAMPP MySQL is running
2. **Verify Database**: Confirm `hr3systemdb` exists and has `employees` table
3. **Check Password Hash**: Run this query to verify:
   ```sql
   SELECT email, LENGTH(password) as password_length FROM employees LIMIT 5;
   ```
   Password length should be 60 characters (bcrypt hash)

### If Employee Portal Shows 404:
1. **Clear Laravel Cache**:
   ```bash
   php artisan route:clear
   php artisan config:clear
   php artisan cache:clear
   ```

2. **Check Routes**: Verify `routes/employee_portal.php` is included in `routes/web.php`

## Security Features
- ✅ CSRF protection on all forms
- ✅ Password hashing with bcrypt
- ✅ Session-based authentication
- ✅ Guard separation (admin vs employee)
- ✅ Middleware protection for employee routes

## Next Steps After Login
Once employees can log in, they will have access to:
1. **Employee Dashboard** - Overview and quick actions
2. **Time & Attendance** - Clock in/out and attendance logs
3. **Timesheet Management** - Submit and view timesheets
4. **Shift Schedule** - View assigned shifts and requests
5. **Leave Management** - Apply for leave and check balances
6. **Claims & Reimbursement** - Submit expense claims
7. **Profile Management** - Update personal information

## Files Modified/Created
- ✅ `scripts/update_employee_passwords.sql` - Password setup script
- ✅ `scripts/set_employee_passwords.php` - Alternative PHP script
- ✅ Employee authentication already configured in:
  - `app/Models/Employee.php`
  - `config/auth.php`
  - `routes/employee_portal.php`
  - `app/Http/Controllers/EmployeeESSController.php`

---

**Execute the SQL script in phpMyAdmin and employees will be able to log in immediately with password "password123"**
