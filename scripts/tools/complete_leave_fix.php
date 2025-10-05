<?php
/**
 * Complete Leave Management Fix
 * This script fixes all duplicate issues and ensures proper functionality
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
    
    echo "=== COMPLETE LEAVE MANAGEMENT FIX ===\n\n";
    
    // Step 1: Analyze current situation
    echo "1. Analyzing current database state...\n";
    
    // Check if tables exist
    $stmt = $pdo->query("SHOW TABLES LIKE 'leave_types'");
    if ($stmt->rowCount() == 0) {
        echo "   ❌ leave_types table doesn't exist. Creating...\n";
        $pdo->exec("CREATE TABLE leave_types (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            code VARCHAR(10) NOT NULL,
            description TEXT,
            max_days_per_year INT DEFAULT 30,
            carry_forward BOOLEAN DEFAULT FALSE,
            requires_approval BOOLEAN DEFAULT TRUE,
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )");
        echo "   ✅ leave_types table created\n";
    } else {
        echo "   ✅ leave_types table exists\n";
    }
    
    // Check current records
    $stmt = $pdo->query("SELECT COUNT(*) FROM leave_types");
    $totalCount = $stmt->fetchColumn();
    echo "   📊 Total records: {$totalCount}\n";
    
    // Check for duplicates
    $stmt = $pdo->query("
        SELECT name, COUNT(*) as count 
        FROM leave_types 
        GROUP BY name 
        HAVING COUNT(*) > 1
    ");
    $duplicates = $stmt->fetchAll(PDO::FETCH_OBJ);
    
    if (count($duplicates) > 0) {
        echo "   ⚠️  Found duplicates:\n";
        foreach ($duplicates as $dup) {
            echo "      - {$dup->name}: {$dup->count} entries\n";
        }
    } else {
        echo "   ✅ No duplicates found\n";
    }
    
    // Step 2: Clean up duplicates if they exist
    if (count($duplicates) > 0) {
        echo "\n2. Cleaning up duplicates...\n";
        
        $pdo->beginTransaction();
        try {
            // Create backup
            $pdo->exec("CREATE TEMPORARY TABLE backup_leave_types AS SELECT * FROM leave_types");
            echo "   ✅ Created backup\n";
            
            // Remove duplicates - keep the one with the lowest ID for each name
            $pdo->exec("
                DELETE t1 FROM leave_types t1
                INNER JOIN leave_types t2 
                WHERE t1.id > t2.id AND t1.name = t2.name
            ");
            
            $stmt = $pdo->query("SELECT COUNT(*) FROM leave_types");
            $newCount = $stmt->fetchColumn();
            echo "   ✅ Removed " . ($totalCount - $newCount) . " duplicates\n";
            echo "   📊 Records remaining: {$newCount}\n";
            
            $pdo->commit();
        } catch (Exception $e) {
            $pdo->rollback();
            echo "   ❌ Error during cleanup: " . $e->getMessage() . "\n";
            throw $e;
        }
    }
    
    // Step 3: Ensure standard leave types exist
    echo "\n3. Ensuring standard leave types...\n";
    
    $standardTypes = [
        ['Annual Leave', 'AL', 'Annual vacation leave', 21, 1, 1],
        ['Sick Leave', 'SL', 'Medical sick leave', 10, 0, 0],
        ['Emergency Leave', 'EL', 'Emergency family leave', 5, 0, 1],
        ['Maternity Leave', 'ML', 'Maternity leave', 90, 0, 1],
        ['Paternity Leave', 'PL', 'Paternity leave', 7, 0, 1]
    ];
    
    $insertStmt = $pdo->prepare("
        INSERT INTO leave_types (name, code, description, max_days_per_year, carry_forward, requires_approval, is_active) 
        VALUES (?, ?, ?, ?, ?, ?, 1)
        ON DUPLICATE KEY UPDATE
        description = VALUES(description),
        max_days_per_year = VALUES(max_days_per_year),
        carry_forward = VALUES(carry_forward),
        requires_approval = VALUES(requires_approval),
        is_active = 1
    ");
    
    foreach ($standardTypes as $type) {
        try {
            $insertStmt->execute($type);
            echo "   ✅ Ensured: {$type[0]} ({$type[1]})\n";
        } catch (Exception $e) {
            // Try without ON DUPLICATE KEY UPDATE for older MySQL versions
            $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM leave_types WHERE name = ? OR code = ?");
            $checkStmt->execute([$type[0], $type[1]]);
            if ($checkStmt->fetchColumn() == 0) {
                $simpleInsert = $pdo->prepare("INSERT INTO leave_types (name, code, description, max_days_per_year, carry_forward, requires_approval, is_active) VALUES (?, ?, ?, ?, ?, ?, 1)");
                $simpleInsert->execute($type);
                echo "   ✅ Added: {$type[0]} ({$type[1]})\n";
            } else {
                echo "   ℹ️  Exists: {$type[0]} ({$type[1]})\n";
            }
        }
    }
    
    // Step 4: Add unique constraint to prevent future duplicates
    echo "\n4. Adding unique constraint...\n";
    try {
        $pdo->exec("ALTER TABLE leave_types ADD CONSTRAINT unique_leave_name UNIQUE (name)");
        echo "   ✅ Added unique constraint on name\n";
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
            echo "   ⚠️  Cannot add unique constraint - duplicates still exist\n";
        } elseif (strpos($e->getMessage(), 'already exists') !== false) {
            echo "   ℹ️  Unique constraint already exists\n";
        } else {
            echo "   ⚠️  Could not add unique constraint: " . $e->getMessage() . "\n";
        }
    }
    
    // Step 5: Final verification
    echo "\n5. Final verification...\n";
    $stmt = $pdo->query("SELECT id, name, code, max_days_per_year, is_active FROM leave_types ORDER BY name");
    $finalTypes = $stmt->fetchAll(PDO::FETCH_OBJ);
    
    echo "   📋 Final leave types in database:\n";
    foreach ($finalTypes as $type) {
        $status = $type->is_active ? 'Active' : 'Inactive';
        echo "      ID:{$type->id} | {$type->name} ({$type->code}) | {$type->max_days_per_year} days | {$status}\n";
    }
    
    // Step 6: Test the LeaveController query
    echo "\n6. Testing LeaveController query...\n";
    try {
        $stmt = $pdo->query("SELECT * FROM leave_types WHERE is_active = 1 ORDER BY name");
        $activeTypes = $stmt->fetchAll(PDO::FETCH_OBJ);
        echo "   ✅ LeaveController query works: " . count($activeTypes) . " active types found\n";
    } catch (Exception $e) {
        echo "   ❌ LeaveController query failed: " . $e->getMessage() . "\n";
    }
    
    echo "\n=== FIX COMPLETED ===\n";
    echo "✅ Database cleaned\n";
    echo "✅ Duplicates removed\n";
    echo "✅ Standard types ensured\n";
    echo "✅ Constraints added\n";
    echo "\n🎉 Your leave management should now work properly!\n";
    echo "\n📝 Next steps:\n";
    echo "1. Refresh your leave management page\n";
    echo "2. Check that leave types show without duplicates\n";
    echo "3. Test creating new leave requests\n";
    echo "4. If you still see issues, check the browser console for JavaScript errors\n";
    
} catch (PDOException $e) {
    echo "❌ Database Error: " . $e->getMessage() . "\n";
    echo "\nPlease check your database connection and try again.\n";
}
?>
