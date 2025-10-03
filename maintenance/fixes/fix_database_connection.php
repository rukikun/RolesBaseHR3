<?php

// Simple database connection test and fix
echo "🔍 Testing database connection...\n";

try {
    // Test direct PDO connection
    $pdo = new PDO('mysql:host=localhost;dbname=hr3systemdb', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Direct PDO connection successful!\n";
    
    // Test basic query
    $result = $pdo->query("SELECT 1 as test")->fetch();
    echo "✅ Basic query test passed!\n";
    
    // Check if employees table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'employees'");
    if ($stmt->rowCount() > 0) {
        echo "✅ Employees table exists!\n";
        
        // Count employees
        $count = $pdo->query("SELECT COUNT(*) FROM employees")->fetchColumn();
        echo "📊 Found $count employees in database\n";
    } else {
        echo "⚠️  Employees table not found\n";
    }
    
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
    
    // Try to create database if it doesn't exist
    try {
        echo "🔧 Attempting to create database...\n";
        $pdo = new PDO('mysql:host=localhost', 'root', '');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $pdo->exec("CREATE DATABASE IF NOT EXISTS hr3systemdb");
        echo "✅ Database hr3systemdb created/verified!\n";
        
        // Test connection to new database
        $pdo = new PDO('mysql:host=localhost;dbname=hr3systemdb', 'root', '');
        echo "✅ Connection to hr3systemdb successful!\n";
        
    } catch (Exception $e2) {
        echo "❌ Failed to create database: " . $e2->getMessage() . "\n";
    }
}

echo "\n🔧 Clearing Laravel caches...\n";

// Clear various Laravel caches
$commands = [
    'php artisan config:clear',
    'php artisan cache:clear',
    'php artisan route:clear',
    'php artisan view:clear'
];

foreach ($commands as $command) {
    echo "Running: $command\n";
    $output = shell_exec($command . ' 2>&1');
    if ($output) {
        echo "Output: " . trim($output) . "\n";
    }
}

echo "\n✅ Database connection fix completed!\n";
echo "🚀 Try accessing your application now.\n";
