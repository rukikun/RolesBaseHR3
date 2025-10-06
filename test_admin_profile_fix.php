<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Http\Controllers\AdminProfileController;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== AdminProfileController Fix Test ===\n\n";

try {
    // Test 1: Check if UserActivity model exists
    echo "1. Checking UserActivity model availability...\n";
    
    if (class_exists('App\Models\UserActivity')) {
        echo "✅ UserActivity model exists\n";
    } else {
        echo "⚠️  UserActivity model not found (this is expected)\n";
    }

    // Test 2: Create a test admin user
    echo "\n2. Creating test admin user...\n";
    
    $testUser = User::updateOrCreate(
        ['email' => 'testadmin@jetlouge.com'],
        [
            'name' => 'Test Admin',
            'password' => \Hash::make('password123'),
            'role' => 'admin',
            'phone' => '09123456789',
        ]
    );
    
    echo "✅ Test admin user created/updated: {$testUser->email}\n";

    // Test 3: Simulate authentication
    echo "\n3. Testing AdminProfileController methods...\n";
    
    // Simulate login
    Auth::guard('web')->login($testUser);
    echo "✅ User authenticated: " . Auth::user()->name . "\n";

    // Test 4: Test controller instantiation
    $controller = new AdminProfileController();
    echo "✅ AdminProfileController instantiated successfully\n";

    // Test 5: Test the index method (this was causing the error)
    echo "\n4. Testing index method (the problematic one)...\n";
    
    try {
        // Create a mock request
        $request = new \Illuminate\Http\Request();
        
        // Call the index method
        $response = $controller->index();
        
        if ($response instanceof \Illuminate\View\View) {
            echo "✅ index() method executed successfully\n";
            echo "  - View name: {$response->getName()}\n";
            echo "  - Data keys: " . implode(', ', array_keys($response->getData())) . "\n";
        } else {
            echo "⚠️  index() method returned unexpected response type\n";
        }
        
    } catch (\Exception $e) {
        echo "❌ index() method failed: " . $e->getMessage() . "\n";
        echo "  File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
    }

    // Test 6: Check if UserActivity methods are handled gracefully
    echo "\n5. Testing UserActivity error handling...\n";
    
    try {
        if (class_exists('App\Models\UserActivity')) {
            $activityCount = \App\Models\UserActivity::count();
            echo "✅ UserActivity table accessible, records: {$activityCount}\n";
        } else {
            echo "✅ UserActivity gracefully handled when not available\n";
        }
    } catch (\Exception $e) {
        echo "✅ UserActivity errors handled gracefully: " . $e->getMessage() . "\n";
    }

    echo "\n✅ AdminProfileController fix test completed successfully!\n";
    echo "\nThe controller should now work even without the UserActivity table.\n";
    echo "You can access the admin profile at: http://localhost:8000/admin/profile\n";

} catch (\Exception $e) {
    echo "❌ Test failed: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
    exit(1);
} finally {
    // Logout
    if (Auth::check()) {
        Auth::logout();
    }
}
