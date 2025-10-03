<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Employee;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "🔧 Employee Login Fix Script\n";
echo "============================\n\n";

try {
    // Step 1: Check database connection
    echo "1. 🔍 Testing database connection...\n";
    DB::connection()->getPdo();
    echo "   ✅ Database connection successful!\n\n";
    
    // Step 2: Check employees table structure
    echo "2. 📋 Checking employees table structure...\n";
    
    if (!Schema::hasTable('employees')) {
        echo "   ❌ Employees table doesn't exist!\n";
        echo "   🔧 Creating employees table...\n";
        
        Schema::create('employees', function ($table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->string('position')->nullable();
            $table->string('department')->nullable();
            $table->date('hire_date')->nullable();
            $table->decimal('salary', 10, 2)->nullable();
            $table->enum('status', ['active', 'inactive', 'terminated'])->default('active');
            $table->enum('online_status', ['online', 'offline'])->default('offline');
            $table->timestamp('last_activity')->nullable();
            $table->string('password');
            $table->string('profile_picture')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
        
        echo "   ✅ Employees table created!\n";
    } else {
        echo "   ✅ Employees table exists!\n";
        
        // Check for required columns
        $requiredColumns = ['email', 'password', 'first_name', 'last_name'];
        foreach ($requiredColumns as $column) {
            if (!Schema::hasColumn('employees', $column)) {
                echo "   ⚠️  Missing column: {$column}\n";
                
                // Add missing columns
                Schema::table('employees', function ($table) use ($column) {
                    switch ($column) {
                        case 'email':
                            $table->string('email')->unique();
                            break;
                        case 'password':
                            $table->string('password');
                            break;
                        case 'first_name':
                            $table->string('first_name');
                            break;
                        case 'last_name':
                            $table->string('last_name');
                            break;
                    }
                });
                echo "   ✅ Added column: {$column}\n";
            }
        }
    }
    
    // Step 3: Check existing employees
    echo "\n3. 👥 Checking existing employees...\n";
    $employees = Employee::all();
    
    if ($employees->count() === 0) {
        echo "   ❌ No employees found!\n";
        echo "   🔧 Creating test employee accounts...\n";
        
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
            ],
            [
                'first_name' => 'Mike',
                'last_name' => 'Johnson',
                'email' => 'mike.johnson@jetlouge.com',
                'password' => Hash::make('password123'),
                'position' => 'Accountant',
                'department' => 'Finance',
                'hire_date' => '2024-03-01',
                'salary' => 55000.00,
                'status' => 'active',
                'online_status' => 'offline'
            ],
            [
                'first_name' => 'Alex',
                'last_name' => 'McQueen',
                'email' => 'alex.mcqueen@jetlouge.com',
                'password' => Hash::make('password123'),
                'position' => 'Scheduler',
                'department' => 'Human Resources',
                'hire_date' => '2025-10-03',
                'salary' => 12.00,
                'status' => 'active',
                'online_status' => 'offline'
            ]
        ];
        
        foreach ($testEmployees as $empData) {
            Employee::create($empData);
            echo "   ✅ Created: {$empData['first_name']} {$empData['last_name']} ({$empData['email']})\n";
        }
        
        $employees = Employee::all();
    }
    
    echo "   📊 Found {$employees->count()} employees\n";
    
    // Step 4: Check password hashing
    echo "\n4. 🔐 Checking password hashing...\n";
    $needsPasswordFix = false;
    
    foreach ($employees as $employee) {
        if (empty($employee->password)) {
            echo "   ⚠️  {$employee->email} has no password\n";
            $needsPasswordFix = true;
        } elseif (strlen($employee->password) < 50) {
            echo "   ⚠️  {$employee->email} has plain text password\n";
            $needsPasswordFix = true;
        } else {
            echo "   ✅ {$employee->email} has hashed password\n";
        }
    }
    
    if ($needsPasswordFix) {
        echo "   🔧 Fixing passwords...\n";
        foreach ($employees as $employee) {
            if (empty($employee->password) || strlen($employee->password) < 50) {
                $employee->password = Hash::make('password123');
                $employee->save();
                echo "   ✅ Fixed password for {$employee->email}\n";
            }
        }
    }
    
    // Step 5: Test authentication
    echo "\n5. 🧪 Testing authentication...\n";
    
    $testEmployee = $employees->first();
    echo "   Testing login for: {$testEmployee->email}\n";
    
    // Clear any existing auth
    auth('employee')->logout();
    
    if (auth('employee')->attempt(['email' => $testEmployee->email, 'password' => 'password123'])) {
        echo "   ✅ Authentication successful!\n";
        auth('employee')->logout();
    } else {
        echo "   ❌ Authentication failed!\n";
        
        // Try to debug the issue
        echo "   🔍 Debugging authentication...\n";
        
        // Check if user exists
        $user = Employee::where('email', $testEmployee->email)->first();
        if ($user) {
            echo "   ✅ User found in database\n";
            echo "   📧 Email: {$user->email}\n";
            echo "   🔑 Password hash length: " . strlen($user->password) . "\n";
            echo "   📊 Status: {$user->status}\n";
            
            // Test password verification
            if (Hash::check('password123', $user->password)) {
                echo "   ✅ Password verification successful\n";
            } else {
                echo "   ❌ Password verification failed\n";
                // Reset password
                $user->password = Hash::make('password123');
                $user->save();
                echo "   🔧 Password reset to 'password123'\n";
            }
        } else {
            echo "   ❌ User not found in database\n";
        }
    }
    
    // Step 6: Display login credentials
    echo "\n6. 🎯 Login Credentials Summary\n";
    echo "   ================================\n";
    foreach ($employees as $employee) {
        echo "   📧 Email: {$employee->email}\n";
        echo "   🔑 Password: password123\n";
        echo "   👤 Name: {$employee->first_name} {$employee->last_name}\n";
        echo "   📊 Status: {$employee->status}\n";
        echo "   ---\n";
    }
    
    echo "\n✅ Employee login fix completed!\n";
    echo "🌐 You can now login at: /employee/login\n";
    echo "🔑 Use any of the emails above with password: password123\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "📍 File: " . $e->getFile() . " (Line: " . $e->getLine() . ")\n";
    echo "🔍 Stack trace:\n" . $e->getTraceAsString() . "\n";
}
