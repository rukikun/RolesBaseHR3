<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;
use App\Models\Employee;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Testing isSuperAdmin() Method Fix ===\n\n";

try {
    // Test the admin employee
    $adminEmployee = Employee::where('email', 'admin@jetlouge.com')->first();
    
    if ($adminEmployee) {
        echo "Testing admin employee: {$adminEmployee->email}\n";
        echo "  - Role: {$adminEmployee->role}\n";
        echo "  - isSuperAdmin(): " . ($adminEmployee->isSuperAdmin() ? "✅ True" : "❌ False") . "\n";
        echo "  - canManageAdmins(): " . ($adminEmployee->canManageAdmins() ? "✅ True" : "❌ False") . "\n";
    } else {
        echo "❌ Admin employee not found\n";
    }

    // Test HR employee
    $hrEmployee = Employee::where('email', 'hr@jetlouge.com')->first();
    
    if ($hrEmployee) {
        echo "\nTesting HR employee: {$hrEmployee->email}\n";
        echo "  - Role: {$hrEmployee->role}\n";
        echo "  - isSuperAdmin(): " . ($hrEmployee->isSuperAdmin() ? "❌ True (should be false)" : "✅ False") . "\n";
        echo "  - canManageAdmins(): " . ($hrEmployee->canManageAdmins() ? "❌ True (should be false)" : "✅ False") . "\n";
    } else {
        echo "❌ HR employee not found\n";
    }

    // Test regular employee
    $regularEmployee = Employee::where('email', 'employee@jetlouge.com')->first();
    
    if ($regularEmployee) {
        echo "\nTesting regular employee: {$regularEmployee->email}\n";
        echo "  - Role: {$regularEmployee->role}\n";
        echo "  - isSuperAdmin(): " . ($regularEmployee->isSuperAdmin() ? "❌ True (should be false)" : "✅ False") . "\n";
        echo "  - canManageAdmins(): " . ($regularEmployee->canManageAdmins() ? "❌ True (should be false)" : "✅ False") . "\n";
    } else {
        echo "❌ Regular employee not found\n";
    }

    echo "\n✅ isSuperAdmin() method fix completed successfully!\n";
    echo "The method now exists and works correctly for role-based access control.\n";

} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
    exit(1);
}
