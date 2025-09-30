<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class UnifiedHRSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Disable foreign key checks temporarily
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Clear existing data in proper order
        DB::table('claims')->truncate();
        DB::table('leave_requests')->truncate();
        DB::table('shifts')->truncate();
        DB::table('time_entries')->truncate();
        DB::table('leave_balances')->truncate();
        DB::table('shift_requests')->truncate();
        DB::table('employees')->truncate();
        DB::table('claim_types')->truncate();
        DB::table('leave_types')->truncate();
        DB::table('shift_types')->truncate();
        
        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Insert employees
        $employees = [
            [
                'id' => 1,
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => 'john.doe@jetlouge.com',
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
                'id' => 2,
                'first_name' => 'Jane',
                'last_name' => 'Smith',
                'email' => 'jane.smith@jetlouge.com',
                'phone' => '+1234567891',
                'position' => 'HR Manager',
                'department' => 'Human Resources',
                'hire_date' => '2022-03-20',
                'salary' => 65000.00,
                'status' => 'active',
                'online_status' => 'offline',
                'last_activity' => now()->subHours(2),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 3,
                'first_name' => 'Mike',
                'last_name' => 'Johnson',
                'email' => 'mike.johnson@jetlouge.com',
                'phone' => '+1234567892',
                'position' => 'Travel Consultant',
                'department' => 'Sales',
                'hire_date' => '2023-06-10',
                'salary' => 55000.00,
                'status' => 'active',
                'online_status' => 'online',
                'last_activity' => now()->subMinutes(15),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 4,
                'first_name' => 'Sarah',
                'last_name' => 'Wilson',
                'email' => 'sarah.wilson@jetlouge.com',
                'phone' => '+1234567893',
                'position' => 'Marketing Specialist',
                'department' => 'Marketing',
                'hire_date' => '2023-02-28',
                'salary' => 60000.00,
                'status' => 'active',
                'online_status' => 'offline',
                'last_activity' => now()->subHours(1),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 5,
                'first_name' => 'David',
                'last_name' => 'Brown',
                'email' => 'david.brown@jetlouge.com',
                'phone' => '+1234567894',
                'position' => 'Finance Manager',
                'department' => 'Finance',
                'hire_date' => '2022-11-15',
                'salary' => 70000.00,
                'status' => 'active',
                'online_status' => 'online',
                'last_activity' => now()->subMinutes(5),
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        DB::table('employees')->insert($employees);

        // Insert shift types
        $shiftTypes = [
            [
                'id' => 1,
                'name' => 'Morning Shift',
                'start_time' => '08:00:00',
                'end_time' => '16:00:00',
                'description' => 'Standard morning shift',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 2,
                'name' => 'Afternoon Shift',
                'start_time' => '14:00:00',
                'end_time' => '22:00:00',
                'description' => 'Afternoon to evening shift',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 3,
                'name' => 'Night Shift',
                'start_time' => '22:00:00',
                'end_time' => '06:00:00',
                'description' => 'Overnight shift',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        DB::table('shift_types')->insert($shiftTypes);

        // Insert leave types
        $leaveTypes = [
            [
                'id' => 1,
                'name' => 'Annual Leave',
                'description' => 'Yearly vacation days',
                'max_days_per_year' => 21,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 2,
                'name' => 'Sick Leave',
                'description' => 'Medical leave for illness',
                'max_days_per_year' => 10,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 3,
                'name' => 'Personal Leave',
                'description' => 'Personal time off',
                'max_days_per_year' => 5,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        DB::table('leave_types')->insert($leaveTypes);

        // Insert claim types
        $claimTypes = [
            [
                'id' => 1,
                'name' => 'Travel Expenses',
                'description' => 'Business travel related expenses',
                'max_amount' => 2000.00,
                'requires_receipt' => true,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 2,
                'name' => 'Meal Allowance',
                'description' => 'Business meal expenses',
                'max_amount' => 100.00,
                'requires_receipt' => true,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 3,
                'name' => 'Office Supplies',
                'description' => 'Work-related office supplies',
                'max_amount' => 500.00,
                'requires_receipt' => true,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        DB::table('claim_types')->insert($claimTypes);

        // Insert time entries
        $timeEntries = [];
        for ($i = 1; $i <= 15; $i++) {
            $timeEntries[] = [
                'employee_id' => rand(1, 5),
                'work_date' => Carbon::now()->subDays(rand(0, 30))->format('Y-m-d'),
                'hours_worked' => rand(6, 9) + (rand(0, 1) * 0.5),
                'overtime_hours' => rand(0, 3),
                'description' => 'Daily work activities',
                'status' => ['pending', 'approved', 'rejected'][rand(0, 2)],
                'clock_in' => Carbon::now()->subDays(rand(0, 30))->setHour(8)->setMinute(rand(0, 59)),
                'clock_out' => Carbon::now()->subDays(rand(0, 30))->setHour(17)->setMinute(rand(0, 59)),
                'created_at' => now(),
                'updated_at' => now()
            ];
        }

        DB::table('time_entries')->insert($timeEntries);

        // Insert shifts
        $shifts = [];
        for ($i = 1; $i <= 10; $i++) {
            $shiftTypeId = rand(1, 3);
            $shiftType = $shiftTypes[$shiftTypeId - 1];
            
            $shifts[] = [
                'employee_id' => rand(1, 5),
                'shift_type_id' => $shiftTypeId,
                'shift_date' => Carbon::now()->addDays(rand(-5, 15))->format('Y-m-d'),
                'start_time' => $shiftType['start_time'],
                'end_time' => $shiftType['end_time'],
                'status' => ['scheduled', 'completed', 'cancelled'][rand(0, 2)],
                'notes' => 'Regular shift assignment',
                'created_at' => now(),
                'updated_at' => now()
            ];
        }

        DB::table('shifts')->insert($shifts);

        // Insert leave requests
        $leaveRequests = [];
        for ($i = 1; $i <= 8; $i++) {
            $startDate = Carbon::now()->addDays(rand(1, 60));
            $leaveRequests[] = [
                'employee_id' => rand(1, 5),
                'leave_type_id' => rand(1, 3),
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $startDate->addDays(rand(1, 5))->format('Y-m-d'),
                'reason' => 'Personal time off request',
                'status' => ['pending', 'approved', 'rejected'][rand(0, 2)],
                'admin_notes' => null,
                'created_at' => now(),
                'updated_at' => now()
            ];
        }

        DB::table('leave_requests')->insert($leaveRequests);

        // Insert leave balances
        $leaveBalances = [];
        foreach ($employees as $employee) {
            foreach ($leaveTypes as $leaveType) {
                $leaveBalances[] = [
                    'employee_id' => $employee['id'],
                    'leave_type_id' => $leaveType['id'],
                    'allocated_days' => $leaveType['max_days_per_year'],
                    'used_days' => rand(0, $leaveType['max_days_per_year'] / 2),
                    'year' => date('Y'),
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }
        }

        DB::table('leave_balances')->insert($leaveBalances);

        // Insert claims
        $claims = [];
        for ($i = 1; $i <= 12; $i++) {
            $claims[] = [
                'employee_id' => rand(1, 5),
                'claim_type_id' => rand(1, 3),
                'amount' => rand(50, 500) + (rand(0, 99) / 100),
                'claim_date' => Carbon::now()->subDays(rand(0, 30))->format('Y-m-d'),
                'description' => 'Business expense claim',
                'receipt_path' => null,
                'status' => ['pending', 'approved', 'rejected', 'paid'][rand(0, 3)],
                'admin_notes' => null,
                'created_at' => now(),
                'updated_at' => now()
            ];
        }

        DB::table('claims')->insert($claims);
    }
}
