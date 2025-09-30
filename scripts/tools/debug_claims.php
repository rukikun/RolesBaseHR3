<?php
// Debug script for claims data issue
echo "Claims Debug Script\n";
echo "==================\n\n";

// Test database connection
try {
    $pdo = new PDO('mysql:host=localhost;dbname=hr3systemdb', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✓ Database connection successful\n\n";
    
    // Check table structures
    echo "Table Structures:\n";
    echo "-----------------\n";
    
    $tables = ['claim_types', 'claims', 'employees'];
    foreach ($tables as $table) {
        echo "\n$table table:\n";
        try {
            $stmt = $pdo->query("DESCRIBE $table");
            $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($columns as $col) {
                echo "  - {$col['Field']} ({$col['Type']}) " . ($col['Null'] == 'YES' ? 'NULL' : 'NOT NULL') . "\n";
            }
            
            // Count records
            $stmt = $pdo->query("SELECT COUNT(*) FROM $table");
            $count = $stmt->fetchColumn();
            echo "  Records: $count\n";
            
        } catch (Exception $e) {
            echo "  ERROR: " . $e->getMessage() . "\n";
        }
    }
    
    // Test the exact query from ClaimController
    echo "\n\nTesting ClaimController queries:\n";
    echo "--------------------------------\n";
    
    // Test claim types query
    echo "\nClaim Types (is_active = 1):\n";
    try {
        $stmt = $pdo->query("SELECT * FROM claim_types WHERE is_active = 1 ORDER BY name");
        $claimTypes = $stmt->fetchAll(PDO::FETCH_OBJ);
        echo "Found " . count($claimTypes) . " active claim types:\n";
        foreach ($claimTypes as $ct) {
            echo "  - {$ct->name} ({$ct->code}) - Max: \${$ct->max_amount}\n";
        }
    } catch (Exception $e) {
        echo "ERROR: " . $e->getMessage() . "\n";
    }
    
    // Test employees query
    echo "\nEmployees (status = 'active'):\n";
    try {
        $stmt = $pdo->query("SELECT * FROM employees WHERE status = 'active' ORDER BY first_name");
        $employees = $stmt->fetchAll(PDO::FETCH_OBJ);
        echo "Found " . count($employees) . " active employees:\n";
        foreach ($employees as $emp) {
            echo "  - {$emp->first_name} {$emp->last_name} (ID: {$emp->id})\n";
        }
    } catch (Exception $e) {
        echo "ERROR: " . $e->getMessage() . "\n";
    }
    
    // Test claims query with joins
    echo "\nClaims with joins:\n";
    try {
        $stmt = $pdo->query("
            SELECT 
                c.*,
                COALESCE(e.first_name, 'Unknown') as first_name,
                COALESCE(e.last_name, 'Employee') as last_name,
                CONCAT(COALESCE(e.first_name, 'Unknown'), ' ', COALESCE(e.last_name, 'Employee')) as employee_name,
                COALESCE(ct.name, 'Unknown Type') as claim_type_name,
                COALESCE(ct.code, 'N/A') as claim_type_code
            FROM claims c
            LEFT JOIN employees e ON c.employee_id = e.id
            LEFT JOIN claim_types ct ON c.claim_type_id = ct.id
            ORDER BY c.created_at DESC
        ");
        $claims = $stmt->fetchAll(PDO::FETCH_OBJ);
        echo "Found " . count($claims) . " claims:\n";
        foreach ($claims as $claim) {
            echo "  - {$claim->employee_name} - {$claim->claim_type_name} - \${$claim->amount} ({$claim->status})\n";
        }
    } catch (Exception $e) {
        echo "ERROR: " . $e->getMessage() . "\n";
    }
    
    // If no data exists, create some sample data
    if (count($claimTypes) == 0) {
        echo "\nNo claim types found. Creating sample data...\n";
        $pdo->exec("INSERT INTO claim_types (code, name, description, max_amount, requires_receipt, requires_approval, is_active) VALUES
            ('TR', 'Travel Reimbursement', 'Travel related expenses', 1000.00, 1, 1, 1),
            ('ME', 'Medical Expenses', 'Medical and health expenses', 500.00, 1, 1, 1),
            ('OF', 'Office Supplies', 'Office supplies and equipment', 200.00, 1, 0, 1),
            ('TC', 'Training Costs', 'Training and development costs', 2000.00, 1, 1, 1),
            ('OT', 'Other Expenses', 'Miscellaneous expenses', 300.00, 1, 1, 1)");
        echo "✓ Sample claim types created\n";
    }
    
    if (count($employees) == 0) {
        echo "\nNo active employees found. You may need to check the employees table.\n";
    }
    
    if (count($claims) == 0 && count($employees) > 0 && count($claimTypes) > 0) {
        echo "\nNo claims found. Creating sample claim...\n";
        $emp = $employees[0];
        $ct = $claimTypes[0];
        $pdo->exec("INSERT INTO claims (employee_id, claim_type_id, amount, description, status, claim_date) VALUES 
            ({$emp->id}, {$ct->id}, 150.00, 'Sample expense claim for testing', 'pending', CURDATE())");
        echo "✓ Sample claim created\n";
    }
    
} catch (Exception $e) {
    echo "✗ Database connection failed: " . $e->getMessage() . "\n";
}

echo "\n\nDone!\n";
