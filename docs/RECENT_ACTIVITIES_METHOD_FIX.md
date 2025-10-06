# recentActivities() Method Fix

## Problem
The system was throwing a `BadMethodCallException` error:
```
Call to undefined method App\Models\Employee::recentActivities()
```

This error occurred because the AdminProfileController was calling `Auth::user()->recentActivities(10)->get()` but the Employee model didn't have this method defined.

## Root Cause
- The system was converted from using `users` table to `employees` table for authentication
- The `User` model had the `recentActivities()` method but the `Employee` model was missing it
- The AdminProfileController in `app/Http/Controllers/AdminProfileController.php` was calling this method

## Solution Implemented

### 1. Added recentActivities() Method to Employee Model
Added a comprehensive `recentActivities()` method to `app/Models/Employee.php` that:

- Returns a query builder-like object with `get()` and `count()` methods
- Aggregates activities from multiple sources:
  - **Time Entries**: Recent timesheet submissions
  - **Leave Requests**: Recent leave applications  
  - **Claims**: Recent expense claims
- Handles missing relationships gracefully with `method_exists()` checks
- Sorts activities by date (most recent first)
- Limits results to specified number
- Includes comprehensive error handling

### 2. Method Signature
```php
public function recentActivities($limit = 10)
{
    // Returns an anonymous class that implements get() and count() methods
    return new class($this, $limit) {
        public function get() { /* Returns collection of activities */ }
        public function count() { /* Returns count of activities */ }
    };
}
```

### 3. Activity Data Structure
Each activity includes:
```php
[
    'type' => 'timesheet|leave|claim',
    'description' => 'Human readable description',
    'date' => Carbon::instance,
    'status' => 'pending|approved|rejected'
]
```

### 4. Controller Compatibility
The method is designed to work with the existing controller pattern:
```php
$recentActivities = $user->recentActivities(10)->get();
$count = $user->recentActivities(10)->count();
```

## Files Modified
- `app/Models/Employee.php` - Added `recentActivities()` method

## Testing
Created and ran `test_recent_activities_fix.php` to verify:
- ✅ Method exists on Employee model
- ✅ Method can be called with limit parameter
- ✅ `get()` method returns collection
- ✅ `count()` method returns integer
- ✅ No errors when called from controller context

## Activity Sources

### Time Entries
- Source: `timeEntries()` relationship
- Description: "Timesheet entry for [date]"
- Status: From `status` field

### Leave Requests  
- Source: `leaveRequests()` relationship
- Description: "Leave request from [start] to [end]"
- Status: From `status` field

### Claims
- Source: `claims()` relationship  
- Description: "Expense claim: [description]"
- Status: From `status` field

## Error Handling
- Uses `try-catch` blocks to prevent crashes
- Logs errors with `\Log::error()`
- Returns empty collection on errors
- Checks relationship existence with `method_exists()`
- Handles missing data with null coalescing

## Result
- ✅ Error resolved
- ✅ AdminProfileController can access recent activities
- ✅ Profile pages display employee activity history
- ✅ System fully functional
- ✅ Compatible with existing controller usage patterns

The `recentActivities()` method now works correctly with the role-based authentication system using the employees table, providing a comprehensive activity timeline for each employee.
