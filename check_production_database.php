<?php
/**
 * Production Database Diagnostic Script
 * Run this on your production server to check database configuration
 */

require_once __DIR__ . '/vendor/autoload.php';

// Load Laravel application
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== PRODUCTION DATABASE DIAGNOSTIC ===\n\n";

try {
    // Check current database configuration
    $defaultConnection = config('database.default');
    echo "1. Default Connection: " . $defaultConnection . "\n";
    
    $dbConfig = config('database.connections.' . $defaultConnection);
    echo "2. Database Host: " . $dbConfig['host'] . "\n";
    echo "3. Database Port: " . $dbConfig['port'] . "\n";
    echo "4. Database Name: " . $dbConfig['database'] . "\n";
    echo "5. Database Username: " . $dbConfig['username'] . "\n\n";
    
    // Test database connection
    echo "6. Testing Database Connection...\n";
    $pdo = DB::connection()->getPdo();
    echo "   ✅ Database connection successful!\n\n";
    
    // Get actual database name being used
    $actualDbName = DB::connection()->getDatabaseName();
    echo "7. Actual Database Name in Use: " . $actualDbName . "\n\n";
    
    // Check if employees table exists
    echo "8. Checking Tables...\n";
    $tables = DB::select('SHOW TABLES');
    $tableNames = array_map(function($table) use ($actualDbName) {
        $key = 'Tables_in_' . $actualDbName;
        return $table->$key ?? '';
    }, $tables);
    
    $requiredTables = ['employees', 'shift_types', 'claim_types', 'leave_types'];
    foreach ($requiredTables as $table) {
        if (in_array($table, $tableNames)) {
            echo "   ✅ Table '$table' exists\n";
        } else {
            echo "   ❌ Table '$table' MISSING\n";
        }
    }
    
    echo "\n9. Employee Count Test...\n";
    
    // Test direct SQL query
    $directCount = DB::select('SELECT COUNT(*) as count FROM employees')[0]->count;
    echo "   Direct SQL Query: " . $directCount . " employees\n";
    
    // Test Eloquent query (this might fail due to hardcoded database)
    try {
        $eloquentCount = \App\Models\Employee::count();
        echo "   Eloquent Model Query: " . $eloquentCount . " employees\n";
    } catch (Exception $e) {
        echo "   ❌ Eloquent Query Failed: " . $e->getMessage() . "\n";
    }
    
    echo "\n10. Database Environment Check...\n";
    echo "    APP_ENV: " . env('APP_ENV') . "\n";
    echo "    DB_DATABASE from .env: " . env('DB_DATABASE') . "\n";
    echo "    Hardcoded in Employee model: hr3systemdb\n";
    
    if ($actualDbName !== 'hr3systemdb') {
        echo "\n🚨 ISSUE FOUND:\n";
        echo "   Employee model is hardcoded to use 'hr3systemdb'\n";
        echo "   But your production database is: '$actualDbName'\n";
        echo "   This explains why queries return no results!\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== DIAGNOSTIC COMPLETE ===\n";
