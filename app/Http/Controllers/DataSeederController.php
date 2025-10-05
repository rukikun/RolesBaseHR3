<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Database\Seeders\DashboardDataSeeder;
use App\Models\Attendance;
use Exception;

class DataSeederController extends Controller
{
    /**
     * Populate dashboard data
     */
    public function populateDashboard()
    {
        try {
            $seeder = new DashboardDataSeeder();
            $seeder->run();
            return response()->json([
                'success' => true,
                'message' => 'Dashboard data populated successfully!'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error populating data: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Populate timesheet data
     */
    public function populateTimesheets()
    {
        try {
            $count = DB::table('time_entries')->count();
            if ($count > 0) {
                return "Timesheet data already exists. Count: {$count}";
            }
            
            $timesheetData = [
                [
                    'employee_id' => 1,
                    'work_date' => '2024-10-01',
                    'clock_in_time' => '09:00:00',
                    'clock_out_time' => '17:30:00',
                    'hours_worked' => 8.0,
                    'overtime_hours' => 0.5,
                    'status' => 'pending'
                ],
                [
                    'employee_id' => 1,
                    'work_date' => '2024-09-30',
                    'clock_in_time' => '08:45:00',
                    'clock_out_time' => '17:15:00',
                    'hours_worked' => 8.0,
                    'overtime_hours' => 0.5,
                    'status' => 'approved'
                ],
                [
                    'employee_id' => 2,
                    'work_date' => '2024-10-01',
                    'clock_in_time' => '08:30:00',
                    'clock_out_time' => '17:00:00',
                    'hours_worked' => 8.0,
                    'overtime_hours' => 0.5,
                    'status' => 'approved'
                ],
                [
                    'employee_id' => 2,
                    'work_date' => '2024-09-30',
                    'clock_in_time' => '09:00:00',
                    'clock_out_time' => '17:30:00',
                    'hours_worked' => 8.0,
                    'overtime_hours' => 0.5,
                    'status' => 'pending'
                ],
                [
                    'employee_id' => 3,
                    'work_date' => '2024-10-01',
                    'clock_in_time' => '09:30:00',
                    'clock_out_time' => '18:15:00',
                    'hours_worked' => 8.0,
                    'overtime_hours' => 0.75,
                    'status' => 'pending'
                ],
                [
                    'employee_id' => 3,
                    'work_date' => '2024-09-30',
                    'clock_in_time' => '09:00:00',
                    'clock_out_time' => '17:00:00',
                    'hours_worked' => 8.0,
                    'overtime_hours' => 0.0,
                    'status' => 'rejected'
                ],
                [
                    'employee_id' => 4,
                    'work_date' => '2024-10-01',
                    'clock_in_time' => '08:45:00',
                    'clock_out_time' => '17:15:00',
                    'hours_worked' => 8.0,
                    'overtime_hours' => 0.5,
                    'status' => 'approved'
                ],
                [
                    'employee_id' => 4,
                    'work_date' => '2024-09-30',
                    'clock_in_time' => '09:15:00',
                    'clock_out_time' => '18:00:00',
                    'hours_worked' => 8.0,
                    'overtime_hours' => 0.75,
                    'status' => 'pending'
                ],
                [
                    'employee_id' => 5,
                    'work_date' => '2024-10-01',
                    'clock_in_time' => '09:00:00',
                    'clock_out_time' => '17:30:00',
                    'hours_worked' => 8.0,
                    'overtime_hours' => 0.5,
                    'status' => 'pending'
                ],
                [
                    'employee_id' => 5,
                    'work_date' => '2024-09-30',
                    'clock_in_time' => '08:30:00',
                    'clock_out_time' => '17:00:00',
                    'hours_worked' => 8.0,
                    'overtime_hours' => 0.5,
                    'status' => 'approved'
                ]
            ];

            DB::table('time_entries')->insert($timesheetData);
            $newCount = DB::table('time_entries')->count();
            
            return "Successfully populated timesheet data! Total entries: {$newCount}";
            
        } catch (Exception $e) {
            return "Error: " . $e->getMessage();
        }
    }

    /**
     * Populate attendance data
     */
    public function populateAttendance()
    {
        try {
            $count = DB::table('attendances')->count();
            if ($count > 0) {
                return "Attendance data already exists. Count: {$count}";
            }
            
            // Get employees
            $employees = DB::table('employees')->limit(5)->get();
            
            if ($employees->isEmpty()) {
                return "No employees found. Please ensure employees exist first.";
            }
            
            $attendanceData = [];
            $dates = [
                '2024-10-01',
                '2024-09-30', 
                '2024-09-29',
                '2024-09-28',
                '2024-09-27'
            ];
            
            foreach ($employees as $employee) {
                foreach ($dates as $date) {
                    $clockIn = Carbon::parse($date . ' ' . sprintf('%02d:%02d:00', rand(8, 9), rand(0, 59)));
                    $clockOut = $clockIn->copy()->addHours(8)->addMinutes(rand(0, 60));
                    $totalHours = $clockOut->diffInHours($clockIn, true);
                    $overtimeHours = max(0, $totalHours - 8);
                    
                    $attendanceData[] = [
                        'employee_id' => $employee->id,
                        'date' => $date,
                        'clock_in_time' => $clockIn,
                        'clock_out_time' => $clockOut,
                        'total_hours' => round($totalHours, 2),
                        'overtime_hours' => round($overtimeHours, 2),
                        'status' => 'clocked_out',
                        'location' => 'ESS Portal',
                        'ip_address' => '127.0.0.1',
                        'created_at' => now(),
                        'updated_at' => now()
                    ];
                }
            }
            
            DB::table('attendances')->insert($attendanceData);
            $newCount = DB::table('attendances')->count();
            
            return "Successfully populated attendance data! Total entries: {$newCount}";
            
        } catch (Exception $e) {
            return "Error: " . $e->getMessage();
        }
    }

    /**
     * Populate all data (combined)
     */
    public function populateAllData()
    {
        try {
            $results = [];
            
            // Check existing data
            $timesheetCount = DB::table('time_entries')->count();
            $attendanceCount = DB::table('attendances')->count();
            
            $results[] = "Current data - Timesheets: {$timesheetCount}, Attendances: {$attendanceCount}";
            
            // Get employees
            $employees = DB::table('employees')->limit(5)->get();
            
            if ($employees->isEmpty()) {
                return "No employees found. Please ensure employees exist first.";
            }
            
            $dates = [
                '2024-10-01',
                '2024-09-30', 
                '2024-09-29',
                '2024-09-28',
                '2024-09-27'
            ];
            
            $timesheetData = [];
            $attendanceData = [];
            
            foreach ($employees as $employee) {
                foreach ($dates as $date) {
                    // Generate realistic times
                    $clockInHour = rand(8, 9);
                    $clockInMinute = rand(0, 59);
                    $clockInTime = sprintf('%02d:%02d:00', $clockInHour, $clockInMinute);
                    
                    $clockInDateTime = Carbon::parse($date . ' ' . $clockInTime);
                    $clockOutDateTime = $clockInDateTime->copy()->addHours(8)->addMinutes(rand(0, 60));
                    
                    $totalHours = $clockOutDateTime->diffInHours($clockInDateTime, true);
                    $regularHours = min(8, $totalHours);
                    $overtimeHours = max(0, $totalHours - 8);
                    
                    // Create timesheet entry
                    if ($timesheetCount == 0) {
                        $timesheetData[] = [
                            'employee_id' => $employee->id,
                            'work_date' => $date,
                            'clock_in_time' => $clockInTime,
                            'clock_out_time' => $clockOutDateTime->format('H:i:s'),
                            'hours_worked' => round($regularHours, 2),
                            'overtime_hours' => round($overtimeHours, 2),
                            'break_duration' => 1.0,
                            'description' => 'Regular work day',
                            'status' => rand(0, 1) ? 'approved' : 'pending',
                            'created_at' => now(),
                            'updated_at' => now()
                        ];
                    }
                    
                    // Create attendance entry
                    if ($attendanceCount == 0) {
                        $attendanceData[] = [
                            'employee_id' => $employee->id,
                            'date' => $date,
                            'clock_in_time' => $clockInDateTime,
                            'clock_out_time' => $clockOutDateTime,
                            'total_hours' => round($totalHours, 2),
                            'overtime_hours' => round($overtimeHours, 2),
                            'status' => 'clocked_out',
                            'location' => 'ESS Portal',
                            'ip_address' => '127.0.0.1',
                            'created_at' => now(),
                            'updated_at' => now()
                        ];
                    }
                }
            }
            
            // Insert data
            if (!empty($timesheetData)) {
                DB::table('time_entries')->insert($timesheetData);
                $results[] = "Inserted " . count($timesheetData) . " timesheet entries";
            }
            
            if (!empty($attendanceData)) {
                DB::table('attendances')->insert($attendanceData);
                $results[] = "Inserted " . count($attendanceData) . " attendance entries";
            }
            
            // Final counts
            $finalTimesheetCount = DB::table('time_entries')->count();
            $finalAttendanceCount = DB::table('attendances')->count();
            
            $results[] = "Final data - Timesheets: {$finalTimesheetCount}, Attendances: {$finalAttendanceCount}";
            
            return implode('<br>', $results);
            
        } catch (Exception $e) {
            return "Error: " . $e->getMessage();
        }
    }

    /**
     * Create sample shifts
     */
    public function createSampleShifts()
    {
        try {
            // Check if we have employees and shift types
            $employees = DB::table('employees')->where('status', 'active')->limit(3)->get();
            $shiftTypes = DB::table('shift_types')->where('is_active', 1)->limit(3)->get();
            
            if ($employees->isEmpty() || $shiftTypes->isEmpty()) {
                return response()->json([
                    'error' => 'Need employees and shift types first',
                    'employees_count' => $employees->count(),
                    'shift_types_count' => $shiftTypes->count()
                ]);
            }
            
            // Create sample shifts for the next few days
            $dates = [
                Carbon::today()->format('Y-m-d'),
                Carbon::today()->addDay()->format('Y-m-d'),
                Carbon::today()->addDays(2)->format('Y-m-d'),
            ];
            
            $created = 0;
            foreach ($employees as $employee) {
                foreach ($dates as $date) {
                    $shiftType = $shiftTypes->random();
                    
                    // Check if shift already exists
                    $exists = DB::table('shifts')
                        ->where('employee_id', $employee->id)
                        ->where('shift_date', $date)
                        ->exists();
                        
                    if (!$exists) {
                        DB::table('shifts')->insert([
                            'employee_id' => $employee->id,
                            'shift_type_id' => $shiftType->id,
                            'shift_date' => $date,
                            'start_time' => $shiftType->default_start_time ?? '09:00:00',
                            'end_time' => $shiftType->default_end_time ?? '17:00:00',
                            'location' => 'Main Office',
                            'notes' => 'Sample shift for testing',
                            'status' => 'scheduled',
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                        $created++;
                    }
                }
            }
            
            return response()->json([
                'success' => true,
                'message' => "Created {$created} sample shifts",
                'employees_used' => $employees->count(),
                'shift_types_available' => $shiftTypes->count()
            ]);
            
        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Fix and populate data after fixing ID columns
     */
    public function fixAndPopulate()
    {
        try {
            $results = [];
            
            // Fix both table ID columns
            try {
                DB::statement('ALTER TABLE attendances MODIFY id BIGINT UNSIGNED AUTO_INCREMENT');
                $results[] = "✅ Fixed attendances ID column";
            } catch (Exception $e) {
                $results[] = "⚠️ Attendances ID: " . $e->getMessage();
            }
            
            try {
                DB::statement('ALTER TABLE time_entries MODIFY id BIGINT UNSIGNED AUTO_INCREMENT');
                $results[] = "✅ Fixed time_entries ID column";
            } catch (Exception $e) {
                $results[] = "⚠️ Time entries ID: " . $e->getMessage();
            }
            
            // Create sample data
            $employees = DB::table('employees')->limit(3)->get();
            
            if ($employees->isEmpty()) {
                $results[] = "❌ No employees found";
                return implode('<br>', $results);
            }
            
            $attendanceCount = 0;
            $timesheetCount = 0;
            
            foreach ($employees as $employee) {
                // Create attendance for today
                try {
                    DB::table('attendances')->insert([
                        'employee_id' => $employee->id,
                        'date' => Carbon::today(),
                        'clock_in_time' => Carbon::now()->subHours(2),
                        'clock_out_time' => Carbon::now(),
                        'total_hours' => 8.0,
                        'overtime_hours' => 0.0,
                        'status' => 'clocked_out',
                        'location' => 'ESS Portal',
                        'ip_address' => '127.0.0.1',
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                    $attendanceCount++;
                } catch (Exception $e) {
                    $results[] = "⚠️ Attendance for employee {$employee->id}: " . $e->getMessage();
                }
                
                // Create timesheet entry
                try {
                    DB::table('time_entries')->insert([
                        'employee_id' => $employee->id,
                        'work_date' => Carbon::today(),
                        'clock_in_time' => '09:00:00',
                        'clock_out_time' => '17:00:00',
                        'hours_worked' => 8.0,
                        'overtime_hours' => 0.0,
                        'status' => 'pending',
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                    $timesheetCount++;
                } catch (Exception $e) {
                    $results[] = "⚠️ Timesheet for employee {$employee->id}: " . $e->getMessage();
                }
            }
            
            $results[] = "✅ Created {$attendanceCount} attendance records";
            $results[] = "✅ Created {$timesheetCount} timesheet records";
            $results[] = "<br><strong>🎉 System should now work! Try clock-in/out functionality.</strong>";
            
            return implode('<br>', $results);
            
        } catch (Exception $e) {
            return "Error: " . $e->getMessage();
        }
    }

    /**
     * Create sample attendance data for testing ESS attendance logs
     */
    public function createSampleAttendance()
    {
        try {
            $results = [];
            $results[] = "<h3>🕐 Creating Sample Attendance Data</h3>";
            
            // Get employees
            $employees = DB::table('employees')->limit(3)->get();
            
            if ($employees->isEmpty()) {
                return "❌ No employees found. Please create employees first.";
            }
            
            $results[] = "Found {$employees->count()} employees";
            
            // Clear existing attendance data for testing
            $existingCount = DB::table('attendances')->count();
            if ($existingCount > 0) {
                DB::table('attendances')->truncate();
                $results[] = "🗑️ Cleared {$existingCount} existing attendance records";
            }
            
            $totalCreated = 0;
            
            foreach ($employees as $employee) {
                $results[] = "<br><strong>Creating attendance for: {$employee->first_name} {$employee->last_name}</strong>";
                
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
                    
                    $totalCreated++;
                }
            }
            
            $results[] = "<br>✅ Sample attendance data created successfully!";
            $results[] = "📊 Created {$totalCreated} attendance records for {$employees->count()} employees";
            
            // Show summary
            $totalRecords = DB::table('attendances')->count();
            $todayRecords = DB::table('attendances')->whereDate('date', Carbon::today())->count();
            
            $results[] = "<br><strong>📈 Summary:</strong>";
            $results[] = "• Total attendance records: {$totalRecords}";
            $results[] = "• Today's attendance records: {$todayRecords}";
            $results[] = "<br>🎯 <strong>Now you can test the ESS Dashboard attendance logs!</strong>";
            $results[] = "Go to: <a href='/employee/login' target='_blank'>Employee Login</a>";
            
            return implode('<br>', $results);
            
        } catch (Exception $e) {
            return "❌ Error creating sample attendance data: " . $e->getMessage();
        }
    }
}
