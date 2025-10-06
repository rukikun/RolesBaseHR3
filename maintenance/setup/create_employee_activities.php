<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;
use App\Models\Employee;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Creating Employee Activities ===\n\n";

try {
    // Get the employee
    $employee = Employee::where('email', 'Renze.Olea@gmail.com')->first();
    
    if (!$employee) {
        echo "❌ Employee not found, using first available employee\n";
        $employee = Employee::first();
        if (!$employee) {
            echo "❌ No employees found\n";
            exit(1);
        }
    }
    
    echo "✅ Using employee: {$employee->email} (ID: {$employee->id})\n\n";

    // Create time_entries table if it doesn't exist
    if (!Schema::hasTable('time_entries')) {
        echo "Creating time_entries table...\n";
        Schema::create('time_entries', function ($table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->date('work_date');
            $table->time('time_in')->nullable();
            $table->time('time_out')->nullable();
            $table->integer('break_duration')->default(60);
            $table->decimal('total_hours', 5, 2)->default(0);
            $table->decimal('overtime_hours', 5, 2)->default(0);
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('description')->nullable();
            $table->timestamps();
        });
        echo "✅ time_entries table created\n";
    }

    // Add sample time entries
    echo "Adding time entries...\n";
    DB::table('time_entries')->where('employee_id', $employee->id)->delete(); // Clear existing
    
    $timeEntries = [
        [
            'employee_id' => $employee->id,
            'work_date' => now()->subDays(2)->format('Y-m-d'),
            'time_in' => '08:00:00',
            'time_out' => '17:00:00',
            'break_duration' => 60,
            'total_hours' => 8.0,
            'overtime_hours' => 0.0,
            'status' => 'approved',
            'description' => 'Regular work day',
            'created_at' => now()->subDays(2),
            'updated_at' => now()->subDays(2)
        ],
        [
            'employee_id' => $employee->id,
            'work_date' => now()->subDays(1)->format('Y-m-d'),
            'time_in' => '08:30:00',
            'time_out' => '18:00:00',
            'break_duration' => 60,
            'total_hours' => 8.5,
            'overtime_hours' => 0.5,
            'status' => 'pending',
            'description' => 'Overtime work',
            'created_at' => now()->subDays(1),
            'updated_at' => now()->subDays(1)
        ],
        [
            'employee_id' => $employee->id,
            'work_date' => now()->format('Y-m-d'),
            'time_in' => '08:15:00',
            'time_out' => null,
            'break_duration' => 0,
            'total_hours' => 0.0,
            'overtime_hours' => 0.0,
            'status' => 'pending',
            'description' => 'Current work day',
            'created_at' => now(),
            'updated_at' => now()
        ]
    ];
    
    foreach ($timeEntries as $entry) {
        DB::table('time_entries')->insert($entry);
    }
    echo "✅ Added 3 time entries\n";

    // Test recent activities
    echo "\nTesting recent activities...\n";
    try {
        $activities = $employee->recentActivities(5)->get();
        echo "✅ Recent activities working: " . $activities->count() . " activities found\n";
        
        if ($activities->count() > 0) {
            echo "Recent activities:\n";
            foreach ($activities as $activity) {
                echo "  • {$activity['type']}: {$activity['description']}\n";
            }
        }
    } catch (\Exception $e) {
        echo "❌ Recent activities error: " . $e->getMessage() . "\n";
    }

    echo "\n✅ EMPLOYEE ACTIVITIES CREATED SUCCESSFULLY!\n";
    echo "The Recent Activity section should now show timesheet activities.\n";
    echo "Refresh the profile page to see the changes.\n";

} catch (\Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}
