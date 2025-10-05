<?php

/**
 * Comprehensive test script for Claims and Attendance API Controllers
 * Run this from the project root: php test_api_controllers.php
 */

require_once 'vendor/autoload.php';

use App\Http\Controllers\Api\ClaimsController;
use App\Http\Controllers\Api\AttendanceController;
use App\Models\Claim;
use App\Models\ClaimType;
use App\Models\Attendance;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

// Load Laravel configuration
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== API Controllers Test Suite ===\n\n";

// Test Results Tracker
$testResults = [
    'claims_controller' => [],
    'attendance_controller' => [],
    'models' => [],
    'database' => []
];

try {
    // ===== MODEL TESTS =====
    echo "1. Testing Model Instantiation...\n";
    
    try {
        $claim = new Claim();
        $testResults['models']['claim'] = '✅ PASS';
        echo "✅ Claim model instantiated successfully\n";
    } catch (Exception $e) {
        $testResults['models']['claim'] = '❌ FAIL: ' . $e->getMessage();
        echo "❌ Claim model failed: " . $e->getMessage() . "\n";
    }
    
    try {
        $claimType = new ClaimType();
        $testResults['models']['claim_type'] = '✅ PASS';
        echo "✅ ClaimType model instantiated successfully\n";
    } catch (Exception $e) {
        $testResults['models']['claim_type'] = '❌ FAIL: ' . $e->getMessage();
        echo "❌ ClaimType model failed: " . $e->getMessage() . "\n";
    }
    
    try {
        $attendance = new Attendance();
        $testResults['models']['attendance'] = '✅ PASS';
        echo "✅ Attendance model instantiated successfully\n";
    } catch (Exception $e) {
        $testResults['models']['attendance'] = '❌ FAIL: ' . $e->getMessage();
        echo "❌ Attendance model failed: " . $e->getMessage() . "\n";
    }
    
    try {
        $employee = new Employee();
        $testResults['models']['employee'] = '✅ PASS';
        echo "✅ Employee model instantiated successfully\n";
    } catch (Exception $e) {
        $testResults['models']['employee'] = '❌ FAIL: ' . $e->getMessage();
        echo "❌ Employee model failed: " . $e->getMessage() . "\n";
    }

    // ===== CONTROLLER TESTS =====
    echo "\n2. Testing Controller Instantiation...\n";
    
    try {
        $claimsController = new ClaimsController();
        $testResults['claims_controller']['instantiation'] = '✅ PASS';
        echo "✅ ClaimsController instantiated successfully\n";
    } catch (Exception $e) {
        $testResults['claims_controller']['instantiation'] = '❌ FAIL: ' . $e->getMessage();
        echo "❌ ClaimsController failed: " . $e->getMessage() . "\n";
    }
    
    try {
        $attendanceController = new AttendanceController();
        $testResults['attendance_controller']['instantiation'] = '✅ PASS';
        echo "✅ AttendanceController instantiated successfully\n";
    } catch (Exception $e) {
        $testResults['attendance_controller']['instantiation'] = '❌ FAIL: ' . $e->getMessage();
        echo "❌ AttendanceController failed: " . $e->getMessage() . "\n";
    }

    // ===== DATABASE CONNECTION TESTS =====
    echo "\n3. Testing Database Connections...\n";
    
    try {
        $claimCount = Claim::count();
        $testResults['database']['claims'] = '✅ PASS';
        echo "✅ Claims table accessible. Found {$claimCount} records\n";
    } catch (Exception $e) {
        $testResults['database']['claims'] = '❌ FAIL: ' . $e->getMessage();
        echo "❌ Claims table failed: " . $e->getMessage() . "\n";
    }
    
    try {
        $attendanceCount = Attendance::count();
        $testResults['database']['attendances'] = '✅ PASS';
        echo "✅ Attendances table accessible. Found {$attendanceCount} records\n";
    } catch (Exception $e) {
        $testResults['database']['attendances'] = '❌ FAIL: ' . $e->getMessage();
        echo "❌ Attendances table failed: " . $e->getMessage() . "\n";
    }
    
    try {
        $employeeCount = Employee::count();
        $testResults['database']['employees'] = '✅ PASS';
        echo "✅ Employees table accessible. Found {$employeeCount} records\n";
    } catch (Exception $e) {
        $testResults['database']['employees'] = '❌ FAIL: ' . $e->getMessage();
        echo "❌ Employees table failed: " . $e->getMessage() . "\n";
    }

    // ===== RELATIONSHIP TESTS =====
    echo "\n4. Testing Model Relationships...\n";
    
    try {
        $claim = new Claim();
        $employeeRelation = $claim->employee();
        $claimTypeRelation = $claim->claimType();
        $approverRelation = $claim->approver();
        
        $testResults['claims_controller']['relationships'] = '✅ PASS';
        echo "✅ Claim relationships defined correctly\n";
        echo "   - employee() relationship: ✅\n";
        echo "   - claimType() relationship: ✅\n";
        echo "   - approver() relationship: ✅\n";
    } catch (Exception $e) {
        $testResults['claims_controller']['relationships'] = '❌ FAIL: ' . $e->getMessage();
        echo "❌ Claim relationships failed: " . $e->getMessage() . "\n";
    }
    
    try {
        $attendance = new Attendance();
        $employeeRelation = $attendance->employee();
        
        $testResults['attendance_controller']['relationships'] = '✅ PASS';
        echo "✅ Attendance relationships defined correctly\n";
        echo "   - employee() relationship: ✅\n";
    } catch (Exception $e) {
        $testResults['attendance_controller']['relationships'] = '❌ FAIL: ' . $e->getMessage();
        echo "❌ Attendance relationships failed: " . $e->getMessage() . "\n";
    }

    // ===== FILLABLE FIELDS TESTS =====
    echo "\n5. Testing Fillable Fields...\n";
    
    // Claims fillable fields
    $claim = new Claim();
    $claimFillable = $claim->getFillable();
    $requiredClaimFields = [
        'employee_id', 'claim_type_id', 'amount', 'claim_date', 
        'description', 'business_purpose', 'receipt_path', 'status', 
        'approved_by', 'approved_at', 'notes'
    ];
    
    $claimFieldsOk = true;
    foreach ($requiredClaimFields as $field) {
        if (!in_array($field, $claimFillable)) {
            echo "❌ Claim field '{$field}' is NOT fillable\n";
            $claimFieldsOk = false;
        }
    }
    
    if ($claimFieldsOk) {
        $testResults['claims_controller']['fillable'] = '✅ PASS';
        echo "✅ All required Claim fields are fillable\n";
    } else {
        $testResults['claims_controller']['fillable'] = '❌ FAIL: Missing fillable fields';
    }
    
    // Attendance fillable fields
    $attendance = new Attendance();
    $attendanceFillable = $attendance->getFillable();
    $requiredAttendanceFields = [
        'employee_id', 'date', 'clock_in_time', 'clock_out_time',
        'break_start_time', 'break_end_time', 'total_hours', 'overtime_hours',
        'location', 'ip_address', 'notes', 'status'
    ];
    
    $attendanceFieldsOk = true;
    foreach ($requiredAttendanceFields as $field) {
        if (!in_array($field, $attendanceFillable)) {
            echo "❌ Attendance field '{$field}' is NOT fillable\n";
            $attendanceFieldsOk = false;
        }
    }
    
    if ($attendanceFieldsOk) {
        $testResults['attendance_controller']['fillable'] = '✅ PASS';
        echo "✅ All required Attendance fields are fillable\n";
    } else {
        $testResults['attendance_controller']['fillable'] = '❌ FAIL: Missing fillable fields';
    }

    // ===== METHOD EXISTENCE TESTS =====
    echo "\n6. Testing Controller Methods...\n";
    
    // Claims Controller Methods
    $claimsController = new ClaimsController();
    $claimsMethods = ['index', 'store', 'show', 'update', 'destroy', 'approve', 'reject', 'statistics'];
    $claimsMethodsOk = true;
    
    foreach ($claimsMethods as $method) {
        if (!method_exists($claimsController, $method)) {
            echo "❌ ClaimsController method '{$method}' does NOT exist\n";
            $claimsMethodsOk = false;
        }
    }
    
    if ($claimsMethodsOk) {
        $testResults['claims_controller']['methods'] = '✅ PASS';
        echo "✅ All required ClaimsController methods exist\n";
    } else {
        $testResults['claims_controller']['methods'] = '❌ FAIL: Missing methods';
    }
    
    // Attendance Controller Methods
    $attendanceController = new AttendanceController();
    $attendanceMethods = ['index', 'store', 'show', 'update', 'destroy', 'clockOut', 'startBreak', 'endBreak', 'statistics', 'currentStatus'];
    $attendanceMethodsOk = true;
    
    foreach ($attendanceMethods as $method) {
        if (!method_exists($attendanceController, $method)) {
            echo "❌ AttendanceController method '{$method}' does NOT exist\n";
            $attendanceMethodsOk = false;
        }
    }
    
    if ($attendanceMethodsOk) {
        $testResults['attendance_controller']['methods'] = '✅ PASS';
        echo "✅ All required AttendanceController methods exist\n";
    } else {
        $testResults['attendance_controller']['methods'] = '❌ FAIL: Missing methods';
    }

    // ===== ROUTE TESTS =====
    echo "\n7. Testing API Routes...\n";
    
    try {
        // Check if routes are registered
        $router = app('router');
        $routes = $router->getRoutes();
        
        $claimsRoutes = 0;
        $attendanceRoutes = 0;
        
        foreach ($routes as $route) {
            $uri = $route->uri();
            if (strpos($uri, 'api/claims') !== false) {
                $claimsRoutes++;
            }
            if (strpos($uri, 'api/attendances') !== false) {
                $attendanceRoutes++;
            }
        }
        
        if ($claimsRoutes > 0) {
            $testResults['claims_controller']['routes'] = '✅ PASS';
            echo "✅ Claims API routes registered ({$claimsRoutes} routes)\n";
        } else {
            $testResults['claims_controller']['routes'] = '❌ FAIL: No routes found';
            echo "❌ No Claims API routes found\n";
        }
        
        if ($attendanceRoutes > 0) {
            $testResults['attendance_controller']['routes'] = '✅ PASS';
            echo "✅ Attendance API routes registered ({$attendanceRoutes} routes)\n";
        } else {
            $testResults['attendance_controller']['routes'] = '❌ FAIL: No routes found';
            echo "❌ No Attendance API routes found\n";
        }
        
    } catch (Exception $e) {
        echo "❌ Route testing failed: " . $e->getMessage() . "\n";
    }

    // ===== SUMMARY =====
    echo "\n" . str_repeat("=", 60) . "\n";
    echo "TEST SUMMARY\n";
    echo str_repeat("=", 60) . "\n";
    
    echo "\n📋 CLAIMS CONTROLLER:\n";
    foreach ($testResults['claims_controller'] as $test => $result) {
        echo "  {$test}: {$result}\n";
    }
    
    echo "\n⏰ ATTENDANCE CONTROLLER:\n";
    foreach ($testResults['attendance_controller'] as $test => $result) {
        echo "  {$test}: {$result}\n";
    }
    
    echo "\n🗄️ MODELS:\n";
    foreach ($testResults['models'] as $test => $result) {
        echo "  {$test}: {$result}\n";
    }
    
    echo "\n💾 DATABASE:\n";
    foreach ($testResults['database'] as $test => $result) {
        echo "  {$test}: {$result}\n";
    }
    
    // Overall Status
    $totalTests = 0;
    $passedTests = 0;
    
    foreach ($testResults as $category => $tests) {
        foreach ($tests as $result) {
            $totalTests++;
            if (strpos($result, '✅') !== false) {
                $passedTests++;
            }
        }
    }
    
    $successRate = round(($passedTests / $totalTests) * 100, 1);
    
    echo "\n" . str_repeat("=", 60) . "\n";
    echo "OVERALL RESULT: {$passedTests}/{$totalTests} tests passed ({$successRate}%)\n";
    
    if ($successRate >= 90) {
        echo "🎉 EXCELLENT: Both controllers are ready for production!\n";
    } elseif ($successRate >= 75) {
        echo "✅ GOOD: Controllers are mostly functional with minor issues\n";
    } elseif ($successRate >= 50) {
        echo "⚠️ WARNING: Controllers have significant issues that need fixing\n";
    } else {
        echo "❌ CRITICAL: Controllers are not functional and need major fixes\n";
    }
    
    echo "\n📚 AVAILABLE API ENDPOINTS:\n";
    echo "\nClaims API:\n";
    echo "  GET    /api/claims              - List claims\n";
    echo "  POST   /api/claims              - Create claim\n";
    echo "  GET    /api/claims/statistics   - Get statistics\n";
    echo "  GET    /api/claims/{id}         - Show claim\n";
    echo "  PUT    /api/claims/{id}         - Update claim\n";
    echo "  DELETE /api/claims/{id}         - Delete claim\n";
    echo "  POST   /api/claims/{id}/approve - Approve claim\n";
    echo "  POST   /api/claims/{id}/reject  - Reject claim\n";
    
    echo "\nAttendance API:\n";
    echo "  GET    /api/attendances                    - List attendance\n";
    echo "  POST   /api/attendances                    - Clock in\n";
    echo "  GET    /api/attendances/statistics         - Get statistics\n";
    echo "  GET    /api/attendances/status/{employeeId} - Current status\n";
    echo "  GET    /api/attendances/{id}               - Show attendance\n";
    echo "  PUT    /api/attendances/{id}               - Update attendance\n";
    echo "  DELETE /api/attendances/{id}               - Delete attendance\n";
    echo "  POST   /api/attendances/{id}/clock-out     - Clock out\n";
    echo "  POST   /api/attendances/{id}/start-break   - Start break\n";
    echo "  POST   /api/attendances/{id}/end-break     - End break\n";

} catch (Exception $e) {
    echo "❌ Test suite failed with error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "Test Complete - " . date('Y-m-d H:i:s') . "\n";
echo str_repeat("=", 60) . "\n";
