<?php
// Simple test to check if data exists in database and controller
echo "Testing Claims Data\n";
echo "==================\n\n";

// Test 1: Direct database check
echo "1. Direct Database Check:\n";
try {
    $pdo = new PDO('mysql:host=localhost;dbname=hr3systemdb', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check claim_types
    $stmt = $pdo->query("SELECT COUNT(*) FROM claim_types WHERE is_active = 1");
    $claimTypesCount = $stmt->fetchColumn();
    echo "   Active claim types: $claimTypesCount\n";
    
    if ($claimTypesCount == 0) {
        echo "   Creating sample claim types...\n";
        $pdo->exec("INSERT INTO claim_types (name, code, description, max_amount, requires_attachment, is_active) VALUES
            ('Travel Expenses', 'TRAVEL', 'Business travel related expenses', 5000.00, 1, 1),
            ('Office Supplies', 'OFFICE', 'Office supplies and equipment', 1000.00, 1, 1),
            ('Meal Allowance', 'MEAL', 'Business meal expenses', 500.00, 1, 1)");
        
        $stmt = $pdo->query("SELECT COUNT(*) FROM claim_types WHERE is_active = 1");
        $claimTypesCount = $stmt->fetchColumn();
        echo "   Created claim types: $claimTypesCount\n";
    }
    
    // Show sample claim types
    $stmt = $pdo->query("SELECT * FROM claim_types WHERE is_active = 1 LIMIT 3");
    $types = $stmt->fetchAll(PDO::FETCH_OBJ);
    foreach ($types as $type) {
        echo "   - {$type->name} ({$type->code})\n";
    }
    
    // Check employees
    $stmt = $pdo->query("SELECT COUNT(*) FROM employees WHERE status = 'active'");
    $employeesCount = $stmt->fetchColumn();
    echo "   Active employees: $employeesCount\n";
    
    // Check claims
    $stmt = $pdo->query("SELECT COUNT(*) FROM claims");
    $claimsCount = $stmt->fetchColumn();
    echo "   Total claims: $claimsCount\n";
    
} catch (Exception $e) {
    echo "   ERROR: " . $e->getMessage() . "\n";
}

echo "\n2. Testing Controller Data:\n";

// Test 2: Check what the controller returns
try {
    // Simulate Laravel environment
    require_once 'vendor/autoload.php';
    
    // Create a mock request to test the controller
    $app = require_once 'bootstrap/app.php';
    $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
    
    $controller = new \App\Http\Controllers\ClaimController();
    $response = $controller->index();
    
    if ($response instanceof \Illuminate\View\View) {
        $data = $response->getData();
        echo "   Controller data keys: " . implode(', ', array_keys($data)) . "\n";
        echo "   Claim types count: " . $data['claimTypes']->count() . "\n";
        echo "   Employees count: " . $data['employees']->count() . "\n";
        echo "   Claims count: " . $data['claims']->count() . "\n";
        
        if ($data['claimTypes']->count() > 0) {
            echo "   First claim type: " . $data['claimTypes']->first()->name . "\n";
        }
    } else {
        echo "   ERROR: Controller did not return a view\n";
    }
    
} catch (Exception $e) {
    echo "   ERROR: " . $e->getMessage() . "\n";
}

echo "\nDone!\n";
