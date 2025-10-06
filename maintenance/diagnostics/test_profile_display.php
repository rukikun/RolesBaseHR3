<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;
use App\Models\Employee;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Testing Profile Display Changes ===\n\n";

try {
    // Test all employee accounts
    $employees = Employee::whereIn('email', [
        'admin@jetlouge.com',
        'hr@jetlouge.com', 
        'manager@jetlouge.com',
        'employee@jetlouge.com'
    ])->get();

    foreach ($employees as $employee) {
        echo "Employee: {$employee->email}\n";
        echo "  - First Name: {$employee->first_name}\n";
        echo "  - Last Name: {$employee->last_name}\n";
        echo "  - Full Name: {$employee->full_name}\n";
        echo "  - Role: {$employee->role}\n";
        echo "  - Department: {$employee->department}\n";
        echo "  - Position: {$employee->position}\n";
        echo "  - Initial: " . strtoupper(substr($employee->first_name ?? 'A', 0, 1)) . "\n";
        echo "  - Display Role: " . ucfirst($employee->role) . "\n";
        echo "  - Display Department: " . ($employee->department ?? 'Administration') . "\n";
        echo "\n";
    }

    echo "✅ Profile display test completed!\n";
    echo "\nExpected changes in UI:\n";
    echo "- Navbar dropdown will show employee's full name instead of 'Admin'\n";
    echo "- Profile dropdown will show employee's full name and role\n";
    echo "- Sidebar will show employee's full name and role-department\n";
    echo "- Initial in avatar will be based on first name\n";

} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
    exit(1);
}
