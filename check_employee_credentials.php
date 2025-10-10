<?php

// Bootstrap Laravel
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== EMPLOYEE CREDENTIALS CHECK ===\n\n";

$testEmail = 'johnkaizer19.jh@gmail.com';

echo "Checking employee: {$testEmail}\n\n";

try {
    $employee = \App\Models\Employee::where('email', $testEmail)->first();
    
    if (!$employee) {
        echo "❌ Employee not found!\n\n";
        echo "Creating test employee...\n";
        
        // Create employee if doesn't exist
        $employee = \App\Models\Employee::create([
            'employee_number' => 'EMP001',
            'first_name' => 'John',
            'last_name' => 'Kaizer',
            'email' => $testEmail,
            'password' => \Illuminate\Support\Facades\Hash::make('password123'),
            'position' => 'System Administrator',
            'department' => 'IT',
            'role' => 'admin',
            'status' => 'active',
            'hire_date' => now(),
        ]);
        
        echo "✅ Employee created successfully!\n";
    }
    
    echo "Employee Details:\n";
    echo "  - ID: {$employee->id}\n";
    echo "  - Name: {$employee->first_name} {$employee->last_name}\n";
    echo "  - Email: {$employee->email}\n";
    echo "  - Position: {$employee->position}\n";
    echo "  - Department: {$employee->department}\n";
    echo "  - Role: {$employee->role}\n";
    echo "  - Status: {$employee->status}\n";
    echo "  - Can Access Dashboard: " . ($employee->canAccessDashboard() ? 'YES' : 'NO') . "\n";
    echo "  - Password Hash: " . substr($employee->password, 0, 30) . "...\n";
    
    // Test password verification
    echo "\nTesting password 'password123':\n";
    if (\Illuminate\Support\Facades\Hash::check('password123', $employee->password)) {
        echo "✅ Password verification successful\n";
    } else {
        echo "❌ Password verification failed\n";
        echo "Updating password...\n";
        $employee->update(['password' => \Illuminate\Support\Facades\Hash::make('password123')]);
        echo "✅ Password updated\n";
    }
    
    // Test Auth attempt
    echo "\nTesting Laravel Auth::attempt:\n";
    $credentials = ['email' => $testEmail, 'password' => 'password123'];
    
    if (\Illuminate\Support\Facades\Auth::guard('employee')->attempt($credentials)) {
        echo "✅ Auth::attempt successful\n";
        \Illuminate\Support\Facades\Auth::guard('employee')->logout();
    } else {
        echo "❌ Auth::attempt failed\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n=== SUMMARY ===\n";
echo "Employee should now be ready for 2FA login testing.\n";
echo "Test at: https://hr3.jetlougetravels-ph.com/admin/login\n";
echo "Email: {$testEmail}\n";
echo "Password: password123\n";

?>
