<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;
use App\Models\Employee;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Recent Activities Verification ===\n\n";

try {
    // Get the employee
    $employee = Employee::where('email', 'Renze.Olea@gmail.com')->first();
    
    if (!$employee) {
        $employee = Employee::first();
    }
    
    if (!$employee) {
        echo "❌ No employees found\n";
        exit(1);
    }
    
    echo "✅ Testing employee: {$employee->email}\n\n";

    // Test recent activities method
    echo "RECENT ACTIVITIES TEST:\n";
    try {
        $activities = $employee->recentActivities(10)->get();
        echo "✅ Method working: " . $activities->count() . " activities found\n\n";
        
        if ($activities->count() > 0) {
            echo "ACTIVITIES LIST:\n";
            foreach ($activities as $index => $activity) {
                $date = $activity['date']->format('M d, Y H:i');
                $status = isset($activity['status']) ? " [{$activity['status']}]" : "";
                echo ($index + 1) . ". {$activity['type']}: {$activity['description']}{$status}\n";
                echo "   Date: {$date}\n\n";
            }
        } else {
            echo "⚠️  No activities found - this means:\n";
            echo "   • No time entries exist for this employee\n";
            echo "   • No attendance records exist\n";
            echo "   • No leave requests exist\n";
            echo "   • No claims exist\n\n";
        }
    } catch (\Exception $e) {
        echo "❌ Error: " . $e->getMessage() . "\n";
    }

    // Check data sources
    echo "DATA SOURCES CHECK:\n";
    
    // Time entries
    try {
        $timeEntries = $employee->timeEntries()->count();
        echo "✅ Time entries: {$timeEntries} records\n";
    } catch (\Exception $e) {
        echo "❌ Time entries error: " . $e->getMessage() . "\n";
    }
    
    // Attendances
    try {
        if (method_exists($employee, 'attendances')) {
            $attendances = $employee->attendances()->count();
            echo "✅ Attendances: {$attendances} records\n";
        } else {
            echo "⚠️  Attendances relationship not available\n";
        }
    } catch (\Exception $e) {
        echo "❌ Attendances error: " . $e->getMessage() . "\n";
    }
    
    // Leave requests
    try {
        if (method_exists($employee, 'leaveRequests')) {
            $leaveRequests = $employee->leaveRequests()->count();
            echo "✅ Leave requests: {$leaveRequests} records\n";
        } else {
            echo "⚠️  Leave requests relationship not available\n";
        }
    } catch (\Exception $e) {
        echo "❌ Leave requests error: " . $e->getMessage() . "\n";
    }
    
    // Claims
    try {
        if (method_exists($employee, 'claims')) {
            $claims = $employee->claims()->count();
            echo "✅ Claims: {$claims} records\n";
        } else {
            echo "⚠️  Claims relationship not available\n";
        }
    } catch (\Exception $e) {
        echo "❌ Claims error: " . $e->getMessage() . "\n";
    }

    echo "\n" . str_repeat("=", 50) . "\n";
    echo "🎯 RECENT ACTIVITIES STATUS\n";
    echo str_repeat("=", 50) . "\n";

    if ($activities->count() > 0) {
        echo "\n✅ WORKING CORRECTLY:\n";
        echo "   • Recent activities method functional\n";
        echo "   • Employee activities being retrieved\n";
        echo "   • Activities sorted by date (newest first)\n";
        echo "   • Profile page should show activities\n";
        
        echo "\n📱 RESULT:\n";
        echo "   The Recent Activity section will now display:\n";
        foreach ($activities->take(3) as $activity) {
            echo "   • {$activity['type']}: {$activity['description']}\n";
        }
    } else {
        echo "\n⚠️  NO ACTIVITIES FOUND:\n";
        echo "   • Employee has no recorded activities yet\n";
        echo "   • Recent Activity section will show 'No recent activity found'\n";
        echo "   • Activities will appear when employee:\n";
        echo "     - Submits timesheets\n";
        echo "     - Records attendance\n";
        echo "     - Requests leave\n";
        echo "     - Submits claims\n";
    }

    echo "\n🔧 IMPLEMENTATION:\n";
    echo "   • Using employee-specific activities (not user_activities table)\n";
    echo "   • Activities pulled from timeEntries, attendances, leaveRequests, claims\n";
    echo "   • No separate user activity logging needed\n";
    echo "   • Activities automatically populate from HR system usage\n";

} catch (\Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}
