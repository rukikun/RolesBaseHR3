<?php
/**
 * Fix Database Migration Issues
 * This script fixes the shift_types table structure and ensures proper data insertion
 */

require_once __DIR__ . '/../../vendor/autoload.php';

// Load Laravel application
$app = require_once __DIR__ . '/../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

echo "=== HR3 System Database Migration Fix ===\n\n";

try {
    // Test database connection
    DB::connection()->getPdo();
    echo "✅ Database connection successful\n";
    
    // Fix shift_types table
    echo "\n--- Fixing shift_types table ---\n";
    
    // Drop existing table if it exists
    if (Schema::hasTable('shift_types')) {
        Schema::drop('shift_types');
        echo "✅ Dropped existing shift_types table\n";
    }
    
    // Create shift_types table with correct structure
    Schema::create('shift_types', function (Blueprint $table) {
        $table->id();
        $table->string('name', 100);
        $table->string('code', 20)->unique();
        $table->text('description')->nullable();
        $table->time('default_start_time');
        $table->time('default_end_time');
        $table->integer('break_duration')->default(0);
        $table->decimal('hourly_rate', 8, 2)->nullable();
        $table->string('color_code', 7)->default('#007bff');
        $table->enum('type', ['day', 'night', 'swing', 'split', 'rotating'])->default('day');
        $table->boolean('is_active')->default(true);
        $table->timestamps();
    });
    
    echo "✅ Created shift_types table with correct structure\n";
    
    // Insert sample data
    $shiftTypes = [
        [
            'id' => 1,
            'name' => 'Morning Shift',
            'code' => 'MORNING',
            'description' => 'Standard morning shift for regular operations',
            'default_start_time' => '08:00:00',
            'default_end_time' => '16:00:00',
            'break_duration' => 60,
            'hourly_rate' => 25.00,
            'color_code' => '#28a745',
            'type' => 'day',
            'is_active' => 1,
            'created_at' => '2025-09-10 16:39:11',
            'updated_at' => '2025-09-10 16:39:11'
        ],
        [
            'id' => 2,
            'name' => 'Afternoon Shift',
            'code' => 'AFTERNOON',
            'description' => 'Afternoon to evening coverage shift',
            'default_start_time' => '14:00:00',
            'default_end_time' => '22:00:00',
            'break_duration' => 45,
            'hourly_rate' => 27.50,
            'color_code' => '#ffc107',
            'type' => 'swing',
            'is_active' => 1,
            'created_at' => '2025-09-10 16:39:11',
            'updated_at' => '2025-09-10 16:39:11'
        ],
        [
            'id' => 3,
            'name' => 'Night Shift',
            'code' => 'NIGHT',
            'description' => 'Overnight shift with premium pay',
            'default_start_time' => '22:00:00',
            'default_end_time' => '06:00:00',
            'break_duration' => 60,
            'hourly_rate' => 32.00,
            'color_code' => '#6f42c1',
            'type' => 'night',
            'is_active' => 1,
            'created_at' => '2025-09-10 16:39:11',
            'updated_at' => '2025-09-10 16:39:11'
        ],
        [
            'id' => 4,
            'name' => 'Split Shift',
            'code' => 'SPLIT',
            'description' => 'Split shift with extended break period',
            'default_start_time' => '09:00:00',
            'default_end_time' => '17:00:00',
            'break_duration' => 120,
            'hourly_rate' => 24.00,
            'color_code' => '#17a2b8',
            'type' => 'split',
            'is_active' => 1,
            'created_at' => '2025-09-10 16:39:11',
            'updated_at' => '2025-09-10 16:39:11'
        ],
        [
            'id' => 5,
            'name' => 'Weekend Shift',
            'code' => 'WEEKEND',
            'description' => 'Weekend coverage with rotating schedule',
            'default_start_time' => '10:00:00',
            'default_end_time' => '18:00:00',
            'break_duration' => 45,
            'hourly_rate' => 30.00,
            'color_code' => '#fd7e14',
            'type' => 'rotating',
            'is_active' => 1,
            'created_at' => '2025-09-10 16:39:11',
            'updated_at' => '2025-09-10 16:39:11'
        ]
    ];
    
    foreach ($shiftTypes as $shiftType) {
        DB::table('shift_types')->insert($shiftType);
    }
    
    echo "✅ Inserted " . count($shiftTypes) . " shift types\n";
    
    // Verify the data
    $count = DB::table('shift_types')->count();
    echo "✅ Verification: {$count} shift types in database\n";
    
    // Check other critical tables
    echo "\n--- Checking other critical tables ---\n";
    
    $criticalTables = [
        'employees',
        'attendances', 
        'time_entries',
        'shifts',
        'ai_generated_timesheets'
    ];
    
    foreach ($criticalTables as $table) {
        if (Schema::hasTable($table)) {
            $count = DB::table($table)->count();
            echo "✅ {$table}: {$count} records\n";
        } else {
            echo "⚠️  {$table}: Table does not exist\n";
        }
    }
    
    echo "\n=== Migration Fix Complete ===\n";
    echo "✅ shift_types table structure fixed and data inserted\n";
    echo "✅ You can now run your SQL migration without errors\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
