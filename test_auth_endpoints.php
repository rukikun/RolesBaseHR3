<?php
// Test authentication endpoints

echo "🔍 Testing Employee Authentication Endpoints\n";
echo "==========================================\n\n";

$baseUrl = 'http://127.0.0.1:8000';

// Function to make HTTP request
function makeRequest($url) {
    $context = stream_context_create([
        'http' => [
            'timeout' => 10,
            'ignore_errors' => true
        ]
    ]);
    
    $response = file_get_contents($url, false, $context);
    
    if ($response === false) {
        return ['error' => 'Failed to connect to server'];
    }
    
    return json_decode($response, true);
}

// 1. Debug employee authentication
echo "1. Testing debug endpoint...\n";
$debugResult = makeRequest($baseUrl . '/debug-employee-auth');

if (isset($debugResult['error'])) {
    echo "   ❌ Error: " . $debugResult['error'] . "\n";
} else {
    echo "   📊 Database Connection: " . ($debugResult['database_connection'] ?? 'Unknown') . "\n";
    echo "   👤 Employee Lookup: " . ($debugResult['employee_lookup'] ?? 'Unknown') . "\n";
    echo "   🔑 Password Verify: " . ($debugResult['password_verify'] ?? 'Unknown') . "\n";
    echo "   🔐 Laravel Hash: " . ($debugResult['laravel_hash'] ?? 'Unknown') . "\n";
    echo "   🛡️  Auth Guard: " . ($debugResult['auth_guard'] ?? 'Unknown') . "\n";
    
    if (isset($debugResult['all_employees'])) {
        echo "   📋 Available Employees:\n";
        foreach ($debugResult['all_employees'] as $emp) {
            echo "      - {$emp['name']} ({$emp['email']}) - {$emp['status']}\n";
        }
    }
}

echo "\n";

// 2. Fix employee passwords if needed
if (isset($debugResult['password_verify']) && strpos($debugResult['password_verify'], '❌') !== false) {
    echo "2. Fixing employee passwords...\n";
    $fixResult = makeRequest($baseUrl . '/fix-employee-passwords');
    
    if (isset($fixResult['success']) && $fixResult['success']) {
        echo "   ✅ Passwords fixed successfully\n";
        echo "   📧 Test with: " . $fixResult['test_credentials']['email'] . "\n";
        echo "   🔑 Password: " . $fixResult['test_credentials']['password'] . "\n";
    } else {
        echo "   ❌ Failed to fix passwords\n";
    }
    echo "\n";
}

// 3. Create test employee
echo "3. Creating fresh test employee...\n";
$createResult = makeRequest($baseUrl . '/create-test-employee');

if (isset($createResult['success']) && $createResult['success']) {
    echo "   ✅ Test employee created successfully\n";
    echo "   📧 Email: " . $createResult['credentials']['email'] . "\n";
    echo "   🔑 Password: " . $createResult['credentials']['password'] . "\n";
    echo "   🛡️  Auth Test: " . $createResult['auth_test'] . "\n";
} else {
    echo "   ❌ Failed to create test employee\n";
    if (isset($createResult['error'])) {
        echo "   Error: " . $createResult['error'] . "\n";
    }
}

echo "\n🎯 NEXT STEPS:\n";
echo "=============\n";
echo "1. Go to: http://127.0.0.1:8000/portal-selection\n";
echo "2. Click 'Employee Portal'\n";
echo "3. Try these credentials:\n";
echo "   Email: test.login@jetlouge.com\n";
echo "   Password: password123\n";
echo "\nOr try:\n";
echo "   Email: john.doe@jetlouge.com\n";
echo "   Password: password123\n";

?>
