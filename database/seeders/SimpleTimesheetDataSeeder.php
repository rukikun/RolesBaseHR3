<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SimpleTimesheetDataSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // Get existing employee IDs
        $employees = DB::table('employees')->pluck('id')->toArray();
        
        if (empty($employees)) {
            echo "No employees found. Please ensure employees exist in the database first.\n";
            return;
        }

        // Clear existing time entries to avoid conflicts
        DB::table('time_entries')->truncate();

        // Insert time entries for existing employees
        $timeEntries = [];
        
        // Add time entries for today for some employees
        foreach (array_slice($employees, 0, 4) as $index => $employeeId) {
            $timeEntries[] = [
                'employee_id' => $employeeId,
                'work_date' => today()->format('Y-m-d'),
                'clock_in_time' => '08:' . str_pad($index * 15, 2, '0', STR_PAD_LEFT) . ':00',
                'clock_out_time' => $index < 2 ? '17:00:00' : null, // Some still clocked in
                'hours_worked' => $index < 2 ? 8.0 : 0.0,
                'overtime_hours' => $index == 1 ? 1.0 : 0.0,
                'description' => 'Daily work - Employee ' . $employeeId,
                'status' => $index < 2 ? 'approved' : 'pending',
                'created_at' => now(),
                'updated_at' => now()
            ];
        }

        // Add some historical entries
        foreach (array_slice($employees, 0, 3) as $index => $employeeId) {
            $timeEntries[] = [
                'employee_id' => $employeeId,
                'work_date' => today()->subDays(1)->format('Y-m-d'),
                'clock_in_time' => '08:00:00',
                'clock_out_time' => '17:00:00',
                'hours_worked' => 8.0,
                'overtime_hours' => 0.0,
                'description' => 'Previous day work - Employee ' . $employeeId,
                'status' => 'approved',
                'created_at' => now(),
                'updated_at' => now()
            ];
        }

        // Insert time entries
        foreach ($timeEntries as $entry) {
            try {
                DB::table('time_entries')->insert($entry);
            } catch (\Exception $e) {
                echo "Error inserting time entry for employee {$entry['employee_id']}: " . $e->getMessage() . "\n";
            }
        }

        // Update employee online status for some employees
        try {
            DB::table('employees')
                ->whereIn('id', array_slice($employees, 0, 3))
                ->update([
                    'online_status' => 'online',
                    'last_activity' => now()
                ]);
        } catch (\Exception $e) {
            echo "Note: Could not update online status (column may not exist): " . $e->getMessage() . "\n";
        }

        $insertedCount = DB::table('time_entries')->count();
        echo "Successfully seeded timesheet integration data!\n";
        echo "- Time entries created: {$insertedCount}\n";
        echo "- Employees with timesheets today: " . DB::table('time_entries')->whereDate('work_date', today())->distinct('employee_id')->count() . "\n";
        echo "- Total employees: " . count($employees) . "\n";
    }
}
