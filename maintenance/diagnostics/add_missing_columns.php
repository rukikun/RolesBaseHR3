<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Adding Missing Columns to Employees Table ===\n\n";

try {
    $columnsToAdd = [
        'date_of_birth' => "ALTER TABLE employees ADD COLUMN date_of_birth DATE NULL",
        'gender' => "ALTER TABLE employees ADD COLUMN gender ENUM('Male', 'Female', 'Other', 'Prefer not to say') NULL",
        'address' => "ALTER TABLE employees ADD COLUMN address TEXT NULL",
        'emergency_contact_name' => "ALTER TABLE employees ADD COLUMN emergency_contact_name VARCHAR(255) NULL",
        'emergency_contact_phone' => "ALTER TABLE employees ADD COLUMN emergency_contact_phone VARCHAR(20) NULL",
        'profile_picture' => "ALTER TABLE employees ADD COLUMN profile_picture VARCHAR(255) NULL"
    ];
    
    foreach ($columnsToAdd as $columnName => $sql) {
        if (!Schema::hasColumn('employees', $columnName)) {
            echo "Adding column: {$columnName}...\n";
            try {
                DB::statement($sql);
                echo "✅ Successfully added {$columnName}\n";
            } catch (\Exception $e) {
                echo "❌ Error adding {$columnName}: " . $e->getMessage() . "\n";
            }
        } else {
            echo "✅ Column {$columnName} already exists\n";
        }
    }
    
    echo "\n=== Verification ===\n";
    $columns = Schema::getColumnListing('employees');
    echo "Current columns in employees table:\n";
    foreach ($columns as $column) {
        echo "- {$column}\n";
    }
    
    echo "\n✅ Column addition process complete!\n";
    echo "The profile update should now work correctly.\n";
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
