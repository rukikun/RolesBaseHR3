<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AddSampleAttendanceData extends Command
{
    protected $signature = 'attendance:add-sample {employeeId=1}';
    protected $description = 'Add sample attendance data for testing';

    public function handle()
    {
        $employeeId = $this->argument('employeeId');
        
        try {
            // Check if employee exists
            $employee = DB::table('employees')->where('id', $employeeId)->first();
            if (!$employee) {
                $availableEmployees = DB::table('employees')->pluck('id')->implode(', ');
                $this->error("Employee ID {$employeeId} not found. Available employees: {$availableEmployees}");
                return 1;
            }
            
            // Clear existing attendance for this employee
            $deleted = DB::table('attendances')->where('employee_id', $employeeId)->delete();
            if ($deleted > 0) {
                $this->info("Cleared {$deleted} existing attendance records for employee {$employeeId}");
            }
            
            $attendanceData = [];
            
            // Add sample data for the past 10 days
            for ($i = 9; $i >= 0; $i--) {
                $date = Carbon::now()->subDays($i);
                
                // Skip weekends
                if ($date->dayOfWeek == 0 || $date->dayOfWeek == 6) {
                    continue;
                }
                
                if ($i == 0) {
                    // Today - only clock in
                    $attendanceData[] = [
                        'employee_id' => $employeeId,
                        'date' => $date->format('Y-m-d'),
                        'clock_in_time' => $date->setTime(8, 30, 0),
                        'clock_out_time' => null,
                        'status' => 'present',
                        'location' => 'Office',
                        'ip_address' => '192.168.1.100',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                } elseif ($i == 1) {
                    // Yesterday - late with overtime
                    $clockIn = $date->setTime(9, 15, 0);
                    $clockOut = $date->setTime(18, 30, 0);
                    $attendanceData[] = [
                        'employee_id' => $employeeId,
                        'date' => $date->format('Y-m-d'),
                        'clock_in_time' => $clockIn,
                        'clock_out_time' => $clockOut,
                        'break_start_time' => $date->copy()->setTime(12, 0, 0),
                        'break_end_time' => $date->copy()->setTime(13, 0, 0),
                        'total_hours' => 8.25,
                        'overtime_hours' => 0.25,
                        'status' => 'late',
                        'location' => 'Office',
                        'ip_address' => '192.168.1.101',
                        'notes' => 'Traffic delay',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                } elseif ($i == 3) {
                    // Absent day
                    $attendanceData[] = [
                        'employee_id' => $employeeId,
                        'date' => $date->format('Y-m-d'),
                        'clock_in_time' => null,
                        'clock_out_time' => null,
                        'status' => 'absent',
                        'location' => null,
                        'ip_address' => null,
                        'notes' => 'Sick leave',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                } elseif ($i == 5) {
                    // Another day with overtime
                    $clockIn = $date->setTime(8, 0, 0);
                    $clockOut = $date->setTime(19, 30, 0);
                    $attendanceData[] = [
                        'employee_id' => $employeeId,
                        'date' => $date->format('Y-m-d'),
                        'clock_in_time' => $clockIn,
                        'clock_out_time' => $clockOut,
                        'break_start_time' => $date->copy()->setTime(12, 0, 0),
                        'break_end_time' => $date->copy()->setTime(13, 0, 0),
                        'total_hours' => 10.5,
                        'overtime_hours' => 2.5,
                        'status' => 'present',
                        'location' => 'Office',
                        'ip_address' => '192.168.1.102',
                        'notes' => 'Project deadline',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                } else {
                    // Regular working days
                    $clockInHour = rand(8, 9);
                    $clockInMinute = rand(0, 30);
                    $clockIn = $date->setTime($clockInHour, $clockInMinute, 0);
                    $clockOut = $date->setTime(rand(17, 18), rand(0, 59), 0);
                    $totalHours = round($clockOut->diffInMinutes($clockIn) / 60 - 1, 2); // Subtract 1 hour lunch
                    
                    $attendanceData[] = [
                        'employee_id' => $employeeId,
                        'date' => $date->format('Y-m-d'),
                        'clock_in_time' => $clockIn,
                        'clock_out_time' => $clockOut,
                        'break_start_time' => $date->copy()->setTime(12, 0, 0),
                        'break_end_time' => $date->copy()->setTime(13, 0, 0),
                        'total_hours' => $totalHours,
                        'overtime_hours' => max(0, $totalHours - 8),
                        'status' => ($clockInHour > 8 || ($clockInHour == 8 && $clockInMinute > 30)) ? 'late' : 'present',
                        'location' => 'Office',
                        'ip_address' => '192.168.1.' . rand(100, 200),
                        'notes' => rand(1, 4) == 1 ? 'Productive day' : null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
            
            // Insert the data
            DB::table('attendances')->insert($attendanceData);
            
            $this->info("✅ Successfully added " . count($attendanceData) . " sample attendance records for employee: {$employee->first_name} {$employee->last_name} (ID: {$employeeId})");
            
            // Show summary
            $this->table(
                ['Date', 'Clock In', 'Clock Out', 'Status', 'Total Hours'],
                collect($attendanceData)->map(function ($record) {
                    return [
                        $record['date'],
                        $record['clock_in_time'] ? Carbon::parse($record['clock_in_time'])->format('H:i') : '--',
                        $record['clock_out_time'] ? Carbon::parse($record['clock_out_time'])->format('H:i') : '--',
                        ucfirst($record['status']),
                        $record['total_hours'] ?? '--'
                    ];
                })->toArray()
            );
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error("Failed to add sample data: " . $e->getMessage());
            return 1;
        }
    }
}
