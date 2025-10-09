<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Attendance;

class AttendanceLogsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the first employee (or create test data for employee ID 1)
        $employeeId = 1; // Assuming super admin has employee ID 1
        
        // Clear existing attendance data for this employee (optional)
        // Attendance::where('employee_id', $employeeId)->delete();
        
        // Create attendance logs for the past 15 days
        $attendanceLogs = [];
        
        for ($i = 14; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            
            // Skip weekends (Saturday = 6, Sunday = 0)
            if ($date->dayOfWeek == 0 || $date->dayOfWeek == 6) {
                continue;
            }
            
            // Determine status and times based on day
            $clockInTime = null;
            $clockOutTime = null;
            $breakStartTime = null;
            $breakEndTime = null;
            $status = 'present';
            $totalHours = 0;
            $overtimeHours = 0;
            
            if ($i == 0) {
                // Today - only clock in (current session)
                $clockInTime = $date->setTime(8, 30, 0);
                $status = 'present';
            } elseif ($i == 1) {
                // Yesterday - late arrival
                $clockInTime = $date->setTime(9, 15, 0);
                $clockOutTime = $date->setTime(18, 30, 0);
                $breakStartTime = $date->setTime(12, 0, 0);
                $breakEndTime = $date->setTime(13, 0, 0);
                $status = 'late';
                $totalHours = 8.25;
                $overtimeHours = 0.25;
            } elseif ($i == 2) {
                // Day before yesterday - normal day with overtime
                $clockInTime = $date->setTime(8, 0, 0);
                $clockOutTime = $date->setTime(19, 0, 0);
                $breakStartTime = $date->setTime(12, 0, 0);
                $breakEndTime = $date->setTime(13, 0, 0);
                $status = 'present';
                $totalHours = 10.0;
                $overtimeHours = 2.0;
            } elseif ($i == 5) {
                // Absent day
                $status = 'absent';
            } else {
                // Regular working days
                $clockInHour = rand(8, 9);
                $clockInMinute = rand(0, 30);
                $clockInTime = $date->setTime($clockInHour, $clockInMinute, 0);
                
                $clockOutHour = rand(17, 18);
                $clockOutMinute = rand(0, 59);
                $clockOutTime = $date->setTime($clockOutHour, $clockOutMinute, 0);
                
                // Add lunch break
                $breakStartTime = $date->copy()->setTime(12, 0, 0);
                $breakEndTime = $date->copy()->setTime(13, 0, 0);
                
                $status = ($clockInHour > 8 || ($clockInHour == 8 && $clockInMinute > 30)) ? 'late' : 'present';
                
                // Calculate hours (excluding 1 hour lunch break)
                $workMinutes = $clockOutTime->diffInMinutes($clockInTime) - 60; // Subtract lunch break
                $totalHours = round($workMinutes / 60, 2);
                $overtimeHours = max(0, $totalHours - 8);
            }
            
            $attendanceLogs[] = [
                'employee_id' => $employeeId,
                'date' => $date->format('Y-m-d'),
                'clock_in_time' => $clockInTime,
                'clock_out_time' => $clockOutTime,
                'break_start_time' => $breakStartTime,
                'break_end_time' => $breakEndTime,
                'total_hours' => $totalHours,
                'overtime_hours' => $overtimeHours,
                'status' => $status,
                'location' => 'Office',
                'ip_address' => '192.168.1.' . rand(100, 200),
                'notes' => $this->getRandomNote($status),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
        // Insert the attendance logs
        DB::table('attendances')->insert($attendanceLogs);
        
        $this->command->info('Created ' . count($attendanceLogs) . ' attendance log entries for employee ID: ' . $employeeId);
    }
    
    /**
     * Get random notes based on status
     */
    private function getRandomNote($status): ?string
    {
        $notes = [
            'present' => [
                'Regular work day',
                'Productive day at the office',
                'Completed all assigned tasks',
                null, // Some days have no notes
            ],
            'late' => [
                'Traffic delay',
                'Medical appointment in the morning',
                'Family emergency',
                'Public transport delay',
            ],
            'absent' => [
                'Sick leave',
                'Personal leave',
                'Family emergency',
                'Medical appointment',
            ],
        ];
        
        $statusNotes = $notes[$status] ?? [null];
        return $statusNotes[array_rand($statusNotes)];
    }
}
