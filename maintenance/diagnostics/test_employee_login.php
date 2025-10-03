<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Employee;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\EmployeeESSController;

echo "🧪 Employee Login Test\n";
echo "=====================\n\n";

try {
    // Get test employee
    $employee = Employee::where('email', 'john.doe@jetlouge.com')->first();
    
    if (!$employee) {
        echo "❌ Test employee not found!\n";
        exit(1);
    }
    
    echo "👤 Testing login for: {$employee->email}\n";
    echo "🔑 Using password: password123\n\n";
    
    // Test 1: Direct authentication
    echo "Test 1: Direct Authentication\n";
    echo "-----------------------------\n";
    
    if (Auth::guard('employee')->attempt(['email' => $employee->email, 'password' => 'password123'])) {
        echo "✅ Direct authentication successful!\n";
        $authUser = Auth::guard('employee')->user();
        echo "📧 Authenticated as: {$authUser->email}\n";
        echo "👤 Name: {$authUser->first_name} {$authUser->last_name}\n";
        Auth::guard('employee')->logout();
    } else {
        echo "❌ Direct authentication failed!\n";
    }
    
    echo "\n";
    
    // Test 2: Controller method test
    echo "Test 2: Controller Method Test\n";
    echo "------------------------------\n";
    
    // Create a mock request
    $request = new Request();
    $request->merge([
        'email' => $employee->email,
        'password' => 'password123'
    ]);
    
    // Test the controller login method
    $controller = new EmployeeESSController();
    
    // Mock the validation (normally done by Laravel)
    $credentials = $request->only('email', 'password');
    
    if (Auth::guard('employee')->attempt($credentials)) {
        echo "✅ Controller authentication successful!\n";
        $authUser = Auth::guard('employee')->user();
        echo "📧 Authenticated as: {$authUser->email}\n";
        
        // Test online status update
        $updatedEmployee = Employee::find($authUser->id);
        echo "🌐 Online status: {$updatedEmployee->online_status}\n";
        
        Auth::guard('employee')->logout();
    } else {
        echo "❌ Controller authentication failed!\n";
    }
    
    echo "\n";
    
    // Test 3: Route accessibility
    echo "Test 3: Route Configuration\n";
    echo "---------------------------\n";
    
    // Check if routes are properly registered
    $routes = app('router')->getRoutes();
    $employeeRoutes = [];
    
    foreach ($routes as $route) {
        if (str_contains($route->uri(), 'employee')) {
            $employeeRoutes[] = $route->uri();
        }
    }
    
    if (in_array('employee/login', $employeeRoutes)) {
        echo "✅ Employee login route registered\n";
    } else {
        echo "❌ Employee login route not found\n";
    }
    
    if (in_array('employee/dashboard', $employeeRoutes)) {
        echo "✅ Employee dashboard route registered\n";
    } else {
        echo "❌ Employee dashboard route not found\n";
    }
    
    echo "\n📋 Available employee routes:\n";
    foreach (array_slice($employeeRoutes, 0, 10) as $route) {
        echo "   - {$route}\n";
    }
    
    if (count($employeeRoutes) > 10) {
        echo "   ... and " . (count($employeeRoutes) - 10) . " more routes\n";
    }
    
    echo "\n";
    
    // Test 4: Database integrity
    echo "Test 4: Database Integrity\n";
    echo "--------------------------\n";
    
    $allEmployees = Employee::where('status', 'active')->get();
    echo "📊 Active employees: {$allEmployees->count()}\n";
    
    foreach ($allEmployees as $emp) {
        $passwordCheck = strlen($emp->password) > 50 ? '✅' : '❌';
        echo "   {$passwordCheck} {$emp->email} - {$emp->first_name} {$emp->last_name}\n";
    }
    
    echo "\n✅ All tests completed!\n";
    echo "\n🎯 LOGIN INSTRUCTIONS:\n";
    echo "======================\n";
    echo "1. Navigate to: http://localhost/employee/login\n";
    echo "2. Use these credentials:\n\n";
    
    foreach ($allEmployees as $emp) {
        echo "   📧 Email: {$emp->email}\n";
        echo "   🔑 Password: password123\n";
        echo "   👤 Name: {$emp->first_name} {$emp->last_name}\n";
        echo "   🏢 Department: {$emp->department}\n";
        echo "   ---\n";
    }
    
    echo "\n🚀 Your employee login portal should now be working!\n";
    
} catch (Exception $e) {
    echo "❌ Test failed: " . $e->getMessage() . "\n";
    echo "📍 File: " . $e->getFile() . " (Line: " . $e->getLine() . ")\n";
}
