<?php
// Simple database check for claims data
try {
    $pdo = new PDO('mysql:host=localhost;dbname=hr3systemdb', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Database Connection: SUCCESS\n";
    echo "============================\n\n";
    
    // Check tables exist and count records
    $tables = ['claim_types', 'claims', 'employees'];
    foreach ($tables as $table) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) FROM $table");
            $count = $stmt->fetchColumn();
            echo "$table: $count records\n";
        } catch (Exception $e) {
            echo "$table: ERROR - " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n--- CLAIM TYPES ---\n";
    try {
        $stmt = $pdo->query("SELECT id, name, code, is_active FROM claim_types ORDER BY name");
        $claimTypes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (empty($claimTypes)) {
            echo "No claim types found!\n";
            
            // Insert sample data
            echo "Inserting sample claim types...\n";
            $pdo->exec("INSERT INTO claim_types (code, name, description, max_amount, requires_receipt, requires_approval, is_active) VALUES
                ('TR', 'Travel Reimbursement', 'Travel related expenses', 1000.00, 1, 1, 1),
                ('ME', 'Medical Expenses', 'Medical and health expenses', 500.00, 1, 1, 1),
                ('OF', 'Office Supplies', 'Office supplies and equipment', 200.00, 1, 0, 1),
                ('TC', 'Training Costs', 'Training and development costs', 2000.00, 1, 1, 1),
                ('OT', 'Other Expenses', 'Miscellaneous expenses', 300.00, 1, 1, 1)");
            
            $stmt = $pdo->query("SELECT id, name, code, is_active FROM claim_types ORDER BY name");
            $claimTypes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        foreach ($claimTypes as $ct) {
            echo "- {$ct['name']} ({$ct['code']}) - Active: " . ($ct['is_active'] ? 'Yes' : 'No') . "\n";
        }
    } catch (Exception $e) {
        echo "Error with claim_types: " . $e->getMessage() . "\n";
    }
    
    echo "\n--- EMPLOYEES ---\n";
    try {
        $stmt = $pdo->query("SELECT id, first_name, last_name, status FROM employees WHERE status = 'active' ORDER BY first_name LIMIT 5");
        $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (empty($employees)) {
            echo "No active employees found!\n";
        } else {
            foreach ($employees as $emp) {
                echo "- {$emp['first_name']} {$emp['last_name']} (ID: {$emp['id']})\n";
            }
        }
    } catch (Exception $e) {
        echo "Error with employees: " . $e->getMessage() . "\n";
    }
    
    echo "\n--- CLAIMS ---\n";
    try {
        $stmt = $pdo->query("
            SELECT c.id, c.amount, c.status, c.created_at,
                   e.first_name, e.last_name,
                   ct.name as claim_type_name
            FROM claims c 
            LEFT JOIN employees e ON c.employee_id = e.id 
            LEFT JOIN claim_types ct ON c.claim_type_id = ct.id 
            ORDER BY c.created_at DESC 
            LIMIT 5
        ");
        $claims = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (empty($claims)) {
            echo "No claims found!\n";
            
            // Insert sample claim if we have employees and claim types
            $empStmt = $pdo->query("SELECT id FROM employees LIMIT 1");
            $emp = $empStmt->fetch(PDO::FETCH_ASSOC);
            $ctStmt = $pdo->query("SELECT id FROM claim_types LIMIT 1");
            $ct = $ctStmt->fetch(PDO::FETCH_ASSOC);
            
            if ($emp && $ct) {
                echo "Inserting sample claim...\n";
                $pdo->exec("INSERT INTO claims (employee_id, claim_type_id, amount, description, status, claim_date) VALUES 
                    ({$emp['id']}, {$ct['id']}, 150.00, 'Sample expense claim', 'pending', CURDATE())");
                
                // Re-query
                $stmt = $pdo->query("
                    SELECT c.id, c.amount, c.status, c.created_at,
                           e.first_name, e.last_name,
                           ct.name as claim_type_name
                    FROM claims c 
                    LEFT JOIN employees e ON c.employee_id = e.id 
                    LEFT JOIN claim_types ct ON c.claim_type_id = ct.id 
                    ORDER BY c.created_at DESC 
                    LIMIT 5
                ");
                $claims = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
        }
        
        foreach ($claims as $claim) {
            echo "- {$claim['first_name']} {$claim['last_name']} - {$claim['claim_type_name']} - \${$claim['amount']} ({$claim['status']})\n";
        }
    } catch (Exception $e) {
        echo "Error with claims: " . $e->getMessage() . "\n";
    }
    
} catch (Exception $e) {
    echo "Database connection failed: " . $e->getMessage() . "\n";
}
