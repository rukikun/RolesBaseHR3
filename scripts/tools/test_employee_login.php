<?php
// Test script to verify employee login functionality

try {
    // Test database connection
    $pdo = new PDO('mysql:host=localhost;dbname=hr3_hr3systemdb', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Database connection successful\n";
    
    // Check if employees table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'employees'");
    if ($stmt->rowCount() > 0) {
        echo "✅ Employees table exists\n";
        
        // Check employee records
        $stmt = $pdo->query("SELECT id, first_name, last_name, email, password FROM employees LIMIT 5");
        $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "📊 Found " . count($employees) . " employee records:\n";
        foreach ($employees as $employee) {
            echo "  - {$employee['first_name']} {$employee['last_name']} ({$employee['email']})\n";
            echo "    Password hash: " . (strlen($employee['password']) > 10 ? "✅ Set" : "❌ Missing") . "\n";
        }
        
        // Test password verification for first employee
        if (!empty($employees)) {
            $testEmployee = $employees[0];
            $testPassword = 'password123';
            
            // Check if password is hashed correctly
            if (password_verify($testPassword, $testEmployee['password'])) {
                echo "✅ Password verification works for {$testEmployee['email']}\n";
            } else {
                echo "❌ Password verification failed for {$testEmployee['email']}\n";
                echo "    Expected: password123\n";
                echo "    Hash: {$testEmployee['password']}\n";
            }
        }
        
    } else {
        echo "❌ Employees table does not exist\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
}

echo "\n🔍 Issues to check:\n";
echo "1. Make sure XAMPP MySQL is running\n";
echo "2. Database 'hr3_hr3systemdb' exists\n";
echo "3. Employees table has data with proper password hashes\n";
echo "4. Laravel authentication guard is properly configured\n";
echo "5. Route redirects are correct\n";
?>
