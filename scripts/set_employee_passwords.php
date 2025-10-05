<?php
/**
 * Set Default Employee Passwords Script
 * This script sets the default password "password123" for all employees
 * and ensures they can access the employee portal
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Illuminate\Support\Facades\Hash;

// Database configuration
$host = '127.0.0.1';
$dbname = 'hr3_hr3systemdb';
$username = 'root';
$password = '';

try {
    // Connect to database
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connected to database successfully!\n";
    
    // Check if employees table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'employees'");
    if ($stmt->rowCount() == 0) {
        echo "Error: employees table does not exist!\n";
        exit(1);
    }
    
    // Hash the default password using Laravel's Hash facade equivalent
    $hashedPassword = password_hash('password123', PASSWORD_DEFAULT);
    
    echo "Setting default password 'password123' for all employees...\n";
    
    // Update all employees with the hashed password
    $updateStmt = $pdo->prepare("
        UPDATE employees 
        SET password = ?, 
            updated_at = NOW() 
        WHERE password IS NULL OR password = '' OR password = 'password123'
    ");
    
    $result = $updateStmt->execute([$hashedPassword]);
    
    if ($result) {
        $affectedRows = $updateStmt->rowCount();
        echo "Successfully updated passwords for $affectedRows employees.\n";
        
        // Display all employees with their login credentials
        echo "\n=== EMPLOYEE LOGIN CREDENTIALS ===\n";
        $stmt = $pdo->query("
            SELECT id, first_name, last_name, email, position, department, status 
            FROM employees 
            WHERE status = 'active' 
            ORDER BY id
        ");
        
        $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($employees)) {
            echo "No active employees found. Creating sample employees...\n";
            
            // Create sample employees if none exist
            $sampleEmployees = [
                ['John', 'Doe', 'john.doe@jetlouge.com', 'Software Developer', 'IT'],
                ['Jane', 'Smith', 'jane.smith@jetlouge.com', 'HR Manager', 'Human Resources'],
                ['Mike', 'Johnson', 'mike.johnson@jetlouge.com', 'Sales Manager', 'Sales'],
                ['Sarah', 'Wilson', 'sarah.wilson@jetlouge.com', 'Marketing Specialist', 'Marketing'],
                ['David', 'Brown', 'david.brown@jetlouge.com', 'Finance Analyst', 'Finance']
            ];
            
            $insertStmt = $pdo->prepare("
                INSERT INTO employees (first_name, last_name, email, position, department, hire_date, salary, status, password, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, CURDATE(), 50000.00, 'active', ?, NOW(), NOW())
            ");
            
            foreach ($sampleEmployees as $emp) {
                $insertStmt->execute([
                    $emp[0], $emp[1], $emp[2], $emp[3], $emp[4], $hashedPassword
                ]);
                echo "Created employee: {$emp[0]} {$emp[1]} ({$emp[2]})\n";
            }
            
            // Fetch the newly created employees
            $stmt = $pdo->query("
                SELECT id, first_name, last_name, email, position, department, status 
                FROM employees 
                WHERE status = 'active' 
                ORDER BY id
            ");
            $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        echo "\nEmployee Login Credentials:\n";
        echo str_repeat("-", 80) . "\n";
        printf("%-4s %-15s %-15s %-30s %-20s %-15s\n", 
               "ID", "First Name", "Last Name", "Email", "Position", "Department");
        echo str_repeat("-", 80) . "\n";
        
        foreach ($employees as $employee) {
            printf("%-4s %-15s %-15s %-30s %-20s %-15s\n",
                   $employee['id'],
                   $employee['first_name'],
                   $employee['last_name'],
                   $employee['email'],
                   substr($employee['position'], 0, 19),
                   substr($employee['department'], 0, 14)
            );
        }
        
        echo str_repeat("-", 80) . "\n";
        echo "Default Password for ALL employees: password123\n";
        echo "\nEmployee Portal URL: http://localhost/employee/login\n";
        echo "Admin Portal URL: http://localhost/admin/login\n";
        
        // Verify password hashing
        echo "\n=== PASSWORD VERIFICATION ===\n";
        $testStmt = $pdo->query("SELECT email, password FROM employees LIMIT 1");
        $testEmployee = $testStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($testEmployee && password_verify('password123', $testEmployee['password'])) {
            echo "✓ Password hashing verified successfully!\n";
            echo "✓ Employees can now log in with 'password123'\n";
        } else {
            echo "✗ Password verification failed!\n";
        }
        
    } else {
        echo "Error: Failed to update employee passwords.\n";
        exit(1);
    }
    
} catch (PDOException $e) {
    echo "Database Error: " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n=== SCRIPT COMPLETED SUCCESSFULLY ===\n";
echo "All employees can now log in to the Employee Portal using:\n";
echo "- Their email address\n";
echo "- Password: password123\n";
echo "\nTo test, visit: http://localhost/employee/login\n";
?>
