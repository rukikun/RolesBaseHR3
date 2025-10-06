<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Employees Table Structure Check ===\n\n";

try {
    // Check if employees table exists
    if (Schema::hasTable('employees')) {
        echo "✅ Employees table exists\n\n";
        
        // Get all columns
        $columns = Schema::getColumnListing('employees');
        echo "Current columns in employees table:\n";
        foreach ($columns as $column) {
            echo "- {$column}\n";
        }
        
        echo "\n=== Missing Columns Check ===\n";
        $requiredColumns = [
            'date_of_birth',
            'gender', 
            'address',
            'emergency_contact_name',
            'emergency_contact_phone',
            'profile_picture'
        ];
        
        $missingColumns = [];
        foreach ($requiredColumns as $column) {
            if (Schema::hasColumn('employees', $column)) {
                echo "✅ {$column} - EXISTS\n";
            } else {
                echo "❌ {$column} - MISSING\n";
                $missingColumns[] = $column;
            }
        }
        
        if (empty($missingColumns)) {
            echo "\n✅ All required columns exist!\n";
        } else {
            echo "\n❌ Missing columns: " . implode(', ', $missingColumns) . "\n";
            echo "\nWe need to add these columns to fix the profile update.\n";
        }
        
    } else {
        echo "❌ Employees table does not exist\n";
    }
    
    // Check a sample employee record
    echo "\n=== Sample Employee Data ===\n";
    $employee = DB::table('employees')->first();
    if ($employee) {
        echo "Sample employee found:\n";
        foreach ((array)$employee as $key => $value) {
            echo "- {$key}: " . (is_null($value) ? 'NULL' : $value) . "\n";
        }
    } else {
        echo "No employees found in table\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
