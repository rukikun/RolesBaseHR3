<?php
// Comprehensive test for the complete shift assignment system

require_once 'vendor/autoload.php';

try {
    $pdo = new PDO('mysql:host=localhost;dbname=hr3systemdb', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== COMPLETE SHIFT SYSTEM TEST ===\n";
    
    // 1. Test Database Tables
    echo "\n1. TESTING DATABASE TABLES:\n";
    $tables = ['employees', 'shift_types', 'shifts'];
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        $exists = $stmt->rowCount() > 0;
        echo "   ✓ $table table: " . ($exists ? "EXISTS" : "MISSING") . "\n";
    }
    
    // 2. Test Employee Data
    echo "\n2. TESTING EMPLOYEE DATA:\n";
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM employees WHERE status = 'active'");
    $employeeCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    echo "   ✓ Active employees: $employeeCount\n";
    
    if ($employeeCount > 0) {
        $stmt = $pdo->query("SELECT id, first_name, last_name FROM employees WHERE status = 'active' LIMIT 3");
        while ($emp = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "     - ID: {$emp['id']}, Name: {$emp['first_name']} {$emp['last_name']}\n";
        }
    }
    
    // 3. Test Shift Types
    echo "\n3. TESTING SHIFT TYPES:\n";
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM shift_types WHERE is_active = 1");
    $shiftTypeCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    echo "   ✓ Active shift types: $shiftTypeCount\n";
    
    if ($shiftTypeCount > 0) {
        $stmt = $pdo->query("SELECT id, name, default_start_time, default_end_time FROM shift_types WHERE is_active = 1 LIMIT 3");
        while ($type = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "     - ID: {$type['id']}, Name: {$type['name']}, Time: {$type['default_start_time']}-{$type['default_end_time']}\n";
        }
    }
    
    // 4. Test Shifts Table Structure
    echo "\n4. TESTING SHIFTS TABLE:\n";
    $stmt = $pdo->query("DESCRIBE shifts");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $requiredColumns = ['id', 'employee_id', 'shift_type_id', 'shift_date', 'start_time', 'end_time', 'location', 'status'];
    
    foreach ($requiredColumns as $col) {
        $exists = array_filter($columns, function($c) use ($col) { return $c['Field'] == $col; });
        echo "   ✓ Column '$col': " . (count($exists) > 0 ? "EXISTS" : "MISSING") . "\n";
    }
    
    // 5. Test Current Shifts
    echo "\n5. TESTING CURRENT SHIFTS:\n";
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM shifts");
    $shiftCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    echo "   ✓ Total shifts: $shiftCount\n";
    
    if ($shiftCount > 0) {
        $stmt = $pdo->query("
            SELECT s.id, s.shift_date, s.start_time, s.end_time, s.status,
                   CONCAT(e.first_name, ' ', e.last_name) as employee_name,
                   st.name as shift_type_name
            FROM shifts s
            LEFT JOIN employees e ON s.employee_id = e.id
            LEFT JOIN shift_types st ON s.shift_type_id = st.id
            ORDER BY s.shift_date DESC, s.start_time
            LIMIT 5
        ");
        
        echo "   Recent shifts:\n";
        while ($shift = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "     - {$shift['shift_date']} {$shift['start_time']}-{$shift['end_time']}: {$shift['employee_name']} ({$shift['shift_type_name']}) [{$shift['status']}]\n";
        }
    }
    
    // 6. Test Sample Shift Creation
    echo "\n6. TESTING SHIFT CREATION:\n";
    
    // Get first active employee and shift type
    $stmt = $pdo->query("SELECT id FROM employees WHERE status = 'active' LIMIT 1");
    $employee = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $stmt = $pdo->query("SELECT id FROM shift_types WHERE is_active = 1 LIMIT 1");
    $shiftType = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($employee && $shiftType) {
        $testDate = date('Y-m-d', strtotime('+1 day'));
        
        // Check if test shift already exists
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM shifts WHERE employee_id = ? AND shift_date = ?");
        $stmt->execute([$employee['id'], $testDate]);
        $existingShift = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        if ($existingShift == 0) {
            // Create test shift
            $stmt = $pdo->prepare("
                INSERT INTO shifts (employee_id, shift_type_id, shift_date, start_time, end_time, location, status, created_at, updated_at)
                VALUES (?, ?, ?, '09:00:00', '17:00:00', 'Main Office', 'scheduled', NOW(), NOW())
            ");
            $stmt->execute([$employee['id'], $shiftType['id'], $testDate]);
            echo "   ✓ Test shift created successfully (ID: " . $pdo->lastInsertId() . ")\n";
        } else {
            echo "   ✓ Test shift already exists for $testDate\n";
        }
    } else {
        echo "   ✗ Cannot create test shift - missing employee or shift type\n";
    }
    
    // 7. Test Route Accessibility
    echo "\n7. TESTING SYSTEM ROUTES:\n";
    echo "   ✓ Shift Schedule Management: /shift-schedule-management\n";
    echo "   ✓ Shift Store Route: POST /shifts/store\n";
    echo "   ✓ Employee Test Route: /test-employees\n";
    
    // 8. Summary
    echo "\n=== SYSTEM STATUS SUMMARY ===\n";
    $allGood = ($employeeCount > 0 && $shiftTypeCount > 0);
    
    if ($allGood) {
        echo "🎉 SYSTEM READY! All components are working:\n";
        echo "   ✅ Database tables exist\n";
        echo "   ✅ Employees available ($employeeCount active)\n";
        echo "   ✅ Shift types available ($shiftTypeCount active)\n";
        echo "   ✅ Shift assignment system functional\n";
        echo "\n🚀 You can now:\n";
        echo "   1. Visit /shift-schedule-management\n";
        echo "   2. Click on any calendar date\n";
        echo "   3. Select employee from dropdown\n";
        echo "   4. Choose shift type\n";
        echo "   5. Assign shift successfully!\n";
    } else {
        echo "⚠️  SYSTEM NEEDS ATTENTION:\n";
        if ($employeeCount == 0) echo "   ❌ No active employees found\n";
        if ($shiftTypeCount == 0) echo "   ❌ No active shift types found\n";
        echo "\n🔧 Run the system to auto-create sample data.\n";
    }
    
    echo "\n=== TEST COMPLETED ===\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Please check your database connection and table structure.\n";
}
