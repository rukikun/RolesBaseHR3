<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as DB;
use Carbon\Carbon;

// Load Laravel configuration
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "🔍 Checking attendance data for Jane Doe and Jonny Duyanon...\n\n";

try {
    // Find employees
    $employees = DB::table('employees')
        ->whereIn('first_name', ['Jane', 'Jonny'])
        ->select('id', 'first_name', 'last_name')
        ->get();

    echo "=== EMPLOYEES FOUND ===\n";
    foreach ($employees as $emp) {
        echo "ID: {$emp->id}, Name: {$emp->first_name} {$emp->last_name}\n";
    }

    echo "\n=== ATTENDANCE DATA ===\n";
    foreach ($employees as $emp) {
        echo "\n--- {$emp->first_name} {$emp->last_name} (ID: {$emp->id}) ---\n";
        
        $attendance = DB::table('attendances')
            ->where('employee_id', $emp->id)
            ->orderBy('date', 'desc')
            ->get();
        
        if ($attendance->count() > 0) {
            foreach ($attendance as $att) {
                echo "Date: {$att->date}, Clock In: {$att->clock_in_time}, Clock Out: {$att->clock_out_time}, Total Hours: {$att->total_hours}\n";
            }
        } else {
            echo "No attendance records found\n";
        }
    }

    // Check week calculation
    echo "\n=== WEEK CALCULATION TEST ===\n";
    $weekStart = Carbon::parse('2025-10-06')->startOfWeek(Carbon::MONDAY);
    echo "Week Start: " . $weekStart->format('Y-m-d') . "\n";
    echo "Week End: " . $weekStart->copy()->endOfWeek()->format('Y-m-d') . "\n";

    echo "\n=== ATTENDANCE IN CURRENT WEEK ===\n";
    foreach ($employees as $emp) {
        echo "\n--- {$emp->first_name} {$emp->last_name} ---\n";
        
        $weekAttendance = DB::table('attendances')
            ->where('employee_id', $emp->id)
            ->where('date', '>=', $weekStart->format('Y-m-d'))
            ->where('date', '<=', $weekStart->copy()->endOfWeek()->format('Y-m-d'))
            ->orderBy('date', 'asc')
            ->get();
        
        if ($weekAttendance->count() > 0) {
            foreach ($weekAttendance as $att) {
                echo "Date: {$att->date}, Total Hours: {$att->total_hours}, Overtime: {$att->overtime_hours}\n";
            }
        } else {
            echo "No attendance records in current week\n";
        }
    }

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
