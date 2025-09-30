<?php
// Debug script to check claims employee issue

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== DEBUGGING CLAIMS EMPLOYEE ISSUE ===\n\n";

try {
    // Test database connection
    echo "1. Testing database connection...\n";
    $pdo = new PDO('mysql:host=localhost;dbname=hr3systemdb', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Database connection successful\n\n";
    
    // Check employees table
    echo "2. Checking employees table...\n";
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM employees");
    $employeeCount = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Total employees: " . $employeeCount['count'] . "\n";
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM employees WHERE status = 'active'");
    $activeEmployeeCount = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Active employees: " . $activeEmployeeCount['count'] . "\n";
    
    // Show first 5 employees
    echo "\nFirst 5 employees:\n";
    $stmt = $pdo->query("SELECT id, first_name, last_name, status FROM employees ORDER BY id LIMIT 5");
    $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($employees as $employee) {
        echo "- ID: {$employee['id']}, Name: {$employee['first_name']} {$employee['last_name']}, Status: {$employee['status']}\n";
    }
    
    // Check claim_types table
    echo "\n3. Checking claim_types table...\n";
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM claim_types");
    $claimTypeCount = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Total claim types: " . $claimTypeCount['count'] . "\n";
    
    // Show first 5 claim types
    echo "\nFirst 5 claim types:\n";
    $stmt = $pdo->query("SELECT id, name, code FROM claim_types ORDER BY id LIMIT 5");
    $claimTypes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($claimTypes as $claimType) {
        echo "- ID: {$claimType['id']}, Name: {$claimType['name']}, Code: {$claimType['code']}\n";
    }
    
    // Test ClaimController data retrieval
    echo "\n4. Testing ClaimController data retrieval...\n";
    $controller = new \App\Http\Controllers\ClaimController();
    
    // Use reflection to call the index method
    $reflection = new ReflectionClass($controller);
    $method = $reflection->getMethod('index');
    
    // Capture the view data
    ob_start();
    $response = $method->invoke($controller);
    ob_end_clean();
    
    // Extract view data
    if ($response instanceof \Illuminate\View\View) {
        $viewData = $response->getData();
        echo "Controller data:\n";
        echo "- Employees count: " . (isset($viewData['employees']) ? $viewData['employees']->count() : 'NOT SET') . "\n";
        echo "- Claim types count: " . (isset($viewData['claimTypes']) ? $viewData['claimTypes']->count() : 'NOT SET') . "\n";
        echo "- Claims count: " . (isset($viewData['claims']) ? $viewData['claims']->count() : 'NOT SET') . "\n";
        
        if (isset($viewData['employees']) && $viewData['employees']->count() > 0) {
            echo "\nFirst employee from controller:\n";
            $firstEmployee = $viewData['employees']->first();
            echo "- ID: " . ($firstEmployee->id ?? 'N/A') . "\n";
            echo "- Name: " . ($firstEmployee->first_name ?? 'N/A') . " " . ($firstEmployee->last_name ?? 'N/A') . "\n";
            echo "- Status: " . ($firstEmployee->status ?? 'N/A') . "\n";
        }
    }
    
    echo "\n✅ Debug completed successfully!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
