<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\Employee;

class DebugAuthController extends Controller
{
    public function debugEmployeeAuth()
    {
        try {
            $results = [];
            
            // 1. Test database connection
            $results['database_connection'] = 'Testing...';
            try {
                $count = DB::table('employees')->count();
                $results['database_connection'] = "✅ Connected - Found {$count} employees";
            } catch (\Exception $e) {
                $results['database_connection'] = "❌ Failed: " . $e->getMessage();
            }
            
            // 2. Test specific employee
            $testEmail = 'john.doe@jetlouge.com';
            $testPassword = 'password123';
            
            $results['employee_lookup'] = 'Testing...';
            try {
                $employee = DB::table('employees')->where('email', $testEmail)->first();
                if ($employee) {
                    $results['employee_lookup'] = "✅ Found: {$employee->first_name} {$employee->last_name}";
                    $results['password_length'] = strlen($employee->password);
                    
                    // Test password verification
                    if (password_verify($testPassword, $employee->password)) {
                        $results['password_verify'] = '✅ Password verification works';
                    } else {
                        $results['password_verify'] = '❌ Password verification failed';
                        
                        // Try to fix password
                        $newHash = password_hash($testPassword, PASSWORD_DEFAULT);
                        DB::table('employees')
                            ->where('email', $testEmail)
                            ->update(['password' => $newHash]);
                        $results['password_fix'] = '🔧 Updated password hash';
                    }
                } else {
                    $results['employee_lookup'] = '❌ Employee not found';
                }
            } catch (\Exception $e) {
                $results['employee_lookup'] = "❌ Error: " . $e->getMessage();
            }
            
            // 3. Test Laravel Hash
            $results['laravel_hash'] = 'Testing...';
            try {
                $employee = DB::table('employees')->where('email', $testEmail)->first();
                if ($employee && Hash::check($testPassword, $employee->password)) {
                    $results['laravel_hash'] = '✅ Laravel Hash::check works';
                } else {
                    $results['laravel_hash'] = '❌ Laravel Hash::check failed';
                }
            } catch (\Exception $e) {
                $results['laravel_hash'] = "❌ Error: " . $e->getMessage();
            }
            
            // 4. Test Auth Guard
            $results['auth_guard'] = 'Testing...';
            try {
                $credentials = ['email' => $testEmail, 'password' => $testPassword];
                
                if (Auth::guard('employee')->attempt($credentials)) {
                    $results['auth_guard'] = '✅ Authentication successful';
                    Auth::guard('employee')->logout();
                } else {
                    $results['auth_guard'] = '❌ Authentication failed';
                }
            } catch (\Exception $e) {
                $results['auth_guard'] = "❌ Error: " . $e->getMessage();
            }
            
            // 5. List all employees
            $results['all_employees'] = [];
            try {
                $employees = DB::table('employees')->select('id', 'first_name', 'last_name', 'email', 'status')->get();
                foreach ($employees as $emp) {
                    $results['all_employees'][] = [
                        'id' => $emp->id,
                        'name' => "{$emp->first_name} {$emp->last_name}",
                        'email' => $emp->email,
                        'status' => $emp->status
                    ];
                }
            } catch (\Exception $e) {
                $results['all_employees'] = "❌ Error: " . $e->getMessage();
            }
            
            return response()->json($results, 200, [], JSON_PRETTY_PRINT);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }
    
    public function fixEmployeePasswords()
    {
        try {
            $results = [];
            $testPassword = 'password123';
            
            // Get all employees
            $employees = DB::table('employees')->get();
            
            foreach ($employees as $employee) {
                $newHash = Hash::make($testPassword);
                
                DB::table('employees')
                    ->where('id', $employee->id)
                    ->update(['password' => $newHash]);
                
                $results[] = "✅ Updated password for {$employee->email}";
            }
            
            return response()->json([
                'success' => true,
                'message' => 'All employee passwords updated',
                'results' => $results,
                'test_credentials' => [
                    'email' => 'john.doe@jetlouge.com',
                    'password' => $testPassword
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    public function createTestEmployee()
    {
        try {
            $testEmail = 'test.login@jetlouge.com';
            $testPassword = 'password123';
            
            // Delete if exists
            DB::table('employees')->where('email', $testEmail)->delete();
            
            // Create new employee
            $employeeId = DB::table('employees')->insertGetId([
                'first_name' => 'Test',
                'last_name' => 'Login',
                'email' => $testEmail,
                'phone' => '+63 999 888 7777',
                'position' => 'Test Employee',
                'department' => 'Testing',
                'hire_date' => now()->toDateString(),
                'salary' => 50000.00,
                'status' => 'active',
                'online_status' => 'offline',
                'password' => Hash::make($testPassword),
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            // Test authentication immediately
            $credentials = ['email' => $testEmail, 'password' => $testPassword];
            $authTest = Auth::guard('employee')->attempt($credentials);
            
            if ($authTest) {
                Auth::guard('employee')->logout();
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Test employee created successfully',
                'employee_id' => $employeeId,
                'credentials' => [
                    'email' => $testEmail,
                    'password' => $testPassword
                ],
                'auth_test' => $authTest ? '✅ Authentication works' : '❌ Authentication failed'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
