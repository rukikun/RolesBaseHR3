# HR3 System - Table, Migration, and Model Mapping

This document provides a comprehensive mapping of all database tables, their corresponding migration files, and Eloquent models in the HR3 System.

## Overview

The HR3 System has been refactored to separate the authoritative schema into individual migrations for better maintainability and clearer model-table relationships.

## Table Mapping

| Table Name | Migration File | Model File | Model Class | Protected $table |
|------------|----------------|------------|-------------|------------------|
| `users` | `2025_10_04_220001_create_users_table.php` | `app/Models/User.php` | `User` | ✅ `users` |
| `employees` | `2025_10_04_220002_create_employees_table.php` | `app/Models/Employee.php` | `Employee` | ✅ `employees` |
| `time_entries` | `2025_10_04_220003_create_time_entries_table.php` | `app/Models/TimeEntry.php` | `TimeEntry` | ✅ `time_entries` |
| `attendances` | `2025_10_04_220004_create_attendances_table.php` | `app/Models/Attendance.php` | `Attendance` | ✅ `attendances` |
| `shift_types` | `2025_10_04_220005_create_shift_types_table.php` | `app/Models/ShiftType.php` | `ShiftType` | ✅ `shift_types` |
| `shifts` | `2025_10_04_220006_create_shifts_table.php` | `app/Models/Shift.php` | `Shift` | ✅ `shifts` |
| `shift_requests` | `2025_10_04_220007_create_shift_requests_table.php` | `app/Models/ShiftRequest.php` | `ShiftRequest` | ✅ `shift_requests` |
| `leave_types` | `2025_10_04_220008_create_leave_types_table.php` | `app/Models/LeaveType.php` | `LeaveType` | ✅ `leave_types` |
| `leave_requests` | `2025_10_04_220009_create_leave_requests_table.php` | `app/Models/LeaveRequest.php` | `LeaveRequest` | ✅ `leave_requests` |
| `claim_types` | `2025_10_04_220010_create_claim_types_table.php` | `app/Models/ClaimType.php` | `ClaimType` | ✅ `claim_types` |
| `claims` | `2025_10_04_220011_create_claims_table.php` | `app/Models/Claim.php` | `Claim` | ✅ `claims` |
| `ai_generated_timesheets` | `2025_10_04_220012_create_ai_generated_timesheets_table.php` | `app/Models/AIGeneratedTimesheet.php` | `AIGeneratedTimesheet` | ✅ `ai_generated_timesheets` |

## Migration Details

### Core Tables (Dependencies: None)
1. **Users Table** - Laravel authentication with HR extensions
2. **Employees Table** - Core HR entity with ESS login capability

### Time Management Tables (Dependencies: employees)
3. **Time Entries Table** - Payroll/Timesheet management
4. **Attendances Table** - ESS Clock-in/out tracking

### Shift Management Tables (Dependencies: employees)
5. **Shift Types Table** - Shift templates
6. **Shifts Table** - Employee shift assignments (depends on shift_types)
7. **Shift Requests Table** - Employee shift change requests (depends on shifts, shift_types)

### Leave Management Tables (Dependencies: employees)
8. **Leave Types Table** - Leave type definitions
9. **Leave Requests Table** - Employee leave requests (depends on leave_types)

### Claims Management Tables (Dependencies: employees)
10. **Claim Types Table** - Expense claim type definitions
11. **Claims Table** - Employee expense claims (depends on claim_types)

### AI Features Tables (Dependencies: employees)
12. **AI Generated Timesheets Table** - AI-powered timesheet generation

## Model Features

### All Models Include:
- ✅ `protected $table` property explicitly defined
- ✅ Proper fillable attributes
- ✅ Appropriate casts for data types
- ✅ Eloquent relationships
- ✅ Scopes for common queries
- ✅ Helper methods for business logic

### Key Relationships:

#### Employee Model (Central Hub)
- `hasMany(TimeEntry::class)`
- `hasMany(Attendance::class)`
- `hasMany(Shift::class)`
- `hasMany(ShiftRequest::class)`
- `hasMany(LeaveRequest::class)`
- `hasMany(Claim::class)`
- `hasMany(AIGeneratedTimesheet::class)`

#### Foreign Key Relationships
- All dependent tables have `employee_id` foreign key to `employees.id`
- Approval relationships use `approved_by` foreign key to `employees.id`
- Type tables (shift_types, leave_types, claim_types) linked to their respective request/assignment tables

## Migration Order

The migrations are numbered sequentially to ensure proper dependency order:

1. `220001` - users (independent)
2. `220002` - employees (independent)
3. `220003` - time_entries (depends on employees)
4. `220004` - attendances (depends on employees)
5. `220005` - shift_types (independent)
6. `220006` - shifts (depends on employees, shift_types)
7. `220007` - shift_requests (depends on employees, shifts, shift_types)
8. `220008` - leave_types (independent)
9. `220009` - leave_requests (depends on employees, leave_types)
10. `220010` - claim_types (independent)
11. `220011` - claims (depends on employees, claim_types)
12. `220012` - ai_generated_timesheets (depends on employees)

## Benefits of This Structure

### ✅ **Clear Separation of Concerns**
- Each table has its own migration file
- Easy to identify what each model represents
- Clear dependency relationships

### ✅ **Better Maintainability**
- Individual migrations can be modified without affecting others
- Easy to add new tables or modify existing ones
- Clear rollback capabilities per table

### ✅ **Explicit Table Definitions**
- All models have `protected $table` property
- No ambiguity about which table a model uses
- Easier debugging and development

### ✅ **Professional Structure**
- Follows Laravel best practices
- Production-ready code organization
- Easy for new developers to understand

## Usage

### Running Migrations
```bash
# Run all migrations in order
php artisan migrate

# Run specific migration
php artisan migrate --path=database/migrations/2025_10_04_220001_create_users_table.php

# Rollback specific migration
php artisan migrate:rollback --path=database/migrations/2025_10_04_220001_create_users_table.php
```

### Using Models
```php
// All models now explicitly define their table
$users = User::all(); // Uses 'users' table
$employees = Employee::all(); // Uses 'employees' table
$timeEntries = TimeEntry::all(); // Uses 'time_entries' table

// Relationships work seamlessly
$employee = Employee::find(1);
$timeEntries = $employee->timeEntries; // Gets all time entries for employee
$claims = $employee->claims; // Gets all claims for employee
```

## Original Authoritative Schema - REMOVED

The original authoritative schema file (`2025_10_04_143640_create_hr3_authoritative_schema.php`) has been **removed** as it's no longer needed. All functionality has been successfully separated into individual migrations for better maintainability and to prevent conflicts.

---

**Last Updated:** October 4, 2025  
**Status:** ✅ Complete - All tables separated with proper models and protected $table properties
