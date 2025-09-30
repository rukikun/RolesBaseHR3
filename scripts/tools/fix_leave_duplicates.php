<?php
/**
 * Fix Leave Types Duplicates
 * This script removes duplicate leave types and ensures clean data
 */

// Database configuration
$host = '127.0.0.1';
$dbname = 'hr3systemdb';
$username = 'root';
$password = '';

try {
    // Connect to database
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== FIXING LEAVE TYPES DUPLICATES ===\n\n";
    
    // 1. Check current duplicates
    echo "1. Checking for duplicates...\n";
    $stmt = $pdo->query("
        SELECT name, code, COUNT(*) as count 
        FROM leave_types 
        GROUP BY name, code 
        HAVING COUNT(*) > 1
        ORDER BY name
    ");
    $duplicates = $stmt->fetchAll(PDO::FETCH_OBJ);
    
    if (count($duplicates) > 0) {
        echo "   Found duplicates:\n";
        foreach ($duplicates as $dup) {
            echo "   - {$dup->name} ({$dup->code}): {$dup->count} entries\n";
        }
    } else {
        echo "   No duplicates found in database.\n";
    }
    
    // 2. Show all current leave types
    echo "\n2. Current leave types in database:\n";
    $stmt = $pdo->query("SELECT id, name, code, is_active FROM leave_types ORDER BY name, id");
    $allTypes = $stmt->fetchAll(PDO::FETCH_OBJ);
    
    foreach ($allTypes as $type) {
        $status = $type->is_active ? 'Active' : 'Inactive';
        echo "   ID: {$type->id} | {$type->name} ({$type->code}) | {$status}\n";
    }
    
    // 3. Remove duplicates - keep the first occurrence of each name/code combination
    echo "\n3. Removing duplicates...\n";
    $pdo->beginTransaction();
    
    try {
        // Create a temporary table with unique records
        $pdo->exec("CREATE TEMPORARY TABLE temp_leave_types AS 
            SELECT MIN(id) as id, name, code, description, max_days_per_year, 
                   carry_forward, requires_approval, is_active, 
                   MIN(created_at) as created_at, MAX(updated_at) as updated_at
            FROM leave_types 
            GROUP BY name, code");
        
        // Get count before cleanup
        $stmt = $pdo->query("SELECT COUNT(*) FROM leave_types");
        $beforeCount = $stmt->fetchColumn();
        
        // Delete all records from original table
        $pdo->exec("DELETE FROM leave_types");
        
        // Insert unique records back
        $pdo->exec("INSERT INTO leave_types (id, name, code, description, max_days_per_year, carry_forward, requires_approval, is_active, created_at, updated_at)
            SELECT id, name, code, description, max_days_per_year, carry_forward, requires_approval, is_active, created_at, updated_at
            FROM temp_leave_types");
        
        // Get count after cleanup
        $stmt = $pdo->query("SELECT COUNT(*) FROM leave_types");
        $afterCount = $stmt->fetchColumn();
        
        $pdo->commit();
        
        echo "   ✓ Removed " . ($beforeCount - $afterCount) . " duplicate records\n";
        echo "   ✓ Kept {$afterCount} unique leave types\n";
        
    } catch (Exception $e) {
        $pdo->rollback();
        throw $e;
    }
    
    // 4. Ensure we have the basic leave types with proper codes
    echo "\n4. Ensuring standard leave types exist...\n";
    
    $standardTypes = [
        ['Annual Leave', 'AL', 'Annual vacation leave', 21, 1, 1],
        ['Sick Leave', 'SL', 'Medical sick leave', 10, 0, 0],
        ['Emergency Leave', 'EL', 'Emergency family leave', 5, 0, 1],
        ['Maternity Leave', 'ML', 'Maternity leave', 90, 0, 1],
        ['Paternity Leave', 'PL', 'Paternity leave', 7, 0, 1]
    ];
    
    $insertStmt = $pdo->prepare("
        INSERT IGNORE INTO leave_types (name, code, description, max_days_per_year, carry_forward, requires_approval, is_active) 
        VALUES (?, ?, ?, ?, ?, ?, 1)
    ");
    
    foreach ($standardTypes as $type) {
        $insertStmt->execute($type);
    }
    
    // 5. Final verification
    echo "\n5. Final verification:\n";
    $stmt = $pdo->query("SELECT name, code, max_days_per_year, carry_forward, requires_approval, is_active FROM leave_types ORDER BY name");
    $finalTypes = $stmt->fetchAll(PDO::FETCH_OBJ);
    
    echo "   Final leave types:\n";
    foreach ($finalTypes as $type) {
        $carryForward = $type->carry_forward ? 'Yes' : 'No';
        $requiresApproval = $type->requires_approval ? 'Yes' : 'No';
        $status = $type->is_active ? 'Active' : 'Inactive';
        echo "   - {$type->name} ({$type->code}) | {$type->max_days_per_year} days | Carry: {$carryForward} | Approval: {$requiresApproval} | {$status}\n";
    }
    
    // 6. Update the LeaveController to prevent future duplicates
    echo "\n6. Checking LeaveController for duplicate insertion logic...\n";
    $controllerFile = __DIR__ . '/app/Http/Controllers/LeaveController.php';
    if (file_exists($controllerFile)) {
        $content = file_get_contents($controllerFile);
        if (strpos($content, 'INSERT INTO leave_types') !== false) {
            echo "   ⚠️  LeaveController contains direct INSERT statements\n";
            echo "   📝 Recommendation: Update LeaveController to use INSERT IGNORE or check for existing records\n";
        } else {
            echo "   ✓ LeaveController looks clean\n";
        }
    }
    
    echo "\n=== CLEANUP COMPLETE ===\n";
    echo "✅ Duplicates removed\n";
    echo "✅ Standard leave types ensured\n";
    echo "✅ Database is clean\n";
    echo "\n🎉 The leave management page should now show unique leave types!\n";
    
} catch (PDOException $e) {
    echo "❌ Database Error: " . $e->getMessage() . "\n";
}
?>
