<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing data (with error handling)
        try {
            DB::table('time_entries')->truncate();
        } catch (\Exception $e) {
            // Table might not exist, continue
        }
        
        try {
            DB::table('shift_types')->truncate();
        } catch (\Exception $e) {
            // Table might not exist, continue
        }
        
        try {
            DB::table('shifts')->truncate();
        } catch (\Exception $e) {
            // Table might not exist, continue
        }
        
        try {
            DB::table('leave_requests')->truncate();
        } catch (\Exception $e) {
            // Table might not exist, continue
        }
        
        try {
            DB::table('leave_types')->truncate();
        } catch (\Exception $e) {
            // Table might not exist, continue
        }
        
        // Ensure we have some employees
        $this->seedEmployees();
        
        // Seed shift types
        $this->seedShiftTypes();
        
        // Seed leave types
        $this->seedLeaveTypes();
        
        // Seed time entries
        $this->seedTimeEntries();
        
        // Seed shifts
        $this->seedShifts();
        
        // Seed leave requests
        $this->seedLeaveRequests();
    }
    
    private function seedEmployees()
    {
        $employees = [
            [
                'first_name' => 'John',
                'last_name' => 'Anderson',
                'email' => 'john.anderson@jetlouge.com',
                'phone' => '+1234567890',
                'position' => 'Software Developer',
                'department' => 'IT',
                'hire_date' => '2023-01-15',
                'salary' => 75000.00,
                'status' => 'active',
                'online_status' => 'online',
                'last_activity' => now(),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'first_name' => 'Jane',
                'last_name' => 'Smith',
                'email' => 'jane.smith@jetlouge.com',
                'phone' => '+1234567891',
                'position' => 'HR Manager',
                'department' => 'Human Resources',
                'hire_date' => '2022-06-10',
                'salary' => 65000.00,
                'status' => 'active',
                'online_status' => 'online',
                'last_activity' => now(),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'first_name' => 'Mike',
                'last_name' => 'Johnson',
                'email' => 'mike.johnson@jetlouge.com',
                'phone' => '+1234567892',
                'position' => 'Travel Consultant',
                'department' => 'Operations',
                'hire_date' => '2023-03-20',
                'salary' => 55000.00,
                'status' => 'active',
                'online_status' => 'offline',
                'last_activity' => now()->subHours(2),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'first_name' => 'Sarah',
                'last_name' => 'Wilson',
                'email' => 'sarah.wilson@jetlouge.com',
                'phone' => '+1234567893',
                'position' => 'Marketing Specialist',
                'department' => 'Marketing',
                'hire_date' => '2023-08-05',
                'salary' => 60000.00,
                'status' => 'active',
                'online_status' => 'online',
                'last_activity' => now(),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'first_name' => 'David',
                'last_name' => 'Brown',
                'email' => 'david.brown@jetlouge.com',
                'phone' => '+1234567894',
                'position' => 'Accountant',
                'department' => 'Finance',
                'hire_date' => '2022-11-12',
                'salary' => 58000.00,
                'status' => 'active',
                'online_status' => 'online',
                'last_activity' => now(),
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];
        
        foreach ($employees as $employee) {
            DB::table('employees')->updateOrInsert(
                ['email' => $employee['email']],
                $employee
            );
        }
    }
    
    private function seedShiftTypes()
    {
        $shiftTypes = [
            [
                'name' => 'Morning Shift',
                'start_time' => '08:00:00',
                'end_time' => '16:00:00',
                'description' => 'Standard morning shift',
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Afternoon Shift',
                'start_time' => '14:00:00',
                'end_time' => '22:00:00',
                'description' => 'Afternoon to evening shift',
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Night Shift',
                'start_time' => '22:00:00',
                'end_time' => '06:00:00',
                'description' => 'Night shift for 24/7 operations',
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];
        
        try {
            DB::table('shift_types')->insert($shiftTypes);
        } catch (\Exception $e) {
            // Table might not exist or have different structure
            echo "Warning: Could not seed shift_types table: " . $e->getMessage() . "\n";
        }
    }
    
    private function seedLeaveTypes()
    {
        $leaveTypes = [
            [
                'name' => 'Annual Leave',
                'description' => 'Yearly vacation leave',
                'days_per_year' => 21,
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Sick Leave',
                'description' => 'Medical leave',
                'days_per_year' => 10,
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Personal Leave',
                'description' => 'Personal time off',
                'days_per_year' => 5,
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];
        
        try {
            DB::table('leave_types')->insert($leaveTypes);
        } catch (\Exception $e) {
            // Table might not exist or have different structure
            echo "Warning: Could not seed leave_types table: " . $e->getMessage() . "\n";
        }
    }
    
    private function seedTimeEntries()
    {
        $employees = DB::table('employees')->where('status', 'active')->get();
        
        foreach ($employees as $employee) {
            // Create time entries for the last 7 days
            for ($i = 0; $i < 7; $i++) {
                $date = now()->subDays($i);
                
                // Skip weekends for some variety
                if ($date->isWeekend() && rand(0, 1)) {
                    continue;
                }
                
                $clockIn = $date->copy()->setTime(8 + rand(0, 2), rand(0, 59), 0);
                $clockOut = $clockIn->copy()->addHours(8 + rand(0, 2))->addMinutes(rand(0, 59));
                
                try {
                    DB::table('time_entries')->insert([
                    'employee_id' => $employee->id,
                    'work_date' => $date->format('Y-m-d'),
                    'clock_in_time' => $clockIn->format('H:i:s'),
                    'clock_out_time' => $clockOut->format('H:i:s'),
                    'hours_worked' => $clockOut->diffInHours($clockIn),
                    'overtime_hours' => max(0, $clockOut->diffInHours($clockIn) - 8),
                    'status' => ['pending', 'approved', 'approved'][rand(0, 2)],
                    'notes' => 'Regular work day',
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                } catch (\Exception $e) {
                    // Skip this entry if there's an error
                    continue;
                }
            }
        }
    }
    
    private function seedShifts()
    {
        $employees = DB::table('employees')->where('status', 'active')->get();
        $shiftTypes = DB::table('shift_types')->get();
        
        if ($shiftTypes->isEmpty()) {
            echo "Warning: No shift types found, skipping shift assignments\n";
            return;
        }
        
        // Distribute employees across different shifts for better variety
        $employeeChunks = $employees->chunk(ceil($employees->count() / $shiftTypes->count()));
        
        foreach ($shiftTypes as $index => $shiftType) {
            $assignedEmployees = $employeeChunks->get($index, collect());
            
            foreach ($assignedEmployees as $employee) {
                try {
                    // Assign for today
                    DB::table('shifts')->insert([
                        'employee_id' => $employee->id,
                        'shift_type_id' => $shiftType->id,
                        'shift_date' => now()->format('Y-m-d'),
                        'start_time' => $shiftType->start_time,
                        'end_time' => $shiftType->end_time,
                        'status' => 'scheduled',
                        'notes' => 'Regular shift assignment',
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                    
                    // Also assign for tomorrow for continuity
                    DB::table('shifts')->insert([
                        'employee_id' => $employee->id,
                        'shift_type_id' => $shiftType->id,
                        'shift_date' => now()->addDay()->format('Y-m-d'),
                        'start_time' => $shiftType->start_time,
                        'end_time' => $shiftType->end_time,
                        'status' => 'scheduled',
                        'notes' => 'Regular shift assignment',
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                } catch (\Exception $e) {
                    // Skip this entry if there's an error
                    continue;
                }
            }
        }
    }
    
    private function seedLeaveRequests()
    {
        $employees = DB::table('employees')->where('status', 'active')->get();
        $leaveTypes = DB::table('leave_types')->get();
        
        foreach ($employees->take(3) as $employee) {
            $leaveType = $leaveTypes->random();
            $startDate = now()->addDays(rand(5, 30));
            $endDate = $startDate->copy()->addDays(rand(1, 5));
            
            try {
                DB::table('leave_requests')->insert([
                    'employee_id' => $employee->id,
                    'leave_type_id' => $leaveType->id,
                    'start_date' => $startDate->format('Y-m-d'),
                    'end_date' => $endDate->format('Y-m-d'),
                    'days_requested' => $startDate->diffInDays($endDate) + 1,
                    'reason' => 'Personal time off request',
                    'status' => 'pending',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            } catch (\Exception $e) {
                // Skip this entry if there's an error
                continue;
            }
        }
    }
}
