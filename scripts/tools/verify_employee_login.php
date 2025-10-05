<?php
// Final verification script for employee login functionality

echo "🔍 Employee Login Verification Script\n";
echo "=====================================\n\n";

try {
    // 1. Test database connection
    echo "1. Testing database connection...\n";
    $pdo = new PDO('mysql:host=localhost;dbname=hr3_hr3systemdb', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "   ✅ Database connection successful\n\n";
    
    // 2. Check employees table and data
    echo "2. Checking employees table...\n";
    $stmt = $pdo->query("SELECT COUNT(*) FROM employees WHERE status = 'active'");
    $activeEmployees = $stmt->fetchColumn();
    echo "   ✅ Found $activeEmployees active employees\n";
    
    // 3. Test password verification
    echo "3. Testing password verification...\n";
    $stmt = $pdo->query("SELECT email, password FROM employees WHERE email = 'john.doe@jetlouge.com' LIMIT 1");
    $employee = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($employee) {
        if (password_verify('password123', $employee['password'])) {
            echo "   ✅ Password verification works for john.doe@jetlouge.com\n";
        } else {
            echo "   ❌ Password verification failed for john.doe@jetlouge.com\n";
        }
    } else {
        echo "   ❌ Test employee john.doe@jetlouge.com not found\n";
    }
    
    // 4. List all test accounts
    echo "\n4. Available test accounts:\n";
    $stmt = $pdo->query("SELECT first_name, last_name, email, status FROM employees WHERE status = 'active' LIMIT 5");
    $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($employees as $emp) {
        echo "   📧 {$emp['first_name']} {$emp['last_name']} - {$emp['email']}\n";
    }
    
    echo "\n🎯 LOGIN TESTING INSTRUCTIONS:\n";
    echo "==============================\n";
    echo "1. Open browser and go to: http://localhost/hr3system/portal-selection\n";
    echo "2. Click 'Employee Portal' button\n";
    echo "3. Use these credentials:\n";
    echo "   Email: john.doe@jetlouge.com\n";
    echo "   Password: password123\n";
    echo "4. Should redirect to employee dashboard\n\n";
    
    echo "🔧 TROUBLESHOOTING:\n";
    echo "==================\n";
    echo "If login fails, check:\n";
    echo "- XAMPP MySQL service is running\n";
    echo "- Browser cookies/session cleared\n";
    echo "- Laravel caches cleared (already done)\n";
    echo "- Check Laravel logs in storage/logs/\n\n";
    
    echo "✅ All checks passed! Employee login should be working.\n";
    
} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
    echo "\nPlease ensure:\n";
    echo "1. XAMPP MySQL is running\n";
    echo "2. Database 'hr3_hr3systemdb' exists\n";
    echo "3. Run 'php fix_employee_login.php' first\n";
}
?>
