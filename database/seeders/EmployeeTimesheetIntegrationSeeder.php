<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EmployeeTimesheetIntegrationSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // Clear existing data
        DB::table('time_entries')->delete();
        DB::table('claims')->delete();
        DB::table('claim_types')->delete();
        DB::table('leave_requests')->delete();
        DB::table('leave_types')->delete();
        DB::table('shifts')->delete();
        DB::table('shift_types')->delete();
        DB::table('employees')->delete();

        // Insert sample employees
        $employees = [
            [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => 'john.doe@jetlouge.com',
                'phone' => '+1-555-0101',
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
                'phone' => '+1-555-0102',
                'position' => 'HR Manager',
                'department' => 'Human Resources',
                'hire_date' => '2022-06-01',
                'salary' => 65000.00,
                'status' => 'active',
                'online_status' => 'offline',
                'last_activity' => now()->subHours(2),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'first_name' => 'Mike',
                'last_name' => 'Johnson',
                'email' => 'mike.johnson@jetlouge.com',
                'phone' => '+1-555-0103',
                'position' => 'Financial Analyst',
                'department' => 'Finance',
                'hire_date' => '2023-03-10',
                'salary' => 60000.00,
                'status' => 'active',
                'online_status' => 'online',
                'last_activity' => now()->subMinutes(15),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'first_name' => 'Sarah',
                'last_name' => 'Wilson',
                'email' => 'sarah.wilson@jetlouge.com',
                'phone' => '+1-555-0104',
                'position' => 'Marketing Specialist',
                'department' => 'Marketing',
                'hire_date' => '2023-08-20',
                'salary' => 55000.00,
                'status' => 'active',
                'online_status' => 'online',
                'last_activity' => now()->subMinutes(5),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'first_name' => 'David',
                'last_name' => 'Brown',
                'email' => 'david.brown@jetlouge.com',
                'phone' => '+1-555-0105',
                'position' => 'Operations Manager',
                'department' => 'Operations',
                'hire_date' => '2021-11-05',
                'salary' => 70000.00,
                'status' => 'inactive',
                'online_status' => 'offline',
                'last_activity' => now()->subDays(3),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'first_name' => 'Lisa',
                'last_name' => 'Garcia',
                'email' => 'lisa.garcia@jetlouge.com',
                'phone' => '+1-555-0106',
                'position' => 'Sales Representative',
                'department' => 'Sales',
                'hire_date' => '2024-01-02',
                'salary' => 50000.00,
                'status' => 'active',
                'online_status' => 'offline',
                'last_activity' => now()->subHours(1),
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        foreach ($employees as $employee) {
            DB::table('employees')->insert($employee);
        }

        // Insert shift types
        $shiftTypes = [
            [
                'name' => 'Morning Shift',
                'description' => 'Standard morning working hours',
                'default_start_time' => '08:00:00',
                'default_end_time' => '17:00:00',
                'hourly_rate' => 25.00,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Evening Shift',
                'description' => 'Evening working hours',
                'default_start_time' => '14:00:00',
                'default_end_time' => '23:00:00',
                'hourly_rate' => 28.00,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Night Shift',
                'description' => 'Overnight working hours',
                'default_start_time' => '22:00:00',
                'default_end_time' => '07:00:00',
                'hourly_rate' => 32.00,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        foreach ($shiftTypes as $shiftType) {
            DB::table('shift_types')->insert($shiftType);
        }

        // Insert time entries for today
        $timeEntries = [
            [
                'employee_id' => 1,
                'work_date' => today(),
                'clock_in_time' => '08:00:00',
                'clock_out_time' => '17:00:00',
                'hours_worked' => 8.0,
                'overtime_hours' => 0.0,
                'description' => 'Regular work day - software development',
                'status' => 'approved',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'employee_id' => 2,
                'work_date' => today(),
                'clock_in_time' => '09:00:00',
                'clock_out_time' => '18:00:00',
                'hours_worked' => 8.0,
                'overtime_hours' => 1.0,
                'description' => 'HR meetings and employee onboarding',
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'employee_id' => 3,
                'work_date' => today(),
                'clock_in_time' => '08:30:00',
                'clock_out_time' => null,
                'hours_worked' => 0.0,
                'overtime_hours' => 0.0,
                'description' => 'Financial analysis and reporting',
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'employee_id' => 4,
                'work_date' => today(),
                'clock_in_time' => '09:15:00',
                'clock_out_time' => null,
                'hours_worked' => 0.0,
                'overtime_hours' => 0.0,
                'description' => 'Marketing campaign development',
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        foreach ($timeEntries as $entry) {
            DB::table('time_entries')->insert($entry);
        }

        // Insert shifts for this week
        $shifts = [
            [
                'employee_id' => 1,
                'shift_type_id' => 1,
                'shift_date' => today(),
                'start_time' => '08:00:00',
                'end_time' => '17:00:00',
                'status' => 'completed',
                'notes' => 'Regular morning shift',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'employee_id' => 2,
                'shift_type_id' => 1,
                'shift_date' => today()->addDay(),
                'start_time' => '08:00:00',
                'end_time' => '17:00:00',
                'status' => 'scheduled',
                'notes' => 'Scheduled morning shift',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'employee_id' => 3,
                'shift_type_id' => 2,
                'shift_date' => today()->addDays(2),
                'start_time' => '14:00:00',
                'end_time' => '23:00:00',
                'status' => 'scheduled',
                'notes' => 'Evening shift coverage',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        foreach ($shifts as $shift) {
            DB::table('shifts')->insert($shift);
        }

        // Insert leave types
        $leaveTypes = [
            [
                'name' => 'Annual Leave',
                'description' => 'Yearly vacation days',
                'max_days_per_year' => 25,
                'requires_approval' => true,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Sick Leave',
                'description' => 'Medical leave for illness',
                'max_days_per_year' => 10,
                'requires_approval' => false,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Personal Leave',
                'description' => 'Personal time off',
                'max_days_per_year' => 5,
                'requires_approval' => true,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        foreach ($leaveTypes as $leaveType) {
            DB::table('leave_types')->insert($leaveType);
        }

        // Insert claim types
        $claimTypes = [
            [
                'name' => 'Travel Expenses',
                'description' => 'Business travel related expenses',
                'max_amount' => 1000.00,
                'requires_receipt' => true,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Office Supplies',
                'description' => 'Office equipment and supplies',
                'max_amount' => 500.00,
                'requires_receipt' => true,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Training & Development',
                'description' => 'Professional development courses',
                'max_amount' => 2000.00,
                'requires_receipt' => true,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        foreach ($claimTypes as $claimType) {
            DB::table('claim_types')->insert($claimType);
        }

        // Insert sample leave requests
        $leaveRequests = [
            [
                'employee_id' => 1,
                'leave_type_id' => 1,
                'start_date' => today()->addDays(10),
                'end_date' => today()->addDays(12),
                'days_requested' => 3,
                'reason' => 'Family vacation',
                'status' => 'pending',
                'manager_notes' => null,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'employee_id' => 2,
                'leave_type_id' => 2,
                'start_date' => today()->subDays(2),
                'end_date' => today()->subDays(1),
                'days_requested' => 2,
                'reason' => 'Medical appointment',
                'status' => 'approved',
                'manager_notes' => 'Approved for medical reasons',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        foreach ($leaveRequests as $request) {
            DB::table('leave_requests')->insert($request);
        }

        // Insert sample claims
        $claims = [
            [
                'employee_id' => 1,
                'claim_type_id' => 1,
                'amount' => 250.75,
                'expense_date' => today()->subDays(5),
                'description' => 'Client meeting travel expenses',
                'receipt_path' => null,
                'status' => 'pending',
                'manager_notes' => null,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'employee_id' => 3,
                'claim_type_id' => 2,
                'amount' => 89.99,
                'expense_date' => today()->subDays(3),
                'description' => 'Office supplies for department',
                'receipt_path' => null,
                'status' => 'approved',
                'manager_notes' => 'Approved - necessary supplies',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        foreach ($claims as $claim) {
            DB::table('claims')->insert($claim);
        }

        echo "Employee-Timesheet integration sample data seeded successfully!\n";
        echo "Created:\n";
        echo "- 6 employees (4 active, 1 inactive, 1 recent hire)\n";
        echo "- 4 time entries for today\n";
        echo "- 3 shift types and 3 scheduled shifts\n";
        echo "- 3 leave types and 2 leave requests\n";
        echo "- 3 claim types and 2 expense claims\n";
    }
}
