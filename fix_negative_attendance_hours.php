<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as DB;
use Carbon\Carbon;

// Load Laravel configuration
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "🔧 Fixing negative attendance hours in database...\n\n";

try {
    // Find all attendance records with negative total_hours
    $negativeRecords = DB::table('attendances')
        ->where('total_hours', '<', 0)
        ->orWhere('overtime_hours', '<', 0)
        ->get();

    echo "📊 Found " . $negativeRecords->count() . " records with negative hours\n\n";

    if ($negativeRecords->count() === 0) {
        echo "✅ No negative hours found in database!\n";
        exit(0);
    }

    $fixedCount = 0;
    $errorCount = 0;

    foreach ($negativeRecords as $record) {
        try {
            echo "🔍 Processing record ID: {$record->id}\n";
            echo "   Employee ID: {$record->employee_id}\n";
            echo "   Date: {$record->date}\n";
            echo "   Current total_hours: {$record->total_hours}\n";
            echo "   Current overtime_hours: {$record->overtime_hours}\n";

            // Recalculate hours using the fixed logic
            if ($record->clock_in_time && $record->clock_out_time) {
                $clockIn = Carbon::parse($record->clock_in_time);
                $clockOut = Carbon::parse($record->clock_out_time);
                
                // Handle overnight shifts
                if ($clockOut->lt($clockIn)) {
                    $clockOut->addDay();
                }

                // Use absolute difference to ensure positive values
                $totalMinutes = abs($clockOut->diffInMinutes($clockIn));
                
                // Subtract break time if available
                if ($record->break_start_time && $record->break_end_time) {
                    $breakStart = Carbon::parse($record->break_start_time);
                    $breakEnd = Carbon::parse($record->break_end_time);
                    $breakMinutes = abs($breakEnd->diffInMinutes($breakStart));
                    $totalMinutes = max(0, $totalMinutes - $breakMinutes);
                }

                // Calculate new hours
                $newTotalHours = round($totalMinutes / 60, 2);
                $newTotalHours = max(0, min($newTotalHours, 24)); // Ensure reasonable bounds
                
                $newOvertimeHours = $newTotalHours > 8 ? $newTotalHours - 8 : 0;

                // Update the record
                DB::table('attendances')
                    ->where('id', $record->id)
                    ->update([
                        'total_hours' => $newTotalHours,
                        'overtime_hours' => $newOvertimeHours,
                        'updated_at' => now()
                    ]);

                echo "   ✅ Fixed! New total_hours: {$newTotalHours}, New overtime_hours: {$newOvertimeHours}\n\n";
                $fixedCount++;
            } else {
                echo "   ⚠️  Skipped - Missing clock in/out times\n\n";
            }

        } catch (Exception $e) {
            echo "   ❌ Error fixing record ID {$record->id}: " . $e->getMessage() . "\n\n";
            $errorCount++;
        }
    }

    echo "📈 Summary:\n";
    echo "   ✅ Fixed records: {$fixedCount}\n";
    echo "   ❌ Errors: {$errorCount}\n";
    echo "   📊 Total processed: " . ($fixedCount + $errorCount) . "\n\n";

    if ($fixedCount > 0) {
        echo "🎉 Successfully fixed negative attendance hours!\n";
        echo "💡 The Attendance model has been updated to prevent future negative values.\n";
    }

} catch (Exception $e) {
    echo "❌ Fatal error: " . $e->getMessage() . "\n";
    exit(1);
}
