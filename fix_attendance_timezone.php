<?php

require_once 'vendor/autoload.php';

// Load Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

echo "Fixing attendance timezone records...\n\n";

// Get all attendance records that need timezone correction
$attendances = DB::table('attendances')
    ->whereNotNull('clock_in_time')
    ->get();

$updated = 0;

foreach ($attendances as $attendance) {
    try {
        $updates = [];
        
        // Fix clock_in_time if it exists
        if ($attendance->clock_in_time) {
            // Parse as UTC and convert to Asia/Manila (+8 hours)
            $clockInUTC = Carbon::parse($attendance->clock_in_time, 'UTC');
            $clockInLocal = $clockInUTC->setTimezone('Asia/Manila');
            $updates['clock_in_time'] = $clockInLocal->format('Y-m-d H:i:s');
            
            echo "ID {$attendance->id}: Clock In UTC: {$clockInUTC->format('Y-m-d H:i:s')} -> Local: {$clockInLocal->format('Y-m-d H:i:s')}\n";
        }
        
        // Fix clock_out_time if it exists
        if ($attendance->clock_out_time) {
            $clockOutUTC = Carbon::parse($attendance->clock_out_time, 'UTC');
            $clockOutLocal = $clockOutUTC->setTimezone('Asia/Manila');
            $updates['clock_out_time'] = $clockOutLocal->format('Y-m-d H:i:s');
            
            echo "ID {$attendance->id}: Clock Out UTC: {$clockOutUTC->format('Y-m-d H:i:s')} -> Local: {$clockOutLocal->format('Y-m-d H:i:s')}\n";
        }
        
        if (!empty($updates)) {
            DB::table('attendances')
                ->where('id', $attendance->id)
                ->update($updates);
            $updated++;
        }
        
    } catch (Exception $e) {
        echo "Error processing ID {$attendance->id}: {$e->getMessage()}\n";
    }
}

echo "\nCompleted! Updated {$updated} attendance records.\n";
echo "All times have been converted from UTC to Asia/Manila timezone (+8 hours).\n";
