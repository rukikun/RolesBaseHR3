<?php

/**
 * Final Migration Cleanup Script
 * 
 * This script moves all conflicting migrations to backup and keeps only
 * the essential Laravel migrations plus our authoritative HR3 schema
 */

echo "🧹 Final Migration Cleanup\n";
echo "==========================\n\n";

$migrationPath = __DIR__ . '/../../database/migrations';
$backupPath = __DIR__ . '/../../database-backups/conflicting_migrations';

// Create backup directory
if (!is_dir($backupPath)) {
    mkdir($backupPath, 0755, true);
}

// Keep only these essential migrations
$keepMigrations = [
    '0001_01_01_000001_create_cache_table.php',
    '0001_01_01_000002_create_jobs_table.php',
    '2025_08_15_112816_create_personal_access_tokens_table.php',
    '2025_08_27_043945_create_sessions_table.php',
    '2025_10_04_143640_create_hr3_authoritative_schema.php'
];

$files = glob($migrationPath . '/*.php');
$moved = 0;

foreach ($files as $file) {
    $filename = basename($file);
    
    // If this file is not in our keep list, move it to backup
    if (!in_array($filename, $keepMigrations)) {
        $newPath = $backupPath . '/' . $filename;
        
        if (rename($file, $newPath)) {
            echo "🗑️ Moved: {$filename}\n";
            $moved++;
        } else {
            echo "❌ Failed to move: {$filename}\n";
        }
    } else {
        echo "✅ Kept: {$filename}\n";
    }
}

echo "\n📊 Summary:\n";
echo "   ✅ Kept " . count($keepMigrations) . " essential migrations\n";
echo "   🗑️ Moved {$moved} conflicting migrations to backup\n";
echo "\n🎯 Ready to run: php artisan migrate:fresh\n";
