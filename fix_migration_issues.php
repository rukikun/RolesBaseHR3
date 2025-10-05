<?php
/**
 * Fix Migration Issues Script
 * 
 * This script fixes the database configuration and migration issues
 */

echo "🔧 HR3 System Migration Fix Script\n";
echo "==================================\n\n";

// Step 1: Check if .env file exists and has correct database name
echo "📋 Step 1: Checking .env configuration...\n";

$envFile = __DIR__ . '/.env';
$envExampleFile = __DIR__ . '/.env.example';

if (!file_exists($envFile)) {
    echo "⚠️  .env file not found. Copying from .env.example...\n";
    copy($envExampleFile, $envFile);
    echo "✅ .env file created from .env.example\n";
} else {
    echo "✅ .env file exists\n";
}

// Read .env file and check database name
$envContent = file_get_contents($envFile);
if (strpos($envContent, 'DB_DATABASE=hr3_hr3systemdb') !== false) {
    echo "✅ Database name is correctly set to 'hr3_hr3systemdb'\n";
} else if (strpos($envContent, 'DB_DATABASE=hr3') !== false) {
    echo "⚠️  Database name is set to 'hr3', changing to 'hr3_hr3systemdb'...\n";
    $envContent = str_replace('DB_DATABASE=hr3', 'DB_DATABASE=hr3_hr3systemdb', $envContent);
    file_put_contents($envFile, $envContent);
    echo "✅ Database name updated to 'hr3_hr3systemdb'\n";
} else {
    echo "⚠️  Database name not found in .env, please check manually\n";
}

echo "\n📋 Step 2: Checking migration files...\n";

// Check if problematic migration exists
$problematicMigration = __DIR__ . '/database/migrations/2025_10_04_174200_update_shift_requests_table_for_modal_fields.php';
if (file_exists($problematicMigration)) {
    echo "⚠️  Found problematic migration file, removing...\n";
    unlink($problematicMigration);
    echo "✅ Problematic migration removed\n";
} else {
    echo "✅ No problematic migration found\n";
}

// Count individual migration files
$migrationFiles = glob(__DIR__ . '/database/migrations/2025_10_04_2200*.php');
echo "✅ Found " . count($migrationFiles) . " individual migration files\n";

echo "\n📋 Step 3: Migration commands to run...\n";
echo "Run these commands in order:\n\n";

echo "1. Clear Laravel caches:\n";
echo "   php artisan config:clear\n";
echo "   php artisan cache:clear\n";
echo "   php artisan route:clear\n\n";

echo "2. Reset database (WARNING: This will delete all data):\n";
echo "   php artisan migrate:reset\n\n";

echo "3. Run fresh migrations:\n";
echo "   php artisan migrate\n\n";

echo "4. Or run fresh migrations with seeding:\n";
echo "   php artisan migrate:fresh --seed\n\n";

echo "📊 Alternative: If you want to keep existing data:\n";
echo "1. Backup your database first\n";
echo "2. Run: php artisan migrate:status\n";
echo "3. Run: php artisan migrate\n\n";

echo "✅ Migration fix script completed!\n";
echo "💡 Make sure your database 'hr3_hr3systemdb' exists before running migrations.\n";
?>
