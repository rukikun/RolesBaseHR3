<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Employee;
use Illuminate\Support\Facades\Hash;

echo "🔍 Checking employee records...\n\n";

try {
    $employees = Employee::select('id', 'email', 'first_name', 'last_name', 'password', 'status')->get();
    
    if ($employees->count() === 0) {
        echo "❌ No employees found in database!\n";
        echo "🔧 Creating test employee accounts...\n";
        
        // Create test employees
        $testEmployees = [
            [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => 'john.doe@jetlouge.com',
                'password' => Hash::make('password123'),
                'position' => 'Software Developer',
                'department' => 'Information Technology',
                'hire_date' => '2024-01-15',
                'salary' => 75000.00,
                'status' => 'active',
                'online_status' => 'offline'
            ],
            [
                'first_name' => 'Jane',
                'last_name' => 'Smith',
                'email' => 'jane.smith@jetlouge.com',
                'password' => Hash::make('password123'),
                'position' => 'HR Manager',
                'department' => 'Human Resources',
                'hire_date' => '2024-02-01',
                'salary' => 65000.00,
                'status' => 'active',
                'online_status' => 'offline'
            ]
        ];
        
        foreach ($testEmployees as $empData) {
            Employee::create($empData);
            echo "✅ Created employee: {$empData['first_name']} {$empData['last_name']} ({$empData['email']})\n";
        }
        
        echo "\n🔄 Re-checking employees...\n";
        $employees = Employee::select('id', 'email', 'first_name', 'last_name', 'password', 'status')->get();
    }
    
    echo "📊 Found {$employees->count()} employees:\n\n";
    
    foreach ($employees as $employee) {
        $passwordStatus = empty($employee->password) ? '❌ No Password' : 
                         (strlen($employee->password) > 50 ? '✅ Hashed' : '⚠️ Plain Text');
        
        echo "ID: {$employee->id}\n";
        echo "Name: {$employee->first_name} {$employee->last_name}\n";
        echo "Email: {$employee->email}\n";
        echo "Status: {$employee->status}\n";
        echo "Password: {$passwordStatus}\n";
        echo "---\n";
    }
    
    // Test authentication
    echo "\n🔐 Testing authentication...\n";
    
    $testEmail = $employees->first()->email;
    echo "Testing login for: {$testEmail}\n";
    
    // Test with common passwords
    $testPasswords = ['password123', 'password', '123456', 'admin'];
    
    foreach ($testPasswords as $password) {
        if (auth('employee')->attempt(['email' => $testEmail, 'password' => $password])) {
            echo "✅ Login successful with password: {$password}\n";
            auth('employee')->logout();
            break;
        } else {
            echo "❌ Failed with password: {$password}\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n✅ Employee check completed!\n";
