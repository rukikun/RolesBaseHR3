<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CurrentWeekAttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing attendance data for current week
        $weekStart = Carbon::now()->startOfWeek();
        $weekEnd = Carbon::now()->endOfWeek();
        
        DB::table('attendances')
            ->whereBetween('date', [$weekStart->format('Y-m-d'), $weekEnd->format('Y-m-d')])
            ->delete();
        
        // Get some employee IDs
        $employees = DB::table('employees')->limit(5)->pluck('id');
        
        if ($employees->isEmpty()) {
            $this->command->info('No employees found. Please run employee seeder first.');
            return;
        }
        
        $attendanceData = [];
        
        foreach ($employees as $employeeId) {
            // Add attendance for Monday, Tuesday, and Friday only (to test mixed data)
            $workDays = [0, 1, 4]; // Monday, Tuesday, Friday
            
            foreach ($workDays as $dayOffset) {
                $date = $weekStart->copy()->addDays($dayOffset);
                
                // Generate realistic times
                $clockIn = $date->copy()->setTime(8, rand(0, 30)); // 8:00-8:30 AM
                $clockOut = $date->copy()->setTime(17, rand(0, 59)); // 5:00-5:59 PM
                
                // Add some variation for Friday
                if ($dayOffset === 4) {
                    $clockIn = $date->copy()->setTime(7, rand(0, 30)); // Earlier on Friday
                    $clockOut = $date->copy()->setTime(19, rand(0, 59)); // Later on Friday
                }
                
                // Calculate total hours worked (in decimal format)
                $totalMinutes = $clockOut->diffInMinutes($clockIn);
                $totalHours = round($totalMinutes / 60, 2);
                
                // Calculate overtime hours (anything over 8 hours)
                $overtimeHours = max(0, round($totalHours - 8, 2));
                
                $attendanceData[] = [
                    'employee_id' => $employeeId,
                    'date' => $date->format('Y-m-d'),
                    'clock_in_time' => $clockIn->format('H:i:s'),
                    'clock_out_time' => $clockOut->format('H:i:s'),
                    'total_hours' => $totalHours,
                    'overtime_hours' => $overtimeHours,
                    'status' => 'present',
                    'notes' => 'Regular work day',
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }
        }
        
        DB::table('attendances')->insert($attendanceData);
        
        $this->command->info('Current week attendance data seeded successfully!');
        $this->command->info('Added ' . count($attendanceData) . ' attendance records');
        $this->command->info('Week range: ' . $weekStart->format('Y-m-d') . ' to ' . $weekEnd->format('Y-m-d'));
    }
}
