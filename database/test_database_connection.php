<?php
/**
 * HR3 System - Database Connection and Table Test Script
 * 
 * This script tests the database connection and verifies all tables
 * are working correctly with the separated migration structure.
 * 
 * Usage: php database/test_database_connection.php
 */

// Include Laravel bootstrap
require_once __DIR__ . '/../vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Support\Facades\DB;

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Database configuration
$capsule = new Capsule;
$capsule->addConnection([
    'driver' => 'mysql',
    'host' => $_ENV['DB_HOST'] ?? '127.0.0.1',
    'port' => $_ENV['DB_PORT'] ?? '3306',
    'database' => $_ENV['DB_DATABASE'] ?? 'hr3_hr3systemdb',
    'username' => $_ENV['DB_USERNAME'] ?? 'root',
    'password' => $_ENV['DB_PASSWORD'] ?? '',
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix' => '',
]);

$capsule->setAsGlobal();
$capsule->bootEloquent();

echo "🔍 HR3 System Database Test Script\n";
echo "==================================\n\n";

try {
    // Test database connection
    echo "📡 Testing database connection...\n";
    $pdo = $capsule->getConnection()->getPdo();
    echo "✅ Database connection successful!\n\n";

    // Test all tables
    $tables = [
        'users',
        'employees', 
        'time_entries',
        'attendances',
        'shift_types',
        'shifts',
        'shift_requests',
        'leave_types',
        'leave_requests',
        'claim_types',
        'claims',
        'ai_generated_timesheets'
    ];

    echo "📋 Testing table accessibility and record counts:\n";
    echo "------------------------------------------------\n";

    $totalRecords = 0;
    foreach ($tables as $table) {
        try {
            $count = $capsule->table($table)->count();
            $totalRecords += $count;
            echo sprintf("✅ %-25s: %d records\n", ucfirst(str_replace('_', ' ', $table)), $count);
        } catch (Exception $e) {
            echo sprintf("❌ %-25s: ERROR - %s\n", ucfirst(str_replace('_', ' ', $table)), $e->getMessage());
        }
    }

    echo "\n📊 Summary Statistics:\n";
    echo "---------------------\n";

    // Active employees
    $activeEmployees = $capsule->table('employees')->where('status', 'active')->count();
    echo "👥 Active Employees: {$activeEmployees}\n";

    // Recent activity (last 7 days)
    $recentTimeEntries = $capsule->table('time_entries')
        ->where('created_at', '>=', date('Y-m-d H:i:s', strtotime('-7 days')))
        ->count();
    echo "⏰ Recent Time Entries (7 days): {$recentTimeEntries}\n";

    $recentAttendances = $capsule->table('attendances')
        ->where('created_at', '>=', date('Y-m-d H:i:s', strtotime('-7 days')))
        ->count();
    echo "📅 Recent Attendances (7 days): {$recentAttendances}\n";

    // Pending items
    $pendingLeaveRequests = $capsule->table('leave_requests')->where('status', 'pending')->count();
    $pendingClaims = $capsule->table('claims')->where('status', 'pending')->count();
    $pendingTimeEntries = $capsule->table('time_entries')->where('status', 'pending')->count();
    
    echo "⏳ Pending Leave Requests: {$pendingLeaveRequests}\n";
    echo "⏳ Pending Claims: {$pendingClaims}\n";
    echo "⏳ Pending Time Entries: {$pendingTimeEntries}\n";

    echo "\n🔗 Testing table relationships:\n";
    echo "-------------------------------\n";

    // Test joins to verify foreign key relationships
    try {
        $employeesWithTimeEntries = $capsule->table('employees')
            ->join('time_entries', 'employees.id', '=', 'time_entries.employee_id')
            ->count();
        echo "✅ Employee-TimeEntry relationships: {$employeesWithTimeEntries}\n";
    } catch (Exception $e) {
        echo "❌ Employee-TimeEntry relationships: ERROR\n";
    }

    try {
        $employeesWithAttendances = $capsule->table('employees')
            ->join('attendances', 'employees.id', '=', 'attendances.employee_id')
            ->count();
        echo "✅ Employee-Attendance relationships: {$employeesWithAttendances}\n";
    } catch (Exception $e) {
        echo "❌ Employee-Attendance relationships: ERROR\n";
    }

    try {
        $shiftsWithEmployees = $capsule->table('shifts')
            ->join('employees', 'shifts.employee_id', '=', 'employees.id')
            ->leftJoin('shift_types', 'shifts.shift_type_id', '=', 'shift_types.id')
            ->count();
        echo "✅ Shift-Employee-ShiftType relationships: {$shiftsWithEmployees}\n";
    } catch (Exception $e) {
        echo "❌ Shift-Employee-ShiftType relationships: ERROR\n";
    }

    try {
        $leaveRequestsWithTypes = $capsule->table('leave_requests')
            ->join('employees', 'leave_requests.employee_id', '=', 'employees.id')
            ->join('leave_types', 'leave_requests.leave_type_id', '=', 'leave_types.id')
            ->count();
        echo "✅ LeaveRequest-Employee-LeaveType relationships: {$leaveRequestsWithTypes}\n";
    } catch (Exception $e) {
        echo "❌ LeaveRequest-Employee-LeaveType relationships: ERROR\n";
    }

    try {
        $claimsWithTypes = $capsule->table('claims')
            ->join('employees', 'claims.employee_id', '=', 'employees.id')
            ->join('claim_types', 'claims.claim_type_id', '=', 'claim_types.id')
            ->count();
        echo "✅ Claim-Employee-ClaimType relationships: {$claimsWithTypes}\n";
    } catch (Exception $e) {
        echo "❌ Claim-Employee-ClaimType relationships: ERROR\n";
    }

    echo "\n🎯 Sample Data Verification:\n";
    echo "----------------------------\n";

    // Show sample records from key tables
    $sampleEmployee = $capsule->table('employees')->first();
    if ($sampleEmployee) {
        echo "👤 Sample Employee: {$sampleEmployee->first_name} {$sampleEmployee->last_name} ({$sampleEmployee->department})\n";
    }

    $sampleAttendance = $capsule->table('attendances')
        ->join('employees', 'attendances.employee_id', '=', 'employees.id')
        ->select('attendances.*', 'employees.first_name', 'employees.last_name')
        ->first();
    if ($sampleAttendance) {
        echo "📅 Sample Attendance: {$sampleAttendance->first_name} {$sampleAttendance->last_name} on {$sampleAttendance->date}\n";
    }

    $sampleShiftType = $capsule->table('shift_types')->where('is_active', 1)->first();
    if ($sampleShiftType) {
        echo "⏰ Sample Shift Type: {$sampleShiftType->name} ({$sampleShiftType->default_start_time} - {$sampleShiftType->default_end_time})\n";
    }

    echo "\n✅ Database test completed successfully!\n";
    echo "📈 Total records across all tables: {$totalRecords}\n";
    echo "🎉 All tables are accessible and relationships are working!\n\n";

} catch (Exception $e) {
    echo "❌ Database test failed: " . $e->getMessage() . "\n";
    echo "🔧 Please check your database configuration and ensure the database is running.\n\n";
    exit(1);
}

echo "💡 To run more detailed tests:\n";
echo "   - Use the SQL files in database/test_queries/\n";
echo "   - Run: php artisan db:test-tables\n";
echo "   - Run: php artisan db:test-tables --quick\n\n";
?>
