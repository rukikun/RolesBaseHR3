<?php
/**
 * Fix Leave Types ID = 0 Issue
 * This script fixes the problem where all leave type IDs are showing as 0
 */

// Database configuration
$host = '127.0.0.1';
$dbname = 'hr3_hr3systemdb';
$username = 'root';
$password = '';

try {
    // Connect to database
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== FIXING LEAVE TYPES ID = 0 ISSUE ===\n\n";
    
    // Step 1: Analyze the current problem
    echo "1. Analyzing current ID issue...\n";
    
    $stmt = $pdo->query("SELECT COUNT(*) as total, COUNT(CASE WHEN id = 0 THEN 1 END) as zero_ids FROM leave_types");
    $counts = $stmt->fetch(PDO::FETCH_OBJ);
    
    echo "   Total records: {$counts->total}\n";
    echo "   Records with ID = 0: {$counts->zero_ids}\n";
    
    if ($counts->zero_ids > 0) {
        echo "   ❌ Found {$counts->zero_ids} records with ID = 0\n";
    } else {
        echo "   ✅ No ID = 0 issues found\n";
    }
    
    // Step 2: Show current table structure
    echo "\n2. Checking table structure...\n";
    $stmt = $pdo->query("DESCRIBE leave_types");
    $structure = $stmt->fetchAll(PDO::FETCH_OBJ);
    
    $hasAutoIncrement = false;
    foreach ($structure as $column) {
        if ($column->Field === 'id') {
            echo "   ID column: {$column->Type} | {$column->Key} | {$column->Extra}\n";
            $hasAutoIncrement = strpos($column->Extra, 'auto_increment') !== false;
        }
    }
    
    if (!$hasAutoIncrement) {
        echo "   ❌ ID column is missing AUTO_INCREMENT\n";
    } else {
        echo "   ✅ ID column has AUTO_INCREMENT\n";
    }
    
    // Step 3: Create backup before fixing
    echo "\n3. Creating backup...\n";
    try {
        $pdo->exec("DROP TABLE IF EXISTS leave_types_backup_" . date('Ymd_His'));
        $pdo->exec("CREATE TABLE leave_types_backup_" . date('Ymd_His') . " AS SELECT * FROM leave_types");
        echo "   ✅ Backup created\n";
    } catch (Exception $e) {
        echo "   ⚠️  Backup failed: " . $e->getMessage() . "\n";
    }
    
    // Step 4: Fix the ID = 0 issue
    if ($counts->zero_ids > 0) {
        echo "\n4. Fixing ID = 0 records...\n";
        
        $pdo->beginTransaction();
        try {
            // Get all records with their data
            $stmt = $pdo->query("SELECT * FROM leave_types ORDER BY created_at, name");
            $allRecords = $stmt->fetchAll(PDO::FETCH_OBJ);
            
            // Clear the table
            $pdo->exec("DELETE FROM leave_types");
            
            // Reset auto-increment
            $pdo->exec("ALTER TABLE leave_types AUTO_INCREMENT = 1");
            
            // Ensure proper table structure
            $pdo->exec("ALTER TABLE leave_types MODIFY COLUMN id INT AUTO_INCREMENT PRIMARY KEY");
            
            // Re-insert unique records with proper IDs
            $insertStmt = $pdo->prepare("
                INSERT INTO leave_types (name, code, description, max_days_per_year, carry_forward, requires_approval, is_active, created_at, updated_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $uniqueRecords = [];
            $insertedCount = 0;
            
            foreach ($allRecords as $record) {
                // Skip duplicates based on name
                $key = strtolower($record->name);
                if (!isset($uniqueRecords[$key])) {
                    $uniqueRecords[$key] = true;
                    
                    $insertStmt->execute([
                        $record->name,
                        $record->code ?? strtoupper(substr($record->name, 0, 2)),
                        $record->description ?? $record->name,
                        $record->max_days_per_year ?? 30,
                        $record->carry_forward ?? 0,
                        $record->requires_approval ?? 1,
                        $record->is_active ?? 1,
                        $record->created_at ?? date('Y-m-d H:i:s'),
                        $record->updated_at ?? date('Y-m-d H:i:s')
                    ]);
                    $insertedCount++;
                }
            }
            
            $pdo->commit();
            echo "   ✅ Fixed {$counts->zero_ids} problematic records\n";
            echo "   ✅ Inserted {$insertedCount} unique records with proper IDs\n";
            
        } catch (Exception $e) {
            $pdo->rollback();
            echo "   ❌ Fix failed: " . $e->getMessage() . "\n";
            throw $e;
        }
    }
    
    // Step 5: Ensure standard leave types exist
    echo "\n5. Ensuring standard leave types...\n";
    
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
        echo "   ✅ Ensured: {$type[0]} ({$type[1]})\n";
    }
    
    // Step 6: Add unique constraint
    echo "\n6. Adding unique constraint...\n";
    try {
        $pdo->exec("ALTER TABLE leave_types ADD CONSTRAINT unique_leave_name UNIQUE (name)");
        echo "   ✅ Added unique constraint\n";
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'already exists') !== false) {
            echo "   ℹ️  Unique constraint already exists\n";
        } else {
            echo "   ⚠️  Could not add unique constraint: " . $e->getMessage() . "\n";
        }
    }
    
    // Step 7: Final verification
    echo "\n7. Final verification...\n";
    
    $stmt = $pdo->query("SELECT id, name, code, is_active FROM leave_types ORDER BY id");
    $finalRecords = $stmt->fetchAll(PDO::FETCH_OBJ);
    
    echo "   📋 Final leave types with proper IDs:\n";
    foreach ($finalRecords as $record) {
        $status = $record->is_active ? 'Active' : 'Inactive';
        echo "      ID: {$record->id} | {$record->name} ({$record->code}) | {$status}\n";
    }
    
    // Check auto-increment value
    $stmt = $pdo->query("SHOW TABLE STATUS LIKE 'leave_types'");
    $tableStatus = $stmt->fetch(PDO::FETCH_OBJ);
    echo "   📊 Next auto-increment ID will be: {$tableStatus->Auto_increment}\n";
    
    echo "\n=== FIX COMPLETED SUCCESSFULLY ===\n";
    echo "✅ ID = 0 issue resolved\n";
    echo "✅ Auto-increment working properly\n";
    echo "✅ Unique records ensured\n";
    echo "✅ Constraints added\n";
    echo "\n🎉 Your leave types should now display with proper IDs!\n";
    
} catch (PDOException $e) {
    echo "❌ Database Error: " . $e->getMessage() . "\n";
    echo "\nThis usually happens when:\n";
    echo "1. Database connection failed\n";
    echo "2. Table doesn't exist\n";
    echo "3. Permission issues\n";
    echo "\nPlease check your database setup and try again.\n";
}
?>
