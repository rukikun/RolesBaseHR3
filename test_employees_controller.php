<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use App\Http\Controllers\EmployeesController;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== EmployeesController API Integration Test ===\n\n";

try {
    // Create controller instance
    $controller = new EmployeesController();
    
    // Create mock request
    $request = new Request();
    
    echo "1. Testing EmployeesController@index method...\n";
    
    // Call the index method
    $response = $controller->index($request);
    
    if ($response instanceof \Illuminate\View\View) {
        echo "✅ Controller method executed successfully\n";
        echo "  - View name: {$response->getName()}\n";
        
        $data = $response->getData();
        echo "  - Data keys: " . implode(', ', array_keys($data)) . "\n";
        
        if (isset($data['employees'])) {
            $employees = $data['employees'];
            echo "  - Employee count: " . $employees->count() . "\n";
            
            if ($employees->count() > 0) {
                echo "\n2. Employee Data Preview:\n";
                echo "=" . str_repeat("=", 80) . "\n";
                printf("%-4s %-25s %-20s %-15s %-10s\n", "ID", "Name", "Position", "Department", "Status");
                echo str_repeat("-", 80) . "\n";
                
                foreach ($employees->take(5) as $employee) {
                    printf("%-4s %-25s %-20s %-15s %-10s\n", 
                        "#" . str_pad($employee->id, 3, '0', STR_PAD_LEFT),
                        substr($employee->name, 0, 24),
                        substr($employee->position, 0, 19),
                        substr($employee->department, 0, 14),
                        ucfirst($employee->status)
                    );
                }
                
                if ($employees->count() > 5) {
                    echo "... and " . ($employees->count() - 5) . " more employees\n";
                }
            }
        }
        
        if (isset($data['stats'])) {
            $stats = $data['stats'];
            echo "\n3. Statistics:\n";
            foreach ($stats as $key => $value) {
                echo "  - " . ucwords(str_replace('_', ' ', $key)) . ": {$value}\n";
            }
        }
        
    } else {
        echo "⚠️  Controller returned unexpected response type\n";
        echo "Response type: " . get_class($response) . "\n";
    }
    
    echo "\n✅ EmployeesController test completed successfully!\n";
    echo "\nThe Employee Directory should now display the API data from:\n";
    echo "http://hr4.jetlougetravels-ph.com/api/employees\n";
    echo "\nAccess the Employee Directory at: http://localhost:8000/employees\n";
    
} catch (\Exception $e) {
    echo "❌ Test failed: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
    exit(1);
}
