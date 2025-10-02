<?php

/**
 * Setup script for AI Timesheet System
 * Run this script to set up the AI timesheet functionality
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

// Bootstrap Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "🤖 AI Timesheet System Setup\n";
echo "============================\n\n";

try {
    // 1. Run migration
    echo "1. Running AI timesheet migration...\n";
    
    if (!Schema::hasTable('ai_generated_timesheets')) {
        \Artisan::call('migrate', [
            '--path' => 'database/migrations/2024_10_03_020000_create_ai_generated_timesheets_table.php'
        ]);
        echo "   ✅ Migration completed successfully\n";
    } else {
        echo "   ℹ️  AI timesheets table already exists\n";
    }

    // 2. Check required tables
    echo "\n2. Checking required tables...\n";
    
    $requiredTables = ['employees', 'shifts', 'shift_types', 'attendances'];
    $missingTables = [];
    
    foreach ($requiredTables as $table) {
        if (Schema::hasTable($table)) {
            echo "   ✅ Table '$table' exists\n";
        } else {
            echo "   ❌ Table '$table' missing\n";
            $missingTables[] = $table;
        }
    }
    
    if (!empty($missingTables)) {
        echo "\n⚠️  Warning: Missing tables detected. AI timesheet generation may not work properly.\n";
        echo "   Please run the main database setup first.\n";
    }

    // 3. Create sample shift types if they don't exist
    echo "\n3. Setting up sample shift types...\n";
    
    $shiftTypes = [
        ['name' => 'Morning Shift', 'start_time' => '08:00:00', 'end_time' => '17:00:00', 'description' => 'Standard morning shift'],
        ['name' => 'Afternoon Shift', 'start_time' => '14:00:00', 'end_time' => '22:00:00', 'description' => 'Afternoon to evening shift'],
        ['name' => 'Night Shift', 'start_time' => '22:00:00', 'end_time' => '06:00:00', 'description' => 'Overnight shift']
    ];
    
    if (Schema::hasTable('shift_types')) {
        foreach ($shiftTypes as $shiftType) {
            $existing = DB::table('shift_types')->where('name', $shiftType['name'])->first();
            if (!$existing) {
                DB::table('shift_types')->insert(array_merge($shiftType, [
                    'created_at' => now(),
                    'updated_at' => now()
                ]));
                echo "   ✅ Created shift type: {$shiftType['name']}\n";
            } else {
                echo "   ℹ️  Shift type already exists: {$shiftType['name']}\n";
            }
        }
    }

    // 4. Create sample shift assignments for current week
    echo "\n4. Creating sample shift assignments...\n";
    
    if (Schema::hasTable('employees') && Schema::hasTable('shifts') && Schema::hasTable('shift_types')) {
        $employees = DB::table('employees')->where('status', 'active')->limit(5)->get();
        $shiftTypeIds = DB::table('shift_types')->pluck('id')->toArray();
        
        if ($employees->count() > 0 && !empty($shiftTypeIds)) {
            $weekStart = Carbon::now()->startOfWeek();
            
            foreach ($employees as $employee) {
                for ($i = 0; $i < 5; $i++) { // Monday to Friday
                    $shiftDate = $weekStart->copy()->addDays($i);
                    
                    // Check if shift already exists
                    $existing = DB::table('shifts')
                        ->where('employee_id', $employee->id)
                        ->where('shift_date', $shiftDate->format('Y-m-d'))
                        ->first();
                        
                    if (!$existing) {
                        $randomShiftType = $shiftTypeIds[array_rand($shiftTypeIds)];
                        $shiftTypeData = DB::table('shift_types')->find($randomShiftType);
                        
                        DB::table('shifts')->insert([
                            'employee_id' => $employee->id,
                            'shift_type_id' => $randomShiftType,
                            'shift_date' => $shiftDate->format('Y-m-d'),
                            'start_time' => $shiftTypeData->start_time,
                            'end_time' => $shiftTypeData->end_time,
                            'status' => 'scheduled',
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                    }
                }
                echo "   ✅ Created shifts for employee: {$employee->first_name} {$employee->last_name}\n";
            }
        } else {
            echo "   ⚠️  No active employees or shift types found for sample data\n";
        }
    }

    // 5. Create sample attendance records
    echo "\n5. Creating sample attendance records...\n";
    
    if (Schema::hasTable('attendances') && Schema::hasTable('employees')) {
        $employees = DB::table('employees')->where('status', 'active')->limit(3)->get();
        
        foreach ($employees as $employee) {
            // Create attendance for last few days
            for ($i = 1; $i <= 3; $i++) {
                $attendanceDate = Carbon::now()->subDays($i);
                
                $existing = DB::table('attendances')
                    ->where('employee_id', $employee->id)
                    ->where('date', $attendanceDate->format('Y-m-d'))
                    ->first();
                    
                if (!$existing) {
                    $clockIn = $attendanceDate->copy()->setTime(8 + rand(0, 2), rand(0, 59));
                    $clockOut = $clockIn->copy()->addHours(8 + rand(0, 2))->addMinutes(rand(0, 59));
                    $totalHours = $clockOut->diffInMinutes($clockIn) / 60;
                    
                    DB::table('attendances')->insert([
                        'employee_id' => $employee->id,
                        'date' => $attendanceDate->format('Y-m-d'),
                        'clock_in_time' => $clockIn->format('Y-m-d H:i:s'),
                        'clock_out_time' => $clockOut->format('Y-m-d H:i:s'),
                        'total_hours' => round($totalHours, 2),
                        'status' => 'clocked_out',
                        'location' => 'Office',
                        'ip_address' => '192.168.1.' . rand(100, 200),
                        'created_at' => $clockIn,
                        'updated_at' => $clockOut
                    ]);
                }
            }
            echo "   ✅ Created attendance records for: {$employee->first_name} {$employee->last_name}\n";
        }
    }

    // 6. Test AI timesheet generation
    echo "\n6. Testing AI timesheet generation...\n";
    
    if (Schema::hasTable('employees')) {
        $testEmployee = DB::table('employees')->where('status', 'active')->first();
        
        if ($testEmployee) {
            try {
                $aiTimesheet = \App\Models\AIGeneratedTimesheet::generateForEmployee($testEmployee->id);
                echo "   ✅ Successfully generated test AI timesheet for: {$testEmployee->first_name} {$testEmployee->last_name}\n";
                echo "   📊 Total hours: {$aiTimesheet->total_hours}, Overtime: {$aiTimesheet->overtime_hours}\n";
            } catch (Exception $e) {
                echo "   ⚠️  AI timesheet generation test failed: " . $e->getMessage() . "\n";
            }
        } else {
            echo "   ⚠️  No active employees found for testing\n";
        }
    }

    echo "\n🎉 AI Timesheet System Setup Complete!\n";
    echo "\nNext steps:\n";
    echo "1. Visit the Timesheet Management page\n";
    echo "2. Click 'Generate AI Timesheet' for any employee\n";
    echo "3. View the generated timesheet with 'See Details'\n";
    echo "4. Approve timesheets to create actual time entries\n";
    
    echo "\nAPI Endpoints available:\n";
    echo "- POST /api/ai-timesheets/generate/{employeeId}\n";
    echo "- POST /api/ai-timesheets/generate-all\n";
    echo "- GET /api/ai-timesheets/view/{employeeId}\n";
    echo "- POST /api/ai-timesheets/approve/{id}\n";

} catch (Exception $e) {
    echo "\n❌ Setup failed: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}

echo "\n✨ Setup completed successfully!\n";
