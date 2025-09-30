<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TimeAttendanceController extends Controller
{
    public function clockIn(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|integer',
            'clock_in' => 'required|date_format:H:i'
        ]);

        DB::table('time_entries')->insert([
            'employee_id' => $validated['employee_id'],
            'date' => now()->format('Y-m-d'),
            'clock_in' => $validated['clock_in'],
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return response()->json(['success' => true]);
    }
}
