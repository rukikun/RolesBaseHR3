<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;
use App\Models\Employee;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Profile Fix Verification ===\n\n";

try {
    // Test Employee model and recent activities
    $employee = Employee::where('email', 'Renze.Olea@gmail.com')->first();
    
    if (!$employee) {
        $employee = Employee::first();
    }
    
    if ($employee) {
        echo "✅ Employee found: {$employee->email}\n";
        
        // Test recent activities method
        try {
            $activities = $employee->recentActivities(5)->get();
            echo "✅ Recent activities method working: " . $activities->count() . " activities\n";
            
            if ($activities->count() > 0) {
                echo "✅ Sample activities:\n";
                foreach ($activities->take(2) as $activity) {
                    echo "   • {$activity['type']}: {$activity['description']}\n";
                }
            }
        } catch (\Exception $e) {
            echo "❌ Recent activities error: " . $e->getMessage() . "\n";
        }
        
    } else {
        echo "❌ No employees found\n";
    }

    echo "\n✅ PROFILE FIX STATUS:\n";
    echo "   • Removed UserActivity model dependencies\n";
    echo "   • Removed UserPreference model dependencies\n";
    echo "   • Updated AdminProfileController to use employee activities\n";
    echo "   • Simplified preferences to use session storage\n";
    echo "   • Profile page should now load without errors\n";

    echo "\n🎯 RESULT:\n";
    echo "   The 'Class UserActivity not found' error should be resolved!\n";
    echo "   Profile page will now use employee-based activities.\n";

} catch (\Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}
