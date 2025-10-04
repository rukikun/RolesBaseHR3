# Database Types Tables Implementation

## Overview
This document outlines the implementation of SQL queries and migration updates for the `claim_types`, `leave_types`, and `shift_types` tables based on the reference SQL data from `hr3systemdb (33).sql`.

## Files Created/Updated

### 1. SQL Queries File
**File:** `database_types_table.sql`
- Contains INSERT queries for all three type tables
- Uses reference data from production SQL dump
- Includes proper timestamps with NOW() function
- Ready for direct execution in database

### 2. Migration Updates
**File:** `database/migrations/2025_10_04_143640_create_hr3_authoritative_schema.php`
- Updated column structures to match reference SQL exactly
- Added missing columns and constraints
- Enhanced indexing for better performance

### 3. Seeders (Already Existing)
**Files:**
- `database/seeders/ClaimTypeSeeder.php`
- `database/seeders/LeaveTypeSeeder.php`
- `database/seeders/ShiftTypeSeeder.php`
- `database/seeders/TypeTablesSeeder.php` (New comprehensive runner)

## Table Structures

### Claim Types Table
```sql
CREATE TABLE `claim_types` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `code` varchar(10) NOT NULL UNIQUE,
  `description` text DEFAULT NULL,
  `max_amount` decimal(10,2) DEFAULT NULL,
  `requires_attachment` tinyint(1) DEFAULT 0,
  `auto_approve` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `claim_types_is_active_index` (`is_active`),
  UNIQUE KEY `claim_types_code_unique` (`code`)
);
```

**Reference Data:**
1. Travel Expenses (TRAVEL) - $5,000 max
2. Office Supplies (OFFICE) - $1,000 max
3. Meal Allowance (MEAL) - $500 max
4. Training Costs (TRAINING) - $2,000 max
5. Medical Expenses (MEDICAL) - $3,000 max

### Leave Types Table
```sql
CREATE TABLE `leave_types` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `code` varchar(10) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `days_allowed` int(11) DEFAULT 0,
  `max_days_per_year` int(11) DEFAULT 0,
  `carry_forward` tinyint(1) DEFAULT 0,
  `requires_approval` tinyint(1) DEFAULT 1,
  `status` enum('active','inactive') DEFAULT 'active',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `leave_types_is_active_index` (`is_active`),
  KEY `leave_types_status_index` (`status`)
);
```

**Reference Data:**
1. Annual Leave (AL) - 21 days/year, carry forward allowed
2. Sick Leave (SL) - 10 days/year, no approval required
3. Emergency Leave (EL) - 5 days/year
4. Maternity Leave (ML) - 90 days/year
5. Paternity Leave (PL) - 7 days/year

### Shift Types Table
```sql
CREATE TABLE `shift_types` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `code` varchar(20) NOT NULL UNIQUE,
  `description` text DEFAULT NULL,
  `default_start_time` time NOT NULL,
  `default_end_time` time NOT NULL,
  `break_duration` int(11) DEFAULT 0,
  `hourly_rate` decimal(8,2) DEFAULT NULL,
  `color_code` varchar(7) DEFAULT '#007bff',
  `type` enum('day','night','swing','split','rotating') DEFAULT 'day',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `shift_types_is_active_index` (`is_active`),
  KEY `shift_types_type_index` (`type`),
  UNIQUE KEY `shift_types_code_unique` (`code`)
);
```

**Reference Data:**
1. Morning Shift (MORNING) - 8:00-16:00, $25/hr, Day type
2. Afternoon Shift (AFTERNOON) - 14:00-22:00, $27.50/hr, Swing type
3. Night Shift (NIGHT) - 22:00-06:00, $32/hr, Night type
4. Split Shift (SPLIT) - 9:00-17:00, $24/hr, Split type
5. Weekend Shift (WEEKEND) - 10:00-18:00, $30/hr, Rotating type

## Key Changes Made

### Migration Column Updates
1. **Claim Types:**
   - Added `code` column with unique constraint
   - Renamed `requires_receipt` to `requires_attachment`
   - Added `auto_approve` column
   - Added proper string length limits

2. **Leave Types:**
   - Added `code` column
   - Added `days_allowed` and `max_days_per_year` columns
   - Added `carry_forward` and `requires_approval` columns
   - Added `status` enum column
   - Added proper indexing

3. **Shift Types:**
   - Added `code` column with unique constraint
   - Renamed `start_time`/`end_time` to `default_start_time`/`default_end_time`
   - Added `hourly_rate` column
   - Added `type` enum column for shift categorization
   - Enhanced indexing

## Usage Instructions

### 1. Run Migrations
```bash
php artisan migrate:fresh
```

### 2. Seed Type Tables
```bash
# Seed all type tables at once
php artisan db:seed --class=TypeTablesSeeder

# Or seed individually
php artisan db:seed --class=ClaimTypeSeeder
php artisan db:seed --class=LeaveTypeSeeder
php artisan db:seed --class=ShiftTypeSeeder
```

### 3. Execute SQL Directly (Alternative)
```bash
# Import the SQL file directly
mysql -u username -p hr3systemdb < database_types_table.sql
```

## Verification Queries

### Check Claim Types
```sql
SELECT id, name, code, max_amount, requires_attachment FROM claim_types ORDER BY id;
```

### Check Leave Types
```sql
SELECT id, name, code, max_days_per_year, carry_forward, requires_approval FROM leave_types ORDER BY id;
```

### Check Shift Types
```sql
SELECT id, name, code, default_start_time, default_end_time, hourly_rate, type FROM shift_types ORDER BY id;
```

## Integration with HR System

These type tables are fundamental to the HR3 System and integrate with:

1. **Claims Management** - Uses claim_types for expense categorization
2. **Leave Management** - Uses leave_types for leave request categorization
3. **Shift Management** - Uses shift_types for employee scheduling
4. **AI Timesheet System** - References shift_types for intelligent scheduling
5. **Dashboard Analytics** - Aggregates data across all type tables

## Notes

- All seeders include data truncation for clean re-seeding
- Proper foreign key relationships maintained in dependent tables
- Indexing optimized for common query patterns
- Column names and types match production SQL exactly
- All reference data preserved from original system

## Status: ✅ COMPLETE

All SQL queries created, migrations updated, and seeders ready for deployment. The implementation matches the reference SQL structure exactly and maintains full compatibility with the existing HR3 System.
