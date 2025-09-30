<?php
/**
 * Test script to verify employee creation functionality
 * Run this script to test if the employee creation works properly
 */

require_once 'vendor/autoload.php';

// Load Laravel application
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

try {
    echo "Testing Employee Creation Functionality\n";
    echo "=====================================\n\n";
    
    // Set database connection
    Config::set('database.connections.mysql.database', 'hr3systemdb');
    DB::purge('mysql');
    
    // Test 1: Check if employees table exists
    echo "1. Checking if employees table exists...\n";
    $tablesExist = DB::select("SHOW TABLES LIKE 'employees'");
    if (empty($tablesExist)) {
        echo "❌ FAILED: Employees table does not exist!\n";
        echo "Please run the SQL script: database/sql/setup_employees_table.sql in phpMyAdmin\n\n";
        exit(1);
    }
    echo "✅ PASSED: Employees table exists\n\n";
    
    // Test 2: Check table structure
    echo "2. Checking table structure...\n";
    $columns = DB::select("DESCRIBE employees");
    $requiredColumns = ['id', 'first_name', 'last_name', 'email', 'position', 'department', 'hire_date', 'status'];
    $existingColumns = array_column($columns, 'Field');
    
    foreach ($requiredColumns as $column) {
        if (in_array($column, $existingColumns)) {
            echo "✅ Column '{$column}' exists\n";
        } else {
            echo "❌ Missing column '{$column}'\n";
        }
    }
    echo "\n";
    
    // Test 3: Test employee creation
    echo "3. Testing employee creation...\n";
    $testEmployee = [
        'first_name' => 'Test',
        'last_name' => 'Employee',
        'email' => 'test.employee.' . time() . '@jetlouge.com',
        'phone' => '+63 999 123 4567',
        'position' => 'Test Position',
        'department' => 'IT',
        'hire_date' => date('Y-m-d'),
        'salary' => 35000.00,
        'status' => 'active',
        'online_status' => 'offline',
        'created_at' => now(),
        'updated_at' => now()
    ];
    
    try {
        $employeeId = DB::table('employees')->insertGetId($testEmployee);
        echo "✅ PASSED: Employee created successfully with ID: {$employeeId}\n";
        
        // Verify the employee was created
        $createdEmployee = DB::table('employees')->where('id', $employeeId)->first();
        if ($createdEmployee) {
            echo "✅ PASSED: Employee verification successful\n";
            echo "   Name: {$createdEmployee->first_name} {$createdEmployee->last_name}\n";
            echo "   Email: {$createdEmployee->email}\n";
            echo "   Position: {$createdEmployee->position}\n";
            echo "   Department: {$createdEmployee->department}\n";
        } else {
            echo "❌ FAILED: Could not verify created employee\n";
        }
        
        // Clean up test data
        DB::table('employees')->where('id', $employeeId)->delete();
        echo "✅ Test data cleaned up\n\n";
        
    } catch (Exception $e) {
        echo "❌ FAILED: Employee creation failed - " . $e->getMessage() . "\n\n";
    }
    
    // Test 4: Check existing employees count
    echo "4. Checking existing employees...\n";
    $employeeCount = DB::table('employees')->count();
    echo "Total employees in database: {$employeeCount}\n";
    
    if ($employeeCount > 0) {
        echo "✅ PASSED: Database has existing employees\n";
        $sampleEmployees = DB::table('employees')->limit(3)->get(['first_name', 'last_name', 'email', 'status']);
        echo "Sample employees:\n";
        foreach ($sampleEmployees as $emp) {
            echo "  - {$emp->first_name} {$emp->last_name} ({$emp->email}) - {$emp->status}\n";
        }
    } else {
        echo "⚠️  WARNING: No employees found in database\n";
        echo "Consider running the seeder: php artisan db:seed --class=EmployeeSeeder\n";
    }
    
    echo "\n=====================================\n";
    echo "Employee Creation Test Complete!\n";
    echo "=====================================\n";
    
} catch (Exception $e) {
    echo "❌ CRITICAL ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
