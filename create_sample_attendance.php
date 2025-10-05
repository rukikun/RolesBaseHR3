<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    echo "Creating sample attendance data...\n";
    
    // Get some employees
    $employees = DB::table('employees')->limit(3)->get();
    
    if ($employees->isEmpty()) {
        echo "No employees found. Please create employees first.\n";
        exit(1);
    }
    
    // Clear existing attendance data for testing
    DB::table('attendances')->truncate();
    
    foreach ($employees as $employee) {
        echo "Creating attendance for employee: {$employee->first_name} {$employee->last_name}\n";
        
        // Create attendance records for the last 10 days
        for ($i = 9; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            
            // Skip weekends
            if ($date->isWeekend()) {
                continue;
            }
            
            // Random clock-in time between 8:00 AM and 9:30 AM
            $clockInHour = rand(8, 9);
            $clockInMinute = rand(0, 30);
            $clockInTime = $date->copy()->setTime($clockInHour, $clockInMinute, 0);
            
            // Random clock-out time between 5:00 PM and 6:30 PM
            $clockOutHour = rand(17, 18);
            $clockOutMinute = rand(0, 30);
            $clockOutTime = $date->copy()->setTime($clockOutHour, $clockOutMinute, 0);
            
            // Calculate total hours
            $totalMinutes = $clockOutTime->diffInMinutes($clockInTime);
            $totalHours = round($totalMinutes / 60, 2);
            
            // Calculate overtime (anything over 8 hours)
            $overtimeHours = $totalHours > 8 ? $totalHours - 8 : 0;
            
            // Determine status
            $standardStart = $date->copy()->setTime(9, 0, 0);
            $status = $clockInTime->gt($standardStart) ? 'late' : 'present';
            
            // If it's today and we haven't clocked out yet, don't set clock_out_time
            $isToday = $date->isToday();
            $actualClockOut = $isToday ? null : $clockOutTime;
            $actualTotalHours = $isToday ? 0 : $totalHours;
            $actualOvertimeHours = $isToday ? 0 : $overtimeHours;
            $actualStatus = $isToday ? 'present' : 'clocked_out';
            
            DB::table('attendances')->insert([
                'employee_id' => $employee->id,
                'date' => $date->toDateString(),
                'clock_in_time' => $clockInTime,
                'clock_out_time' => $actualClockOut,
                'total_hours' => $actualTotalHours,
                'overtime_hours' => $actualOvertimeHours,
                'status' => $actualStatus,
                'location' => 'Office',
                'ip_address' => '192.168.1.' . rand(100, 200),
                'notes' => 'Regular work day',
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }
    
    echo "\n✅ Sample attendance data created successfully!\n";
    echo "Created attendance records for " . $employees->count() . " employees.\n";
    
    // Show summary
    $totalRecords = DB::table('attendances')->count();
    $todayRecords = DB::table('attendances')->whereDate('date', Carbon::today())->count();
    
    echo "\nSummary:\n";
    echo "- Total attendance records: {$totalRecords}\n";
    echo "- Today's attendance records: {$todayRecords}\n";
    
} catch (Exception $e) {
    echo "❌ Error creating sample attendance data: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
