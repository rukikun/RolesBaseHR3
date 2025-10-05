<?php
/**
 * Test Individual Migrations Script
 * 
 * This script tests each migration file individually to identify issues
 */

require_once __DIR__ . '/vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

echo "🔍 Testing Individual Migration Files\n";
echo "====================================\n\n";

// Get all individual migration files
$migrationFiles = glob(__DIR__ . '/database/migrations/2025_10_04_2200*.php');
sort($migrationFiles);

echo "Found " . count($migrationFiles) . " migration files to test:\n\n";

foreach ($migrationFiles as $file) {
    $filename = basename($file);
    echo "📄 Testing: {$filename}\n";
    
    try {
        // Try to include the file to check for syntax errors
        include_once $file;
        echo "   ✅ Syntax OK\n";
        
        // Extract table name from filename
        if (preg_match('/create_(\w+)_table\.php$/', $filename, $matches)) {
            $tableName = $matches[1];
            echo "   📋 Table: {$tableName}\n";
        }
        
    } catch (ParseError $e) {
        echo "   ❌ Syntax Error: " . $e->getMessage() . "\n";
    } catch (Exception $e) {
        echo "   ⚠️  Warning: " . $e->getMessage() . "\n";
    }
    
    echo "\n";
}

echo "🔧 Suggested Fix Commands:\n";
echo "=========================\n";
echo "1. Drop all tables manually:\n";
echo "   mysql -u root -p hr3_hr3systemdb -e \"DROP DATABASE IF EXISTS hr3_hr3systemdb; CREATE DATABASE hr3_hr3systemdb;\"\n\n";

echo "2. Run migrations one by one:\n";
foreach ($migrationFiles as $file) {
    $filename = basename($file, '.php');
    echo "   php artisan migrate --path=database/migrations/{$filename}.php\n";
}

echo "\n3. Or try migrate:fresh:\n";
echo "   php artisan migrate:fresh\n\n";

echo "✅ Migration testing completed!\n";
?>
