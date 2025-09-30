<?php
// Direct database test to verify data exists and can be retrieved
echo "Direct Database Test\n";
echo "===================\n\n";

try {
    $pdo = new PDO('mysql:host=localhost;dbname=hr3systemdb', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✓ Database connection successful\n\n";
    
    // Test claim_types table
    echo "CLAIM TYPES:\n";
    echo "------------\n";
    
    // Check table structure
    $stmt = $pdo->query("DESCRIBE claim_types");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Table structure:\n";
    foreach ($columns as $col) {
        echo "  - {$col['Field']} ({$col['Type']})\n";
    }
    
    // Get all data
    $stmt = $pdo->query("SELECT * FROM claim_types");
    $claimTypes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "\nTotal records: " . count($claimTypes) . "\n";
    
    foreach ($claimTypes as $ct) {
        $active = isset($ct['is_active']) ? ($ct['is_active'] ? 'Active' : 'Inactive') : 'N/A';
        echo "  - {$ct['name']} ({$ct['code']}) - $active\n";
    }
    
    // Test with is_active filter
    echo "\nTesting is_active filter:\n";
    try {
        $stmt = $pdo->query("SELECT * FROM claim_types WHERE is_active = 1");
        $activeTypes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "Active claim types: " . count($activeTypes) . "\n";
    } catch (Exception $e) {
        echo "ERROR with is_active filter: " . $e->getMessage() . "\n";
    }
    
    // Test claims table
    echo "\n\nCLAIMS:\n";
    echo "-------\n";
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM claims");
    $claimsCount = $stmt->fetchColumn();
    echo "Total claims: $claimsCount\n";
    
    if ($claimsCount > 0) {
        $stmt = $pdo->query("SELECT * FROM claims LIMIT 3");
        $claims = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($claims as $claim) {
            echo "  - Claim ID {$claim['id']}: \${$claim['amount']} - {$claim['status']}\n";
        }
    }
    
    // Test employees table
    echo "\n\nEMPLOYEES:\n";
    echo "----------\n";
    
    try {
        $stmt = $pdo->query("SELECT COUNT(*) FROM employees WHERE status = 'active'");
        $empCount = $stmt->fetchColumn();
        echo "Active employees: $empCount\n";
        
        if ($empCount > 0) {
            $stmt = $pdo->query("SELECT * FROM employees WHERE status = 'active' LIMIT 3");
            $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($employees as $emp) {
                echo "  - {$emp['first_name']} {$emp['last_name']} (ID: {$emp['id']})\n";
            }
        }
    } catch (Exception $e) {
        echo "ERROR with employees: " . $e->getMessage() . "\n";
    }
    
} catch (Exception $e) {
    echo "✗ Database connection failed: " . $e->getMessage() . "\n";
}

echo "\nDone!\n";
