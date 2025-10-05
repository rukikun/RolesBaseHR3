<?php
// Test script to check claims data
require_once 'vendor/autoload.php';

use App\Http\Controllers\ClaimController;
use Illuminate\Http\Request;

// Create a simple test to see what data is being returned
try {
    $controller = new ClaimController();
    
    // Create a mock request
    $request = new Request();
    
    // Call the index method
    $response = $controller->index();
    
    echo "Claims Controller Test Results:\n";
    echo "==============================\n";
    
    // Check if response has data
    if ($response instanceof \Illuminate\View\View) {
        $data = $response->getData();
        
        echo "Data keys: " . implode(', ', array_keys($data)) . "\n";
        
        if (isset($data['claimTypes'])) {
            echo "Claim Types count: " . $data['claimTypes']->count() . "\n";
            if ($data['claimTypes']->count() > 0) {
                echo "First claim type: " . json_encode($data['claimTypes']->first()) . "\n";
            }
        }
        
        if (isset($data['claims'])) {
            echo "Claims count: " . $data['claims']->count() . "\n";
            if ($data['claims']->count() > 0) {
                echo "First claim: " . json_encode($data['claims']->first()) . "\n";
            }
        }
        
        if (isset($data['employees'])) {
            echo "Employees count: " . $data['employees']->count() . "\n";
            if ($data['employees']->count() > 0) {
                echo "First employee: " . json_encode($data['employees']->first()) . "\n";
            }
        }
        
        echo "Statistics:\n";
        echo "- Total Claims: " . ($data['totalClaims'] ?? 'N/A') . "\n";
        echo "- Pending Claims: " . ($data['pendingClaims'] ?? 'N/A') . "\n";
        echo "- Approved Claims: " . ($data['approvedClaims'] ?? 'N/A') . "\n";
        echo "- Total Amount: " . ($data['totalAmount'] ?? 'N/A') . "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

// Also test direct database connection
echo "\n\nDirect Database Test:\n";
echo "====================\n";

try {
    $pdo = new PDO('mysql:host=localhost;dbname=hr3_hr3systemdb', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check if tables exist
    $tables = ['claim_types', 'claims', 'employees'];
    foreach ($tables as $table) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) FROM $table");
            $count = $stmt->fetchColumn();
            echo "$table table: $count records\n";
        } catch (Exception $e) {
            echo "$table table: ERROR - " . $e->getMessage() . "\n";
        }
    }
    
    // Get sample data
    echo "\nSample claim types:\n";
    try {
        $stmt = $pdo->query("SELECT * FROM claim_types LIMIT 3");
        $claimTypes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($claimTypes as $ct) {
            echo "- " . $ct['name'] . " (" . $ct['code'] . ")\n";
        }
    } catch (Exception $e) {
        echo "Error getting claim types: " . $e->getMessage() . "\n";
    }
    
    echo "\nSample employees:\n";
    try {
        $stmt = $pdo->query("SELECT * FROM employees LIMIT 3");
        $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($employees as $emp) {
            echo "- " . $emp['first_name'] . " " . $emp['last_name'] . " (ID: " . $emp['id'] . ")\n";
        }
    } catch (Exception $e) {
        echo "Error getting employees: " . $e->getMessage() . "\n";
    }
    
    echo "\nSample claims:\n";
    try {
        $stmt = $pdo->query("SELECT c.*, e.first_name, e.last_name, ct.name as claim_type_name FROM claims c LEFT JOIN employees e ON c.employee_id = e.id LEFT JOIN claim_types ct ON c.claim_type_id = ct.id LIMIT 3");
        $claims = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($claims as $claim) {
            echo "- " . ($claim['first_name'] ?? 'Unknown') . " " . ($claim['last_name'] ?? 'Employee') . " - " . ($claim['claim_type_name'] ?? 'Unknown Type') . " - $" . $claim['amount'] . "\n";
        }
    } catch (Exception $e) {
        echo "Error getting claims: " . $e->getMessage() . "\n";
    }
    
} catch (Exception $e) {
    echo "Database connection error: " . $e->getMessage() . "\n";
}
