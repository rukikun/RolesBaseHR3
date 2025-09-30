# Database Setup Instructions

## Current Issue
You're getting "Unknown table 'employees'" because the database tables haven't been created yet.

## Required Steps

### Step 1: Run Database Setup Script
Execute `setup_hr_database_complete.sql` in MySQL/phpMyAdmin

**This script will create:**
- `hr3systemdb` database
- All required tables with proper structure
- Foreign key relationships for employee-shift integration
- Sample data for testing

### Step 2: Verify Setup
After running the setup script, test with:
```sql
USE hr3systemdb;
SHOW TABLES;
DESCRIBE employees;
```

### Step 3: Test Integration
Once tables exist, run:
`verify_employee_shift_integration.sql`

## Script Execution Order
1. `setup_hr_database_complete.sql` ← **Run this first**
2. `verify_employee_shift_integration.sql` ← **Then run this**
3. `fix_timesheet_status.sql` ← **Optional: for status column fixes**

## What Gets Created
- **employees** table with status column
- **time_entries** table with foreign key to employees
- **shifts** table with foreign key to employees  
- **shift_types**, **leave_types**, **claim_types** tables
- **users** table for authentication
- Sample data for immediate testing

## After Setup
The employee-shift integration will work with:
- Cross-module navigation between timesheet and shift management
- Employee statistics and filtering
- Proper foreign key relationships
