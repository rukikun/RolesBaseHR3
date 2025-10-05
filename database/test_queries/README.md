# HR3 System Database Testing Guide

This directory contains comprehensive database testing tools to verify that all tables are working correctly after the migration separation and controller updates.

## 📁 Files Overview

### SQL Query Files
- **`comprehensive_table_tests.sql`** - Complete test suite with 16 test categories
- **`quick_table_verification.sql`** - Quick verification of all tables with sample data

### PHP Testing Tools
- **`../test_database_connection.php`** - Standalone PHP script for database testing
- **`../../app/Console/Commands/TestDatabaseTables.php`** - Laravel Artisan command

## 🚀 How to Run Tests

### Method 1: Quick PHP Script (Recommended for initial testing)
```bash
# Navigate to project root
cd c:\Users\johnk\Herd\hr3system

# Run the standalone test script
php database/test_database_connection.php
```

### Method 2: Laravel Artisan Commands
```bash
# Quick verification
php artisan db:test-tables --quick

# Comprehensive tests
php artisan db:test-tables
```

### Method 3: Direct SQL Execution
```bash
# Using MySQL command line
mysql -u root -p hr3_hr3systemdb < database/test_queries/quick_table_verification.sql

# Or using phpMyAdmin
# Import and run the SQL files directly
```

## 📊 Test Categories

### Quick Verification Tests
- ✅ Basic table accessibility
- ✅ Record counts for all tables
- ✅ Sample data display
- ✅ Summary statistics

### Comprehensive Tests (16 Categories)

#### 1. **Individual Table Tests**
- Users table functionality
- Employees table with status checks
- Time entries with employee relationships
- Attendance records with status distribution
- Shift types and configurations
- Shift assignments and schedules
- Shift requests and approvals
- Leave types and policies
- Leave requests with approvals
- Claim types and limits
- Claims with financial summaries
- AI generated timesheets

#### 2. **Cross-Table Relationship Tests**
- Employee activity summaries
- Department workload analysis
- Recent activity across all tables

#### 3. **Data Integrity Tests**
- Foreign key relationship verification
- Orphaned record detection
- Data consistency checks
- Invalid data identification

#### 4. **Performance Tests**
- Index usage verification
- Query execution time analysis
- Common query performance

#### 5. **System Health Summary**
- Overall statistics
- Pending approvals count
- Active vs inactive records

## 🔍 What Each Test Verifies

### Table Structure Tests
- ✅ All 12 main tables are accessible
- ✅ Proper table relationships exist
- ✅ Foreign key constraints work
- ✅ Indexes are functioning

### Data Quality Tests
- ✅ No orphaned records
- ✅ No negative hours in time tracking
- ✅ Valid date ranges in leave requests
- ✅ No duplicate attendance records
- ✅ Consistent status values

### Functionality Tests
- ✅ Eloquent model relationships
- ✅ Join operations work correctly
- ✅ Aggregation queries function
- ✅ Complex filtering works

### Performance Tests
- ✅ Index usage on common queries
- ✅ Reasonable query execution times
- ✅ Efficient relationship loading

## 📈 Expected Results

### Healthy System Indicators
- All tables show ✅ status
- No orphaned records found
- All relationships working
- Query execution under 100ms
- Consistent data across tables

### Sample Output
```
✅ Users: 2 records
✅ Active Employees: 6 records
✅ Time Entries: 1 records
✅ Attendances: 1 records
✅ Active Shift Types: 5 records
✅ Shifts: 3 records
✅ Leave Requests: 2 records
✅ Claims: 3 records
✅ AI Generated Timesheets: 2 records
```

## 🔧 Troubleshooting

### Common Issues and Solutions

#### Database Connection Errors
```bash
❌ Database connection failed
```
**Solution:** Check `.env` file database configuration

#### Missing Tables
```bash
❌ Table 'employees' doesn't exist
```
**Solution:** Run migrations
```bash
php artisan migrate
```

#### Orphaned Records Found
```bash
⚠️ Found orphaned records: Time Entries: 5
```
**Solution:** Check foreign key relationships and clean up data

#### Performance Issues
```bash
⚠️ Queries taking longer than expected
```
**Solution:** Check database indexes and optimize queries

## 📋 Test Checklist

Before deploying to production, ensure:

- [ ] All tables accessible ✅
- [ ] No orphaned records ✅
- [ ] All relationships working ✅
- [ ] Data consistency verified ✅
- [ ] Performance acceptable ✅
- [ ] Sample data displays correctly ✅

## 🎯 Integration with HR System

These tests verify that the separated migration structure works correctly with:

- ✅ **Updated Controllers** - TimesheetController, HRDashboardController
- ✅ **Eloquent Models** - All models have `protected $table` properties
- ✅ **Route Structure** - All routes use proper controllers
- ✅ **View Compatibility** - Views can access model data correctly

## 📞 Support

If tests fail or you encounter issues:

1. Check the error messages in the test output
2. Verify database configuration in `.env`
3. Ensure all migrations have been run
4. Check that models have correct `protected $table` properties
5. Verify foreign key relationships in the database

## 🔄 Regular Testing

Run these tests:
- ✅ After any database schema changes
- ✅ Before deploying to production
- ✅ After updating controllers or models
- ✅ When troubleshooting data issues

---

**Last Updated:** October 4, 2025  
**Status:** ✅ All test tools ready for use
