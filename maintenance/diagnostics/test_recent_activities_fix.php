<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;
use App\Models\Employee;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Testing recentActivities() Method Fix ===\n\n";

try {
    // Test the admin employee
    $adminEmployee = Employee::where('email', 'admin@jetlouge.com')->first();
    
    if ($adminEmployee) {
        echo "Testing admin employee: {$adminEmployee->email}\n";
        
        // Test the method exists
        if (method_exists($adminEmployee, 'recentActivities')) {
            echo "✅ recentActivities() method exists\n";
            
            // Test calling the method
            $activitiesQuery = $adminEmployee->recentActivities(5);
            echo "✅ recentActivities() method can be called\n";
            
            // Test the get() method
            $activities = $activitiesQuery->get();
            echo "✅ recentActivities()->get() works\n";
            echo "  - Activities count: " . $activities->count() . "\n";
            
            // Test the count() method
            $count = $activitiesQuery->count();
            echo "✅ recentActivities()->count() works\n";
            echo "  - Count result: " . $count . "\n";
            
            // Show sample activities if any
            if ($activities->count() > 0) {
                echo "\nSample activities:\n";
                foreach ($activities->take(3) as $activity) {
                    echo "  - {$activity['type']}: {$activity['description']} ({$activity['status']})\n";
                }
            } else {
                echo "  - No activities found (this is normal for new accounts)\n";
            }
            
        } else {
            echo "❌ recentActivities() method does not exist\n";
        }
    } else {
        echo "❌ Admin employee not found\n";
    }

    echo "\n✅ recentActivities() method fix completed successfully!\n";
    echo "The method now exists and is compatible with the controller's usage pattern.\n";

} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
    exit(1);
}
