<?php

require_once 'vendor/autoload.php';

// Load Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

echo "Checking attendance data...\n\n";

// Get recent attendance records
$attendances = DB::table('attendances')
    ->orderBy('date', 'desc')
    ->limit(5)
    ->get(['id', 'date', 'clock_in_time', 'clock_out_time']);

foreach ($attendances as $attendance) {
    echo "ID: {$attendance->id}\n";
    echo "Date: {$attendance->date}\n";
    echo "Clock In (Raw): {$attendance->clock_in_time}\n";
    echo "Clock Out (Raw): {$attendance->clock_out_time}\n";
    
    if ($attendance->clock_in_time) {
        try {
            $parsed = Carbon::parse($attendance->clock_in_time);
            echo "Clock In (Parsed): {$parsed->format('Y-m-d H:i:s')}\n";
            echo "Clock In (12-hour): {$parsed->format('h:i A')}\n";
        } catch (Exception $e) {
            echo "Clock In Parse Error: {$e->getMessage()}\n";
        }
    }
    
    echo "---\n";
}
