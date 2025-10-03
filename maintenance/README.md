# Maintenance Directory

This directory contains all maintenance, diagnostic, and setup scripts for the HR3 System.

## Directory Structure

### 📁 `/fixes/`
Scripts that resolve specific system issues:

- **`fix_employee_login.php`** - Comprehensive employee authentication fix
  - Creates test employee accounts
  - Fixes password hashing issues
  - Verifies authentication flow
  - Tests database connections

- **`fix_database_connection.php`** - Database connection troubleshooting
  - Tests PDO connections
  - Creates missing databases
  - Clears Laravel caches
  - Verifies table existence

- **`fix_database_issues.php`** - General database issue resolution
  - Comprehensive database diagnostics
  - Schema validation
  - Data integrity checks

- **`fix_attendance_timezone.php`** - Timezone-related attendance fixes
  - Corrects timezone inconsistencies
  - Updates attendance records
  - Fixes time calculations

### 📁 `/diagnostics/`
Scripts for testing and diagnosing system issues:

- **`check_employees.php`** - Employee record verification
  - Validates employee data structure
  - Checks password hashing
  - Verifies authentication setup

- **`check_attendance_data.php`** - Attendance data validation
  - Analyzes attendance records
  - Identifies data inconsistencies
  - Reports missing entries

- **`test_employee_login.php`** - Authentication testing suite
  - End-to-end login testing
  - Route verification
  - Controller method testing
  - Database integrity checks

### 📁 `/setup/`
Initial setup and configuration scripts:

- **`setup_admin_profile_system.php`** - Admin profile system setup
  - Creates admin accounts
  - Sets up profile management
  - Configures permissions

## Usage

### Quick Access
Use the main maintenance tool from the project root:
```bash
php maintenance.php
```

### Direct Script Execution
Run individual scripts directly:
```bash
# Fix employee login issues
php maintenance/fixes/fix_employee_login.php

# Check employee records
php maintenance/diagnostics/check_employees.php

# Setup admin profiles
php maintenance/setup/setup_admin_profile_system.php
```

### Common Workflows

#### 🚨 **Emergency Fix Workflow**
1. Run diagnostics first:
   ```bash
   php maintenance/diagnostics/check_employees.php
   ```

2. Apply appropriate fix:
   ```bash
   php maintenance/fixes/fix_employee_login.php
   ```

3. Verify the fix:
   ```bash
   php maintenance/diagnostics/test_employee_login.php
   ```

#### 🔧 **System Setup Workflow**
1. Fix database connections:
   ```bash
   php maintenance/fixes/fix_database_connection.php
   ```

2. Setup admin system:
   ```bash
   php maintenance/setup/setup_admin_profile_system.php
   ```

3. Fix employee authentication:
   ```bash
   php maintenance/fixes/fix_employee_login.php
   ```

#### 🔍 **Diagnostic Workflow**
1. Check system status:
   ```bash
   php maintenance/diagnostics/check_employees.php
   php maintenance/diagnostics/check_attendance_data.php
   ```

2. Test functionality:
   ```bash
   php maintenance/diagnostics/test_employee_login.php
   ```

## Best Practices

### ✅ **Before Running Scripts**
- Always backup your database
- Ensure you're in the project root directory
- Check that Laravel is properly installed
- Verify database credentials in `.env`

### ✅ **After Running Scripts**
- Clear Laravel caches: `php artisan config:clear`
- Test the application manually
- Check logs for any errors
- Document any changes made

### ✅ **Script Development**
- Add comprehensive error handling
- Include progress indicators
- Provide clear success/failure messages
- Document all changes made

## Troubleshooting

### Common Issues

#### **"Class not found" errors**
```bash
# Clear autoload cache
composer dump-autoload
```

#### **Database connection errors**
```bash
# Run database connection fix
php maintenance/fixes/fix_database_connection.php
```

#### **Permission errors**
```bash
# Fix storage permissions (Linux/Mac)
chmod -R 775 storage bootstrap/cache

# Windows: Run as administrator
```

### Getting Help

1. **Check Documentation**: `/docs/solutions/` directory
2. **Run Diagnostics**: Use scripts in `/diagnostics/` folder
3. **View Logs**: Check `storage/logs/` for error details
4. **Use Maintenance Tool**: `php maintenance.php` for guided assistance

## Contributing

When adding new maintenance scripts:

1. **Place in appropriate subdirectory**:
   - Fixes → `/fixes/`
   - Diagnostics → `/diagnostics/`
   - Setup → `/setup/`

2. **Follow naming convention**:
   - `fix_[issue_name].php`
   - `check_[component_name].php`
   - `setup_[system_name].php`

3. **Include proper documentation**:
   - Add script description
   - Document parameters
   - Include usage examples

4. **Add to main maintenance tool**:
   - Update `maintenance.php` menu
   - Add appropriate case handler

---

**Last Updated**: October 4, 2025  
**Maintainer**: HR3 System Development Team
