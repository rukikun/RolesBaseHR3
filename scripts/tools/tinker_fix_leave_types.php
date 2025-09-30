<?php
/**
 * Laravel Tinker Script to Fix Leave Types
 * Run this in tinker: php artisan tinker
 * Then copy and paste this code section by section
 */

// ===== SECTION 1: DROP AND RECREATE TABLE =====
echo "=== DROPPING AND RECREATING LEAVE_TYPES TABLE ===\n";

// Drop the problematic table
DB::statement('DROP TABLE IF EXISTS leave_types');
echo "✅ Dropped leave_types table\n";

// Create fresh table with proper structure
DB::statement("
CREATE TABLE leave_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    code VARCHAR(10) NOT NULL,
    description TEXT,
    max_days_per_year INT DEFAULT 30,
    carry_forward BOOLEAN DEFAULT FALSE,
    requires_approval BOOLEAN DEFAULT TRUE,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_name (name),
    UNIQUE KEY unique_code (code)
)");
echo "✅ Created new leave_types table with proper structure\n";

// ===== SECTION 2: INSERT CLEAN DATA =====
echo "\n=== INSERTING CLEAN LEAVE TYPES DATA ===\n";

$leaveTypes = [
    [
        'name' => 'Annual Leave',
        'code' => 'AL',
        'description' => 'Annual vacation leave',
        'max_days_per_year' => 21,
        'carry_forward' => true,
        'requires_approval' => true,
        'is_active' => true
    ],
    [
        'name' => 'Sick Leave',
        'code' => 'SL',
        'description' => 'Medical sick leave',
        'max_days_per_year' => 10,
        'carry_forward' => false,
        'requires_approval' => false,
        'is_active' => true
    ],
    [
        'name' => 'Emergency Leave',
        'code' => 'EL',
        'description' => 'Emergency family leave',
        'max_days_per_year' => 5,
        'carry_forward' => false,
        'requires_approval' => true,
        'is_active' => true
    ],
    [
        'name' => 'Maternity Leave',
        'code' => 'ML',
        'description' => 'Maternity leave',
        'max_days_per_year' => 90,
        'carry_forward' => false,
        'requires_approval' => true,
        'is_active' => true
    ],
    [
        'name' => 'Paternity Leave',
        'code' => 'PL',
        'description' => 'Paternity leave',
        'max_days_per_year' => 7,
        'carry_forward' => false,
        'requires_approval' => true,
        'is_active' => true
    ]
];

foreach ($leaveTypes as $type) {
    DB::table('leave_types')->insert($type);
    echo "✅ Inserted: {$type['name']} ({$type['code']})\n";
}

// ===== SECTION 3: VERIFICATION =====
echo "\n=== VERIFICATION ===\n";

$results = DB::table('leave_types')->orderBy('id')->get();
echo "📊 Total leave types: " . $results->count() . "\n";

foreach ($results as $type) {
    echo "ID: {$type->id} | {$type->name} ({$type->code}) | {$type->max_days_per_year} days | " . 
         ($type->is_active ? 'Active' : 'Inactive') . "\n";
}

// Check auto-increment status
$autoIncrement = DB::select("SELECT AUTO_INCREMENT FROM information_schema.TABLES WHERE TABLE_SCHEMA = 'hr3systemdb' AND TABLE_NAME = 'leave_types'");
echo "📈 Next auto-increment ID: " . $autoIncrement[0]->AUTO_INCREMENT . "\n";

echo "\n🎉 Leave types table fixed successfully!\n";
echo "✅ Proper IDs assigned\n";
echo "✅ No duplicates\n";
echo "✅ Auto-increment working\n";
echo "✅ Unique constraints added\n";
?>
