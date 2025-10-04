<?php

require_once 'vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;

// Database configuration
$capsule = new Capsule;
$capsule->addConnection([
    'driver' => 'mysql',
    'host' => '127.0.0.1',
    'database' => 'hr3systemdb',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix' => '',
]);

$capsule->setAsGlobal();
$capsule->bootEloquent();

try {
    echo "Starting data population...\n";

    // Claim Types
    echo "Populating claim_types...\n";
    Capsule::table('claim_types')->truncate();
    
    $claimTypes = [
        ['name' => 'Travel Expenses', 'code' => 'TRAVEL', 'description' => 'Business travel related expenses', 'max_amount' => 5000.00, 'requires_attachment' => 1, 'auto_approve' => 0, 'is_active' => 1],
        ['name' => 'Office Supplies', 'code' => 'OFFICE', 'description' => 'Office supplies and equipment', 'max_amount' => 1000.00, 'requires_attachment' => 1, 'auto_approve' => 0, 'is_active' => 1],
        ['name' => 'Meal Allowance', 'code' => 'MEAL', 'description' => 'Business meal expenses', 'max_amount' => 500.00, 'requires_attachment' => 1, 'auto_approve' => 0, 'is_active' => 1],
        ['name' => 'Training Costs', 'code' => 'TRAINING', 'description' => 'Professional development and training', 'max_amount' => 2000.00, 'requires_attachment' => 1, 'auto_approve' => 0, 'is_active' => 1],
        ['name' => 'Medical Expenses', 'code' => 'MEDICAL', 'description' => 'Medical and health related expenses', 'max_amount' => 3000.00, 'requires_attachment' => 1, 'auto_approve' => 0, 'is_active' => 1],
    ];

    foreach ($claimTypes as $claimType) {
        Capsule::table('claim_types')->insert(array_merge($claimType, [
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]));
    }
    echo "✓ Claim types populated successfully\n";

    // Leave Types
    echo "Populating leave_types...\n";
    Capsule::table('leave_types')->truncate();
    
    $leaveTypes = [
        ['name' => 'Annual Leave', 'code' => 'AL', 'description' => 'Annual vacation leave', 'days_allowed' => 0, 'max_days_per_year' => 21, 'carry_forward' => 1, 'requires_approval' => 1, 'status' => 'active', 'is_active' => 1],
        ['name' => 'Sick Leave', 'code' => 'SL', 'description' => 'Medical sick leave', 'days_allowed' => 0, 'max_days_per_year' => 10, 'carry_forward' => 0, 'requires_approval' => 0, 'status' => 'active', 'is_active' => 1],
        ['name' => 'Emergency Leave', 'code' => 'EL', 'description' => 'Emergency family leave', 'days_allowed' => 0, 'max_days_per_year' => 5, 'carry_forward' => 0, 'requires_approval' => 1, 'status' => 'active', 'is_active' => 1],
        ['name' => 'Maternity Leave', 'code' => 'ML', 'description' => 'Maternity leave', 'days_allowed' => 0, 'max_days_per_year' => 90, 'carry_forward' => 0, 'requires_approval' => 1, 'status' => 'active', 'is_active' => 1],
        ['name' => 'Paternity Leave', 'code' => 'PL', 'description' => 'Paternity leave', 'days_allowed' => 0, 'max_days_per_year' => 7, 'carry_forward' => 0, 'requires_approval' => 1, 'status' => 'active', 'is_active' => 1],
    ];

    foreach ($leaveTypes as $leaveType) {
        Capsule::table('leave_types')->insert(array_merge($leaveType, [
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]));
    }
    echo "✓ Leave types populated successfully\n";

    // Shift Types
    echo "Populating shift_types...\n";
    Capsule::table('shift_types')->truncate();
    
    $shiftTypes = [
        ['name' => 'Morning Shift', 'code' => 'MORNING', 'description' => 'Standard morning shift for regular operations', 'default_start_time' => '08:00:00', 'default_end_time' => '16:00:00', 'break_duration' => 60, 'hourly_rate' => 25.00, 'color_code' => '#28a745', 'type' => 'day', 'is_active' => 1],
        ['name' => 'Afternoon Shift', 'code' => 'AFTERNOON', 'description' => 'Afternoon to evening coverage shift', 'default_start_time' => '14:00:00', 'default_end_time' => '22:00:00', 'break_duration' => 45, 'hourly_rate' => 27.50, 'color_code' => '#ffc107', 'type' => 'swing', 'is_active' => 1],
        ['name' => 'Night Shift', 'code' => 'NIGHT', 'description' => 'Overnight shift with premium pay', 'default_start_time' => '22:00:00', 'default_end_time' => '06:00:00', 'break_duration' => 60, 'hourly_rate' => 32.00, 'color_code' => '#6f42c1', 'type' => 'night', 'is_active' => 1],
        ['name' => 'Split Shift', 'code' => 'SPLIT', 'description' => 'Split shift with extended break period', 'default_start_time' => '09:00:00', 'default_end_time' => '17:00:00', 'break_duration' => 120, 'hourly_rate' => 24.00, 'color_code' => '#17a2b8', 'type' => 'split', 'is_active' => 1],
        ['name' => 'Weekend Shift', 'code' => 'WEEKEND', 'description' => 'Weekend coverage with rotating schedule', 'default_start_time' => '10:00:00', 'default_end_time' => '18:00:00', 'break_duration' => 45, 'hourly_rate' => 30.00, 'color_code' => '#fd7e14', 'type' => 'rotating', 'is_active' => 1],
    ];

    foreach ($shiftTypes as $shiftType) {
        Capsule::table('shift_types')->insert(array_merge($shiftType, [
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]));
    }
    echo "✓ Shift types populated successfully\n";

    // Verify data
    echo "\nVerification:\n";
    echo "Claim Types: " . Capsule::table('claim_types')->count() . " records\n";
    echo "Leave Types: " . Capsule::table('leave_types')->count() . " records\n";
    echo "Shift Types: " . Capsule::table('shift_types')->count() . " records\n";

    echo "\n✅ All type tables populated successfully!\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
